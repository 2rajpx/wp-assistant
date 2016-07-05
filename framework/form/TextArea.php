<?php

namespace assistant\form;

class TextArea extends Field{

	/**
	 * if there is no template in properties, set defalt template and return it
	 * 
	 * @return string the template of the input field
	 */
	public function template(){
		if(!$template = parent::template()){
			$template =
				"<p>".
					"<label for='{{name}}'>".
						"{{label}}".
					"</label>".
					"<textarea {{attributes}}>".
						"{{value}}".
					"</textarea>".
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

		// set name
		$attributes['name'] = $bindingName;

		// set id attribute
		$attributes['id'] = $bindingName;

		// set prepared attributes
		$this->setAttributes($attributes);

	}

}