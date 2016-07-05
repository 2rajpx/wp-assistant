<?php

namespace assistant\meta\MetaBox;

use assistant\helper\Magic;
use assistant\form\Factory;
use assistant\meta\PostMeta;

class MetaBox extends Magic {

	public function __construct($id){
		$args = func_get_args();
		$this->setId(array_shift($args));
	}

	public function register(){
		$this->_fields2object();
		// add_action('add_meta_boxes', array(&$this, 'addMetaBox'));
		add_action('admin_init', function(){
			add_meta_box(
				$this->id(),
				$this->title(),
				$this->render(),
				$this->screen(),
				$this->context(),
				$this->priority(),
				$this->callbackArgS()
			);
		});
		add_action('save_post', array(&$this, 'save'));
	}

	protected function _fields2object(){
		$fields = [];
		foreach ($this->fieldS() as $key => $value) {
			// $field = new Field($key, $value);
			$field = Factory::field($key, $value);
			$field->setPrefix($this->id());
			$field->bindingName();
			$fields[] = $field;
		}
		$this->setFieldS($fields);
	}

	/**
	 * @return Closure callback to send action('add_meta_boxes', callback)
	 */
	protected function _defaultRender(){

		return function($post, $data){

			// Nonce field for some validation
			wp_nonce_field( plugin_basename( __FILE__ ), 'custom_meta_box' );

			// Loop through fields
			foreach( $this->fieldS() as $field ){
				
				// get meta name from field
				$meta = $field->bindingName();

				// get meta value
				$value = PostMeta::get($meta);

				// bind meta value to field
				$field->setValue($value);

				// print field
				echo $field;

			}
		};
	}

	/**
	 * save meta box fields in database
	 */
	public function save($post){

		global $post;

		// Deny save if we're doing an auto save
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		// Deny save if request type is not post
		if(!isset($_POST)) return;

		// Deny save if meta box not found in request
		if(!isset($_POST['custom_meta_box'])) return;

		// Deny save if our nonce isn't there, or we can't verify it
		if (!wp_verify_nonce( $_POST['custom_meta_box'], plugin_basename(__FILE__) ) ) return;

		// Deny save if post not found
		if(!isset($post->ID)) return;

		// if post is a page
		if ( 'page' == $_POST['post_type'] ) {

			// Deny if our current user can't edit current page
			if ( ! current_user_can( 'edit_page', $post ) ) return;

		}

		// Deny if our current user can't edit current post
		elseif ( ! current_user_can( 'edit_post', $post ) ) return;

		// Loop through all fields
		foreach( $this->fieldS() as $field ){

			// get meta name
			$metaName = $field->bindingName();

			// get posted value
			$postedValue = $_POST[$metaName];

			// get valid tags
			$tags = $field->validTags();

			// escape posted value to save in db
			$escapedValue = $tags

				// if there are tags must be saved
				? wp_kses($postedValue, $tags)

				// just escape
				: esc_attr($postedValue);

			// save meta in db
			update_post_meta($post->ID, $metaName, $escapedValue);

		}

	}

	public function render(){
		if(func_get_args()){
			$this->setRender(func_get_arg(0));
			return $this;
		}
		if(!$render = parent::render()){
			$render = $this->_defaultRender();
		}
		return $render;
	}

}