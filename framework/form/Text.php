<?php

namespace assistant\form;

use tjpx\field\Text as TextBase;
use assistant\form\InputTrait;

/**
 * @link https://github.com/2rajpx/php-field/
 * @license https://github.com/2rajpx/php-field/blob/master/LICENSE
 */
/**
 * Text Class
 * Create text field
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Text extends TextBase {
	
	use InputTrait;

	/**
	 * @inheritdoc
	 */
    protected $showTypeAttr = true;

}