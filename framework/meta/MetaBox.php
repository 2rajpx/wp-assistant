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
     * @var array $elements Holds the elements of the box
     */
    protected $elements = [];
    
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
     */
    public function register() {
        // Build the fields of the box
        $this->buildFields();
        // Adds actions to their respective WordPress hooks.
        add_action('add_meta_boxes', [$this, 'box']);
        add_action('save_post', [$this, 'save']);
    }

    /**
     * 
     * @param string $fieldName The name of the field
     * @param WP_Post $post The post object
     *
     * @return string The value of the field related to the post
     */
    public function get($field, $post) {
        // If field is not an instance of the field
        if(!$field instanceof Field){
            if (!is_string($field)) {
                // Throw Exception if the field is not a string
                throw new Exception("First argument must be the name of the field", 1);
            } elseif (!isset($this->elements[$field])) {
                // Throw Exception if the field not found in the meta box
                throw new Exception("$field not found in meta box : $this->name", 1);
            } else {
                // Get the object related to the field name
                $field = $this->elements[$field];
            }
        }
        // Get meta value
        return PostMeta::get($post, $field->getBindingName());
    }
    
    /**
     * Build the objects of the form elements
     * This method is invoked at the top of the init method
     */
    protected function buildFields() {
        $elements = [];
        // Loop the elements of the box
        foreach ($this->elements as $key => $element) {
            // If $element is a field
            if (is_array($element)) {
                $element['name'] = $key;
                // Set the prefix of the element
                $element['prefix'] = $this->name;
                // Build an instance of the field
                $element = FieldFactory::getInstance($element);
                // Push the field to $elements
                $elements[$key] = $element;
            } else {
                // Push the element to $elements
                $elements[] = $element;
            }
        }
        // Set new value to elements 
        $this->elements = $elements;
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
        // Deny save if meta box not found in request
        if (!isset($_POST[$this->name . '_nonce']))
            return;
        // Deny save if our nonce isn't there, or we can't verify it
        if (!wp_verify_nonce($_POST[$this->name . '_nonce'], $this->name . '_data'))
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
        foreach ($this->elements as $element) {
            // Gump to next element if it's not a field object
            if (!$element instanceof Field)
                continue;
            // Get meta name
            $metaName = $element->getBindingName();
            // Set the value of the element
            $element->value = $_POST[$metaName];
            // Validate field
            if (!$element->validate())
                break;
            // Get valid tags
            $tags = $element->tags;
            // Escape sanitized value to save in db
            $escapedValue = !empty($tags)
                // If there are tags must be saved
                ? wp_kses($element->value, $tags)
                // Just escape
                : esc_attr($element->value);
            // Set value 'zero' if it's 0
            if (0===$value) {
                $value = 'zero';
            }
            // Save meta in db
            update_post_meta((int) $post->ID, $metaName, (string) $escapedValue);
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
        // Loop through elements
        foreach ($this->elements as $element) {
            // If field is a closure
            if($element instanceof \Closure){
                // Print result of the callback 
                echo $element($post, $data);
                // Jump to the next element
                continue;
            } elseif ($element instanceof Field) {
                // If post is not new
                if ($post->post_status !== 'auto-draft') {
                    // Bind meta value to the field
                    $element->value = $this->get($element->name, $post);
                }
            }
            // Print the element
            echo $element;
        }
    }

}
