<?php
namespace Lamplight\Datain;


use Lamplight\Client;
use Lamplight\Datain\Exception\LastRequestWasNotDataInException;
use Lamplight\Response\SuccessResponse;
use Psr\Http\Message\StreamInterface;

/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history     1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history     1.2 Update for add profile functionality
 * @version    2.0 New version
 */


/**
 *
 *
 * Lamplight\Datain\Response provides a wrapper for responses from the Lamplight datain API
 * module to provide convenient access.
 * @category   Lamplight
 * @package    Lamplight\Client
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history    1.2 Fix for minor bug in success() checking
 * @version    2.0 New version
 * @link       http://www.lamplight-publishing.co.uk/examples/addreferral.php  Worked examples and documentation for
 *     using the client library
 *
 *
 */
class Response implements \Lamplight\Response, \Iterator {




    /**
     * @var SuccessResponse          The response created
     */
    protected $response;

    /**
     * @var Object                      stdClass json decoded Lamplight response
     */
    protected $response_json;


    /**
     * @var String                      http response code
     */
    protected $response_http_status = null;

    /**
     * @var array                       If we add to multiple records at once,
     *                                  we create a Lamplight_Datain_Response
     *                                  for each record, and use the parent
     *                                  to hold and iterate through the children
     */
    protected array $child_responses = array();

    /**
     * @var Int                         Internal array pointer for multiple ones
     */
    protected int $child_response_pointer = 0;

    /**
     * @var Boolean                     Whether this is an add multi
     */
    protected $is_multiple = null;

    /**
     * @var Response
     */
    protected $parent_response = null;

    /**
     * @var Client            The Lamplight client used for the request
     */
    protected $lamplight_client;

    /**
     * @var Boolean                     Whether the request was successful
     */
    protected $was_request_success = null;

    /**
     * @var Int                         Error code returned by Lamplight (not HTTP)
     */
    protected $lamplight_error_code = null;

    /**
     * @var String                      Error message returned by Lamplight
     */
    protected $lamplight_error_message = null;

    /**
     * @var int
     */
    protected int $record_id = 0;


    /**
     * Wrapper for the response, providing some easier to use
     * methods for datain responses
     * @param Client|null $client
     * @param Response|null $parent
     */
    public function __construct (Client $client = null, Response $parent = null) {


        if ($client) {

            $this->setClient($client);

        }

        if ($parent) {
            $this->parent_response = $parent;
        }

    }


    /**
     * Sets the client
     * @param Client $client
     * @return Response
     */
    public function setClient (Client $client) {

        // check last request was a datain one:
        $validDataMethod = array("attend");
        $validDataAction = array("work");
        $validMA = array(
            "work/attend",
            "referral/add",
            "people/add",
            "people/update",
            "orgs/add",
            "orgs/update"
        );
        $ma = $client->getLastLamplightAction() . "/" . $client->getLastLamplightMethod();


        if (!in_array($ma, $validMA)) {

            throw new LastRequestWasNotDataInException("The last request was not a datain one.");

        }

        $this->response = $client->getLastResponse();
        $this->lamplight_client = $client;

        // Work out if we did lots of datain's, and if so set up
        // a child response for each
        $this->_handleMultiples();

        return $this;

    }


    /**
     * Whether this was a multi-submission and therefore
     * multiple response
     * @var Boolean
     */
    public function isMultiple () {
        return $this->is_multiple;
    }


    /**
     * Returns the json-decoded (as object) response from Lamplight
     * @return array
     */
    public function getJsonResponse () : ?\stdClass {

        if ($this->response_json === null) {

            $this->response_json = json_decode($this->response->getBody()->getContents());//, Zend_Json::TYPE_OBJECT);
        }
        return $this->response_json;
    }


    /**
     * Gets the ID of the record we've added to
     * @return Int
     */
    public function getId () {

        return (int)$this->record_id;

    }


    /**
     * Checks if the datain request was successful
     * @return Boolean
     */
    public function success () {

        if ($this->was_request_success === null) {

            // If multiple, return a number from the children:
            if ($this->is_multiple) {

                foreach ($this->child_responses as $child) {

                    if ($child->success()) {
                        $this->was_request_success++;
                    }
                }

            } else {

                $json = $this->getJsonResponse();
                $client = $this->lamplight_client;
                $id = $client->getParameter('id');
                $type = $client->getLastLamplightAction();


                // check property exists and id matches originally passed id
                if ($json && property_exists($json, 'data')) {

                    if (is_array($json->data)) {

                        // multiples: check each

                    } else if (is_object($json->data) && property_exists($json->data, 'id')) {


                        // single record: if it has an id, does it match?
                        if ($id > 0) {
                            $this->was_request_success = ($json->data->id == $id);
                        } else {
                            $this->was_request_success = true;
                        }

                    } else if (is_numeric($json->data) && $json->data > 0) {

                        // single record: if it has an id, does it match?
                        if ($id > 0) {
                            $this->was_request_success = ($json->data == $id);
                        } else {
                            $this->was_request_success = true;
                        }

                    }

                } else {

                    $this->was_request_success = false;
                }

            }

        }

        if ($this->is_multiple) {
            return $this->was_request_success == count($this->child_responses);
        }
        return $this->was_request_success;

    }


    /**
     * Were there any errors?  Or rather, was it not successful?
     * Provided for consistency with Lamplight_Recordset
     * @return Boolean
     */
    public function getErrors () {
        return !$this->success();
    }


