<?php

namespace assistant\helper;

class Html{

	/**
	 * @param array|object html attributes
	 * 
	 * @return string html attributes
	 */
	public static function array2attrs($assoc){
		$attrs = [];
		foreach ((array) $assoc as $key => $value) {
			$attrs[] = is_string($key)
				? "$key='$value'"
				: $value;
		}
		return implode(' ', $attrs);
	}

}