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
        $response = $client->getLastResponse();

        if (!$response) {
            throw new \Exception("No request has been made");
        }

        $records = array();
        $errors = false;

        $status = $response->getStatus();

        // Check we've got a response OK:
        if ($response && !$response->isError()) {

            // Work out what kind of object to fill with
            if ($recordClass == '') {
                $recordClass = $this->_buildRecordClassName($action, $method);
            }
            $parsed_data = null;
            try {
                $parsed_data = $this->_parseResponseBody($response->getBody()->getContents());
            } catch (\Error $parse_data_error) {
                $errors = true;
            }
            if ($parsed_data === null) {
                $errors = true;
            }

            if (is_array($parsed_data) && array_key_exists('data', $parsed_data)) {

                if (is_object($parsed_data['data'])) {
                    $parsed_data['data'] = array($parsed_data['data']);
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
        $record_set = new RecordSet($records);
        $record_set->setErrors($errors);
        $record_set->setResponseStatus($status);


        // Set error state:
        if ($errors) {
            // try and parse error message
            $parsed_data = $this->_parseResponseBody($response->getBody()->getContents());
            if ($parsed_data) {
                if (is_array($parsed_data) && array_key_exists('error', $parsed_data)) {
                    $record_set->setErrorCode($parsed_data['error']);
                    $record_set->setErrorMessage($parsed_data['msg']);
                } else {
                    $record_set->setErrorCode(1101);
                    $record_set->setErrorMessage("The response from the server was an error, "
                        . "we parsed it as json OK, but it doesn't have the expected"
                        . " error code and message.");
                }
            } else {
                $record_set->setErrorCode(1100);
                $record_set->setErrorMessage("Could not parse response body as json");
            }
        }


        return $record_set;
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
     * @return array
     */
    protected  function _parseResponseBody ($data, $format = 'json') : ?array {
        // only json supported
        return json_decode($data, true, JSON_THROW_ON_ERROR);
    }

}
