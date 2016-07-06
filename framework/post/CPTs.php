<?php

namespace assistant\post;

use assistant\helper\Inflector;
use assistant\Exception\ExceptionHandler as Exception;
use CPT as MainCPT;

/**
 * Use CPT class methods and properties by camelCase invoke.
 * @author Tooraj Khatibi
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @see https://github.com/jjgrainger/wp-custom-post-type-class
 */
class CPT {

	/**
	 * @var CPT $_cptObject
	 */
	private $_cptObject;

	public function __construct(array $post_type_names, array $options = [])
	{
		// Make a instance of CPT
		$this->_cptObject = new MainCPT($post_type_names, $options);
	}

	/**
	 * Run related method of the CPT object
	 *
	 * @param string $method camelCase method name
	 * @param array $args Arguments to send function
	 *
	 * @return result of CPT::{{method}}()
	 *
	 * @throws Exception if there is not method in CPT
	 *
	 * @see CPT
	 */
	public function __call($method, $args)
	{
		// Make CPT method name
		$method = Inflector::camel2id($method, '_');
		// Check method existing
		if (method_exists($this->_cptObject, $method)) {
			// Return result of the method
			return call_user_func_array([$this->_cptObject, $method], $args);
		} else {
			// Throw exception
			throw new Exception("There is not CPT::$method() name", 1);
		}
	}

}
