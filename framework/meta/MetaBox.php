<?php

namespace assistant\meta;

use assistant\form\Factory;
use assistant\meta\PostMeta;
use assistant\base\Object;

class MetaBox extends Object {
    
    /**
     * @var string $name Holds the name of the box
     */
    protected $name;
    
    /**
     * @var string $title Holds the title of the box
     */
    protected $title;

    /**
     * The post types that work with the box
     *
     * @var array $screen Holds the screen of the box
     */
    protected $screen = [];
    
    /**
     * @var string $context Holds the context of the box
     */
    protected $context = 'normal';
    
    /**
     * @var string $priority Holds the priority of the box
     */
    protected $priority = 'default';

    /**
     * @var array $elements Holds the elements of the box
     */
    protected $elements = [];
    
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init() {
        // Build the fields of the form elements
        $this->buildFields();
        // Adds actions to their respective WordPress hooks.
        add_action('admin_init', [&$this, 'addMetaBox']);
        add_action('save_post', [&$this, 'save']);
    }
    
    /**
     * Build the objects of the form elements
     * This method is invoked at the top of the init method
     */
    protected function buildFields() {
        $elements = [];
        // Loop the elements of the box
        foreach ($this->elements as $key => $element) {
            // If element is not a field
            if (!is_string($key)) {
                // Push the text to $elements
                $elements[] = $element;
                // Jump to next element
                continue;
            }
            // Build an instance of the field
            $field = Factory::field($key, $element);
            // Set the prefix of the element
            $field->setPrefix($this->name);
            // Build the binding name to bind meta value
            $field->bindingName();
            // Push the field object to $elements
            $elements[$key] = $field;
        }
        // Set new value to elements 
        $this->elements = $elements;
    }

    /**
     * Hooks into WordPress admin_init function.
     */
    public function addMetaBox() {
        // Add meta box
        add_meta_box(
            $this->name,
            $this->title,
            [&$this, 'callback'],
            $this->screen,
            $this->context,
            $this->priority
        );
    }

    /**
     * Generates the HTML for the meta box
     * 
     * @param object $post WordPress post object
     * @param array $data WordPress meta values
     */
    public function callback($post, $data) {
        // Nonce field for some validation
        wp_nonce_field(plugin_basename(__FILE__), $this->name . '_meta_box');
        // Loop through fields
        foreach ($this->elements as $key => $element) {
            // If $element is a field object
            if(!is_string($element)){
                // Get meta name from field
                $meta = $element->bindingName();
                // Get meta value
                $value = PostMeta::get($meta);
                // Bind meta value to field
                $element->setValue($value);
            }
            // Print element
            echo $element;
        }
    }

    /**
     * save meta box fields in database
     */
    public function save($post) {
        global $post;
        // Deny save if we're doing an auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        // Deny save if request type is not post
        if (!isset($_POST))
            return;
        // Deny save if meta box not found in request
        if (!isset($_POST[$this->name . '_meta_box']))
            return;
        // Deny save if our nonce isn't there, or we can't verify it
        if (!wp_verify_nonce($_POST[$this->name . '_meta_box'], plugin_basename(__FILE__)))
            return;
        // Deny save if post not found
        if (!isset($post->ID))
            return;
        // if post is a page
        if ('page' == $_POST['post_type']) {
            // Deny if our current user can't edit current page
            if (!current_user_can('edit_page', $post))
                return;
        } elseif (!current_user_can('edit_post', $post))
            // Deny if our current user can't edit current post
            return;
        // Loop through all fields
        foreach ($this->elements as $key => $element) {
            // Gump to next element if it's not a field object
            if (!is_string($key))
                continue;
            // Get meta name
            $metaName = $element->bindingName();
            // Get posted value
            $postedValue = $_POST[$metaName];
            // Get sanitized value
            $sanitizedValue = $element->sanitize($postedValue);
            // Get valid tags
            $tags = $element->validTags();
            // Escape sanitized value to save in db
            $escapedValue = $tags
                    // If there are tags must be saved
                    ? wp_kses($sanitizedValue, $tags)
                    // Just escape
                    : esc_attr($sanitizedValue);
            // Save meta in db
            update_post_meta($post->ID, $metaName, $escapedValue);
        }
    }

}
