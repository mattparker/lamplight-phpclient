<?php
/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Update for add profile functionality
 */


/**
 *
 *
 * Lamplight_Datain_Response provides a wrapper for responses from the Lamplight datain API
 * module to provide convenient access.
 * @category   Lamplight
 * @package    Lamplight_Client
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Fix for minor bug in success() checking
 * @link       http://www.lamplight-publishing.co.uk/examples/addreferral.php  Worked examples and documentation for
 *     using the client library
 *
 *
 */
class Lamplight_Datain_Response implements Iterator {

    /**
     * @var Zend_Http_Response          The response created
     */
    protected $_response;

    /**
     * @var Object                      stdClass json decoded Lamplight response
     */
    protected $_responseJson;


    /**
     * @var String                      http response code
     */
    protected $_responseHttpStatus = null;

    /**
     * @var Array                       If we add to multiple records at once,
     *                                  we create a Lamplight_Datain_Response
     *                                  for each record, and use the parent
     *                                  to hold and iterate through the children
     */
    protected $_responseChildren = array();

    /**
     * @var Int                         Internal array pointer for multiple ones
     */
    protected $_responseChildrenPointer = 0;

    /**
     * @var Boolean                     Whether this is an add multi
     */
    protected $_isMultiple = null;

    /**
     * @var Lamplight_Datain_Response
     */
    protected $_parentResponse = null;

    /**
     * @var Lamplight_Client            The Lamplight client used for the request
     */
    protected $_client;

    /**
     * @var Boolean                     Whether the request was successful
     */
    protected $_success = null;

    /**
     * @var Int                         Error code returned by Lamplight (not HTTP)
     */
    protected $_error = null;

    /**
     * @var String                      Error message returned by Lamplight
     */
    protected $_errorMessage = null;


    /**
     * Wrapper for the response, providing some easier to use
     * methods for datain responses
     * @param Lamplight_Client                The Lamplight client wrapping the request
     */
    public function __construct (Lamplight_Client $client = null, Lamplight_Datain_Response $parent = null) {


        if ($client) {

            $this->setClient($client);

        }

        if ($parent) {
            $this->_parentResponse = $parent;
        }

    }


    /**
     * Sets the client
     * @param Lamplight_Client
     * @return Lamplight_Datain_Response
     */
    public function setClient (Lamplight_Client $client) {

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

            throw new Exception("The last request was not a datain one.");

        }

        $this->_response = $client->getLastResponse();
        $this->_client = $client;

