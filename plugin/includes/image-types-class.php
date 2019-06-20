<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Types
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Image_Types {

	/**
	 * Image Types
	 *
	 * @var  array
	 */
	private $types = array();

	/**
	 * Register Image Types
	 *
	 * @internal  Private. Called via the `init` action.
	 */
	public function register_image_types() {

		// Add "Featured" type by default
		$this->types[] = new Image_Type( '', _x( 'Featured', 'taxonomy image type', 'taxonomy-images' ) );

		$this->add_image_types();
		$this->validate_image_types();
		$this->dedupe_image_types();

	}

	/**
	 * Get Image Types for a Taxonomy
	 *
	 * @param   string  $taxonomy  Taxonomy.
	 * @return  array              Image types.
	 *
	 * @internal  Private. Do not call externally.
	 */
	public function get_image_types( $taxonomy ) {

		$taxonomy_image_types = array();

		foreach ( $this->types as $type ) {

			if ( $type->supports_taxonomy( $taxonomy ) ) {
				$taxonomy_image_types[] = $type;
			}

		}

		return $taxonomy_image_types;

	}

	/**
	 * Add Image Types
	 *
	 * New image types can be added via the `taxononomy_images_types` filter.
	 * Example:
	 *
	 * function my_taxononomy_images_types( $types ) {
	 *
	 *    $types[] = new TaxonomyImages\Image_Type( 'background', __( 'Background' ), array( 'category' ) );
	 *    $types[] = new TaxonomyImages\Image_Type( 'preview', __( 'Preview' ), array( 'category', 'post_tag' ) );
	 *
	 *    return $types;
	 *
	 * }
	 * add_filter( 'taxononomy_images_types', 'my_taxononomy_images_types' );
	 */
	private function add_image_types() {

		$this->types = apply_filters( 'taxononomy_images_types', $this->types );

	}

	/**
	 * Validate Image Types
	 */
	private function validate_image_types() {

		foreach ( $this->types as $key => $image_type ) {

			if ( ! is_a( $image_type, __NAMESPACE__ . '\Image_Type' ) ) {
				unset( $this->types[ $key ] );
			}

		}

	}

	/**
	 * De-duplicate Image Types
	 *
	 * Check and remove image types with duplicate IDs.
	 */
	private function dedupe_image_types() {

		$image_type_ids = array();

		foreach ( $this->types as $key => $image_type ) {

			$type_id = $image_type->get_id();

			if ( in_array( $type_id, $image_type_ids ) ) {
				unset( $this->types[ $key ] );
			} else {
				$image_type_ids[] = $type_id;
			}

		}

	}

}
