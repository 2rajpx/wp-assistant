<?php

namespace assistant\postType;

use assistant\base\BaseClass;
use assistant\Assistant;
use assistant\helper\ArrayHelper;
use assistant\exception\ExceptionHandler as Exception;
use assistant\taxonomy\Taxonomy;

/**
 *
 * @link http://code.tutsplus.com/articles/custom-post-type-helper-class--wp-25104
 */
class BasePostType extends BaseClass{

	use CPT;

	/**
	 * The name of the post type.
	 * Hooks and other wordpress functions use it.
	 * @var string $name Holds the name of the post type.
	 */
	public $name;

	/**
	 * This is a human friendly name.
	 * Default labels use it.
	 * @var string $singular Holds the singular word of the post type.
	 */
	public $singular;

	/**
	 * This is a human friendly name.
	 * Default labels use it.
	 * @var string $plural Holds the plural word of the post type.
	 */
	public $plural;

	/**
	 * Post type slug. This is a robot friendly name.
	 * All lowercase and using '-' instead of '_' or ' '.
	 * @var string $slug Holds the slug name of the post type.
	 */
	public $slug;

	/**
	 * User submitted options
	 * @see https://developer.wordpress.org/reference/functions/get_post_type_labels
	 * @var array $options Holds the options of the post type.
	 */
	public $options = [];

	/**
	 * Taxonomies associated with the post type
	 * @var array $taxonomies Holds an array of taxonomies associated with the post type.
	 */
	public $taxonomies = [];

	/**
	 * Defines which columns are to Appear on the admin edit screen.
	 * Used in add_admin_columns().
	 * @see http://your-wp-site.com/wp-admin/edit.php?post_type=example
	 * @var array $columns Columns visible in admin edit screen.
	 */
	public $columns = [];

	/**
	 * User defined functions to populate admin columns.
	 * @var array $customColumns User functions to populate columns.
	 */
	public $customColumns = [];

	/**
	 * Sortable columns.
	 * @var array $sortable Define which columns are sortable on the admin edit screen.
	 */
	public $sortable = [];

	/**
	 * Returns the default labels based on the singular name and plural name.
	 * @param string $singular Lowercase singular name
	 * @param string $plural Lowercase plural name
	 * @return array Default labels
	 * @see https://developer.wordpress.org/reference/functions/get_post_type_labels
	 */
	public static function defaultLabels($singular, $plural){
		// get language
		$language = Assistant::language();
		// Humanize plural name
		$Plural = ucfirst($plural);
		// Humanize singular name
		$Singular = ucfirst($singular);
		// return defaults
		return [			
			'add_new_item'			=> sprintf(__('Add New %s', $language), $singular),
			'edit_item'				=> sprintf(__('Edit %s', $language), $singular),
			'new_item'				=> sprintf(__('New %s', $language), $singular),
			'view_item'				=> sprintf(__('View %s', $language), $singular),
			'search_items'			=> sprintf(__('Search %s', $language), $plural),
			'not_found'				=> sprintf(__('No %s found', $language), $plural),
			'not_found_in_trash'	=> sprintf(__('No %s found in Trash', $language), $plural),
			'parent_item_colon'		=> sprintf(__('Parent %s:', $language), $singular),
			'all_items'				=> sprintf(__('%s', $language), $Plural),
			'archives'				=> sprintf(__('%s archives', $language), $Plural),
			'insert_into_item'		=> sprintf(__('Insert into %s', $language), $plural),
			'uploaded_to_this_item'	=> sprintf(__('Uploaded to this %s', $language), $plural),
			'menu_name'				=> sprintf(__('%s', $language), $Plural),
			'name'					=> sprintf(__('%s', $language), $Plural),
			'singular_name'			=> sprintf(__('%s', $language), $Singular),
			'add_new'				=> __('Add New', $language),
			'featured_image'		=> __('Featured Image', $language),
			'set_featured_image'	=> __('Set featured image', $language),
			'remove_featured_image'	=> __('Remove featured image', $language),
			'use_featured_image'	=> __('Use as featured image', $language),
		];
	}

	/**
	 * Returns the default options based on the singular name and plural name.
	 * @param string $singular Lowercase singular name
	 * @param string $plural Lowercase plural name
	 * @param string $slug Lowercase slug name
	 * @return array Default options
	 * @see https://developer.wordpress.org/resource/dashicons/
	 */
	public static function defaultOptions($singular, $plural, $slug){
		// Get default labels
		$labels = static::defaultLabels($singular, $plural);
		// Humanize plural name
		$Plural = ucfirst($plural);
		// Return defaults
		return [
			'menu_icon'				=> 'dashicons-admin-page',
			'labels'				=> $labels,
			'label'					=> $Plural,
			'public'				=> true,
			'show_ui'				=> true,
			'supports'				=> ['title', 'editor'],
			'show_in_nav_menus'		=> true,
			'_builtin'				=> false,
			'rewrite' => [
				'slug' => $slug,
			],
		];
	}

	/**
	 * Registers the post type and the the taxonomies associated the post type
	 * @throws Exception if the taxonomy be invalid
	 * @see http://codex.wordpress.org/Function_Reference/post_type_exists
	 * @see https://codex.wordpress.org/Function_Reference/register_post_type
	 * @see CPT::_sortable()
	 */
	public function register(){
		// Prepares the post type before register
		$this->_prepare();
		// If the post type does not already exist
		if(!post_type_exists($this->name)){
			// Add action to register the post type
			add_action('init', function(){
				// Register the post type
				register_post_type($this->name, $this->options);
			});
		}
		// Attach the taxonomies to the post type
		foreach ($this->taxonomies as $taxonomy) {
			// If the taxonomy is the instance of Taxonomy
			if($taxonomy instanceof Taxonomy){
				// Register taxonomy
				$taxonomy->register();
			}
			// If the taxonomy is invalid
			else{
				// Throw Exception
				throw new Exception("Taxonomy must be an instance of Taxonomy class", 1);
			}
		}
		// Columns cortable by CPT trait
		$this->_sortable();
	}

	/**
	 * Prepares the post type before register
	 * Configure some properties
	 * @see http://www.yiiframework.com/doc-2.0/yii-helpers-basearrayhelper.html#merge()-detail
	 */
	protected function _prepare(){
		// Get default options
		$defaultOptions = static::defaultOptions($this->singular, $this->plural, $this->slug);
		// Merge custom options with default options
		$this->options = ArrayHelper::merge($defaultOptions, $this->options);
	}

}