<?php

namespace assistant\meta;

use tjpx\helper\Object;
use tjpx\helper\Inflector;
use assistant\form\FieldFactory;
use assistant\meta\PostMeta;
use assistant\helper\Session;
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
     * The description of the meta box
     * It's printed at the top of the box
     * 
     * @var string $description Holds the description of the meta box
     */
    public $description;

    /**
     * The post type(s) that work with the box
     *
     * @var array|string $screen Holds the screen of the box
     */
    protected $screen;

    
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
     * Prepares fields and adds actions to their respective WordPress hooks.
     * 
     * @return MetaBox $this current meta box obejct
     */
    public function register() {
        // Build the fields of the box
        $this->buildFields();
        // Adds actions to their respective WordPress hooks.
        add_action('add_meta_boxes', [$this, 'box']);
        add_action('admin_footer', [$this, 'footer']);
        add_action('save_post', [$this, 'save']);
        return $this;
    }

    /**
     * Get meta name
     * The name that integrates the field in server and client
     * Server using : Get meta name and set element value
     * Client using : Html element name, id, label assign
     * 
     * @param string $fieldName The name of the field
     *
     * @return string The binding name
     *
     * @throws Exception if the name of the field is invalid
     * @throws Exception if the field not found in the meta box
     */
    public function getMetaName($field) {
        if (!is_string($field)) {
            // Throw Exception if the field is not a string
            throw new Exception("First argument must be the name of the field", 1);
        }
        // Check field name existing
        if (!isset($this->fields[$field])) {
            // Throw Exception if the field not found in the meta box
            throw new Exception("$field not found in the meta box : $this->name", 1);
        }
        // If the meta name not set in the field
        if (!isset($this->fields[$field]['metaName'])) {
            // Binding names start with the name of the box
            $metaName = Inflector::camelize($this->name);
            // Append the field name to the binding name
            $metaName.= Inflector::camelize($field);
            // Use (_) seperator insetead of camelCase
            $this->fields[$field]['metaName'] = Inflector::camel2id($metaName, '_');
        }
        return $this->fields[$field]['metaName'];
    }

    /**
     * Get a specific meta value
     * 
     * @param string $fieldName The name of the field
     * @param WP_Post $post The post object
     *
     * @return string The value of the field related to the post
     */
    public function getMetaValue($field, $post) {
        // Get meta value
        return PostMeta::get($post, $this->getMetaName($field));
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
        foreach ($this->fields as $field => $options) {
            // If $options is options
            if (is_array($options)) {
                // Set the name of the field
                $options['name'] = $this->getMetaName($field);
                // Build an instance of the field
                $fields[$field]['object'] = FieldFactory::getInstance($options);
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
            __($this->title, 'fa'),
            [$this, 'render'],
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
        // // Deny save if we're doing an auto save
        // if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        //     return;
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
        // Check if it's not an autosave.
        if (wp_is_post_autosave($post->ID))
            return;
        // Check if it's not a revision.
        if (wp_is_post_revision($post->ID))
            return;
        // if post is a page
        if ('page' == $_POST['post_type']) {
            // Deny save if the current user doesn't have permission to edit the current page
            if (!current_user_can('edit_page', $post))
                return;
        } elseif (!current_user_can('edit_post', $post)) {
            // Deny save if the current user doesn't have permission to edit the current post
            return;
        }
        // Loop through all fields
        foreach ($this->fields as $fieldName => $field) {
            // print_r($_POST);
            // Get meta name
            $metaName = $this->getMetaName($fieldName);
            // Get field object
            $fieldObject = $field['object'];
            // Set the value of the field
            $fieldObject->value = $_POST[$metaName];
            // Validate field
            if ($fieldObject->validate()){
                // Get valid tags
                $tags = $fieldObject->tags;
                // Escape sanitized value to save in db
                $escapedValue = !empty($tags)
                    // If there are tags must be saved
                    ? wp_kses($fieldObject->value, $tags)
                    // Just escape
                    : esc_attr($fieldObject->value);
                // Set value 'zero' if it's 0
                if (0===$value) {
                    $value = 'zero';
                }
                // Update field meta value in database
                update_post_meta((int) $post->ID, $metaName, (string) $escapedValue);
            } else {
                // Set errors in session
                Session::manager()->setFlash($metaName, $fieldObject->errors);
            }
        }
    }

    /**
     * Generates HTML code
     * 
     * @param object $post WordPress post object
     * @param array $data WordPress meta values
     */
    public function render($post, $data) {
        // Ptint the deacription of the meta box
        echo $this->description;
        // Nonce field for some validation
        wp_nonce_field($this->name . '_data', $this->name . '_nonce');
        // Loop through fields
        echo "<table class='form-table'>";
        foreach ($this->fields as $fieldName => $field) {
            // Get field object
            $fieldObject = $field['object'];
            // If post is not new
            if ($post->post_status !== 'auto-draft') {
                // Bind meta value to the field
                $fieldObject->value = $this->getMetaValue($fieldName, $post);
                // Get errors from session
                $fieldObject->errors = Session::manager()->getFlash($this->getMetaName($fieldName), []);
            }
            // Print field
            echo $fieldObject;
        }
        echo "</table>";
    }

    /**
     * Footer
     * 
     * @param   
     *
     * @return 
     */
    public function footer() {
        
        return ;
    }

}
