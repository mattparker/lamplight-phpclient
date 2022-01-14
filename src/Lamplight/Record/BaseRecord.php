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
 * @history    1.2 Update for add profile functionality
 * @version    2.0 New version
 */


/**
 *
 *
 * Lamplight_Record_Abstract provides a base class for concrete implementations
 * of different types of Record.
 *
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @history    1.2 Minor changes for refactoring with Lamplight_Record_Mutable class
 * @version    2.0 New version
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *             client library
 *
 *
 */
abstract class BaseRecord implements \Iterator {


    /**
     * @var array        Data for this record
     */
    protected array $data = [];


    /**
     * @var int          Array pointer
     */
    protected int $index = 0;


    /**
     * Constructor.  Takes an object of data, keys are field names and values
     * data values.
     *
     * @param Object       stdClass object: properties are field names.
     */
    public function __construct (array $data = null) {

        if (!$data) {
            $data = [];
        }
        $this->data = $data;

    }


    /**
     * Initializer, called by the Lamplight_RecordSet::factory method
     * immediately after construction, for additional
     * work by implementing classes
     *
     * @param Client
     */
    public function init (Client $client) {
    }


    /**
     * Returns field value by key
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function get ($field) {

        if (array_key_exists($field, $this->data)) {
            if (is_string($this->data[$field])) {
                return trim($this->data[$field]);
            }
            return $this->data[$field];

        }

        return '';
    }


    /**
     * Renders record data using a simple templating system
     *
     * @param string $template Template to use.  If no template passed, will return
     *                     a comma-separated list of values.
     *
     * @return string
     */
    public function render (string $template = '') : string {

        // If no template, just return comma-separated string:
        if ($template == '') {
            return $this->implodeRecursive(", ", $this->data);
        }

        preg_match_all("/{([a-zA-Z0-9_]+)}/", $template, $matches, PREG_PATTERN_ORDER);

        $ret = $template;
        if ($matches && $matches[1]) {
            foreach ($matches[1] as $m) {
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
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function renderField ($field) {

        $val = $this->get($field);
        if (is_array($val)) {
            $val = $this->implodeRecursive(", ", $val);
        }

        return htmlentities($val, ENT_QUOTES, "UTF-8");
    }


    /**
     * implode() like function, but recurses if elements are themselves arrays
     *
     * @param string $glue Separator
     * @param array $data Of pieces to glue together
     *
     * @return string
     */
    public function implodeRecursive (string $glue, iterable $data) : string {

        $escaped_strings = [];
        foreach ($data as $value) {
            if (is_array($value)) {
                $escaped_strings[] = $this->implodeRecursive($glue, $value);
            } else {
                $escaped_strings[] = htmlentities($value, ENT_QUOTES, "UTF-8");
            }
        }
        return implode($glue, $escaped_strings);

    }


    /**
     * How many fields are there?
     *
     * @return int
     */
    public function count () {

        return count($this->data);
    }


    /////// Iterator methods
    public function rewind () {

        $this->index = 0;
    }

    /**
     * @return Mixed
     */
    public function current () {

        $k = array_keys($this->data);
        $var = $this->data[$k[$this->index]];

        return $var;
    }

    /**
     * @return Mixed
     */
    public function key () {

        $k = array_keys($this->data);
        $var = $k[$this->index];

        return $var;
    }

    /**
     * @return Mixed | false
     */
    public function next () {

        $k = array_keys($this->data);
        if (isset($k[++$this->index])) {
            $var = $this->data[$k[$this->index]];

            return $var;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function valid () {

        $k = array_keys($this->data);
        $var = isset($k[$this->index]);

        return $var;
    }


}
