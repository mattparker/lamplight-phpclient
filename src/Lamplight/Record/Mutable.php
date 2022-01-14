<?php
namespace Lamplight\Record;
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
 * @version    1.2   Adds ability to add/update people and organisation profiles
 */



/**
 *
 *
 * Lamplight_Record_Mutable is an abstract extension of the base Record class to provide
 * mutability - ie Records that may be altered via the API.
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    1.2    Refactoring to separate out get and setting of data
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
abstract class Mutable extends BaseRecord {


    /**
     * @var Boolean       Whether this type of record is editable
     */
    protected $_editable = true;

    /**
     * @var String        The method used for sending requests via the API
     */
    protected $_lamplightMethod = '';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected $_lamplightAction = '';

    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return Array
     */
    abstract public function toAPIArray ();


    /**
     * Called by Lamplight_Client::save() before any preparations are carried out.
     * Provided for implementing class if required
     * @param Lamplight_Client
     */
    public function beforeSave (Lamplight_Client $client) {
    }

    /**
     * Called by Lamplight_Client::save() just after the request()
     * and before it returns.
     * Provided for implementing class if required
     * @param Lamplight_Client
     * @param Zend_Http_Response
     */
    public function afterSave (Lamplight_Client $client, Zend_Http_Response $response) {
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
     * Is this type of record editable?
     * @return Boolean
     */
    public function isEditable () {
        return $this->_editable;
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
            $this->data->{$field} = $value;
        }

        return $this;

    }


    /**
     * Sets the attendee for this record (can only be one, currently)
     * @param string | int                Email address or profile ID
     * @return Lamplight_Record_Abstract
     *
     */
    public function setAttendee ($attendee_identifier) {
        if ($this->_editable) {
            $this->data->attendee = $attendee_identifier;
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
        if (!$this->_editable) {
            return $this;
        }
        if (is_string($workareaID) && strstr($workareaID, ',')) {
            $this->data->workarea = $workareaID;
            return $this;
        }
        if (is_int($workareaID)) {
            $this->data->workarea = (int)$workareaID;
        }
        return $this;
    }


}
