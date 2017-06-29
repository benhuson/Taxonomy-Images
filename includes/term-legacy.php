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
	 * Taxonomy
	 *
	 * @var  string
	 */
	private $taxonomy = '';

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

		$this->query_ttid();

	}

	/**
	 * Get Taxonomy
	 *
	 * @return  string
	 */
	public function get_taxonomy() {

		return $this->taxonomy;

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
				parent::__construct( $data[0]->term_id );
				self::$cache[ $this->ttid ]['term_id'] = absint( $data[0]->term_id );
			}

			if ( isset( $data[0]->taxonomy ) ) {
				$this->taxonomy = $data[0]->taxonomy;
				self::$cache[ $this->ttid ]['taxonomy'] = $data[0]->taxonomy;
			}

			if ( isset( self::$cache[ $this->ttid ] ) ) {
				return self::$cache[ $this->ttid ];
			}

		}

		return array();

	}

}
