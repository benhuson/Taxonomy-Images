<?php

/**
 * Taxonomy Images Error
 *
 * @package    Taxonomy Images
 * @author     Ben Huson <ben@thewhiteroom.net>
 * @copyright  Copyright (c) 2015, Ben Huson
 * @license    GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0
 */

class Taxonomy_Images_Error {

	/**
	 * Error Constructor
	 *
	 * @param  string  $error_msg  Error message.
	 */
	public function __construct( $error_msg ) {

		if ( $this->debug() ) {
			trigger_error( $error_msg );
		}

	}

	/**
	 * Check if debugging is active.
	 *
	 * @return  boolean
	 */
	private function debug() {

		return defined( 'WP_DEBUG' ) && WP_DEBUG && apply_filters( 'taxonomy_images_trigger_error', true );

	}

}
