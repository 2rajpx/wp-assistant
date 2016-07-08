<?php

namespace assistant\form;

class Input extends Field{

    /**
     * show/hide type='example' in field attributes
     * @var boolean
     */
    protected $showTypeAttr = true;

    /**
     * Get field attributes and prepare them to show in the field
     */
    protected function prepareAttributes() {
        // Get binding name
        $bindingName = $this->getBindingName();
        // Get attributes
        $attributes = $this->attributes;
        // Get value
        $value = $this->value;
        // Set name
        $attributes['name'] = $bindingName;
        // Set id
        $attributes['id'] = $bindingName;
        // Set value
        $attributes['value'] = $value;
        // Type must be visible
        if ($this->showTypeAttr) {
            // Set type attribute
            $attributes['type'] = static::TYPE;
        }
        // Set prepared attributes
        $this->attributes = $attributes;
    }

    /**
     * If the template is null, set defalt template
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
                        "<input {{attributes}}/>".
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

}