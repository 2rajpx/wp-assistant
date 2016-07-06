<?php

namespace assistant;

use Exception;
use assistant\base\Object;

class Assistant extends Object{

	protected static $appDir;
	protected static $language;

	public static function __callStatic($name, $args){
		if(property_exists(static::className(), $name)){
			return static::$$name;
		}
		throw new Exception("There is no config with $name name", 1);
	}

	public static function config(array $config = []){
		foreach ($config as $key => $value) {
			static::$$key = $value;
		}
	}

	public static function inc($file){
		if(!static::$appDir){
			throw new Exception("appDir is not set", 1);
		}

		$file = static::$appDir . DS . str_replace(array('/', '\\'), DS, $file) . ".php";
		
		if(!file_exists($file)){
			throw new Exception("there is no file in below address :\n $file ", 1);
		}

		require_once $file;
	}

}