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
 * @version    1.2 Add/edit profile functionality
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 */
 
require_once 'Lamplight/Record/Mutable.php';

 
/**
 *
 *
 * Lamplight_Record_Referral allows the rendering of existing referral records and the
 * creation of new ones.
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    1.2 Refactory to extend Lamplight_Record_Mutable
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the client library   
 * @link       http://www.lamplight-publishing.co.uk/examples/addreferral.php  Worked example showing how to create a new referral record.
 *
 *
 */


class Lamplight_Record_Referral extends Lamplight_Record_Mutable {


    /**
     * We do allow editing referrals because we can make new ones
     * via the API
     * @param Boolean
     */
    protected $_editable = true;


    /**
     * @var String        The method used for sending requests via the API
     */
    protected $_lamplightMethod = 'add';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected $_lamplightAction = 'referral';


    /**
     * Sets the date of the referral.  If none passed, today 
     * is set
     * @param String                In YYYY-mm-dd HH:ii:ss format
     * @return Lamplight_Record_Referral
     *
     */
    public function setDate ($date = '') {

        if (!is_string($date)) {
            throw new Exception("Date must be a string");
        }

        if (!$date) {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = date('Y-m-d H:i:s', strtotime($date));
        }
        $this->_data->date = $date;
        return $this;

    }

    /**
     * Sets the referral reason 
     * @param String                
     * @return Lamplight_Record_Referral
     *
     */
    public function setReason ($reason = '') {
        if (!is_string($reason)) {
            throw new Exception("Referral reason must be a string");
        }
        $this->_data->reason = (string)$reason;
        return $this;
    }




    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     * @return Array
     */
    public function toAPIArray () {

        $built_ins = array('date', 'workarea', 'attendee', 'reason');
        $ar = array(
            'date_from' => $this->get('date'),
            'workareaid' => $this->get('workarea'),
            'attendee' => $this->get('attendee'),
            'referral_reason' => $this->get('reason')
        );

        foreach ($this->_data as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $built_ins)) {
                $ar[$fieldName] = $fieldValue;
            }
        }
        if ($ar['attendee'] == '') {
            throw new Exception("Attendee has not been set but is not optional");
        }
        return $ar;
    }

}
