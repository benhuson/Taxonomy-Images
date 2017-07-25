<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Types
 */

namespace TaxonomyImages;

class Image_Types {

	/**
	 * Image Types
	 *
	 * @var  array
	 */
	private static $types = array();

	/**
	 * Register Image Types
	 *
	 * @internal  Private. Called via the `init` action.
	 */
	public static function register_image_types() {

		self::$types[] = new Image_Type( '', 'Featured' );

		self::register_extra_image_types();

	}

	/**
	 * Get Image Types for a Taxonomy
	 *
	 * @param   string  $taxonomy  Taxonomy.
	 * @return  array              Image types.
	 */
	public static function get_image_types( $taxonomy ) {

		$taxonomy_image_types = array();

		foreach ( self::$types as $type ) {

			if ( $type->supports_taxonomy( $taxonomy ) ) {
				$taxonomy_image_types[] = $type;
			}

		}

		return $taxonomy_image_types;

	}

	/**
	 * Register Extra Image Types
	 *
	 * New image types can be added via the `taxononomy_images_types` filter.
	 * Example:
	 *
	 * function my_taxononomy_images_types( $types ) {
	 *
	 *    $types[] = new TaxonomyImages\Image_Type( 'background', 'Background', array( 'category' ) );
	 *    $types[] = new TaxonomyImages\Image_Type( 'preview', 'dsd', array( 'category', 'post_tag' ) );
	 *
	 *    return $types;
	 *
	 * }
	 * add_filter( 'taxononomy_images_types', 'my_taxononomy_images_types' );
	 */
	private static function register_extra_image_types() {

		$image_types = array();
		$extra_types = apply_filters( 'taxononomy_images_types', array() );

		// Find only value and non-duplicate types
		foreach ( $extra_types as $extra_type ) {
			if ( is_a( $extra_type, __NAMESPACE__ . '\Image_Type' ) ) {

				$id = $extra_type->get_id();

				if ( ! empty( $id ) && ! array_key_exists( $id, $image_types ) ) {
					$image_types[ $id ] = $extra_type;
				}

			}
		}

		unset( $extra_types );

		// Add extra types
		self::$types = array_merge( self::$types, array_values( $image_types ) );

	}

}
