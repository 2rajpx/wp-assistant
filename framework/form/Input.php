<?php

namespace assistant\form;

class Input extends Field{

	/**
	 * show/hide type='example' in field attributes
	 * @var boolean
	 */
	protected $_showTypeAttr = true;

	/**
	 * if there is no template in properties, set defalt template and return it
	 * 
	 * @return string the template of the input field
	 */
	public function template(){
		if( ! $template = parent::template() ){
			$template =
				"<p>".
					"<label for='{{name}}'>".
						"{{label}}".
					"</label>".
					"<input {{attributes}}/>".
				"</p>"
			;
			$this->setTemplate($template);
		}
		return $template;
	}

	/**
	 * get field attributes and prepare them to show in field
	 */
	protected function _prepareAttributes(){

		// get attributes
		$attributes = $this->attributes();

		// get binding name
		$bindingName = $this->bindingName();

		// get value
		$value = $this->value();

		// set name
		$attributes['name'] = $bindingName;

		// set id attribute
		$attributes['id'] = $bindingName;
		
		// set value attribute
		$attributes['value'] = $value;

		if($this->_showTypeAttr){
		// type must be visible

			// set type attribute
			$attributes['type'] = static::TYPE;
		
		}

		// set prepared attributes
		$this->setAttributes($attributes);

	}

}