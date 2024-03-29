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
 * @history      1.2 Update to allow adding and updating people and org records
 * @version     2.0 New version
 */
 

 
/**
 *
 *
 * Family holds detailed data about a family
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history      1.2 extends Lamplight_Record_MutableProfile to enable editing
 * @version     2.0 New version
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the client library   
 *
 *
 */


class Family extends MutableProfile {


    /**
     * @var String        The action used for sending requests via the API
     */
    protected string $lamplightAction = 'family';

}
