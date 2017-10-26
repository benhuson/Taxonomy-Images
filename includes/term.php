<?php

/**
 * Term Class
 *
 * This class provides an interface for term taxonomy image data.
 *
 * @internal  Do not use this class directly. It is intended for internal use
 *            only and external use is unsupported. Methods may be subject to
 *            change without backward compatibility. Instead use the public
 *            filters that can be found in `public-filters.php`.
 */
class Taxonomy_Images_Term {

	/**
	 * Term
	 *
	 * @var  null|WP_Term
	 */
	private $term = null;

	/**
	 * Term ID
	 *
	 * @var  null|int
	 */
	private $term_id = null;

	/**
	 * Taxonomy
	 *
	 * @var  null|string
	 */
	private $taxonomy = null;

	/**
	 * Term Taxonomy ID
	 *
	 * Used for backwards-compatibility with
	 * options data storage.
	 *
	 * @var  null|int
	 */
	private $tt_id = null;

	/**
	 * Cache of terms indexed by tt_id
	 *
	 * @var  array
	 */
	private static $cache = array();

	/**
	 * Construct Term Instance
	 *
	 * Store term identifiers. If only limited identifiers are set
	 * i.e. only term taxonomy ID - then gaps will be filled as requested
	 * by class methods to help with caching.
	 *
	 * @param  int|WP_Term     $term      Term object, term ID, or term taxonomy ID if $taxonomy is true.
	 * @param  boolean|string  $taxonomy  Optional. Taxonomy of true if first $term parameter is a tt_id.
	 */
	public function __construct( $term, $taxonomy = false ) {

		if ( $this->is_term_object( $term ) ) {

			// Set term object vars
			$this->term = $term;
			$this->term_id = $term->term_id;
			$this->taxonomy = $term->taxonomy;
			$this->tt_id = $term->term_taxonomy_id;

		} elseif ( is_numeric( $term ) ) {

			$term = absint( $term );

			if ( true === $taxonomy ) {

				// Set term taxonomy ID
				$this->tt_id = $term;

			} else {

				// Set term ID and taxonomy
				$this->term_id = $term;
				$this->taxonomy = ! empty( $taxonomy ) ? $taxonomy : '';

			}

		}

	}

	/**
	 * Is term object
	 *
	 * @param   mixed    $term  Data to check if term object.
	 * @return  boolean
	 */
	private function is_term_object( $term ) {

		// WP_Term
		if ( is_a( $term, 'WP_Term' ) ) {
			return true;
		}

		// Legacy term object
		if ( is_object( $term ) && isset( $term->term_id ) && isset( $term->taxonomy ) && isset( $term->term_taxonomy_id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get Taxonomy
	 *
	 * If taxonomy is not stored it will be fetched
	 * via the get_terms() method.
	 *
	 * @return  string  Taxonomy
	 */
	public function get_taxonomy() {

		if ( ! is_null( $this->taxonomy ) ) {
			return $this->taxonomy;
		}

		$term = $this->get_term();

		return $this->taxonomy;

	}

	/**
	 * Get Term Taxonomy ID
	 *
	 * If term taxonomy ID is not stored it will
	 * be fetched via the get_terms() method.
	 *
	 * @return  int  Term Taxonomy ID.
	 */
	public function get_tt_id() {

		if ( is_null( $this->tt_id ) ) {
			$term = $this->get_term();
		}

		return is_null( $this->tt_id ) ? 0 : $this->tt_id;

	}

	/**
	 * Get Term
	 *
	 * If term object is not stored it will be fetched via
	 * the term ID if available, otherwise falling back to fetching
	 * manually from the database via the term taxonomy ID.
	 *
	 * @return  WP_Term  Term object.
	 */
	public function get_term() {

		global $wpdb;

		// Get term
		if ( ! is_null( $this->term ) ) {
			return $this->term;
		}

		// Get term via term ID
		if ( ! is_null( $this->term_id ) ) {
			$this->term = get_term( $this->term_id, $this->taxonomy );
			$this->tt_id = $this->term->term_taxonomy_id;
			return $this->term;
		}

		// Get term via term taxonomy ID
		if ( ! is_null( $this->tt_id ) ) {

			if ( isset( self::$cache[ $this->tt_id ] ) ) {
				return self::$cache[ $this->tt_id ];
			}

			$data = current( $wpdb->get_results( $wpdb->prepare( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d LIMIT 1", $this->tt_id ) ) );

			if ( $data ) {
				$this->term_id = $data->term_id;
				$this->taxonomy = $data->taxonomy;
				$this->term = get_term( $this->term_id, $this->taxonomy );

				self::$cache[ $this->tt_id ] = $this->term;

			}

		}

		return null;

	}

	/**
	 * Get Image ID
	 *
	 * @return  integer  Image ID.
	 */
	public function get_image_id() {

		$assoc = taxonomy_image_plugin_get_associations();

		if ( isset( $assoc[ $this->get_tt_id() ] ) ) {
			return $assoc[ $this->get_tt_id() ];
		}

		return 0;

	}

	/**
	 * Add Image ID
	 *
	 * @param   integer  $id  Image ID.
	 * @return  boolean
	 */
	public function add_image_id( $id ) {

	}

	/**
	 * Current User Can Edit
	 *
	 * @return  boolean
	 */
	public function current_user_can_edit() {

		$tax = $this->get_taxonomy();

		if ( empty( $tax ) ) {
			return false;
		}

		$taxonomy = get_taxonomy( $tax );
		if ( ! isset( $taxonomy->cap->edit_terms ) ) {
			return false;
		}

		return current_user_can( $taxonomy->cap->edit_terms );

	}

	/**
	 * Update Image ID
	 *
	 * @param   integer  $id  Image ID.
	 * @return  boolean
	 */
	public function update_image_id( $id ) {

		$assoc = taxonomy_image_plugin_get_associations();
		$assoc[ $this->get_tt_id() ] = $id;

		return update_option( 'taxonomy_image_plugin', taxonomy_image_plugin_sanitize_associations( $assoc ) );

	}

	/**
	 * Delete Image ID
	 *
	 * @return  boolean
	 */
	public function delete_image_id() {

		$assoc = taxonomy_image_plugin_get_associations();

		if ( isset( $assoc[ $this->get_tt_id() ] ) ) {
			unset( $assoc[ $this->get_tt_id() ] );
		}

		return update_option( 'taxonomy_image_plugin', $assoc );

	}

}
