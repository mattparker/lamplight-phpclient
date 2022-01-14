<?php
namespace Lamplight\Record;
use Lamplight\Client;
use Lamplight\Response;
use Lamplight\Response\SuccessResponse;

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
 * @history    1.2   Adds ability to add/update people and organisation profiles
 * @version    2.0 New version
 */



/**
 *
 *
 * Lamplight\Record\Mutable is an abstract extension of the base Record class to provide
 * mutability - ie Records that may be altered via the API.
 *
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history     1.2    Refactoring to separate out get and setting of data
 * @version    2.0 New version
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
abstract class Mutable extends BaseRecord {


    /**
     * @var Boolean       Whether this type of record is editable
     */
    protected bool $editable = true;

    /**
     * @var String        The method used for sending requests via the API
     */
    protected string $lamplightMethod = '';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected string $lamplightAction = '';

    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return array
     */
    abstract public function toAPIArray () : array;


    /**
     * Called by Lamplight_Client::save() before any preparations are carried out.
     * Provided for implementing class if required
     * @param Client
     */
    public function beforeSave (Client $client) {
    }

    /**
     * Called by Lamplight_Client::save() just after the request()
     * and before it returns.
     * Provided for implementing class if required
     * @param Client $client
     * @param Response $response
     */
    public function afterSave (Client $client, Response $response) {
    }


    /**
     * Used by Lamplight_Client to construct the URL
     * @return String
     */
    public function getLamplightMethod () : string {
        return $this->lamplightMethod;
    }

    /**
     * Used by Lamplight_Client to construct the URL
     * @return String
     */
    public function getLamplightAction () : string {
        return $this->lamplightAction;
    }

    /**
     * Is this type of record editable?
     * @return Boolean
     */
    public function isEditable () : bool {
        return $this->editable;
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
     * @return BaseRecord
     */
    public function set ($field, $value) : BaseRecord {


        if (!$this->editable) {
            throw new \Exception("You cannot change this type of Record");
        }
        if (!is_string($field)) {
            throw new \Exception("Fields to be set must be strings");
        }


        // Look for a setter:
        // Construct and then check the method name, calling it if OK:
        $methodName = 'set' . ucfirst(strtolower($field));
        if (method_exists($this, $methodName) && is_callable(array($this, $methodName))) {
            call_user_func(array($this, $methodName), $value);
        } else {
            $this->data[$field] = $value;
        }

        return $this;

    }


    /**
     * Sets the attendee for this record (can only be one, currently)
     * @param string | int                Email address or profile ID
     * @return BaseRecord
     *
     */
    public function setAttendee ($attendee_identifier) : BaseRecord {
        if ($this->editable) {
            $this->data['attendee'] = $attendee_identifier;
        }
        return $this;
    }

    /**
     * Sets the workarea for this record
     * @param Int                Workarea ID or comma separated string of IDs
     * @return BaseRecord
     *
     */
    public function setWorkarea ($workareaID) : BaseRecord {
        if (!$this->editable) {
            return $this;
        }
        if (is_string($workareaID) && strstr($workareaID, ',')) {
            $this->data['workarea'] = $workareaID;
            return $this;
        }
        if (is_int($workareaID)) {
            $this->data['workarea'] = (int)$workareaID;
        }
        return $this;
    }


}
