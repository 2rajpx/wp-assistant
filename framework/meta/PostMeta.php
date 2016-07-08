<?php

namespace assistant\meta;

class PostMeta {

	/**
	 * get meta value(s) and clean for display
	 * @param string|array $value meta value(s)
	 * @return string|array clear meta value(s)
	 */
	public static function clean($value){
		return is_array($value)
			? stripslashes_deep($value)
			: stripslashes(wp_kses_decode_entities($value));
	}

	/**
	 * get the saved value
	 * @param string $meta meta name
	 * @return string|array meta value(s)
	 */
	public static function get($post, $meta){
		$value = get_post_meta($post->ID, $meta, true);
		if ('zero'==$value) {
		    $value = 0;
		}
		return !empty($value)
			? static::clean($value)
			: null;
	}

	/**
	 * Get the saved values
	 * @return string|array meta value(s)
	 */
	public static function getAll($post){
		return get_post_custom($post->ID);
	}

}