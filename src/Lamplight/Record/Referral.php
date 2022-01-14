<?php
namespace Lamplight\Record;
use Lamplight\Record\Exception\MissingAttendeeException;

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
 * @history     1.2 Add/edit profile functionality
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    2.0 New version
 */


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
 * @version    2.0 New version
 * @history    1.2 Refactory to extend Lamplight_Record_Mutable
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 * @link       http://www.lamplight-publishing.co.uk/examples/addreferral.php  Worked example showing how to create a
 *     new referral record.
 *
 *
 */
class Referral extends Mutable {


    /**
     * We do allow editing referrals because we can make new ones
     * via the API
     * @param bool
     */
    protected bool $editable = true;


    /**
     * @var string        The method used for sending requests via the API
     */
    protected string $lamplightMethod = 'add';

    /**
     * @var string        The action used for sending requests via the API
     */
    protected string $lamplightAction = 'referral';


    /**
     * Sets the date of the referral.  If none passed, today
     * is set.
     *
     * @param string|\DateTimeInterface $date In YYYY-mm-dd HH:ii:ss format
     *
     * @return Referral
     */
    public function setDate ($date = '') : Referral {

        if (!$date) {
            $date = date('Y-m-d H:i:s');
        } else if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d H:i:s');
        } else {
            $date = date('Y-m-d H:i:s', strtotime($date));
        }
        $this->data['date'] = $date;
        return $this;

    }

    /**
     * Sets the referral reason
     *
     * @param string $reason
     *
     * @return Referral
     */
    public function setReason (string $reason = '') : Referral {
        $this->data['reason'] = $reason;
        return $this;
    }


    /**
     * Gets all the data for an API call.
     * Used by Lamplight_Client
     *
     * @return array
     * @throws MissingAttendeeException
     */
    public function toAPIArray () : array {

        $built_ins = array('date', 'workarea', 'attendee', 'reason');
        $return_array = array(
            'date_from' => $this->get('date'),
            'workareaid' => $this->get('workarea'),
            'attendee' => $this->get('attendee'),
            'referral_reason' => $this->get('reason')
        );

        foreach ($this->data as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $built_ins)) {
                $return_array[$fieldName] = $fieldValue;
            }
        }
        if ($return_array['attendee'] == '') {
            throw new MissingAttendeeException("Attendee has not been set but is not optional");
        }
        return $return_array;
    }

}
