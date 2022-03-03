<?php
namespace Lamplight\Record;
use Lamplight\Record\Exception\MissingAttendeeException;
use Lamplight\Record\Exception\MissingIdException;

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
 * @version     2.0 New version
 * @history     1.2 Add/edit profile functionality
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 */

/**
 *
 *
 * Lamplight_Record_Work holds detailed data about a work record and allows the
 * addition of attendees
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    2.0 New version
 * @history    1.2 No change
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
class Work extends Mutable {


    /**
     * @var String        The method used for sending requests via the API
     */
    protected string $lamplightMethod = 'attend';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected string $lamplightAction = 'work';


    /**
     * Gets all the data for an API call.  The only thing we can do
     * with work records via the API is add attendees
     *
     * Used by Lamplight_Client
     * @return array
     * @throws MissingIdException
     * @throws MissingAttendeeException
     */
    public function toAPIArray () : array {

        $ar = array(
            'attendee' => $this->get('attendee'),
            'id' => $this->get('id')
        );

        if ($ar['attendee'] == '') {
            throw new MissingAttendeeException("Attendee has not been set but is not optional");
        }
        if ($ar['id'] == '') {
            throw new MissingIdException("ID has not been set but is not optional");
        }
        return $ar;
    }


}
