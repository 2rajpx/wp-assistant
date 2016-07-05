<?php

namespace assistant\form;

class CheckBox extends Input {

	const TYPE = 'checkbox';

	/**
	 * use wp checked and return string instead of print it
	 * @param integer|string $checkedValue first arg in checked()
	 * @param integer|string $trueValue second arg in checked()
	 * @return string saved result in buffer
	 */
	public static function checked($checkedValue, $trueValue = 'on'){
		
		// start buffer
		ob_start();

		// wp checked print
		checked( $checkedValue, $trueValue );

		// return buffer
		return ob_get_clean();
		
	}

	/**
	 * if there is no template in properties, set defalt template and return it
	 * 
	 * @return string the template of the input field
	 */
	public function template(){
		if( ! $template = parent::template() ){
			$template =
				"<p>".
					"<input {{attributes}}/>".
					"<label for='{{name}}'>".
						"{{label}}".
					"</label>".
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
		
		// set checked attribute
		$attributes[] = static::checked($value);

		if($this->_showTypeAttr){
		// type must be visible

			// set type attribute
			$attributes['type'] = static::TYPE;
		
		}

		// set prepared attributes
		$this->setAttributes($attributes);

	}
	
}