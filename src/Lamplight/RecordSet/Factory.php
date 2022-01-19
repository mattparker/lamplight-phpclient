<?php

namespace Lamplight\RecordSet;

use Lamplight\Client;
use Lamplight\Record\BaseRecord;
use Lamplight\RecordSet;
use Lamplight\Response;
use Lamplight\Response\SuccessResponse;

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
     * @throws NoRequestMadeToMakeRecordsFromException
     */
    public function makeRecordSetFromData (Client $client, string $recordClass = '') : RecordSet {

        $response = $client->getLastResponse();

        if (!$response) {
            throw new NoRequestMadeToMakeRecordsFromException("No request has been made");
        }

        $records = array();
        $errors = false;

        // Check we've got a response OK:
        if ($response && !$response->isError()) {

            try {
                $records = $this->parseResponseToRecords($client, $response, $recordClass);
            } catch (ParseReturnedDataException $parse_error) {
                $errors = true;
            }

        } else {
            // error state
            $errors = true;

        }

        // Construct the RecordSet:
        $record_set = new RecordSet($records);
        $record_set->setErrors($errors);
        $record_set->setResponseStatus($response->getStatus());


        // Set error state:
        if ($errors) {
            $this->parseErrorFromResponse($response, $record_set);
        }


        return $record_set;
    }


    /**
     * Builds the class names used for each Record.  May be over-written
     * by implementations to customise Record classes.
     * @param string $action The 'action' (kind of data - work, workarea, people, orgs)
     * @param string $method
     * @return string
     * @method string $method   The 'method' (one|some|all)
     */
    protected function _buildRecordClassName (string $action, string $method) : string {

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
    protected  function _parseResponseBody ($data, $format = 'json') {
        // only json supported
        return json_decode($data, false, JSON_THROW_ON_ERROR);
    }

    /**
     * @param Client $client
     * @param Response $response
     * @param string $recordClass
     *
     * @return array
     * @throws ParseReturnedDataException
     */
    protected function parseResponseToRecords (Client $client, Response $response, string $recordClass): array {


        // Work out what kind of object to fill with
        if ($recordClass == '') {
            $recordClass = $this->_buildRecordClassName($client->getLastLamplightMethod(), $client->getLastLamplightAction());
        }

        // Get the data as an array
        try {
            $response_stream = $response->getBody();
            $response_stream->rewind();
            $parsed_data = $this->_parseResponseBody($response_stream->getContents());

        } catch (\Error $parse_data_error) {
            throw new ParseReturnedDataException($parse_data_error->getMessage(), $parse_data_error->getCode(), $parse_data_error);
        }
        if ($parsed_data === null) {
            throw new ParseReturnedDataException('No data found');
        }

        if (!(is_object($parsed_data) && property_exists($parsed_data, 'data'))) {
            return [];
        }

        if (is_object($parsed_data->data)) {
            $parsed_data->data = [$parsed_data->data];
        }

        $records = [];

        if (is_array($parsed_data->data)) {
            foreach ($parsed_data->data as $rec) {
                /** @var BaseRecord $newRec */
                $newRec = new $recordClass((array)$rec);
                $newRec->init($client);
                $records[$newRec->get('id')] = $newRec;
            }
        }

        return $records;

    }

    /**
     * @param Response|null $response
     * @param RecordSet $record_set
     * @return void
     */
    protected function parseErrorFromResponse (?Response $response, RecordSet $record_set): void {

        // try and parse error message
        $parsed_data = $this->_parseResponseBody($response->getBody()->getContents());

        if ($parsed_data) {
            if (is_object($parsed_data) && property_exists($parsed_data, 'error')) {
                $record_set->setErrorCode($parsed_data->error);
                $record_set->setErrorMessage($parsed_data->msg);
                return;
            }

            $record_set->setErrorCode(1101);
            $record_set->setErrorMessage("The response from the server was an error, "
                . "we parsed it as json OK, but it doesn't have the expected"
                . " error code and message.");
            return;

        }

        $record_set->setErrorCode(1100);
        $record_set->setErrorMessage("Could not parse response body as json");

    }

}
