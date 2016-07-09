<?php

namespace assistant\helper;

/**
 * @link https://github.com/2rajpx/wp-assistant/
 * @license https://github.com/2rajpx/wp-assistant/blob/master/LICENSE
 */
/**
 * Manage sessions
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Session
{

    const MAIN_SEGMENT = '\Vendor\2rajpx\WpAssistant';

    /**
     * Control bit to find session is started
     * 
     * @var boolean $_init Holds thte control bit
     */
    private static $_init;

    /**
     * The session global variable
     * 
     * @var array $_session Holds the session global variable
     */
    private static $_segments = [];

    /**
     * The name of the segment
     * 
     * @var string $_segment Holds the name of th
     */
    protected $_segment;

    /**
     * Returns session object
     *
     * @param string $segment The name of the segment
     *
     * @return object The session object
     */
    public static function manager($segment = self::MAIN_SEGMENT) {
        if (!static::$_init && !session_id()) {
            session_start();
        }
        if (!isset(static::$_segments[$segment])) {
            static::$_segments[$segment] = new self($segment);
        }
        return static::$_segments[$segment];
    }

    /**
     * Make session segment
     *
     * @param string $segment The name of the segment
     *
     * @throws Exception If segment name be invalid
     */
    public function __construct($segment) {
        if (!is_string($segment)) {
            throw new \Exception("Segment name must be a string", 1);
        }
        $this->_segment = $segment;
    }

    /**
     * Unset the key from the segment
     *
     * @param string $key The name of the key  us.
     */
    public function __unset($key) {
        $this->delete($key);
    }

    /**
     * Set data in session
     *
     * Examplde 1 : set('foo', 'bar')
     * Examplde 2 : set([
     *     'foo' => 'bar',
     *     'baz' => 'dib',
     *     'zim' => 'gir',
     * ])
     * 
     * @param string|array
     * @param $value The value that must be saved in session
     *
     * @return Segment $this
     *
     * @throws Exception If arguments are invalid
     */
    public function set($a) {
        if (func_num_args()===2 && is_string($a)) {
            // If arguments are like example 1, set key and value
            $_SESSION[$this->_segment][$a] = func_get_arg(1);
        } elseif (func_num_args()===1 && is_array($a)) {
            // If arguments are like example 2, set keys and values
            foreach ($a as $key => $value) {
                // Set key and value
                $_SESSION[$this->_segment][$key] = $value;
            }
        } else {
            // If argument(s) be invalid
            throw new \Exception("Invalid argument(s) is passed", 1);
        }
        return $this;
    }

    /**
     * Get data from segment
     *
     * Examplde 1 : get() // Ruturns segment
     * Examplde 2 : get('foo') // Returns foo value or null
     * Examplde 3 : get('foo', 'bar') // Returns foo value or 'bar'
     * 
     * @param string $key The key, It's can be null to return all data
     * @param $alt The alternative value, 
     *
     * @return The value(s)
     *
     * @throws Exception If arguments are invalid
     */
    public function get() {
        if (func_num_args()===0) {
            // Ruturns segment
            return $_SESSION[$this->_segment];
        } elseif (func_num_args()===1) {
            // Returns key, if the key not found returns null
            return $_SESSION[$this->_segment][func_get_arg(0)] ?: null;
        } elseif (func_num_args()===2) {
            // Returns key, if the key not found returns alernative value
            return $_SESSION[$this->_segment][func_get_arg(0)] ?: func_get_arg(1);
        } else {
            // If argument(s) be invalid
            throw new \Exception("Invalid argument(s) is passed", 1);
        }
    }

    /**
     * Unset key from segment
     * 
     * @param string $key The key name. You can specify additional
     * strings via second argument, third argument, fourth argument etc.
     *
     * @return Segment $this
     */
    public function delete() {
        try {
            foreach (func_get_args() as $key) {
                unset($_SESSION[$this->_segment][$key]);
            }
        } catch (\Exception $e) {

        }
        return $this;
    }

    /**
     * Set flash
     * 
     * @param string $key Flash key
     * @param $value The value of the flash
     */
    public function setFlash($key, $value) {
        return $this->set('_flash_'.$key, $value);
    }

    /**
     * Get key value and delete it from segment
     * 
     * @param string $key Flash key
     *
     * @return The values related to the key
     */
    public function getFlash($key) {
        $key = '_flash_'.$key;
        $value = $this->get($key);
        $this->delete($key);
        return $value;
    }

}