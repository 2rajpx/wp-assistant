<?php

namespace assistant\helper;

use Inflector;

/**
 * using wordpress functions
 */
class WP {

	/**
	 * Run function if exist
	 * @param string $method camelCase method name
	 * @param array $args Arguments to send function
	 */
	public static function __callStatic($method, $args) {
		$function = Inflector::camel2Id($method, '_');
		if(function_exists($function)){
			return call_user_func_array($function, $args);
		}
	}

}