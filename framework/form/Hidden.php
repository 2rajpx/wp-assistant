<?php

namespace assistant\form;

/**
 * @link https://github.com/2rajpx/wp-assistant/
 * @license https://github.com/2rajpx/wp-assistant/blob/master/LICENSE
 */
/**
 * Hidden form element
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Hidden extends Input
{

    /**
     * If the template is null, set defalt template
     */
    public function prepareTemplate(){
        // Set default template
        $this->template = "<input {{attributes}}/>";
    }

}