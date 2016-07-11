<?php

namespace assistant\form;

/**
 * @link https://github.com/2rajpx/php-field/
 * @license https://github.com/2rajpx/php-field/blob/master/LICENSE
 */
/**
 * Input Trait
 * Creates input field
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
trait InputTrait {

    /**
     * @inheritdoc
     */
    protected function prepare() {
        parent::prepare();
        // Set class
        $this->attributes['class'] = 'regular-text';
    }

    /**
     * @inheritdoc
     */
    public function render(){
        // Set default template
        $template =
            "<tr>".
                "<th scope='row'>".
                    "<label title='%s' for='%s'>".
                        "%s".
                    "</label>".
                "</th>".
                "<td>".
                    "<input %s/>".
                    "<div>".
                        "%s".
                    "</div>".
                "</td>".
            "</tr>";
        return sprintf(
            $template,
            $this->hint,
            $this->name,
            $this->label,
            $this->attributes,
            $this->errors
        );
    }

}
