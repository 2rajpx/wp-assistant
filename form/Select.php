<?php

namespace assistant\form;

class Select extends Field{

	/**
	 * use wp selected and return string instead of print it
	 * @param integer|string $selectedValue first arg in selected()
	 * @param integer|string $optionValue second arg in selected()
	 * @return string saved result in buffer
	 */
	public static function selected($selectedValue, $optionValue){

		// start buffer
		ob_start();

		// wp selected print
		selected( $selectedValue, $optionValue );

		// return buffer
		return ob_get_clean();
		
	}

	/**
	 * generate html code by options array
	 * @param array $options list of options
	 * @param integer|string $selectedValue for add selected='' to option attribute
	 * @return string generated html
	 */
	public static function options2html(array $options = [], $selectedValue = null){

		// buffer prepared html codes
		$buffer = [];

		// loop options
		foreach ($options as $key => $value) {

			if(is_array($value)){
			// make optgroup

				// turn optgroup options to html
				$optgroupOptions = static::options2html($value, $selectedValue);

				// append prepared group to buffer
				$buffer[] = "<optgroup label='$key'>$optgroupOptions</options>";
			
			} else {
			// make option

				// print selected='' if option value and selected value are equivalent
				$selected = static::selected($selectedValue, $value);

				// append prepared option to buffer
				$buffer[] = "<option value='$value' $selected>$key</option>";
			}

		}

		// make html
		$html = implode('', $buffer);

		// return result
		return $html;

	}

	/**
	 * prepare some config to render element
	 * 
	 * @return string rendered element
	 */
	public function __tostring(){

		// prepare options before render
		$this->_prepareOptions();

		// return rendered element
		return parent::__tostring();

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
					"<label for='{{name}}'>".
						"{{label}}".
					"</label>".
					"<select {{attributes}}>".
						"{{options}}".
					"</select>".
				"</p>"
			;
			$this->setTemplate($template);
		}
		return $template;
	}

	/**
	 * get field attributes and prepare them to show in field
	 * implement abstract _prepareAttributes()
	 */
	protected function _prepareAttributes(){

		// get attributes
		$attributes = $this->attributes();

		// get binding name
		$bindingName = $this->bindingName();

		// set id attribute
		$attributes['id'] = $bindingName;

		// set name attribute
		$attributes['name'] = $bindingName;

		// set prepared attributes
		$this->setAttributes($attributes);

	}

	/**
	 * prepare options for show
	 */
	protected function _prepareOptions(){

		// get drop down list options
		$options = $this->options();
		
		// get selected value
		$selectedValue = $this->value();

		// prepare options
		$preparedOptions = static::options2html($options, $selectedValue);

		// update prepared options
		$this->setOptions($preparedOptions);

	}

}