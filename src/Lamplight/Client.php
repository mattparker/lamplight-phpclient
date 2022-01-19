<?php

namespace Lamplight;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Lamplight\Client\Exception\MayNotRequestAllWorkException;
use Lamplight\Datain\ResponseCollection;
use Lamplight\Datain\ResponseCollectionFactory;
use Lamplight\Record\Mutable;
use Lamplight\RecordSet\NoRequestMadeToMakeRecordsFromException;
use Lamplight\Response\ErrorResponse;

/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @package    Lamplight
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history    1.11 Adds returnShortData() and returnFullData() methods for some people/orgs
 * @history    1.2  Adds add/edit profiles functionality.
 * @history    1.21 Adds near() method to do geographic search.
 * @version    2.01 Refactor to new version
 */


/**
 *
 *
 * The Lamplight\Client class provides a php wrapper for the Lamplight
 * publishing API.
 *
 * Lamplight\Client provides convenience methods to request and send data
 * to the Lamplight API and uses the GuzzleHttp\Client to make the requests
 *
 * @category   Lamplight
 * @package    Lamplight
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


    const USER_ROLE = 'user';
    const CONTACT_ROLE = 'contact';
    const STAFF_ROLE = 'staff';
    const FUNDER_ROLE = 'funder';
    const ORGANISATION_ROLE = 'org';


    /**
     * @var String       API key from Lamplight
     */
    protected string $lamplight_key = '';

    /**
     * @var Int          Lamplight ID provided by Lamplight
     */
    protected int $lamplight_id = 0;

    /**
     * @var Int          Lamplight Project ID provided by Lamplight
     */
    protected int $lamplight_project = 0;

    protected string $http_method = 'GET';

    /**
     * @var String       Whether to fetch one, some or all records
     */
    protected string $lamplight_method = '';


    /**
     * @var String       The method used on the last request
     */
    protected string $last_lamplight_method_sent = '';


    /**
     * @var String       Whether to fetch people, orgs, workareas or work
     */
    protected string $lamplight_action = '';


    /**
     * @var String        The action used on the last request
     */
    protected string $last_lamplight_action = '';

    /**
     * @var Response
     */
    protected $last_response;



    /**
     * @var String       Lamplight API base uri
     */
    protected string $_baseUri = "https://lamplight.online/api/";

    /**
     * @var GuzzleClient
     */
    protected GuzzleClient $client;

    /**
     * @var array
     */
    protected array $query_params = [];

    protected array $form_params = [];


    /**
     * Constructor.  Allows for additional API parameters and sets the method.
     * Also overrides the base uri to point at Lamplight
     * @param GuzzleClient
     * @param array          Config options ['lampid' => 123, 'project' => 1, 'key' => '<api key from Lamplight>']
     */
    public function __construct (GuzzleClient $client = null, array $config = []) {

        if (!$client) {
            $client = new GuzzleClient();
        }

        // Allow setting of API key details at construction:
        foreach (['key', 'lampid', 'project'] as $key) {
            if (array_key_exists($key, $config)) {
                $this->setApiParameter($key, $config[$key]);
            }
        }

        $this->client = $client;
    }


    /**
     * Clears GET parameters, but leaves API credentials.  Does not discard
     * previous requests
     * @return Client     Fluent interface
     */
    public function resetClient () : Client {

        $this->lamplight_action = "";
        $this->lamplight_method = "";

        $this->query_params = [];
        $this->form_params = [];

        return $this;
    }


    /**
     * Sets API access credential parameters
     *
     * @param String        Field may be 'key', 'lampid', or 'project'
     * @param Mixed         Values provided by Lamplight admin
     * @return Client
     */
    public function setApiParameter ($field, $value) : Client {

        if ($field === 'key' && is_string($value)) {
            $this->lamplight_key = $value;
            return $this;
        }
        if ($field === 'lampid' && $value > 0) {
            $this->lamplight_id = (int)$value;
            return $this;
        }

        if ($field === 'project' && $value > 0) {
            $this->lamplight_project = (int)$value;
        }
        return $this;

    }


    /**
     * Add a key/value pair to a GET request
     * @param $key
     * @param $value
     */
    public function setParameterGet ($key, $value) : Client {
        $this->query_params[$key] = $value;
        return $this;
    }

    /**
     * Add a data for a POST request
     * @param $key
     * @param $value
     * @return void
     */
    public function setParameterPost ($key, $value) : Client {
        $this->form_params[$key] = $value;
        return $this;
    }

    public function setMethod (string $method) : Client {
        $this->http_method = $method;
        return $this;
    }

    /**
     * Sets parameters to fetch work records
     * @return Client     Fluent interface
     */
    public function fetchWork () : Client {
        $this->lamplight_action = "work";
        return $this;
    }



    /**
     * Sets parameters to fetch people records
     * @param String $role Role of person to fetch (when using some|all) - one of the *_ROLE class constants
     * @return Client     Fluent interface
     */
    public function fetchPeople (string $role = '') : Client {
        $this->lamplight_action = "people";
        if (in_array($role, array(self::USER_ROLE, self::CONTACT_ROLE, self::STAFF_ROLE, self::FUNDER_ROLE))) {
            $this->setParameterGet('role', $role);
        }
        return $this;
    }


    /**
     * Sets parameters to fetch workarea records
     * @return Client     Fluent interface
     */
    public function fetchWorkarea () : Client {
        $this->lamplight_action = "workarea";
        return $this;
    }

    /**
     * Sets parameters to fetch organisation records
     * @param String $role Role of org to fetch (when using some|all)
     * @return Client     Fluent interface
     */
    public function fetchOrgs (string $role = '') : Client {
        $this->lamplight_action = "orgs";
        if (in_array($role, array(self::USER_ROLE, self::CONTACT_ROLE, self::ORGANISATION_ROLE, self::FUNDER_ROLE))) {
            $this->setParameterGet('role', $role);
        }
        return $this;
    }


    /**
     * Sets parameters to fetch one record
     * @param Int        ID of the record to fetch
     * @return Client     Fluent interface
     */
    public function fetchOne (int $id = 0) : Client {
        $this->lamplight_method = "one";
        if ($id > 0) {
            $this->setParameterGet('id', $id);
        }
        return $this;
    }


    /**
     * Sets parameters to fetch some records
     * @return Client     Fluent interface
     */
    public function fetchSome () : Client {
        $this->lamplight_method = "some";
        return $this;
    }


    /**
     * Sets parameters to fetch all records
     * @return Client     Fluent interface
     */
    public function fetchAll () : Client {
        $this->lamplight_method = "all";
        return $this;
    }


    /**
     * Allows geographic search
     * @param String            Lat,Long | Northing,Easting | Postcode
     * @param Int               Search radius
     * @return Client
     * @since 1.21
     */
    public function near (string $where, $howClose) : Client {
        $this->setParameterGet('near', $where);
        $this->setParameterGet('nearRadius', $howClose);
        $this->returnFullData();
        return $this;
    }


    /**
     * Requests summary data (on some or all requests for
     * orgs or people
     * @return Client    Fluent interface
     */
    public function returnShortData () : Client {
        $this->setParameterGet('return', 'short');
        return $this;
    }


    /**
     * Requests all publishable data (on some or all requests for
     * orgs or people.  Response body will be bigger and slower!
     * @return Client    Fluent interface
     */
    public function returnFullData () : Client {
        $this->setParameterGet('return', 'full');
        return $this;
    }



    ////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////
    // Datain methods

    /**
     * @param int $recordid
     * @param int|string $identifier_of_attendee ID or email address of attendee
     * @return $this
     */
    public function attendWork (int $recordid, $identifier_of_attendee) : Client {
        // this is work
        $this->fetchWork();
        $this->setParameterPost('id', $recordid);
        // set it to attend, POST submision + attendee param
        $this->_attend($identifier_of_attendee);

        return $this;

    }


    /**
     * Saves a Lamplight_Record* if that is allowed by the API
     * @param Mutable $record   With all the data to save
     * @return  Response Object
     * @throws \Exception
     */
    public function save (Mutable $record) {

        $record->beforeSave($this);

        $this->lamplight_method = $record->getLamplightMethod();
        $this->lamplight_action = $record->getLamplightAction();
        $this->setMethod('POST');

        $data = $record->toAPIArray();

        foreach ($data as $paramName => $paramValue) {
            $this->setParameterPost($paramName, $paramValue);
        }

        $ret = $this->request();

        $record->afterSave($this, $ret);

        return $ret;
    }


    /**
     * Sets up a new ResponseCollection instance
     * to wrap the response returned and provides
     * convenience methods to access response.  This will
     * only work for datain type responses - calling this after
     * fetch* requests will throw an Exception
     *
     * @return ResponseCollection
     */
    public function getDatainResponse () : ResponseCollection {
        return (new ResponseCollectionFactory())->createResponseFromClient($this);
    }



    /**
     * Sets method to attend and adds the attendee
     * @param int | string     ID or Email address of person wanting to attend
     * @return Client
     */
    protected function _attend ($identifier_of_attendee) : Client {
        $this->setMethod('POST');
        $this->lamplight_method = "attend";
        $this->setParameterPost('attendee', $identifier_of_attendee);
        return $this;
    }





    //////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    // General utility methods

    /**
     * Retrieves the method used in the last request (one|some|all)
     * @return String   Last method used (one|some|all)
     */
    public function getLastLamplightMethod () : string {
        return $this->last_lamplight_method_sent;
    }

    /**
     * Retrieves the action used in the last request (work|people|orgs|workarea)
     * @return String  Last action used
     */
    public function getLastLamplightAction () : string {
        return $this->last_lamplight_action;
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

     */
    public function request () : Response {

        if (!($this->lamplight_key && $this->lamplight_id && $this->lamplight_project)) {
            throw new \Exception("Lamplight API access parameters have not been set");
        }

        if ($this->lamplight_action === 'work' && $this->lamplight_method === 'all') {
            throw new MayNotRequestAllWorkException("Requesting all work records is not supported");
        }


        // Set up the main uri with method and parameters etc.
        $uri = $this->_constructUri();

        // Now add the API authentication parameters
        $this->setParameterGet('key', $this->lamplight_key);
        $this->setParameterGet('lampid', $this->lamplight_id);
        $this->setParameterGet('project', $this->lamplight_project);

        if ($this->http_method === 'GET') {
            $params = ['query' => $this->query_params];
        } else {
            $params = ['query' => $this->query_params, 'form_params' => $this->form_params];
        }

        try {
            $response = $this->client->request($this->http_method, $uri, $params);
            $lamplight_response = new Response\SuccessResponse($response);

        } catch (\Throwable $client_exception) {

            $lamplight_response = new ErrorResponse($client_exception->getCode(), $client_exception->getMessage());

        }

        $this->last_response = $lamplight_response;
        return $lamplight_response;

    }


    /**
     * Returns the last response as a Lamplight_RecordSet
     * which by default will contain instances of (one of):
     *  - Lamplight\Record\WorkSummary
     *  - Lamplight\Record\Work
     *  - Lamplight\Record\PeopleSummary
     *  - Lamplight\Record\People
     *  - Lamplight\Record\OrgsSummary
     *  - Lamplight\Record\Orgs
     *  - Lamplight\Record\WorkareaSummary
     * depending on the last request.
     *
     * @param String            Class name to use for Records, overriding the default.
     * @return RecordSet  RecordSet containing Records
     * @throws NoRequestMadeToMakeRecordsFromException
     */
    public function getRecordSet ($recordClassName = '') : RecordSet {
        return RecordSet::factory($this, $recordClassName);
    }

    /**
     * @return Response|null
     */
    public function getLastResponse () : ?Response {
        return $this->last_response;
    }

    /**
     * Adds the details of what we want and how many to the uri.
     * @return string URI
     * @throws \Exception
     */
    protected function _constructUri () : string {

        $uri = $this->_baseUri;

        if (!($this->lamplight_action && $this->lamplight_method)) {
            throw new \Exception("You need to specify what you want to request, and how many of them");
        }

        $uri .= $this->lamplight_action . '/' . $this->lamplight_method . '/format/json';

        $this->last_lamplight_action = $this->lamplight_action;
        $this->last_lamplight_method_sent = $this->lamplight_method;

        return $uri;
    }


    /**
     * Looks up parameters that have been set.  Looks at
     * GET first, the POST.
     * @param String            Name of the parameter.
     * @return Mixed | null     null returned if key not found
     */
    public function getParameter ($key) {

        if (array_key_exists($key, $this->query_params)) {
            return $this->query_params[$key];
        }
        if (array_key_exists($key, $this->form_params)) {
            return $this->form_params[$key];
        }
        return null;
    }


}
