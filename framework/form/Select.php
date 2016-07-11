<?php

namespace assistant\form;

use tjpx\field\Select as SelectBase;

/**
 * @link https://github.com/2rajpx/php-field/
 * @license https://github.com/2rajpx/php-field/blob/master/LICENSE
 */
/**
 * Select Class
 * Creates select field
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Select extends SelectBase {

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
                    "<select %s>".
                        "%s".
                    "</select>".
                    "<span class='cptui-field-description'>%s</span>".
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
            $this->options,
            $this->hint,
            $this->errors
        );
    }

}