        // Work out if we did lots of datain's, and if so set up
        // a child response for each
        $this->_handleMultiples();

    }


    /**
     * Whether this was a multi-submission and therefore
     * multiple response
     * @var Boolean
     */
    public function isMultiple () {
        return $this->_isMultiple;
    }


    /**
     * Returns the json-decoded (as object) response from Lamplight
     * @return Object
     */
    public function getJsonResponse () {

        if ($this->_responseJson === null) {
            require_once 'Zend/Json.php';
            $this->_responseJson = Zend_Json::decode($this->_response->getBody(), Zend_Json::TYPE_OBJECT);
        }
        return $this->_responseJson;
    }


    /**
     * Gets the ID of the record we've added to
     * @return Int
     */
    public function getId () {

        return (int)$this->_id;

    }


    /**
     * Checks if the datain request was successful
     * @return Boolean
     */
    public function success () {

        if ($this->_success === null) {

            // If multiple, return a number from the children:
            if ($this->_isMultiple) {

                foreach ($this->_responseChildren as $child) {

                    if ($child->success()) {
                        $this->_success++;
                    }
                }

            } else {

                $json = $this->getJsonResponse();
                $client = $this->_client;
                $id = $client->getParameter('id');
                $type = $client->getLastLamplightAction();


                // check property exists and id matches originally passed id
                if ($json && is_object($json) && property_exists($json, 'data')) {

                    if (is_array($json->data)) {

                        // multiples: check each

                    } else if (is_object($json->data)
                        && property_exists($json->data, 'id')) {


                        // single record: if it has an id, does it match?
                        if ($id > 0) {
                            $this->_success = ($json->data->id == $id);
                        } else {
                            $this->_success = true;
                        }

                    } else if (is_numeric($json->data) && $json->data > 0) {

                        // single record: if it has an id, does it match?
                        if ($id > 0) {
                            $this->_success = ($json->data->id == $id);
                        } else {
                            $this->_success = true;
                        }

                    }

                } else {

                    $this->_success = false;
                }

            }

        }

        if ($this->_isMultiple) {
            return $this->_success == count($this->_responseChildren);
        }
        return $this->_success;

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

        if ($this->_responseHttpStatus === null) {
            $this->_responseHttpStatus = $this->_response->getStatus();
        }
        return $this->_responseHttpStatus;
    }


    /**
     * Gets the Lamplight error code, if any
     * @return Int | False
     */
    public function getErrorCode () {

        if ($this->_error === null) {

            $json = $this->getJsonResponse();
            if ($json && is_object($json) && property_exists($json, 'error')) {
                $this->_error = $json->error;
            }
        } else {
            $this->_error = false;
        }

        return $this->_error;
    }


    /**
     * Gets the error message returned by Lamplight, if any
     * @return String | ''
     */
    public function getErrorMessage () {

        if ($this->_errorMessage === null) {

            if ($this->getErrorCode()) {

                $json = $this->getJsonResponse();
                if ($json && is_object($json) && property_exists($json, 'msg')) {

                    $this->_errorMessage = $json->msg;

                } else {
                    $this->_errorMessage = 'There was an error but no message returned with it';
                }
            } else {
                $this->_errorMesage = '';
            }

        }

        return $this->_errorMessage;
    }


    /**
     * Works out if we've got multi records
     */
    protected function _handleMultiples () {

        if ($this->_isMultiple === null) {

            $json = $this->getJsonResponse();
            $client = $this->_client;
            $id = $client->getParameter('id');
            $type = $client->getLastLamplightAction();

            // check property exists and id matches originally passed id
            if ($json && is_object($json) && property_exists($json, 'data')) {

                // this is the multiple one:
                if (is_array($json->data)) {

                    foreach ($json->data as $rec) {

                        $child = new Lamplight_Datain_Response(null, $this);
                        $child->_overRide(
                            array(
                                'id' => $rec->id,
                                'success' => $rec->attend,
                                'error' => (property_exists($rec, 'error') ? $rec->error > 0 : false),
                                'errorMessage' => (property_exists($rec, 'msg') ? $rec->msg : ''),
                                'responseJson' => $rec
                            ),
                            $this
                        );

                        $this->_responseChildren[] = $child;

                    }

                    $this->_isMultiple = true;

                } else {
                    $this->_isMultiple = false;
                    $this->_id = $id;
                }
            }
        }

    }


    // Undocumented method to allow parent to set child properties.
    public function _overRide (array $data = array(), Lamplight_Datain_Response $resp) {

        // Only the parent can override
        if ($resp !== $this->_parentResponse) {
            return;
        }

        $this->_id = $data['id'];
        $this->_success = $data['success'];
        $this->_error = $data['error'];
        $this->_errorMessage = $data['errorMessage'];
        $this->_responseJson = $data['responseJson'];

    }


    /**
     * How many records are there?
     * @return Int
     */
    public function count () {
        return count($this->_responseChildren);
    }

    /////// Iterator methods
    public function rewind () {

        $this->_responseChildrenPointer = 0;
    }

    /**
     * @return Mixed
     */
    public function current () {
        $k = array_keys($this->_responseChildren);
        $var = $this->_responseChildren[$k[$this->_responseChildrenPointer]];
        return $var;
    }

    /**
     * @return Mixed
     */
    public function key () {
        $k = array_keys($this->_responseChildren);
        $var = $k[$this->_responseChildrenPointer];
        return $var;
    }

    /**
     * @return Mixed | false
     */
    public function next () {
        $k = array_keys($this->_responseChildren);
        if (isset($k[++$this->_responseChildrenPointer])) {
            $var = $this->_responseChildren[$k[$this->_responseChildrenPointer]];
            return $var;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function valid () {
        $k = array_keys($this->_responseChildren);
        $var = isset($k[$this->_responseChildrenPointer]);
        return $var;
    }


}
