<?php

namespace assistant\meta;

use assistant\form\FieldFactory;
use assistant\form\Field;
use assistant\meta\PostMeta;
use assistant\base\Object;
use assistant\exception\ExceptionHandler as Exception;

class MetaBox extends Object
{
    
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
     * @var array $fields Holds the fields of the box
     */
    protected $fields = [];
    
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init() {
        if (!$this->name) {
            throw new Exception("You have to set the name of the meta box", 1);
        }
    }

    /**
     * Register meta box
     * 
     * @return MetaBox $this current meta box obejct
     */
    public function register() {
        // Build the fields of the box
        $this->buildFields();
        // Adds actions to their respective WordPress hooks.
        add_action('add_meta_boxes', [$this, 'box']);
        add_action('save_post', [$this, 'save']);
        return $this;
    }

    /**
     * Get a specific meta value
     * 
     * @param string $fieldName The name of the field
     * @param WP_Post $post The post object
     *
     * @return string The value of the field related to the post
     *
     * @throws Exception if the name of the field is invalid
     * @throws Exception if the field not found in the meta box
     */
    public function get($field, $post) {
        // If field is not an instance of the field
        if(!$field instanceof Field){
            if (!is_string($field)) {
                // Throw Exception if the field is not a string
                throw new Exception("First argument must be the name of the field", 1);
            } elseif (!isset($this->fields[$field])) {
                // Throw Exception if the field not found in the meta box
                throw new Exception("$field not found in the meta box : $this->name", 1);
            } else {
                // Get the object related to the field name
                $field = $this->fields[$field];
            }
        }
        // Get meta value
        return PostMeta::get($post, $field->getBindingName());
    }
    
    /**
     * Build the objects of the form fields
     * This method is invoked at the top of the init method
     *
     * @throws Exception if field be invalid
     */
    protected function buildFields() {
        $fields = [];
        // Loop the fields of the box
        foreach ($this->fields as $key => $field) {
            // If $field is options
            if (is_array($field)) {
                // Set the name of the field
                $field['name'] = $key;
                // Set the prefix of the field
                $field['prefix'] = $this->name;
                // Build an instance of the field
                $field = FieldFactory::getInstance($field);
                // Push the field to $fields
                $fields[$key] = $field;
            } else {
                throw new Exception("The field must be an array involved field options", 1);
            }
        }
        // Set new value to fields 
        $this->fields = $fields;
    }

    /**
     * Hooks into WordPress admin_init function.
     */
    public function box() {
        // Add meta box
        add_meta_box(
            $this->name,
            $this->title,
            [$this, 'callback'],
            $this->screen,
            $this->context,
            $this->priority
        );
    }

    /**
     * Save meta box fields in database
     * 
     * @param object $post WordPress post object
     */
    public function save($post) {
        global $post;
        // Deny save if we're doing an auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        // Deny save if request type is not post
        if (!isset($_POST))
            return;
        // Deny save if nonce not found in the request
        if (!isset($_POST[$this->name . '_nonce']))
            return;
        // Deny save if nonce is invalid
        if (!wp_verify_nonce($_POST[$this->name . '_nonce'], $this->name . '_data'))
            return;
        // Deny save if the post not found
        if (!isset($post->ID))
            return;
        // if post is a page
        if ('page' == $_POST['post_type']) {
            // Deny save if the current user doesn't have permission to edit the current page
            if (!current_user_can('edit_page', $post))
                return;
        } elseif (!current_user_can('edit_post', $post))
            // Deny save if the current user doesn't have permission to edit the current post
            return;
        // Loop through all fields
        foreach ($this->fields as $field) {
            // Get meta name
            $metaName = $field->getBindingName();
            // Set the value of the field
            $field->value = $_POST[$metaName];
            // Validate field
            if ($field->validate()){
                // Get valid tags
                $tags = $field->tags;
                // Escape sanitized value to save in db
                $escapedValue = !empty($tags)
                    // If there are tags must be saved
                    ? wp_kses($field->value, $tags)
                    // Just escape
                    : esc_attr($field->value);
                // Set value 'zero' if it's 0
                if (0===$value) {
                    $value = 'zero';
                }
                // Save meta in db
                update_post_meta((int) $post->ID, $metaName, (string) $escapedValue);
            }
        }
    }

    /**
     * Generates the HTML for the meta box
     * 
     * @param object $post WordPress post object
     * @param array $data WordPress meta values
     */
    public function callback($post, $data) {
        // Nonce field for some validation
        wp_nonce_field($this->name . '_data', $this->name . '_nonce');
        // Loop through fields
        foreach ($this->fields as $field) {
            // If post is not new
            if ($post->post_status !== 'auto-draft') {
                // Bind meta value to the field
                $field->value = $this->get($field->name, $post);
            }
            echo $field;
        }
    }

}
