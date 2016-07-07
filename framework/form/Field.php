<?php

namespace assistant\form;

use assistant\helper\Magic;
use assistant\helper\Naming;
use assistant\helper\ArrayHelper;
use assistant\helper\Html;

/**
 * Field Class
 *
 * Used to help create form field for Wordpress.
 * Field provides concrete implementation for Form Elements.
 * @link http://github.com/jjgrainger/wp-custom-post-type-class/
 *
 * @author Tooraj khatibi <2rajpx@gmail.com>
 * @link http://2jpx.ir
 * @version 1.4
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */
abstract class Field extends Magic{

    /**
     * using for the binding name and the default label of the field
     * @var field pascal name
     */
    private $_pascalName;

    /**
     * get field attributes and prepare them to show in field
     */
    abstract protected function _prepareAttributes();

    /**
     * constructor of the field
     * 
     * @param string $name set name of the field for default label, id ,name in the form and binding value
     * @param array $options set field options
     */
    public function __construct($name, $options = []){

        if(isset($options['name'])){
            // delete name attribute from $options
            unset($options['name']);
        }

        // set field name
        $this->setName($name);
        
        // set field properties
        $this->setProperties($options);

    }

    /**
     * make pascal name by name of the element
     * @return string pascalName
     */
    public function pascalName(){
        if( ! $this->_pascalName){
            $this->_pascalName = Naming::camelize($this->name());
        }
        return $this->_pascalName;
    }

    /**
     * the name that integrates the field in server and client
     * Server using : Get meta name and set element value
     * Client using : Html element name, id, label assign
     * 
     * @return string binding name
     */
    public function bindingName(){
        if( ! $bindingName = parent::bindingName()){
            if($prefix = $this->prefix()){
                $bindingName.= Naming::camelize($prefix);
            }
            $bindingName.= $this->pascalName();
            $bindingName = Naming::camel2id($bindingName, '_');
            $this->setBindingName($bindingName);
        }
        return $bindingName;
    }

    /**
     * set default label if label is undefined
     */
    protected function _prepareLabel(){
        if( ! $label = $this->label()){
            $label = Naming::humanize(Naming::camel2words($this->pascalName()));
            $this->setLabel($label);
        }
    }

    /**
     * Get posted value related to the field and return the sanitized value
     *
     * @param string $value The value is posted by client
     *
     * @return string Sanitized value
     */
    public function sanitize($value) {
        return $value;
    }

    /**
     * prepare some config to render element
     * 
     * @return string rendered element
     */
    public function __tostring(){

        // prepare field label
        $this->_prepareLabel();

        // prepare html attributes
        $this->_prepareAttributes();

        // get field properties
        $properties = $this->properties();

        // get all attributes
        $attributes = $this->attributes();

        // generate html by attributes
        $attributes = Html::array2attrs($attributes);

        // set attributes property
        $properties['attributes'] = $attributes;

        // prepare keys and values to replacing in template
        $keys = [];
        $values = [];
        foreach ($properties as $key => $value) {
            $keys[] = '{{'.$key.'}}';
            $values[] = $value;
        }

        // get template
        $template = $this->template();
        
        // get template and replace {{key}} in template with property {{value}}
        return str_replace($keys, $values, $template);

    }
    
}