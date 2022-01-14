<?php

namespace Lamplight\RecordSet;

use Lamplight\Client;
use Lamplight\Record\BaseRecord;
use Lamplight\RecordSet;

/**
 * Makes a RecordSet from the last request made by the Client
 *
 * Used by RecordSet::factory - that's how you'd usually use this class, rather than calling it directly.
 */
class Factory {

    /**
     * Base record class, used when constructing Records from the data
     * @var String
     */
    protected string $baseRecordClassName = '\Lamplight\Record';


    /**
     * @param Client $client
     * @param string $recordClass
     * @return RecordSet
     */
    public function makeRecordSetFromData (Client $client, string $recordClass = '') : RecordSet {

        $action = $client->getLastLamplightMethod();
        $method = $client->getLastLamplightAction();
        $resp = $client->getLastResponse();
        $format = $client->getResponseFormat();
        $records = array();
        $errors = false;

        $status = $resp->getStatus();

        // Check we've got a response OK:
        if ($resp && !$resp->isError() && $status == 200) {

            // Work out what kind of object to fill with
            if ($recordClass == '') {
                $recordClass = $this->_buildRecordClassName($action, $method);
            }
            try {
                $parsed_data = $this->_parseResponseBody($resp->getBody()->getContents(), $format);
            } catch (\Error $parse_data_error) {
                $errors = true;
            }

            if (is_array($parsed_data) && array_key_exists('data', $parsed_data)) {

                if (is_object($parsed_data['data'])) {
                    $parsed_data->data = array($parsed_data->data);
                }

                if (is_array($parsed_data['data'])) {
                    foreach ($parsed_data['data'] as $rec) {
                        /** @var BaseRecord $newRec */
                        $newRec = new $recordClass($rec);
                        $newRec->init($client);
                        $records[$newRec->get('id')] = $newRec;
                    }
                }
            }


        } else {
            // error state
            $errors = true;

        }

        // Construct the RecordSet:
        $rs = new RecordSet($records);
        $rs->setErrors($errors);
        $rs->setResponseStatus($status);


        // Set error state:
        if ($errors) {
            // try and parse error message
            $parsed_data = $this->_parseResponseBody($resp->getBody(), $format);
            if ($parsed_data !== false) {
                if (is_object($parsed_data) && property_exists($parsed_data, 'error')) {
                    $rs->setErrorCode($parsed_data->error);
                    $rs->setErrorMessage($parsed_data->msg);
                } else {
                    $rs->setErrorCode(1101);
                    $rs->setErrorMessage("The response from the server was an error, "
                        . "we parsed it as json OK, but it doesn't have the expected"
                        . " error code and message.");
                }
            } else {
                $rs->setErrorCode(1100);
                $rs->setErrorMessage("Could not parse response body as json");
            }
        }


        return $rs;
    }



    /**
     * Builds the class names used for each Record.  May be over-written
     * by implementations to customise Record classes.
     * @param String $action The 'action' (kind of data - work, workarea, people, orgs)
     * @method String $method   The 'method' (one|some|all)
     * @return String
     */
    protected  function _buildRecordClassName ($action, $method) {

        $class = $this->baseRecordClassName . '\\' . ucfirst($method);
        if ($action != 'one') {
            $class .= 'Summary';
        }
        return $class;
    }


    /**
     * Takes response body and decodes and parses it.
     * Will get an array-like
     *  object that can be used to contruct records
     * @param String $data Data returned in response body
     * @param String $format Default 'json'.  Others may be added in future.
     * @return Object
     */
    protected  function _parseResponseBody ($data, $format = 'json') {
        // only json supported
        return json_decode($data, true);
    }

}
