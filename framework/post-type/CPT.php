<?php

namespace assistant\postType;

/**
 * Used to help create custom post types for Wordpress.
 * @link https://github.com/jjgrainger/wp-custom-post-type-class
 * @author jjgrainger
 * @link http://jjgrainger.co.uk
 * @version 1.4
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */
trait CPT {

	/**
	* Add Action
	* Helper function to add add_action WordPress filters.
	* @param string $action Name of the action.
	* @param string $function Function to hook that will run on action.
	* @param integer $priority Order in which to execute the function, relation to other functions hooked to this action.
	* @param integer $accepted_args The number of arguments the function accepts.
	*/
	function add_action( $action, $function, $priority = 10, $accepted_args = 1 ) {
		// Pass variables into WordPress add_action function
		add_action( $action, $function, $priority, $accepted_args );
	}

	/**
	* Add Filter
	* Create add_filter WordPress filter.
	* @see http://codex.wordpress.org/Function_Reference/add_filter
	* @param  string $action Name of the action to hook to, e.g 'init'.
	* @param  string $function Function to hook that will run on @action.
	* @param  integer $priority Order in which to execute the function, relation to other function hooked to this action.
	* @param  integer $accepted_args The number of arguements the function accepts.
	*/
	function add_filter( $action, $function, $priority = 10, $accepted_args = 1 ) {
		// Pass variables into Wordpress add_action function
		add_filter( $action, $function, $priority, $accepted_args );
	}

/**
* Add admin columns
*
* Adds columns to the admin edit screen. Function is used with add_action
*
* @param array $columns Columns to be added to the admin edit screen.
* @return array
*/
function add_admin_columns( $columns ) {
	// If no user columns have been specified, add taxonomies
	if ( ! isset( $this->columns ) ) {
		$new_columns = array();
		// determine which column to add custom taxonomies after
		if ( is_array( $this->taxonomies ) && in_array( 'post_tag', $this->taxonomies ) || $this->post_type_name === 'post' ) {
			$after = 'tags';
		} elseif( is_array( $this->taxonomies ) && in_array( 'category', $this->taxonomies ) || $this->post_type_name === 'post' ) {
			$after = 'categories';
		} elseif( post_type_supports( $this->post_type_name, 'author' ) ) {
			$after = 'author';
		} else {
			$after = 'title';
		}
		// foreach exisiting columns
		foreach( $columns as $key => $title ) {
			// add exisiting column to the new column array
			$new_columns[$key] = $title;
			// we want to add taxonomy columns after a specific column
			if( $key === $after ) {
				// If there are taxonomies registered to the post type.
				if ( is_array( $this->taxonomies ) ) {
					// Create a column for each taxonomy.
					foreach( $this->taxonomies as $tax ) {
						// WordPress adds Categories and Tags automatically, ignore these
						if( $tax !== 'category' && $tax !== 'post_tag' ) {
								// Get the taxonomy object for labels.
								$taxonomy_object = get_taxonomy( $tax );
								// Column key is the slug, value is friendly name.
								$new_columns[ $tax ] = sprintf( __( '%s', $this->textdomain ), $taxonomy_object->labels->name );
						}
					}
				}
			}
		}
		// overide with new columns
		$columns = $new_columns;
	} else {
		// Use user submitted columns, these are defined using the object columns() method.
		$columns = $this->columns;
	}
	return $columns;
}

	/**
	 * Sortable
	 * Define what columns are sortable in the admin edit screen.
	 */
	protected function _sortable() {
		// Deny if sortable columns are undefined
		if(empty($this->sortable)) return;
		// Run filter to make columns sortable.
		$this->add_filter( 'manage_edit-' . $this->name . '_sortable_columns', [ &$this, 'makeColumnsSortable'] );
		// Run action that sorts columns on request.
		$this->add_action( 'load-edit.php', [ &$this, 'loadEdit' ] );
	}

	/**
	 * Make columns sortable
	 * Internal function that adds user defined sortable columns to WordPress default columns.
	 * @param array $columns Columns to be sortable.
	 * @return array Merged columns to sort
	 */
	public function makeColumnsSortable( $columns ) {
		// For each sortable column.
		foreach ( $this->sortable as $column => $values ) {
			// Make an array to merge into wordpress sortable columns.
			$sortable_columns[ $column ] = $values[0];
		}
		// Merge sortable columns array into wordpress sortable columns.
		$columns = array_merge( $sortable_columns, $columns );
		return $columns;
	}

	/**
	 * Load edit
	 * Sort columns only on the edit.php page when requested.
	 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/request
	 */
	public function loadEdit() {
		// Run filter to sort columns when requested
		$this->add_filter( 'request', [ &$this, 'sortColumns' ] );
	}

	/**
	 * Sort columns
	 * Internal function that sorts columns on request.
	 * @see load_edit()
	 * @param array $vars The query vars submitted by user.
	 * @return array A sorted array.
	 */
	public function sortColumns( $vars ) {
		// Cycle through all sortable columns submitted by the user
		foreach ( $this->sortable as $column => $values ) {
			// Retrieve the meta key from the user submitted array of sortable columns
			$meta_key = $values[0];
			// If the meta_key is a taxonomy
			if( taxonomy_exists( $meta_key ) ) {
				// Sort by taxonomy.
				$key = "taxonomy";
			} else {
				// else by meta key.
				$key = "meta_key";
			}
			// If the optional parameter is set and is set to true
			if ( isset( $values[1] ) && true === $values[1] ) {
				// Vaules needed to be ordered by integer value
				$orderby = 'meta_value_num';
			} else {
				// Values are to be order by string value
				$orderby = 'meta_value';
			}
			// Check if we're viewing this post type
			if ( isset( $vars['post_type'] ) && $this->name == $vars['post_type'] ) {
				// find the meta key we want to order posts by
				if ( isset( $vars['orderby'] ) && $meta_key == $vars['orderby'] ) {
					// Merge the query vars with our custom variables
					$vars = array_merge(
						$vars,
						[
							'meta_key' => $meta_key,
							'orderby' => $orderby
						]
					);
				}
			}
		}
		return $vars;
	}

}