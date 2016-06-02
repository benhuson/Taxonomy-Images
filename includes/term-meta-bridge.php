<?php

/**
 * Term Meta Bridge
 *
 * This class helps transition from the legacy format of storing
 * taxonomy image IDs in an array using term_taxonomy_id as keys,
 * to term meta introduced in WordPress 4.4
 *
 * Use of the newer term meta functions will update the legacy data.
 *
 * @internal  Do not use this class directly. It is intended for internal use
 *            only and external use is unsupported. Methods may be subject to
 *            change without backward compatibility.
 */
class Taxonomy_Images_Term_Meta_Bridge {

	/**
	 * Construct Term Meta Bridge Instance
	 */
	public function __construct() {

		// Hook into term meta functions to reference legacy data
		add_action( 'added_term_meta', array( $this, 'added_legacy_term_metadata' ), 10, 4 );
		add_action( 'deleted_term_meta', array( $this, 'deleted_legacy_term_metadata' ), 10, 4 );
		add_filter( 'get_term_metadata', array( $this, 'get_legacy_term_metadata' ), 10, 4 );
		add_action( 'updated_term_meta', array( $this, 'updated_legacy_term_metadata' ), 10, 4 );

	}

	/**
	 * Added Legacy Term Metadata
	 *
	 * @internal  This method is called via the `added_term_meta` filter and should not be called directly.
	 *
	 * @param  int     $mid          Meta ID.
	 * @param  int     $object_id    Object ID.
	 * @param  string  $meta_key     Meta key.
	 * @param  string  $_meta_value  Meta value.
	 */
	public function added_legacy_term_metadata( $mid, $object_id, $meta_key, $_meta_value ) {
	}

	/**
	 * Deleted Legacy Term Metadata
	 *
	 * @internal  This method is called via the `deleted_term_meta` filter and should not be called directly.
	 *
	 * @param  array   $meta_ids     Meta IDs.
	 * @param  int     $object_id    Object ID.
	 * @param  string  $meta_key     Meta key.
	 * @param  string  $_meta_value  Meta value.
	 */
	public function deleted_legacy_term_metadata( $meta_ids, $object_id, $meta_key, $_meta_value ) {
	}

	/**
	 * Get Legacy Term Metadata Filter
	 *
	 * @internal  This method is called via the `get_term_metadata` filter and should not be called directly.
	 *
	 * @param   mixed    $value      A single meta value or an array of meta values.
	 * @param   integer  $object_id  Term ID.
	 * @param   string   $meta_key   Optional. The meta key to retrieve. If no key is provided, fetches all metadata for the term.
	 * @param   bool     $single     Whether to return a single value. If false, an array of all values matching the `$term_id`/`$key`
	 *                               pair will be returned. Default: false.
	 * @return  mixed                If `$single` is false, an array of metadata values. If `$single` is true, a single metadata value.
	 */
	public function get_legacy_term_metadata( $value, $object_id, $meta_key, $single ) {

		if ( $this->term_meta_supported() && $this->get_meta_key() == $meta_key ) {

			$term = get_term( $object_id );

			$term_legacy = new Taxonomy_Images_Term_Legacy( $term->term_taxonomy_id );
			$value = $term_legacy->get_image_id();

			if ( ! $single ) {
				$value = array( $value );
			}

		}

		return $value;

	}

	/**
	 * Updated Legacy Term Metadata
	 *
	 * @internal  This method is called via the `updated_term_meta` filter and should not be called directly.
	 *
	 * @param   int     $meta_id      Meta ID.
	 * @param   int     $object_id    Object ID.
	 * @param   string  $meta_key     Meta key.
	 * @param   string  $_meta_value  Meta value.
	 */
	public function updated_legacy_term_metadata( $meta_id, $object_id, $meta_key, $_meta_value ) {
	}

	/**
	 * Get Meta Key
	 *
	 * @return  string  Term taxonomy image meta key.
	 */
	private function get_meta_key() {

		return '_taxonomy_image_id';

	}

	/**
	 * Term Meta Supported?
	 *
	 * Check that the version of WordPress supports term meta.
	 *
	 * @see  WordPress get_term_meta() function.
	 *
	 * @return  bool
	 */
	private function term_meta_supported() {

		return get_option( 'db_version' ) > 34370 && function_exists( 'get_term_meta' );

	}

}

new Taxonomy_Images_Term_Meta_Bridge();
