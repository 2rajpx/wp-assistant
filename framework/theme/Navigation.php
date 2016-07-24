<?php

namespace assistant\theme;

use tjpx\helper\Object;

/**
 * @link https://github.com/2rajpx/wp-assistant/
 * @license https://github.com/2rajpx/wp-assistant/blob/master/LICENSE
 */
/**
 * Navigation class for define and get navigation
 *
 * @author Tooraj Khatibi <2rajpx@gmail.com>
 * @link https://github.com/2rajpx/
 */
class Navigation extends Object
{

	/**
	 * The name of the navigation
	 * 
	 * @var string $name Holds the name of the navigation
	 */
	protected $name;

	/**
	 * The location of the navigation
	 * 
	 * @var string $location Holds the localtion of the navigation
	 */
	protected $location;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init() {
		add_action('init', [$this, 'registerNavMenu']);
    }

    /**
     * Register navigation menu
     * 
     * @see https://codex.wordpress.org/Function_Reference/register_nav_menu
     */
    public function registerNavMenu() {
    	register_nav_menu($this->location, __($this->name));
    }

    /**
     * Prints nav
     * 
     * @param   
     *
     * @return 
     *
     * @see https://developer.wordpress.org/reference/functions/wp_nav_menu/
     * @see http://code.tutsplus.com/tutorials/function-examination-wp_nav_menu--wp-25525
     */
    public function render() {
    	wp_nav_menu([
    		'theme_location' => $this->location,
    		'menu' => '',
    		'container' => '',
    		'container_class' => '',
    		'menu_class' => '',
    		'fallback_cb' => '',
    		'before' => '',
    		'after' => '',
    		'link_before' => '',
    		'link_after' => '',
    	]);
    }

}