<?php

namespace Lamplight;
/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @package    Lamplight_Client
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history    1.11 Adds returnShortData() and returnFullData() methods for some people/orgs
 * @history    1.2  Adds add/edit profiles functionality.
 * @history    1.21 Adds near() method to do geographic search.
 * @version    1.22 Change to lamplight.online from lamplight3.info
 */


/**
 *
 *
 * The Lamplight_Client class provides a php wrapper for the Lamplight
 * publishing API.
 *
 * Lamplight_Client provides convenience methods to request and send data
 * to the Lamplight API and extends Zend_Http_Client to run the actual requests.
 *
 * @category   Lamplight
 * @package    Lamplight_Client
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.11 Adds returnShortData() and returnFullData() methods for some people/org
 * @history    1.2  Adds add/edit profiles functionality.
 * @history    1.21 Adds near() method to do geographic search.
 * @version    1.22 Change to lamplight.online from lamplight3.info
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 */
class Client {


    /**
     * @var String       API key from Lamplight
     */
    protected $_key = '';

    /**
     * @var Int          Lamplight ID provided by Lamplight
     */
    protected $_lampid = 0;

    /**
     * @var Int          Lamplight Project ID provided by Lamplight
     */
    protected $_project = 0;

    protected string $http_method = 'GET';

    /**
     * @var String       Whether to fetch one, some or all records
     */
    protected $_lamplightMethod = '';


    /**
     * @var String       The method used on the last request
     */
    protected $_lastMethod = '';


    /**
     * @var String       Whether to fetch people, orgs, workareas or work
     */
    protected $_lamplightAction = '';


    /**
     * @var String        The action used on the last request
     */
    protected $_lastAction = '';


    /**
     * @var String       Lamplight API base uri
     */
    protected string $_baseUri = "https://lamplight.online/api/";
    protected \GuzzleHttp\Client $client;


    /**
     * Constructor.  Allows for additional API parameters and sets the method.
     * Also overrides the base uri to point at Lamplight
     * @param string
     * @param array          Config options
     */
    public function __construct (\GuzzleHttp\Client $client, $config) {


        // Allow setting of API key details at construction:
        $configKeys = array('key', 'lampid', 'project');
        foreach ($configKeys as $key) {
            if (array_key_exists($key, $config)) {
                $this->setApiParameter($key, $config[$key]);
                unset($config[$key]);
            }
        }


        $this->client = $client;
    }


    /**
     * Sets API access credential parameters
     *
     * @param String        Field may be 'key', 'lampid', or 'project'
     * @param Mixed         Values provided by Lamplight admin
     * @return Lamplight_Client
     */
    public function setApiParameter ($field, $value) {

        $keys = array('key', 'lampid', 'project');
        if (in_array(strtolower($field), $keys)) {
            $fieldKey = "_" . $field;
            $this->{$fieldKey} = $value;
        }
        return $this;

    }


    /**
     * Clears GET parameters, but leaves API credentials.  Does not discard
     * previous requests
     * @return Lamplight_Client     Fluent interface
     */
    public function resetClient () {
        $this->_lamplightAction = "";
        $this->_lamplightMethod = "";
        $this->setUri($this->_baseUri);
        $this->resetParameters(false);
        return $this;
    }


    /**
     * Sets parameters to fetch work records
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchWork () {
        $this->_lamplightAction = "work";
        return $this;
    }

    /**
     * Sets parameters to fetch people records
     * @param String $role Role of person to fetch (when using some|all)
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchPeople ($role = '') {
        $this->_lamplightAction = "people";
        if (in_array($role, array('user', 'contact', 'staff', 'funder'))) {
            $this->setParameterGet("role", $role);
        }
        return $this;
    }

    /**
     * Sets parameters to fetch workarea records
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchWorkarea () {
        $this->_lamplightAction = "workarea";
        return $this;
    }

    /**
     * Sets parameters to fetch organisation records
     * @param String $role Role of org to fetch (when using some|all)
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchOrgs ($role = '') {
        $this->_lamplightAction = "orgs";
        if (in_array($role, array('user', 'contact', 'org', 'funder'))) {
            $this->setParameterGet("role", $role);
        }
        return $this;
    }


    /**
     * Sets parameters to fetch one record
     * @param Int        ID of the record to fetch
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchOne ($id = 0) {
        $this->_lamplightMethod = "one";
        if ($id > 0) {
            $this->setParameterGet('id', $id);
        }
        return $this;
    }


    /**
     * Sets parameters to fetch some records
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchSome () {
        $this->_lamplightMethod = "some";
        return $this;
    }


    /**
     * Sets parameters to fetch all records
     * @return Lamplight_Client     Fluent interface
     */
    public function fetchAll () {
        $this->_lamplightMethod = "all";
        return $this;
    }


    /**
     * Allows geographic search
     * @param String            Lat,Long | Northing,Easting | Postcode
     * @param Int               Search radius
     * @return Lamplight_Client
     * @since 1.21
     */
    public function near ($where, $howClose) {
        $this->setParameterGet('near', $where);
        $this->setParameterGet('nearRadius', $howClose);
        $this->returnFullData();
        return $this;
    }


    /**
     * Requests summary data (on some or all requests for
     * orgs or people
     * @return Lamplight_Client    Fluent interface
     */
    public function returnShortData () {
        $this->setParameterGet('return', 'short');
        return $this;
    }


    /**
     * Requests all publishable data (on some or all requests for
     * orgs or people.  Response body will be bigger and slower!
     * @return Lamplight_Client    Fluent interface
     */
    public function returnFullData () {
        $this->setParameterGet('return', 'full');
        return $this;
    }



    ////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////
    // Datain methods


