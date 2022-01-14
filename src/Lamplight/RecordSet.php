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
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Update for add profile functionality
 */


/**
 *
 *
 * The Lamplight_RecordSet provides a container for Lamplight_Record* instances.
 * The RecordSet is constructed using the factory method, based on the
 * Lamplight_Client request object.
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Update for add profile functionality, and better handling of fetchOne() requests
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 */
class RecordSet implements \Iterator {


    /**
     * True if there were errors getting data from response
     * @var  Boolean
     */
    protected bool $errors = false;

    /**
     * Array of Lamplight_Record_* instances
     * @var array
     */
    protected array $records = array();


    /**
     * Error code returned by server or locally
     * @var Int
     */
    protected int $error_code = 0;

    /**
     * Error message returned by server or locally
     * @var String
     */
    protected string $error_message = '';

    /**
     * HTTP response status
     * @var Int
     */
    protected int $response_status = 0;


    /**
     *  Array pointer
     * @var Int
     */
    protected int $index = 0;


    /**
     *  Template used to render Records
     * @var String
     */
    protected string $record_template = '';


    /**
     * Base record class, used when constructing Records from the data
     * @var String
     */
    protected static string $baseRecordClassName = 'Lamplight_Record';


    /**
     * Constructor: called by factory method
     * @param array     Array of Lamplight_Record_* instances
     */
    protected function __construct (array $records = array()) {
        $this->records = $records;
    }


    /**
     * Factory: creates a Lamplight_RecordSet filled with the appropriate
     * kind of Lamplight_Record* instances.
     * If there was a problem of some sort
     *    with the request, there won't be any records and getErrors() will
     *    provide more info: check errors before proceeding.
     * @param Client $client Client that's made a request already
     * @param string $recordClass Name of the class to use for records
     *                                      (over-rides default based on request type)
     * @return RecordSet
     */
    public static function factory (Client $client, string $recordClass = '') : RecordSet {

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
                $recordClass = self::_buildRecordClassName($action, $method);
            }
            $data = self::_parseResponseBody($resp->getBody(), $format);

            if ($data === false) {
                $errors = true;
            } elseif (is_object($data) && property_exists($data, 'data')) {

                if (is_object($data->data)) {
                    $data->data = array($data->data);
                }

                if (is_array($data->data)) {
                    foreach ($data->data as $rec) {
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
            $data = self::_parseResponseBody($resp->getBody(), $format);
            if ($data !== false) {
                if (is_object($data) && property_exists($data, 'error')) {
                    $rs->setErrorCode($data->error);
                    $rs->setErrorMessage($data->msg);
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
    protected static function _buildRecordClassName ($action, $method) {

        $class = self::$baseRecordClassName . '_' . ucfirst($method);
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
    protected static function _parseResponseBody ($data, $format = 'json') {

        switch ($format) {
            case 'json':
            default:

                try {
                    return json_decode($data);
                } catch (\Exception $e) {
                    return false;
                }
                break;
        }

    }


    /**
     * Sets error state
     * @param Boolean
     */
    public function setErrors (bool $error) {
        $this->errors = $error;
    }

    /**
     * Were there any errors with the request?
     * @return Boolean   True is there were errors
     */
    public function getErrors () : bool {
        return $this->errors;
    }


    /**
     * Set the error code returned by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @param Int
     * @return RecordSet      Fluent interface
     */
    public function setErrorCode (int $c) : RecordSet {
        $this->error_code = $c;
        return $this;
    }


    /**
     * Get the error code returned by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @return Int
     */
    public function getErrorCode () : int {
        return $this->error_code;
    }


    /**
     * Set the error code message by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @param String
     * @return RecordSet      Fluent interface
     */
    public function setErrorMessage (string $msg) : RecordSet {
        $this->error_message = $msg;
        return $this;
    }


    /**
     * Get the error code message by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @return String
     */
    public function getErrorMessage () : string {
        return $this->error_message;
    }


    /**
     * Get the http response status returned by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @param Int
     * @return RecordSet      Fluent interface
     */
    public function setResponseStatus (int $status) : RecordSet {
        $this->response_status = $status;
        return $this;
    }


    /**
     * Get the http response status returned by the server
     * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
     * @return int
     */
    public function getResponseStatus () : int {
        return $this->response_status;
    }


    /**
     * Sets the template to use for each record when rendering.
     * The record will scan through the string lookoing for expressions
     * enclosed in {} braces, using the expression found as the field
     * identifier e.g. <div>Name: {name}</div><div>ID: {id}</div>
     * @param string $template Template to use for records
     * @return RecordSet      Fluent interface
     */
    public function setRecordTemplate (string $template) : RecordSet {
        $this->record_template = $template;
        return $this;
    }


    /**
     * Getter for the record template
     * @return String
     */
    public function getRecordTemplate () : string {
        return $this->record_template;
    }


    /**
     * Iterates throught the records, rendering each in turn using $template
     * (overriding template previously set using setRecordTemplate), or
     * using the template previously set if no argument passed.
     * @param String
     * @return String
     * @see setRecordTemplate
     */
    public function render ($template = '') : string {

        if ($template !== '') {
            $this->setRecordTemplate($template);
        }
        $template = $this->getRecordTemplate();
        $ret = '';

        foreach ($this as $rec) {
            $ret .= $rec->render($template);
        }

        return $ret;

    }


    /**
     * How many records are there?
     * @return Int
     */
    public function count () {
        return count($this->records);
    }

    /**
     * Convenience method to give the correct plural ending
     * ('s' if there's not 1 record (i.e. zero or more than one)
     * @return String     's' or ''
     */
    public function plural () : string {
        return ($this->count() != 1 ? "s" : "");
    }


    /////// Iterator methods
    public function rewind () {
        $this->index = 0;
    }

    /**
     * @return Mixed
     */
    public function current () {
        $k = array_keys($this->records);
        $var = $this->records[$k[$this->index]];
        return $var;
    }

    /**
     * @return Mixed
     */
    public function key () {
        $k = array_keys($this->records);
        $var = $k[$this->index];
        return $var;
    }

    /**
     * @return Mixed | false
     */
    public function next () {
        $k = array_keys($this->records);
        if (isset($k[++$this->index])) {
            $var = $this->records[$k[$this->index]];
            return $var;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function valid () {
        $k = array_keys($this->records);
        $var = isset($k[$this->index]);
        return $var;
    }


}
