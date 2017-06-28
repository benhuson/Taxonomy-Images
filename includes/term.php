<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term
 */

namespace TaxonomyImages;

class Term {

	/**
	 * Term ID
	 *
	 * @var  integer
	 */
	private $term_id = 0;

	/**
	 * Constructor
	 *
	 * @param  integer  $term_id  Term ID.
	 */
	public function __construct( $term_id ) {

		$this->term_id = absint( $term_id );

	}

	/**
	 * Get Image ID
	 *
	 * @param   string   $type  Image type.
	 * @return  integer         Image ID.
	 */
	public function get_image_id( $type = '' ) {

		// If not supported...
		if ( ! $this->term_meta_supported() ) {
			return 0;
		}

		$key = $this->get_meta_key( $type );

		return absint( get_term_meta( $this->term_id, $key, true ) );

	}

	/**
	 * Update Image ID
	 *
	 * @param   integer            $image_id  Image ID.
	 * @param   string             $type      Image type.
	 * @return  int|WP_Error|bool             Meta ID if added. True if updated. WP_Error when term_id is ambiguous between taxonomies. False on failure.
	 */
	public function update_image_id( $image_id, $type = '' ) {

		// If not supported...
		if ( ! $this->term_meta_supported() ) {
			return false;
		}

		$image_id = absint( $image_id );

		$key = $this->get_meta_key( $type );

		return update_term_meta( $this->term_id, $key, $image_id );

	}

	/**
	 * Delete Image
	 *
	 * @param   string   $type  Image type.
	 * @return  boolean         True on success, false on failure.
	 */
	public function delete_image( $type = '' ) {

		// If not supported...
		if ( ! $this->term_meta_supported() ) {
			return false;
		}

		$key = $this->get_meta_key( $type );

		return delete_term_meta( $this->term_id, $key );

	}

	/**
	 * Get Meta Key
	 *
	 * @param   string  $type  Image type.
	 * @return  string         Meta key.
	 */
	private function get_meta_key( $type = '' ) {

		$type = sanitize_key( $type );

		return empty( $type ) ? 'taxonomy_image_id' : 'taxonomy_image_' . $type . '_id';

	}

	/**
	 * Check if term meta is supported
	 *
	 * Support was added in WordPress 4.4
	 *
	 * @return  boolean
	 */
	private function term_meta_supported() {

		return function_exists( 'get_term_meta' ) && function_exists( 'update_term_meta' ) && function_exists( 'delete_term_meta' );

	}

}
