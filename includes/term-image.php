<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term Image
 */

namespace TaxonomyImages;

class Term_Image {

	/**
	 * Term ID
	 *
	 * @var  integer
	 */
	protected $term_id = 0;

	/**
	 * Term
	 *
	 * @var  WP_Term|false|null  Null if not fetched, false if not avilable, otherwise term object.
	 */
	private $term = null;

	/**
	 * Constructor
	 *
	 * @param  integer  $term_id  Term ID.
	 */
	public function __construct( $term_id ) {

		$this->term_id = absint( $term_id );

	}

	/**
	 * Get Term ID
	 *
	 * @return  integer
	 */
	public function get_term_id() {

		return $this->term_id;

	}

	/**
	 * Get Term
	 *
	 * @return  WP_Term  Term object
	 */
	public function get_term() {

		if ( is_null( $this->term ) && $this->get_term_id() ) {

			$term = get_term( $this->get_term_id() );

			if ( $term && ! is_wp_error( $term  ) ) {
				$this->term = $term;
			} else {
				$this->term = false;
			}

		}

		return $this->term;

	}

	/**
	 * Get Taxonomy
	 *
	 * @return  string
	 */
	public function get_taxonomy() {

		$term = $this->get_term();

		if ( $term ) {
			return $term->taxonomy;
		}

		return '';

	}

	/**
	 * Get Image ID
	 *
	 * @param   string   $type  Image type.
	 * @return  integer         Image ID.
	 */
	public function get_image_id( $type = '' ) {

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

}
