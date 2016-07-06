<?php

namespace assistant\post;

use assistant\base\Object;
use assistant\Assistant;
use assistant\helper\ArrayHelper;
use assistant\helper\Inflector;
use CPT;

/**
 *
 * @link http://code.tutsplus.com/articles/custom-post-type-helper-class--wp-25104
 */
class PostTypeBase extends Object {

    /**
     * @var CPT $_cpt
     */
    private $_cpt;

    /**
     * Returns the default labels based on the singular name and plural name.
     * @param string $singular Lowercase singular name
     * @param string $plural Lowercase plural name
     * @return array Default labels
     * @see https://developer.wordpress.org/reference/functions/get_post_type_labels
     */
    public static function defaultLabels($singular, $plural) {
        // get language
        $language = Assistant::language();
        // Humanize plural name
        $Plural = ucfirst($plural);
        // Humanize singular name
        $Singular = ucfirst($singular);
        // return defaults
        return [
            'add_new_item' => sprintf(__('Add new %s', $language), $singular),
            'edit_item' => sprintf(__('Edit %s', $language), $singular),
            'new_item' => sprintf(__('New %s', $language), $singular),
            'view_item' => sprintf(__('View %s', $language), $singular),
            'search_items' => sprintf(__('Search %s', $language), $plural),
            'not_found' => sprintf(__('No %s found', $language), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in trash', $language), $plural),
            'parent_item_colon' => sprintf(__('Parent %s:', $language), $singular),
            'all_items' => sprintf(__('%s', $language), $Plural),
            'archives' => sprintf(__('%s archives', $language), $Plural),
            'insert_into_item' => sprintf(__('Insert into %s', $language), $plural),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', $language), $plural),
            'menu_name' => sprintf(__('%s', $language), $Plural),
            'name' => sprintf(__('%s', $language), $Plural),
            'singular_name' => sprintf(__('%s', $language), $Singular),
            'add_new' => __('Add new', $language),
            'featured_image' => __('Featured image', $language),
            'set_featured_image' => __('Set featured image', $language),
            'remove_featured_image' => __('Remove featured image', $language),
            'use_featured_image' => __('Use as featured image', $language),
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
    public static function defaultOptions($singular, $plural, $slug) {
        // Get default labels
        $labels = static::defaultLabels($singular, $plural);
        // Humanize plural name
        $Plural = ucfirst($plural);
        // Return defaults
        return [
            'menu_icon' => 'dashicons-admin-page',
            'labels' => $labels,
            'label' => $Plural,
            'public' => true,
            'show_ui' => true,
            'supports' => ['title', 'editor'],
            'show_in_nav_menus' => true,
            '_builtin' => false,
            'rewrite' => [
                'slug' => $slug,
            ],
        ];
    }

    /**
     * Before construct the object.
     * This method is invoked at the first of the constructor
     * before the object set options.
     */
    public function beforeConstruct($options) {
        // Get post type name
        $postTypeName = $options['postTypeName'];
        // Get singular name
        $singular = $options['singular'];
        // Get plural name
        $plural = $options['plural'];
        // Get slug name
        $slug = $options['slug'];
        // Get default options
        $defaultOptions = static::defaultOptions($singular, $plural, $slug);
        // Merge custom options with default options
        $options = ArrayHelper::merge($defaultOptions, $options);
        // Make instance of CPT
        $this->_cpt = new CPT([
            'post_type_name' => $postTypeName,
            'singular' => $singular,
            'plural' => $plural,
            'slug' => $slug,
        ], $options);
    }

    /**
     * Returns the value of the property from $_cpt.
     *
     * @param string $name the camelCase property name in $_cpt
     * @return mixed the property value
     * @see __set()
     */
    public function __get($name) {
        // Make CPT property name
        $property = Inflector::camel2id($name, '_');
        return $this->_cpt->$property;
    }

    /**
     * Sets the property of $_cpt.
     *
     * @param string $name the camelCase property name in $_cpt
     * @param mixed $value the property value
     * @see __get()
     */
    public function __set($name, $value) {
        // Make CPT property name
        $property = Inflector::camel2id($name, '_');
        $this->_cpt->$property = $value;
    }

    /**
     * Run related method of the CPT object
     *
     * @param string $method camelCase method name
     * @param array $args Arguments to send function
     *
     * @return result of CPT::{{method}}()
     *
     * @see CPT
     */
    public function __call($method, $args) {
        // Make CPT method name
        $method = Inflector::camel2id($method, '_');
        // Return result of the method
        return call_user_func_array([$this->_cpt, $method], $args);
    }

}
