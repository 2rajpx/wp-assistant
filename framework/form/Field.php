<?php

namespace assistant\form;

use assistant\base\Object;
use assistant\helper\Naming;
use assistant\helper\ArrayHelper;
use assistant\helper\Html;
use assistant\exception\ExceptionHandler as Exception;

/**
 * Field Class
 *
 * Used to help create form field for Wordpress.
 * Field provides concrete implementation for Form Elements.
 *
 * @author Tooraj khatibi <2rajpx@gmail.com>
 *
 * @link http://2jpx.ir
 */
abstract class Field extends Object
{

    /**
     * Field name
     * It is used for field name, id, and label invoking
     * 
     * @var string $name Holds the name of the field
     */
    public $name;

    /**
     * Field label
     * 
     * @var string $label Holds the label of the field
     */
    public $label;

    /**
     * The hint of the field
     * 
     * @var string $hint Holds the hint of the field
     */
    public $hint;

    /**
     * The value of the element
     * 
     * @var string $value Holds the value of the element
     */
    public $value;

    /**
     * The template of the field
     * 
     * @var string $template Holds the template of the field
     */
    public $template;

    /**
     * The attributes of the field
     * 
     * @var array|string $attributes Holds the attributes of the field
     */
    public $attributes = [];

    /**
     * Html tags that are valid to save in database
     * 
     * @var array $tags Holds the valid html tags
     */
    public $tags = [];

    /**
     * The rules that reun order by priority
     * 
     * @var array $rules Holds the rules of the field
     */
    public $rules = [];

    /**
     * The errors
     * Made by validator callbacks
     * 
     * @var string $errors Holds the errors pushed by validators
     */
    public $errors;

    /**
     * Prefix of the field
     * It is concated first of the element name to make it uniquely
     * 
     * @var string $prefix Holds the prefix of the field
     */
    public $prefix;

    /**
     * using for the binding name and the default label of the field
     *
     * @var field pascal name
     */
    private $_pascalName;

    /**
     * The name that integrates the field in server and client
     * Server using : Get meta name and set element value
     * Client using : Html element name, id, label assign
     * 
     * @var string $_bindingName Holds the binding name of the field
     */
    private $_bindingName;

    /**
     * Prepare attributes to display in the field
     */
    abstract protected function prepareAttributes();

    /**
     * If the template is null, set defalt template
     */
    abstract protected function prepareTemplate();

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init() {
        // Make _pascalName by name of the element
        $this->_pascalName = Naming::camelize($this->name);
        // Make binding name
        $this->_bindingName = null;
        if ($prefix = $this->prefix) {
            // Prepend the prefix to the binding name
            $this->_bindingName.= Naming::camelize($prefix);
        }
        // Append the pascal name to binding name
        $this->_bindingName.= $this->_pascalName;
        // Use (_) seperator insetead of camelCase
        $this->_bindingName = Naming::camel2id($this->_bindingName, '_');
    }

    /**
     * Prepare some config and render element
     * 
     * @return string Rendered element
     */
    public function __tostring() {
        // Prepare field label
        $this->prepareLabel();
        // Prepare html attributes
        $this->prepareAttributes();
        // Prepare template
        $this->prepareTemplate();
        // Prepare errors
        $this->prepareErrors();
        // Generate html by attributes
        $this->attributes = Html::array2attrs($this->attributes);
        // Prepare keys and values to replacing in template
        $keys = [];
        $values = [];
        foreach (get_object_vars($this) as $key => $value) {
            $keys[] = '{{'.$key.'}}';
            $values[] = $value;
        }
        // Replace {{keys}} in template with property {{values}}
        return str_replace($keys, $values, $this->template);
    }

    /**
     * 
     * @return string binding name
     */
    public function getBindingName() {
        return $this->_bindingName;
    }

    /**
     * Run the rule of the rules
     * Each rule can push error to the field or change the value of the field
     *
     * @return boolean The result of validation
     */
    public function validate() {
        // Deny if rules array is empty
        if (empty($this->rules))
            return true;
        // If rules are invalid
        if (!is_array($this->rules))
            throw new Exception("The rules must be an array of the validator|sanitize rules", 1);
        // Loop the rules
        foreach ($this->rules as $rule) {
            // Run rule
            $rule instanceof \Closure
                ? $rule($this)
                : call_user_func($rule, $this);
        }
        // Check the field is valid
        return !$this->hasErrors();
    }

    /**
     * Check the field has error
     *
     * @return boolean Does the field have any errors
     */
    public function hasErrors() {
        return empty($this->errors);
    }

    /**
     * Add error to the field
     * 
     * @param string $error The message
     */
    public function flash($error) {
        $_SESSION['wp_assistant']['flashes'][$this->getBindingName()][] = $error;
    }

    /**
     * Prepare errors to display in the field
     */
    protected function prepareErrors(){
        // Get binding name
        $bindingName = $this->getBindingName();
        // Get flashes
        $errors = ArrayHelper::getValue($_SESSION, "wp_assistant.flashes.$bindingName");
        // Check errors
        if($errors){
            $html = [];
            foreach ($errors as $error) {
                $html[] = "<p style='color:red'>$error</p>";
            }
            $this->errors = implode('', $html);
            unset($_SESSION['wp_assistant']['flashes'][$bindingName]);
        }
    }

    /**
     * Prepare label 
     * Set default label if label is undefined
     */
    protected function prepareLabel(){
        if (!$label = $this->label) {
            $label = Naming::humanize(Naming::camel2words($this->_pascalName));
            $this->label = $label;
        }
    }
    
}