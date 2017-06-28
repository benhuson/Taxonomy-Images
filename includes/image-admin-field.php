<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Admin Field
 */

namespace TaxonomyImages;

class Image_Admin_Field {

	/**
	 * Term
	 *
	 * @var  WP_Term
	 */
	private $term = null;

	/**
	 * Constructor
	 *
	 * @param  WP_Term  $term  Term object.
	 */
	public function __construct( $term ) {

		if ( is_a( $term, 'WP_Term' ) ) {
			$this->term = $term;
		}

	}

	/**
	 * Output the field description.
	 *
	 * @param  string  $before  Output before.
	 * @param  string  $after   Output after.
	 */
	public function the_description( $before = '', $after = '' ) {

		$name = strtolower( $this->get_taxonomy_singular_name() );
		$description = __( 'Associate an image from your media library to this %1$s.', 'taxonomy-images' );

		echo $before . esc_html( sprintf( $description, $name ) ) . $after;

	}

	/**
	 * Get Taxonomy Singular Name
	 *
	 * @return  string
	 */
	private function get_taxonomy_singular_name() {

		$taxonomy = get_taxonomy( $this->term->taxonomy );

		if ( isset( $taxonomy->labels->singular_name ) ) {
			return $taxonomy->labels->singular_name;
		}

		return _x( 'Term', 'taxonomy singular name', 'taxonomy-images' );

	}

}
