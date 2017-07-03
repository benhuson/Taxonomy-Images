<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Associations (Legacy)
 *
 * This class provides an interface for term image
 * data stored as a seralized option.
 *
 * Hopefully this will be able to be deprecated at some point.
 */

namespace TaxonomyImages;

class Associations_Legacy {

	/**
	 * Associations
	 *
	 * @var  array
	 */
	private static $associations = array();

	/**
	 * Get a list of user-defined associations
	 *
	 * Associations are stored in the WordPress options table.
	 *
	 * @internal  Private.
	 *
	 * @param   bool   Should WordPress query the database for the results.
	 * @return  array  List of associations. Key => taxonomy_term_id; Value => image_id.
	 */
	public static function get( $refresh = false ) {

		if ( empty( self::$associations ) || $refresh ) {
			self::$associations = self::sanitize( get_option( 'taxonomy_image_plugin' ) );
		}

		return self::$associations;

	}

	/**
	 * Sanitize Associations
	 *
	 * Ensures that all key/value pairs are positive integers.
	 * This filter will discard all zero and negative values.
	 *
	 * @internal  Private.
	 *
	 * @param   array  An array of term_taxonomy_id/attachment_id pairs.
	 * @return  array  Sanitized version of parameter.
	 */
	public static function sanitize( $associations ) {

		$o = array();

		foreach ( (array) $associations as $tt_id => $im_id ) {
			$tt_id = absint( $tt_id );
			$im_id = absint( $im_id );
			if ( 0 < $tt_id && 0 < $im_id ) {
				$o[ $tt_id ] = $im_id;
			}
		}

		return $o;

	}

	/**
	 * Create data storage option if not set on activation.
	 * 
	 * 'taxonomy_image_plugin' (array) is a flat list of all associations
	 * made by this plugin. Keys are integers representing the
	 * term_taxonomy_id of terms. Values are integers representing the
	 * ID property of an image attachment.
	 *
	 * @internal  Private.
	 */
	public static function create_option() {

		$associations = get_option( 'taxonomy_image_plugin' );

		if ( false === $associations ) {
			add_option( 'taxonomy_image_plugin', array() );
		}

	}

}
