<?php
namespace Lamplight\Record;

use Lamplight\Client;

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
 * @history     1.2 Update to allow adding and updating people and org records.  MutableProfile
 *             adds profile-specific methods for editing.
 * @version    2.0 New version
 */

/**
 *
 *
 * Lamplight_Record_People holds detailed data about a person
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history    1.2 extends Lamplight_Record_Mutable to enable editing
 * @version    2.0 New version
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
abstract class MutableProfile extends Mutable {


    /**
     * The role (user/staff etc) for this record
     * @param string
     */
    protected string $role = '';


    /**
     * @var string        The method used for sending requests via the API
     *                      This may change to update if there's an ID
     */
    protected string $lamplightMethod = 'add';


    /**
     * Sets the role of the record
     */
    public function init (Client $client) {

        $this->role = $client->getParameter('role');

    }


    /**
     * Sets the role if it's not yet set
     * @param String
     * @return MutableProfile
     */
    public function setRole (string $role) : MutableProfile {
        if ($this->role === '') {
            $this->role = $role;
        }
        return $this;
    }


    /**
     * Called by Lamplight_Client::save() before any preparations are carried out.
     * Sets add or update as needed
     * @param Client $client
     */
    public function beforeSave (Client $client) {
        if ($this->get('id') > 0) {
            $this->lamplightMethod = 'update';
        } else {
            $this->lamplightMethod = 'add';
        }
    }

    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return array
     */
    public function toAPIArray () : array {
        $ar = array(
            'role' => $this->role
        );

        foreach ($this->data as $fieldName => $fieldValue) {
            $ar[$fieldName] = $fieldValue;
        }


        return $ar;
    }


}
