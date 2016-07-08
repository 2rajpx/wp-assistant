<?php

namespace assistant\form;

class Select extends Field
{

    /**
     * The list options
     * 
     * @var array $options Holds the options of the list
     */
    public $options = [];

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
        selected($selectedValue, $optionValue);
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
            // make optgroup
            if(is_array($value)){
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
        $this->prepareOptions();
        // return rendered element
        return parent::__tostring();
    }

    /**
     * Set default template if it's null
     * 
     */
    public function prepareTemplate(){
        // Deny if template is set
        if ($this->template)
            return;
        // Set default template
        $this->template =
            "<table>".
                "<tr>".
                    "<td>".
                        "<label title='{{hint}}' for='{{_bindingName}}'>".
                            "{{label}}".
                        "</label>".
                    "</td>".
                    "<td>".
                        "<select {{attributes}}>".
                            "{{options}}".
                        "</select>".
                    "</td>".
                "</tr>".
                "<tr>".
                    "<td></td>".
                    "<td>".
                        "{{errors}}".
                    "</td>".
                "</tr>".
            "</table>"
        ;
    }

    /**
     * get field attributes and prepare them to show in field
     * implement abstract _prepareAttributes()
     */
    protected function prepareAttributes(){
        // Get binding name
        $bindingName = $this->getBindingName();
        // Get attributes
        $attributes = $this->attributes;
        // Set name
        $attributes['name'] = $bindingName;
        // Set id
        $attributes['id'] = $bindingName;
        // set prepared attributes
        $this->attributes = $attributes;
    }

    /**
     * prepare options for show
     */
    protected function prepareOptions(){
        // get drop down list options
        $options = $this->options;
        // get selected value
        $selectedValue = $this->value;
        // prepare options
        $preparedOptions = static::options2html($options, $selectedValue);
        // update prepared options
        $this->options = $preparedOptions;
    }

}