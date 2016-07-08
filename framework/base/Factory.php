<?php

namespace assistant\base;

use assistant\exception\ExceptionHandler as Exception;

/**
 * @link https://github.com/2rajpx/wp-assistant/
 * @license https://github.com/2rajpx/wp-assistant/blob/master/LICENSE
 */
/**
 * The base factory class to make instance from other classes
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Factory
{

    /**
     * Returns an instance of the object
     *
     * @param array $config Object properties
     * @return object
     */
    public static function getInstance(array $config = []){
        // If class element is unset
        if(!isset($config['class'])){
            throw new Exception("You have to set class element", 1);
        }
        $class = $config['class'];
        unset($config['class']);
        return new $class($config);
    }

}
