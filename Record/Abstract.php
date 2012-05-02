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
 * Lamplight_Record_Abstract provides a base class for concrete implementations
 * of different types of Record.
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the client library   
 *
 *
 */



abstract class Lamplight_Record_Abstract implements Iterator {




  /**
   * @var Array        Data for this record
   */
  protected $_data = array();
  
  
  /**
   * @var Int          Array pointer
   */
  protected $_index = 0;

  /**
   * @var Boolean       Whether this type of record is editable
   */
  protected $_editable = false;

  /**
   * @var String        The method used for sending requests via the API
   */
  protected $_lamplightMethod = '';

  /**
   * @var String        The action used for sending requests via the API
   */
  protected $_lamplightAction = '';


  /**
   * Constructor.  Takes an object of data, keys are field names and values
   * data values.
   * @param Object       stdClass object: properties are field names.
   */
  public function __construct($data = null) {

      if (!$data) {
          $data = new stdClass();
      }
      $this->_data = $data;

  }
  
  
  /**
   * Returns field value by key
   * @param String     Field name
   * @return Mixed
   */  
  public function get($field) { 
    if (is_object($this->_data) && property_exists($this->_data, $field)) {
      return trim($this->_data->{$field});
    }
    return '';
  }
  
  
  /**
   * Renders record data using a simple templating system
   * @param String       Template to use.  If no template passed, will return
   *                     a comma-separated list of values.
   * @return String
   */
  public function render($template = '') {
     
     // If no template, just return comma-separated string:
     if ($template ==  '') {
       return Lamplight_Record_Abstract::implodeRecursive(", ", $this->_data);
     }
     
     preg_match_all("/\{([a-zA-Z0-9_]+)\}/", $template, $matches, PREG_PATTERN_ORDER);

     $ret = $template;
     if ($matches && $matches[1]) {
       foreach($matches[1] as $m) {
         $val = $this->renderField($m);
         $ret = str_replace("{" . $m . "}", $val, $ret);
       }
     }
     return $ret;
    
  }


  /**
   * Renders a particular field.  By default, values that are arrays are recursively
   * comma-separated.  The return value is passed through htmlentities.
   * Implementations may override this method in the subclasses to provide custom
   * formatting etc.
   * @param String          Field name
   * @return String
   */  
  public function renderField($field) {
     $val = $this->get($field);
     if (is_array($val)) {
       $val = Lamplight_Record_Abstract::implodeRecursive(", ", $val);
     }  
     return htmlentities($val, ENT_QUOTES, "UTF-8");
  }
  
  
  /**
   * implode() like function, but recurses if elements are themselves arrays
   * @param String        Separator
   * @param Array         Of pieces to glue together
   * @return String
   */
  public static function implodeRecursive($glue, array $pieces) {
  
    $r = '';
    foreach($pieces as $v) {
      if (is_array($v)) {
        $r .= self::implodeRecursive($glue, $pieces);
      } else {
        $r .= $glue . $v;
      }
    }
    
    return substr($r, strlen($glue));
  
  }






    /////////////////////////////////////////////////////
    //
    // Used to create new records.  Saving happens by Lamplight_Client
    //


    /**
     * Sets the value of a field.  Will call setFieldname($value) where Fieldname
     * is the field passed, if it exists.  If not will just set the property on the
     * _data object
     * @param String                 Name of the field
     * @param Mixed                  Value to set.
     * @return Lamplight_Record_Abstract
     */
    public function set ($field, $value) {


        if (!$this->_editable) {
            throw new Exception("You cannot change this type of Record");
        }
        if (!is_string($field)) {
            throw new Exception("Fields to be set must be strings");
        }


        // Look for a setter:
        // Construct and then check the method name, calling it if OK:
        $methodName = 'set' . ucfirst(strtolower($field));
        if (method_exists($this, $methodName) && is_callable(array($this, $methodName))) {
            call_user_func(array($this, $methodName), $value);
        } else {
            $this->_data->{$field} = $value;
        }

        return $this;

    }

    /**
     * Is this type of record editable?
     * @return Boolean
     */
    public function isEditable () {
        return $this->_editable;
    }

    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return Array
     */
    public function toAPIArray () {
        return array();
    }


    /**
     * Used by Lamplight_Client to construct the URL
     * @return String
     */
    public function getLamplightMethod () {
        return $this->_lamplightMethod;
    }

    /**
     * Used by Lamplight_Client to construct the URL
     * @return String
     */
    public function getLamplightAction () {
        return $this->_lamplightAction;
    }

    /**
     * Sets the attendee for this record (can only be one, currently)
     * @param String                Email address           
     * @return Lamplight_Record_Abstract
     *
     */
    public function setAttendee ($emailAddress) {
        if ($this->_editable && is_string($emailAddress)) {
            $this->_data->attendee = $emailAddress;
        }
        return $this;
    }

    /**
     * Sets the workarea for this record (can only be one, currently)
     * @param Int                Workarea ID           
     * @return Lamplight_Record_Abstract
     *
     */
    public function setWorkarea ($workareaID) {
        if ($this->_editable && is_int($workareaID)) {
            $this->_data->workarea = (int)$workareaID;
        }
        return $this;
    }



  /**
   * How many records are there?
   * @return Int
   */
  public function count() {
    return count($this->_data);
  }
  
  
  
  
  
  /////// Iterator methods
  public function rewind(){

        $this->_index = 0;
  }

  /**
   * @return Mixed
   */
  public function current(){
        $k = array_keys($this->_data);
        $var = $this->_data[$k[$this->_index]];
        return $var;
    }
  /**
   * @return Mixed
   */
    public function key()
    {
        $k = array_keys($this->_data);
        $var = $k[$this->_index];
        return $var;
    }

  /**
   * @return Mixed | false
   */
    public function next()
    {
        $k = array_keys($this->_data);
        if (isset($k[++$this->_index])) {
            $var = $this->_data[$k[$this->_index]];
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
        $k = array_keys($this->_data);
        $var = isset($k[$this->_index]);
        return $var;
    }


}
