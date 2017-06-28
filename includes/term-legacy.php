<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term (Legacy)
 *
 * This class provides an interface for the `Term` class
 * where only a `term_taxonomy_id` (ttid) is available
 * due to legacy code.
 *
 * When passed a ttid this class retrieves the term and
 * extends the functionality of the Term class.
 *
 * Hopefully this will be able to be deprecated at some point.
 */

namespace TaxonomyImages;

class Term_Legacy extends Term {

	/**
	 * Term Taxonomy ID
	 *
	 * @var  integer
	 */
	private $ttid = 0;

	/**
	 * Cached TTIDs
	 *
	 * @var  array
	 */
	private static $cache = array();

	/**
	 * Constructor
	 *
	 * @param  integer  $ttid  Term Taxonomy ID.
	 */
	public function __construct( $ttid ) {

		$this->ttid = absint( $ttid );

	}

	/**
	 * Get Term ID
	 *
	 * @return  integer
	 */
	public function get_term_id() {

		return absint( get_field( 'term_id' ) );

	}

	/**
	 * Get Field
	 *
	 * @param   string  $field  Term field, 'term_id' or 'taxonomy'.
	 * @return  string          Value.
	 */
	private function get_field( $field ) {

		if ( in_array( $field, array( 'term_id', 'taxonomy' ) ) ) {

			$fields = $this->get_fields();

			if ( isset( $fields[ $field ] ) ) {
				return $fields[ $field ];
			}

		}

		return '';

	}

	/**
	 * Get Term Taxonomy Fields
	 *
	 * @return  array  Term ID and Taxonomy values.
	 */
	private function get_fields() {

		if ( isset( $this->cache[ $this->ttid ] ) ) {
			return $this->cache[ $this->ttid ];
		}

		return $this->query_ttid();

	}

	/**
	 * Query Term Taxonomy ID
	 *
	 * Get the `term_id` and `taxonomy` values from the database.
	 * Results are cached. Do not call this method unless the ttid
	 * does not yet exist in the cache.
	 *
	 * @return  array  Term ID and Taxonomy values.
	 */
	private function query_ttid() {

		global $wpdb;

		if ( $this->ttid ) {

			$data = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d LIMIT 1", $this->ttid ) );

			if ( isset( $data[0]->term_id ) ) {
				$this->cache[ $this->ttid ]['term_id'] = absint( $data[0]->term_id );
			}

			if ( isset( $data[0]->taxonomy ) ) {
				$this->cache[ $this->ttid ]['taxonomy'] = $data[0]->taxonomy;
			}

			if ( isset( $this->cache[ $this->ttid ] ) ) {
				return $this->cache[ $this->ttid ];
			}

		}

		return array();

	}

}
