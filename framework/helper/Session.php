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
/**
 * @example Call session function
 * session_cache_expire(30)
 * Session::cacheExpire(30);
 *
 * $_SESSION['test']
 * $manager = Session::manager('test');
 *
 * @example Set types
 * $_SESSION['test']['foo'] = 'bar'
 * $manager->set('foo', 'bar');
 *
 * $_SESSION['test']['baz'] = 'dib'
 * $_SESSION['test']['zim'] = 'gir'
 * $manager->set([
 *     'baz' => 'dib',
 *     'zim' => 'gir',
 * ]);
 * 
 * $_SESSION['test']['baz'] = 'bar'
 * $manager->baz = 'bar';
 *
 * @example Get types
 * $temp = $_SESSION['test']
 * $temp = $manager->get();
 * 
 * $temp = $_SESSION['test']['foo'] ?: null
 * $temp = $manager->get('foo');
 * 
 * $temp = $_SESSION['test']['foo'] ?: 'oops'
 * $temp = $manager->get('foo', 'oops');
 *
 * @example Unset types
 * unset($_SESSION['test']['foo'])
 * unset($_SESSION['test']['baz'])
 * unset($_SESSION['test']['zim'])
 * $manager->delete('foo', 'baz', 'zim');
 * 
 * unset($_SESSION['test'])
 * $manager->delete();
 *
 * @example Call session function
 * session_destroy();
 * Session::destroy();
*/
class Session
{

    const MASTER_SEGMENT = '\Vendor\2rajpx\WpAssistant';

    /**
     * Control bit to find session is started
     * 
     * @var boolean $_init Holds thte control bit
     */
    private static $_init = false;

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
     * Run all session functions by camelCase name
     * 
     * @example session_start => Session::satrt()
     * @example session_destroy => Session::destroy()
     * 
     * @param string $func The session function must be run
     * @param array $args The arguments must be passed to the function
     *
     * @return The result of function
     */
    public static function __callStatic($func, $args) {
        // Turn camelCase to 
        $func = preg_replace('/([a-z])([A-Z])/', '$1_$2', $func);
        $func = strtolower($func);
        $func = 'session_'.$func;
        if(function_exists($func)){
            return call_user_func_array($func, $args);
        }
        throw new Exception("Function $func is undefiend", 1);
    }   

    /**
     * Returns session object
     *
     * @param string $segment The name of the segment
     *
     * @return object The session object
     */
    public static function manager($segment = self::MASTER_SEGMENT) {
        if (!static::$_init && !static::id()) {
            static::start();
            static::$_init = true;
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
     * Magic method
     * Set data in session
     * 
     * @param array $key
     * @param $value The value that must be saved in session
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    /**
     * Get data from segment
     * 
     * @param string $key The key
     *
     * @return The value(s)
     *
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Set data in session
     *
     * set('foo', 'bar')
     * set([
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
     * get() // Ruturns segment
     * get('foo') // Returns foo value or null
     * get('foo', 'bar') // Returns foo value or 'bar'
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
     * Unset key|segment from segment|session
     *
     * delete('foo') // Delete 'foo' from the segment
     * delete('foo', 'bar', 'baz') // Delete 'foo', 'bar', 'baz' from the segment
     * delete() // Unset the segment
     * 
     * @param string $key The key name. You can specify additional
     * strings via second argument, third argument, fourth argument etc.
     *
     * @return Segment $this
     */
    public function delete() {
        if(func_num_args()===0){
            unset($_SESSION[$this->_segment]);
        } else {
            foreach (func_get_args() as $key) {
                unset($_SESSION[$this->_segment][$key]);
            }
            return $this;
        }
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