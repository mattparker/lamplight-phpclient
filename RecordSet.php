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
 * @version    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
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
 * @version    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the client library   
 *
 */
class Lamplight_RecordSet implements Iterator {



   /**
    * True if there were errors getting data from response
    * @var  Boolean  
    */
   protected $_errors = false;
   
   /**
    * Array of Lamplight_Record_* instances
    * @var Array    
    */
   protected $_records = array();
   
   
   /** 
    * Error code returned by server or locally
    * @var Int
    */
   protected $_errorCode = 0;
   
   /**
    * Error message returned by server or locally
    * @var String
    */
   protected $_errorMessage = '';
   
   /**
    * HTTP response status
    * @var Int
    */
   protected $_responseStatus = 0;
   
   
   /**
    *  Array pointer
    * @var Int     
    */
   protected $_index = 0;
   
   
   /**
    *  Template used to render Records
    * @var String  
    */
   protected $_recordTemplate = '';
   
   
   /**
    * Base record class, used when constructing Records from the data
    * @var String   
    */
   protected static $_baseRecordClassName = 'Lamplight_Record';



   /**
    * Constructor: called by factory method
    * @param Array     Array of Lamplight_Record_* instances
    */
   protected function __construct(array $records = array()) {
     $this->_records = $records;
   }

   
   
   
   
   
   /**
    * Factory: creates a Lamplight_RecordSet filled with the appropriate
    * kind of Lamplight_Record* instances.
    * If there was a problem of some sort
    *    with the request, there won't be any records and getErrors() will
    *    provide more info: check errors before proceeding.
    * @param Lamplight_Client $client     Client that's made a request already
    * @param String $recordClass          Name of the class to use for records
    *                                      (over-rides default based on request type)
    * @return Lamplight_RecordSet         
    */
   public static function factory(Lamplight_Client $client, $recordClass = '') {
   
     $action  = $client->getLastLamplightMethod();
     $method  = $client->getLastLamplightAction();
     $resp    = $client->getLastResponse();
     $format  = $client->getResponseFormat();
     $records = array();
     $errors  = false;
     
     $status = $resp->getStatus();
     
     // Check we've got a response OK:
     if ($resp && !$resp->isError() && $status == 200) {
     
       // Work out what kind of object to fill with
       if ($recordClass == '') {
         $recordClass = self::_buildRecordClassName($action, $method);
       }
       require_once str_replace('_', '/', $recordClass) . '.php';
       
       $data = self::_parseResponseBody($resp->getBody(), $format);
       
       if ($data === false) {
         $errors = true;
       } elseif (is_object($data) && property_exists($data, 'data')) {
         foreach($data->data as $rec) {
           $newRec = new $recordClass($rec);
           $records[$newRec->get('id')] = $newRec;
         }
       }
       

     
     } else {
       // error state
       $errors = true;

     }
     
     // Construct the RecordSet:
     $rs = new Lamplight_RecordSet($records);
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
   * @param String $action    The 'action' (kind of data - work, workarea, people, orgs)
   * @method String $method   The 'method' (one|some|all)
   * @return String
   */
  protected static function _buildRecordClassName($action, $method) {
       
     $class = self::$_baseRecordClassName . '_' . ucfirst($method);
     if ($action != 'one') {
       $class .= 'Summary';
     }
     return $class;
  }
  
  
  
  /**
   * Takes response body and decodes and parses it.
   * Will get an array-like
   *  object that can be used to contruct records
   * @param String $data         Data returned in response body
   * @param String $format       Default 'json'.  Others may be added in future.
   * @return Array|Object
   */
  protected static function _parseResponseBody($data, $format = 'json') {
  
    switch($format) {
      case 'json':
      default:
        require_once 'Zend/Json.php';
        try{
          return Zend_Json::decode($data, Zend_Json::TYPE_OBJECT);
        } catch (Exception $e) {
          return false;
        } 
        break;
    }
  
  }
  
  
  /**
   * Sets error state
   * @param Boolean
   */ 
  public function setErrors($error) {
    $this->_errors = $error;
  }
  
  /**
   * Were there any errors with the request?
   * @return Boolean   True is there were errors
   */
  public function getErrors() {
    return $this->_errors;
  }
  
  
  /**
   * Set the error code returned by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @param Int
   * @return Lamplight_RecordSet      Fluent interface
   */  
  public function setErrorCode($c) {
    $this->_errorCode = $c;
    return $this;
  }
  
  
  /**
   * Get the error code returned by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @return Int
   */
  public function getErrorCode() {
    return $this->_errorCode;
  } 


  /**
   * Set the error code message by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @param String
   * @return Lamplight_RecordSet      Fluent interface
   */  
  public function setErrorMessage($msg) {
    $this->_errorMessage = $msg;
    return $this;
  }
  
  
  
  /**
   * Get the error code message by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @return String
   */  
  public function getErrorMessage() {
    return $this->_errorMessage;
  } 
  


  
  /**
   * Get the http response status returned by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @param Int
   * @return Lamplight_RecordSet      Fluent interface
   */
  public function setResponseStatus($status) {
    $this->_responseStatus = $status;
    return $this;
  }
  
   
  
  /**
   * Get the http response status returned by the server
   * @link http://www.lamplight-publishing.co.uk/api/core.php#errors
   * @return Int
   */
  public function getResponseStatus() {
    return $this->_responseStatus;
  }
  
  
  
  /**
   * Sets the template to use for each record when rendering.
   * The record will scan through the string lookoing for expressions
   * enclosed in {} braces, using the expression found as the field
   * identifier e.g. <div>Name: {name}</div><div>ID: {id}</div>
   * @param String $template          Template to use for records
   * @return Lamplight_RecordSet      Fluent interface
   */
  public function setRecordTemplate($template) {
    $this->_recordTemplate = $template;
    return $this;
  }
  
  
  /**
   * Getter for the record template
   * @return String
   */
  public function getRecordTemplate() {
    return $this->_recordTemplate;
  }
  
  
  
  /**
   * Iterates throught the records, rendering each in turn using $template
   * (overriding template previously set using setRecordTemplate), or 
   * using the template previously set if no argument passed.
   * @param String       
   * @see setRecordTemplate
   * @return String
   */
  public function render($template = '') {
  
    if ($template !== '') {
      $this->setRecordTemplate($template);
    }
    $template = $this->getRecordTemplate();
    $ret = '';
    
    foreach($this as $rec) {
      $ret .= $rec->render($template);
    }
    
    return $ret;
  
  }
  
  
  
  
  /**
   * How many records are there?
   * @return Int
   */
  public function count() {
    return count($this->_records);
  }
  
  /**
   * Convenience method to give the correct plural ending 
   * ('s' if there's not 1 record (i.e. zero or more than one)
   * @return String     's' or ''
   */
  public function plural() {
    return ($this->count() != 1 ? "s" : "");
  }
  
  
  
  
  /////// Iterator methods
  public function rewind(){

        $this->_index = 0;
  }
  /**
   * @return Mixed
   */
  public function current(){
        $k = array_keys($this->_records);
        $var = $this->_records[$k[$this->_index]];
        return $var;
    }
  /**
   * @return Mixed
   */
    public function key()
    {
        $k = array_keys($this->_records);
        $var = $k[$this->_index];
        return $var;
    }

  /**
   * @return Mixed | false
   */
    public function next()
    {
        $k = array_keys($this->_records);
        if (isset($k[++$this->_index])) {
            $var = $this->_records[$k[$this->_index]];
            return $var;
        } else {
            return false;
        }
    }
    /**
     * @return Boolean
     */
    public function valid()
    {
        $k = array_keys($this->_records);
        $var = isset($k[$this->_index]);
        return $var;
    }



}
