<?php

namespace assistant\form;

class Factory {

	/**
	 * Builds the field and returns it
	 * @param string $name field name
	 * @param array $name field options
	 * @return Field instance
	 */
	public static function field($name, $options = []){
		if(!isset($options['class'])){
			$options['class'] = __NAMESPACE__ . '\\Text';
		}
		$class = $options['class'];
		return new $class($name, $options);
	}

}