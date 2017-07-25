<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Type
 */

namespace TaxonomyImages;

class Image_Type {

	/**
	 * ID
	 *
	 * @var  string
	 */
	private $id = '';

	/**
	 * Label
	 *
	 * @var  string
	 */
	private $label = '';

	/**
	 * Taxonomies
	 *
	 * @var  array
	 */
	private $taxonomies = array();

	/**
	 * Constructor
	 *
	 * @param  string  $id          Type ID.
	 * @param  string  $label       Admin Label.
	 * @param  array   $taxonomies  Supported taxonomies.
	 */
	public function __construct( $id, $label, $taxonomies = '' ) {

		$this->id = sanitize_key( $id );
		$this->label = sanitize_text_field( $label );
		$this->taxonomies = $this->validate_taxonomies( $taxonomies );

	}

	/**
	 * Get ID
	 *
	 * @return  string  Image type ID.
	 */
	public function get_id() {

		return $this->id;

	}

	/**
	 * Get Label
	 *
	 * @return  string
	 */
	public function get_label() {

		return $this->label;

	}

	/**
	 * Supports Taxonomy?
	 *
	 * @param   string   $taxonomy  Taxonomy.
	 * @return  boolean
	 */
	public function supports_taxonomy( $taxonomy ) {

		return empty( $this->taxonomies ) || in_array( $taxonomy, $this->taxonomies );

	}

	/**
	 * Validate Taxonomies
	 *
	 * @param   array|string  $taxonomies  Taxonomies.
	 * @return  array                      Valid taxonomies.
	 */
	private function validate_taxonomies( $taxonomies ) {

		if ( empty( $taxonomies ) ) {

			return array();

		} elseif ( is_array( $taxonomies ) ) {

			return array_map( 'sanitize_key', $taxonomies );

		}

		return array( sanitize_key( $taxonomies ) );

	}

}
