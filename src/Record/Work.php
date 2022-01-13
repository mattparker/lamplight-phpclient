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
require_once 'Lamplight/Record/Abstract.php';
 
/**
 *
 *
 * Lamplight_Record_Work holds detailed data about a work record and allows the
 * addition of attendees
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    1.2 No change
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the client library   
 *
 *
 */


class Lamplight_Record_Work extends Lamplight_Record_Abstract {


    /**
     * We do allow editing work records because we can add attendees
     * @param Boolean
     */
    protected $_editable = true;


    /**
     * @var String        The method used for sending requests via the API
     */
    protected $_lamplightMethod = 'attend';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected $_lamplightAction = 'work';


    /**
     * Gets all the data for an API call.  The only thing we can do
     * with work records via the API is add attendees
     *
     * Used by Lamplight_Client
     * @return Array
     */
    public function toAPIArray () {

        $ar = array(
            'attendee' => $this->get('attendee'),
            'id' => $this->get('id')
        );

        if ($ar['attendee'] == '' || $ar['id'] == '') {
            throw new Exception("Attendee has not been set but is not optional");
        }
        return $ar;
    }


}
