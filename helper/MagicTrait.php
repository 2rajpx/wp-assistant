<?php

namespace assistant\helper;

use assistant\exception\ExceptionHandler as Exception;

trait MagicTrait{

	public $naming = Naming::UNDER_SCORE;
	public $namingSeperator = '_';

	protected $_dynaMethods = [
		// 'prefix' => 'dynamicMethod'
		'set'	=> '_dynaSet',
		'add'	=> '_dynaAdd',
		'append'=> '_dynaAppend',
		'delete'=> '_dynaDelete',
		'merge'	=> '_dynaMerge',
		''		=> '_dynaGet',
	];
	protected $_properties = [];

	public function properties(){
		return $this->_properties;
	}

	public function setProperties($properties){
		foreach ($properties as $property => $value) {
			$this->_setProperty($property, $value);
		}
		return $this;
	}

	public function mergeProperties(){
		$arrays = func_get_args();
		array_unshift($arrays, (array) $this->properties());
		return $this->setProperties(call_user_func_array([__NAMESPACE__ . '\ArrayHelper', 'merge'], $arrays));
	}

	public function __call($method, $args){
		foreach ($this->_dynaMethods as $prefix => $dynaMethod) {
			if(preg_match('/^'.$prefix.'/', $method)){
				if(substr($method, -1) === 'S'){
					$method = substr($method, 0, count($method)-2);
					$multiple = true;
				}else{
					$multiple = false;
				}
				$property = $this->_naming($method, strlen($prefix));
				return $this->$dynaMethod($property, $args, $multiple);
			}
		}
		$this->_exception(get_class($this)."::$method() is undefined!", 1);
	}

	public function __set($property, $value){
		$property = $this->_naming($property, 0);
		$this->_setProperty($property, $value);
	}

	public function __get($property){
		$property = $this->_naming($property, 0);
		$result = $this->_getValue($property, null, Magic::KEY_NOT_FOUND);
		if($result === Magic::KEY_NOT_FOUND){
			$this->$property = $result = new Magic;
		}
		return $result;
	}

	private function _naming($method, $start){
		$property = substr($method, $start);
		$naming = $this->naming;
		return $naming !== Naming::CUSTOM_ID
			? Naming::$naming($property)
			: Naming::$naming($property, $this->namingSeperator);
	}

	private function _exception($message, $par){
		echo '<pre>';
		throw new Exception($message, 1);
	}

	protected function _dynaSet($property, $args, $multiple){
		$countArgs = count($args);
		if($countArgs===1){
			return $this->_setProperty($property, $args[0]);
		}
		elseif($countArgs>=2){
			return $this->_appendValues($property, $args);
		}
		$this->_exception('Arguments must be (String $value) or ($value1, $value2, $value3, ...)', 1);
		
	}

	protected function _dynaGet($property, $args, $multiple){
		$countArgs = count($args);
		if($countArgs === 0){
			return $this->_getValue($property);
		}
		elseif($countArgs===1 && is_string($args[0])){
			return $this->_getValue($property, $args[0]);
		}
		$this->_exception('Arguments must be () or (String $key)', 1);
	}

	protected function _dynaAdd($property, $args, $multiple){
		$countArgs = count($args);
		if($countArgs===1){
			$rows = array_shift($args);
			if(is_array($rows)){
				return $this->_addFeautures($property, $rows);
			}
		}
		elseif($countArgs===2){
			$key = array_shift($args);
			$value = array_shift($args);
			return $this->_addFeauture($property, $key, $value);
		}
		$this->_exception('Arguments must be (ArrayHelper $features) or (String $key, $value)', 1);
	}

	protected function _dynaAppend($property, $values, $multiple){
		if(count($values)){
			return $this->_appendValues($property, $values);
		}
		$this->_exception('Arguments must be ($value1, $value2, $value3, ...)', 1);
	}

	protected function _dynaDelete($property, $args, $multiple){
		if(count($args)){
			$this->_exception('Arguments must be ()', 1);
		}
		return $this->_deleteProperty($property);
	}

	protected function _dynaMerge($property, $arrays, $multiple){
		if(count($arrays)){
			return $this->_merge($property, $arrays);
		}
		$this->_exception('Arguments must be (ArrayHelper $array1, ArrayHelper $array2, ArrayHelper $array3, ...)', 1);
	}

	protected function _getValue($property, $keyS = null, $default = null){
		return ArrayHelper::getValue($this->_properties, $keyS ? "$property.$keyS" : $property, $default);
	}

	protected function _setProperty($property, $value){
		$this->_properties[$property] = $value;
		return $this;
	}

	protected function _appendValue($property, $value){
		$this->_properties[$property][] = $value;
		return $this;
	}

	protected function _appendValues($property, $values){
		foreach ($values as $value) {
			$this->_appendValue($property, $value);
		}
		return $this;
	}

	protected function _addFeauture($property, $key, $value){
		$this->_properties[$property][$key] = $value;
		return $this;
	}

	protected function _addFeautures($property, $rows){
		foreach ($rows as $key => $value) {
			$this->_addFeauture($property, $key, $value);
		}
		return $this;
	}

	protected function _deleteProperty($property){
		if(isset($this->_properties[$property])){
			unset($this->_properties[$property]);
		}
		return $this;
	}

	protected function _merge($property, $arrays){
		array_unshift($arrays, (array) $this->_getValue($property));
		return $this->_setProperty($property, call_user_func_array([__NAMESPACE__ . '\ArrayHelper', 'merge'], $arrays));
	}

}