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
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Update to allow adding and updating people and org records.  MutableProfile
 *             adds profile-specific methods for editing.
 */

/**
 *
 *
 * Lamplight_Record_People holds detailed data about a person
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version     1.2 extends Lamplight_Record_Mutable to enable editing
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
class MutableProfile extends Mutable {


    /**
     * The role (user/staff etc) for this record
     * @param String
     */
    protected $_role = '';


    /**
     * @var String        The method used for sending requests via the API
     *                      This may change to update if there's an ID
     */
    protected $_lamplightMethod = 'add';


    /**
     * Sets the role of the record
     */
    public function init (Lamplight_Client $client) {

        $this->_role = $client->getParameter('role');

    }


    /**
     * Sets the role if it's not yet set
     * @param String
     * @return Lamplight_Record_People
     */
    public function setRole ($role) {
        if ($this->_role === '') {
            $this->_role = (string)$role;
        }
        return $this;
    }


    /**
     * Called by Lamplight_Client::save() before any preparations are carried out.
     * Sets add or update as needed
     * @param Lamplight_Client
     */
    public function beforeSave (Lamplight_Client $client) {
        if ($this->get('id') > 0) {
            $this->_lamplightMethod = 'update';
        } else {
            $this->_lamplightMethod = 'add';
        }
    }

    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return Array
     */
    public function toAPIArray () {
        $ar = array(
            'role' => $this->_role
        );

        foreach ($this->_data as $fieldName => $fieldValue) {
            $ar[$fieldName] = $fieldValue;
        }


        return $ar;
    }


}
