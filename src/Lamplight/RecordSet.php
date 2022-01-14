<?php
namespace Lamplight;

use Lamplight\RecordSet\Factory;

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
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history    1.2 Update for add profile functionality
 * @version    2.0 Refactored and updated
 */


/**
 *
 *
 * The Lamplight_RecordSet provides a container for Lamplight_Record* instances.
 * The RecordSet is constructed using the factory method, based on the
 * Lamplight_Client request object.
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history    1.2 Update for add profile functionality, and better handling of fetchOne() requests
 * @version    2.0 Refactored and updated
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
     * Constructor: called by factory method
     * @param array     Array of Lamplight_Record_* instances
     */
    public function __construct (array $records = array()) {
        $this->records = $records;
    }


    /**
     * Factory: creates a Lamplight_RecordSet filled with the appropriate
     * kind of Lamplight_Record* instances.
     * If there was a problem of some sort
     *    with the request, there won't be any records and getErrors() will
     *    provide more info: check errors before proceeding.
     *
     * @param Client $client Client that's made a request already
     * @param string $recordClass Name of the class to use for records
     *                                      (over-rides default based on request type)
     * @return RecordSet
     */
    public static function factory (Client $client, string $recordClass = '') : RecordSet {
        $factory = new Factory();
        return $factory->makeRecordSetFromData($client, $recordClass);
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