    /**
     * Gest the http response status
     * @return String
     */
    public function getResponseStatus () {

        if ($this->response_http_status === null) {
            $this->response_http_status = $this->response->getStatus();
        }
        return $this->response_http_status;
    }


    /**
     * Gets the Lamplight error code, if any
     * @return Int | False
     */
    public function getErrorCode () {

        if ($this->lamplight_error_code === null) {

            $json = $this->getJsonResponse();
            if ($json && property_exists($json, 'error')) {
                $this->lamplight_error_code = $json->error;
            }
        } else {
            $this->lamplight_error_code = false;
        }

        return $this->lamplight_error_code;
    }


    /**
     * Gets the error message returned by Lamplight, if any
     * @return String | ''
     */
    public function getErrorMessage () : string {

        if ($this->lamplight_error_message === null) {

            if ($this->getErrorCode()) {

                $json = $this->getJsonResponse();
                if ($json && property_exists($json, 'msg')) {

                    $this->lamplight_error_message = $json->msg;

                } else {
                    $this->lamplight_error_message = 'There was an error but no message returned with it';
                }
            } else {
                $this->_errorMesage = '';
            }

        }

        return $this->lamplight_error_message;
    }


    /**
     * Works out if we've got multi records
     */
    protected function _handleMultiples () {

        if ($this->is_multiple === null) {

            $json = $this->getJsonResponse();
            $client = $this->lamplight_client;
            $id = $client->getParameter('id');
            $type = $client->getLastLamplightAction();

            // check property exists and id matches originally passed id
            if ($json && property_exists($json, 'data')) {

                // this is the multiple one:
                if (is_array($json->data)) {

                    foreach ($json->data as $rec) {

                        $child = new Response(null, $this);
                        $child->_overRide(
                            array(
                                'id' => $rec->id,
                                'success' => $rec->attend,
                                'error' => (property_exists($rec, 'error') && $rec->error > 0),
                                'errorMessage' => (property_exists($rec, 'msg') ? $rec->msg : ''),
                                'responseJson' => $rec
                            ),
                            $this
                        );

                        $this->child_responses[] = $child;

                    }

                    $this->is_multiple = true;

                } else {
                    $this->is_multiple = false;
                    $this->record_id = $id;
                }
            }
        }

    }


    // Undocumented method to allow parent to set child properties.
    public function _overRide (array $data = [], Response $resp) {

        // Only the parent can override
        if ($resp !== $this->parent_response) {
            return;
        }

        $this->record_id = $data['id'];
        $this->was_request_success = $data['success'];
        $this->lamplight_error_code = $data['error'];
        $this->lamplight_error_message = $data['errorMessage'];
        $this->response_json = $data['responseJson'];

    }


    /**
     * How many records are there?
     * @return Int
     */
    public function count () {
        return count($this->child_responses);
    }

    /////// Iterator methods
    public function rewind () {

        $this->child_response_pointer = 0;
    }

    /**
     * @return Response
     */
    public function current () {
        $k = array_keys($this->child_responses);
        $var = $this->child_responses[$k[$this->child_response_pointer]];
        return $var;
    }

    /**
     * @return Mixed
     */
    public function key () {
        $k = array_keys($this->child_responses);
        $var = $k[$this->child_response_pointer];
        return $var;
    }

    /**
     * @return Mixed | false
     */
    public function next () {
        $k = array_keys($this->child_responses);
        if (isset($k[++$this->child_response_pointer])) {
            $var = $this->child_responses[$k[$this->child_response_pointer]];
            return $var;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function valid () {
        $k = array_keys($this->child_responses);
        $var = isset($k[$this->child_response_pointer]);
        return $var;
    }

    protected function getRelevantResponse () : \Lamplight\Response {
        if ($this->response) {
            return $this->response;
        }
        return $this->parent_response;
    }

    public function getProtocolVersion () {
        return $this->getRelevantResponse()->getProtocolVersion();
    }

    public function withProtocolVersion ($version) {
        return $this->getRelevantResponse()->withProtocolVersion($version);
    }

    public function getHeaders () {
        return $this->getRelevantResponse()->getHeaders();
    }

    public function hasHeader ($name) {
        return $this->getRelevantResponse()->hasHeader($name);
    }

    public function getHeader ($name) {
        return $this->getRelevantResponse()->getHeader($name);
    }

    public function getHeaderLine ($name) {
        return $this->getRelevantResponse()->getHeaderLine($name);
    }

    public function withHeader ($name, $value) {
        return $this->getRelevantResponse()->withHeader($name, $value);
    }

    public function withAddedHeader ($name, $value) {
        return $this->getRelevantResponse()->withAddedHeader($name, $value);
    }

    public function withoutHeader ($name) {
        return $this->getRelevantResponse()->withoutHeader($name);
    }

    public function getBody () {
        return $this->getRelevantResponse()->getBody();
    }

    public function withBody (StreamInterface $body) {
        return $this->getRelevantResponse()->withBody($body);
    }

    public function getStatus (): int {
        return $this->getRelevantResponse()->getStatus();
    }

    public function isError (): bool {
        return $this->getRelevantResponse()->isError();
    }

    public function isSuccessful (): bool {
        return $this->getRelevantResponse()->isSuccessful();
    }

    public function getStatusCode () {
        return $this->getRelevantResponse()->getStatusCode();
    }

    public function withStatus ($code, $reasonPhrase = '') {
        return $this->getRelevantResponse()->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase () {
        return $this->getRelevantResponse()->getReasonPhrase();
    }
}
