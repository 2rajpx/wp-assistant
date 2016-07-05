<?php

namespace assistant\helper;

class Naming extends Inflector{

	const CAMEL_CASE = 'variablize';
	const PASCAL_CASE = 'camelize';
	const LOWER_CASE = 'lowercase';
	const UPPER_CASE = 'uppercase';
	const UNDER_SCORE = 'underscore';
	const CUSTOM_ID = 'customId';

    /**
     * Creates an url friendly slug.
     */
	public static function url($value){
		return str_replace([' ', '_'], '-', $value);
	}

	public static function underscore($value){
		return parent::underscore(static::camelize($value));
	}

	public static function customId($value, $seperator){
		return static::camel2id(static::camelize($value), $seperator);
	}

	public static function lowerCase($value){
		return strtolower($value);
	}

	public static function upperCase($value){
		return strtoupper($value);
	}

}