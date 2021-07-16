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
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Update for add profile functionality
 */


/**
 *
 *
 * Lamplight_Record_Abstract provides a base class for concrete implementations
 * of different types of Record.
 *
 * @category   Lamplight
 * @package    Lamplight_Record
 * @copyright  Copyright (c) 2010, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @history    1.1 Update to include 'attend work' and 'add referrals' datain module functionality
 * @version    1.2 Minor changes for refactoring with Lamplight_Record_Mutable class
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *             client library
 *
 *
 */
abstract class Lamplight_Record_Abstract implements Iterator {


    /**
     * @var \stdClass        Data for this record
     */
    protected $_data;


    /**
     * @var int          Array pointer
     */
    protected $_index = 0;


    /**
     * Constructor.  Takes an object of data, keys are field names and values
     * data values.
     *
     * @param Object       stdClass object: properties are field names.
     */
    public function __construct ($data = null) {

        if (!$data) {
            $data = new stdClass();
        }
        $this->_data = $data;

    }


    /**
     * Initializer, called by the Lamplight_RecordSet::factory method
     * immediately after construction, for additional
     * work by implementing classes
     *
     * @param Lamplight_Client
     */
    public function init (Lamplight_Client $client) {
    }


    /**
     * Returns field value by key
     *
     * @param string $field    Field name
     *
     * @return string
     */
    public function get ($field) {

        if (is_object($this->_data) && property_exists($this->_data, $field)) {
            return trim($this->_data->{$field});
        }

        return '';
    }


    /**
     * Renders record data using a simple templating system
     *
     * @param string $template       Template to use.  If no template passed, will return
     *                     a comma-separated list of values.
     *
     * @return string
     */
    public function render ($template = '') {

        // If no template, just return comma-separated string:
        if ($template == '') {
            return Lamplight_Record_Abstract::implodeRecursive(", ", $this->_data);
        }

        preg_match_all("/\{([a-zA-Z0-9_]+)\}/", $template, $matches, PREG_PATTERN_ORDER);

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
     * @param string $field          Field name
     *
     * @return string
     */
    public function renderField ($field) {

        $val = $this->get($field);
        if (is_array($val)) {
            $val = Lamplight_Record_Abstract::implodeRecursive(", ", $val);
        }

        return htmlentities($val, ENT_QUOTES, "UTF-8");
    }


    /**
     * implode() like function, but recurses if elements are themselves arrays
     *
     * @param string $glue        Separator
     * @param array  $pieces         Of pieces to glue together
     *
     * @return string
     */
    public static function implodeRecursive ($glue, array $pieces) {

        $r = '';
        foreach ($pieces as $v) {
            if (is_array($v)) {
                $r .= self::implodeRecursive($glue, $pieces);
            } else {
                $r .= $glue . $v;
            }
        }

        return substr($r, strlen($glue));

    }


    /**
     * How many fields are there?
     *
     * @return int
     */
    public function count () {

        return count($this->_data);
    }


    /////// Iterator methods
    public function rewind () {

        $this->_index = 0;
    }

    /**
     * @return Mixed
     */
    public function current () {

        $k = array_keys($this->_data);
        $var = $this->_data[$k[$this->_index]];

        return $var;
    }

    /**
     * @return Mixed
     */
    public function key () {

        $k = array_keys($this->_data);
        $var = $k[$this->_index];

        return $var;
    }

    /**
     * @return Mixed | false
     */
    public function next () {

        $k = array_keys($this->_data);
        if (isset($k[++$this->_index])) {
            $var = $this->_data[$k[$this->_index]];

            return $var;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function valid () {

        $k = array_keys($this->_data);
        $var = isset($k[$this->_index]);

        return $var;
    }


}
