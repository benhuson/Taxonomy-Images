<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term Image
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Term_Image {

	/**
	 * Term ID
	 *
	 * @var  integer
	 */
	private $term_id = 0;

	/**
	 * Term
	 *
	 * @var  WP_Term|false|null  Null if not fetched, false if not avilable, otherwise term object.
	 */
	private $term = null;

	/**
	 * Type
	 *
	 * @var  string
	 */
	private $type = '';

	/**
	 * Constructor
	 *
	 * @param  integer  $term_id  Term ID.
	 * @param  string   $type     Image type.
	 */
	public function __construct( $term_id, $type = '' ) {

		$this->term_id = absint( $term_id );
		$this->type = sanitize_key( $type );

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
	 * Get Taxonomy Singular Name
	 *
	 * @return  string
	 */
	protected function get_taxonomy_singular_name() {

		$tax = $this->get_taxonomy();

		if ( ! empty( $tax ) ) {

			$taxonomy = get_taxonomy( $tax );

			if ( isset( $taxonomy->labels->singular_name ) ) {
				return $taxonomy->labels->singular_name;
			}

		}

		return _x( 'Term', 'taxonomy singular name', 'taxonomy-images' );

	}

	/**
	 * Get Type
	 *
	 * @return  string
	 */
	public function get_type() {

		return $this->type;

	}

	/**
	 * Get Image ID
	 *
	 * @return  integer  Image ID.
	 */
	public function get_image_id() {

		return absint( get_term_meta( $this->term_id, $this->get_meta_key(), true ) );

	}

	/**
	 * Update Image ID
	 *
	 * Deletes image ID if not a valid image ID.
	 *
	 * @param   integer            $image_id  Image ID.
	 * @return  int|WP_Error|bool             Meta ID if added. True if updated. WP_Error when term_id is ambiguous between taxonomies. False on failure.
	 */
	public function update_image_id( $image_id ) {

		$image_id = absint( $image_id );

		if ( $image_id == 0 ) {
			$this->delete_image();
		}

		return update_term_meta( $this->term_id, $this->get_meta_key(), $image_id );

	}

	/**
	 * Delete Image
	 *
	 * @return  boolean  True on success, false on failure.
	 */
	public function delete_image() {

		return delete_term_meta( $this->term_id, $this->get_meta_key() );

	}

	/**
	 * Get Meta Key
	 *
	 * @return  string  Meta key.
	 */
	private function get_meta_key() {

		$type = $this->get_type();

		return empty( $type ) ? 'taxonomy_image_id' : 'taxonomy_image_' . $type . '_id';

	}

	/**
	 * Get (Blank) Placeholder URL
	 *
	 * @return  string  Placeholder image URL.
	 */
	protected function get_placeholder_url() {

		return Plugin::plugin_url( 'plugin/assets/images/blank.png' );

	}

}
