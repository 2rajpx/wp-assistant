<?php

namespace assistant\form;

use tjpx\field\FieldFactory as BaseFieldFactory;

/**
 * @link https://github.com/2rajpx/wp-assistant/
 * @license https://github.com/2rajpx/wp-assistant/blob/master/LICENSE
 */
/**
 * Make field objects
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class FieldFactory extends BaseFieldFactory {

    /**
     * {inheritdoc}
     */
    public static function getInstance(array $config = []) {
        // Set default class
        if(!isset($config['class'])){
            $config['class'] = Text::className();
        }
        return parent::getInstance($config);
    }

}