    /**
     * Add someone to attend a work record
     * @param Int          ID of work record
     * @param String       Email address of person wanting to attend
     * @return Lamplight_Client    Fluent interface
     */
    public function attendWork ($recordid, $emailOfAttendee) {
        // this is work
        $this->fetchWork();
        $this->setParameterPost('id', $recordid);
        // set it to attend, POST submision + attendee param
        $this->_attend($emailOfAttendee);

        return $this;

    }


    /**
     * Saves a Lamplight_Record* if that is allowed by the API
     * @param Lamplight_Record_Abstract         With all the data to save
     * @return  Zend_Http_Response         Response Object
     */
    public function save (Lamplight_Record_Mutable $rec) {

        if (!$rec->isEditable()) {
            throw new Exception("You are trying to save a record that is not editable");
        }

        $rec->beforeSave($this);

        $this->_lamplightMethod = $rec->getLamplightMethod();
        $this->_lamplightAction = $rec->getLamplightAction();
        $this->setMethod('POST');

        $data = $rec->toAPIArray();

        foreach ($data as $paramName => $paramValue) {
            $this->setParameterPost($paramName, $paramValue);
        }

        $ret = $this->request();

        $rec->afterSave($this, $ret);

        return $ret;
    }


    /**
     * Sets up a new Lamplight_Datain_Response instance
     * to wrap the response returned and provides
     * convenience methods to access response.  This will
     * only work for datain type responses - calling this after
     * fetch* requests will throw an Exception
     *
     * @return Lamplight_Datain_Response
     */
    public function getDatainResponse () {

        require_once('Datain/Response.php');
        return new Lamplight_Datain_Response($this);

    }


    /**
     * Sets method to attend and adds the attendee
     * @param String     Email address of person wanting to attend
     * @return Lamplight_Client
     */
    protected function _attend ($emailOfAttendee) {
        $this->setMethod('POST');
        $this->_lamplightMethod = "attend";
        $this->setParameterPost('attendee', $emailOfAttendee);
        return $this;
    }





    //////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    // General utility methods

    /**
     * Retrieves the method used in the last request (one|some|all)
     * @return String   Last method used (one|some|all)
     */
    public function getLastLamplightMethod () {
        return $this->_lastMethod;
    }

    /**
     * Retrieves the action used in the last request (work|people|orgs|workarea)
     * @return String  Last action used
     */
    public function getLastLamplightAction () {
        return $this->_lastAction;
    }

    /**
     * Retrieves the format requested in the last request (json)
     * @return String       Format
     */
    public function getResponseFormat () {
        return 'json';
    }


    /**
     * Send the HTTP request and return an HTTP response object
     * Sets Lamplight API key parameters before request
     * @param String $method GET || POST
     * @return Zend_Http_Response         Response Object
     * @throws Zend_Http_Client_Exception
     */
    public function request ($method = null) {

        if (!($this->_key && $this->_lampid && $this->_project)) {
            require_once("Zend/Http/Client/Exception.php");
            throw new Zend_Http_Client_Exception("Lamplight API access parameters have not been set");
        }


        // Set up the main uri with method and parameters etc.
        $this->_constructUri();

        // Now add the API authentication parameters
        $this->setParameterGet('key', $this->_key);
        $this->setParameterGet('lampid', $this->_lampid);
        $this->setParameterGet('project', $this->_project);

        return parent::request($method);

    }


    /**
     * Returns the last response as a Lamplight_RecordSet
     * which by default will contain instances of (one of):
     *  - Lamplight_Record_WorkSummary
     *  - Lamplight_Record_Work
     *  - Lamplight_Record_PeopleSummary
     *  - Lamplight_Record_People
     *  - Lamplight_Record_OrgsSummary
     *  - Lamplight_Record_Orgs
     *  - Lamplight_Record_WorkareaSummary
     * depending on the last request.
     *
     * @param String            Class name to use for Records, overriding the default.
     * @return Lamplight_RecordSet  RecordSet containing Records
     * @throws Zend_Http_Client_Exception
     */
    public function getRecordSet ($recordClassName = '') {

        $resp = $this->getLastResponse();
        if ($resp === null) {
            require_once("Zend/Http/Client/Exception.php");
            throw new Zend_Http_Client_Exception("Response not available (not stored or not requested");
        }

        require_once 'Lamplight/RecordSet.php';
        return Lamplight_RecordSet::factory($this, $recordClassName);

    }


    /**
     * Adds the details of what we want and how many to the uri.
     * @return Lamplight_Client     Fluent interface
     * @throws Zend_Http_Client_Exception
     */
    protected function _constructUri () {

        // If the uri is unchanged, we need to change it:
        $uri = $this->getUri(true);
        //    if ($uri == $this->_baseUri) {

        if (!($this->_lamplightAction && $this->_lamplightMethod)) {
            require_once("Zend/Http/Client/Exception.php");
            throw new Zend_Http_Client_Exception("You need to specify what you want to request, and how many of them");
        }

        $uri .= $this->_lamplightAction . '/';
        $uri .= $this->_lamplightMethod . '/';
        $uri .= 'format/json';

        $this->_lastAction = $this->_lamplightAction;
        $this->_lastMethod = $this->_lamplightMethod;

        $this->setUri($uri);

        //    }
        return $this;
    }


    /**
     * Looks up parameters that have been set.  Looks at
     * GET first, the POST.
     * @param String            Name of the parameter.
     * @return Mixed | null     null returned if key not found
     */
    public function getParameter ($key) {
        if (array_key_exists($key, $this->paramsGet)) {
            return $this->paramsGet[$key];
        }
        if (array_key_exists($key, $this->paramsPost)) {
            return $this->paramsPost[$key];
        }
        return null;
    }


}
