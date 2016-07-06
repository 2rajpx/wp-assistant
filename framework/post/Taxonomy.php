<?php

namespace assistant;

use assistant\Assistant;
use assistant\helper\Naming;
use assistant\exception\ExceptionHandler as Exception;

class Taxonomy {

	public function __construct($config, PostType &$postType = null){

		$args = func_get_args();
		$taxonomyConfig = array_shift($args);
		if(is_string($taxonomyConfig)){
			$taxonomyId = $taxonomyConfig;
			$taxonomyConfig = [];
		}elseif(is_array($taxonomyConfig) || is_object($taxonomyConfig)){
			try{
				$taxonomyConfig = (array)$taxonomyConfig;
				$taxonomyId = array_shift($taxonomyConfig);
			}catch(Exception $e){
				throw new Exception("First key of configuration must be taxonomy id<br>".$e->getMessage);
			}
		}else{
			throw new Exception("Taxonomy id have to define in constructor!");
		}

		$this->setId($taxonomyId)
			->setPostType(array_shift($args))
			->_defaultName()
			->_defaultLabels()
			->_defaultProperties()
			->args
				->mergeProperties($taxonomyConfig);
	}

	/* attach the taxonomy to the post type */
	public function register(){
		if(!$this->postType){
			throw new Exception("Taxonomy must set to the post type", 1);
			
		}
		if(!taxonomy_exists($this->id())){
			/* Create taxonomy and attach it to the object type (post type) */
			add_action('init', function(){
				register_taxonomy($this->id(), $this->postType->id(), $this->args->properties());
			});
		} else {
			/* The taxonomy already exists. We are going to attach the existing taxonomy to the object type (post type) */
			add_action('init', function(){
				register_taxonomy_for_object_type($this->id(), $this->postType->id());
			});
		}
	}

	// Capitilize the words and make it plural
	protected function _defaultName(){
		$id = $this->id();
		// $camelize = Naming::camelize($id);
		
		$plural = Naming::pluralize($id);
		$singular = Naming::singularize($id);
		$slug = strtolower($plural);

		$this->setPlural($plural);
		$this->setSingular($singular);
		$this->setSlug($slug);
		
		return $this;
	}

	/**
	 * @link https://codex.wordpress.org/Function_Reference/get_taxonomy_labels
	 * 
	 */
	// We set the default labels based on the taxonomy name and plural. We overwrite them with the given labels.
	protected function _defaultLabels(){
		$Plural = $this->plural();
		$plural = strtolower($Plural);
		$Singular = $this->singular();
		$singular = strtolower($Singular);
		$language = Assistant::LANGUAGE;
		$this->args->setLabels([
			'menu_name'					=> sprintf(__('%s', $language ), $Plural ),
			'name'						=> sprintf(__('%s', $language ), $Plural ),
			'singular_name'				=> sprintf(__('%s', $language ), $Singular ),
			'search_items'				=> sprintf(__('Search %s', $language ), $plural ),
			'popular_items'				=> sprintf(__('Popular %s', $language ), $plural ),
			'all_items'					=> sprintf(__('All %s', $language ), $plural ),
			'parent_item'				=> sprintf(__('Parent %s', $language ), $plural ),
			'parent_item_colon'			=> sprintf(__('Parent %s:', $language ), $plural ),
			'edit_item'					=> sprintf(__('Edit %s', $language ), $singular ),
			'view_item'					=> sprintf(__('View %s', $language ), $singular ),
			'update_item'				=> sprintf(__('Update %s', $language ), $singular ),
			'add_new_item'				=> sprintf(__('Add new %s', $language ), $singular ),
			'new_item_name'				=> sprintf(__('New %s name', $language ), $singular ),
			'separate_items_with_commas'=> sprintf(__('Seperate %s with commas', $language ), $plural ),
			'add_or_remove_items'		=> sprintf(__('Add or remove %s', $language ), $plural ),
			'choose_from_most_used'		=> sprintf(__('Choose from most used %s', $language ), $plural ),
			'not_found'					=> sprintf(__('No %s found', $language ), $plural ),
		]);
		return $this;
	}

	protected function _defaultProperties(){
		$Plural = $this->plural();
		$slug = $this->slug();
		$this->args->setProperties([
			'label'						=> sprintf(__('%s', $language ), $Plural ),
			'public'					=> true,
			'show_ui'					=> true,
			'show_in_nav_menus'			=> true,
			'_builtin'					=> false,
			'hierarchical'				=> true,
			'rewrite' => [
				'slug' => $slug,
			]
		]);
		return $this;
	}
}