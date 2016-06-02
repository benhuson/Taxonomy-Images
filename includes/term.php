<?php

/**
 * Term Legacy Class
 *
 * This class provides an interface for term taxonomy image
 * data. It will facilitate the transition from storing data as a
 * seralized array to using WordPress term meta.
 *
 * @internal  Do not use this class directly. It is intended for internal use
 *            only and external use is unsupported. Methods may be subject to
 *            change without backward compatibility. Instead use the public
 *            filters that can be found in `public-filters.php`.
 */
class Taxonomy_Images_Term_Legacy {

	/**
	 * Term Taxonomy ID
	 *
	 * @var  int
	 */
	private $ttid = 0;

	/**
	 * Construct Term Instance
	 *
	 * Store term taxonomy reference.
	 *
	 * @param  int  $ttid  Term Taxonomy ID.
	 */
	public function __construct( $ttid ) {

		$this->ttid = absint( $ttid );

	}

	/**
	 * Get Image ID
	 *
	 * @return  integer  Image ID.
	 */
	public function get_image_id() {

		$assoc = $this->get_data();

		if ( isset( $assoc[ $this->get_ttid() ] ) ) {
			return absint( $assoc[ $this->get_ttid() ] );
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

		$id = $this->sanitize_image_id( $id );

		if ( $id > 0 ) {

			$assoc = $this->get_data();

			if ( ! isset( $assoc[ $this->get_ttid() ] ) ) {

				$assoc[ $this->get_ttid() ] = $id;

				return update_option( 'taxonomy_image_plugin', $assoc );

			}

		}

		return false;

	}

	/**
	 * Update Image ID
	 *
	 * @param   integer  $id  Image ID.
	 * @return  boolean
	 */
	public function update_image_id( $id ) {

		$id = $this->sanitize_image_id( $id );

		if ( $id > 0 ) {

			$assoc = $this->get_data();
			$assoc[ $this->get_ttid() ] = $id;

			return update_option( 'taxonomy_image_plugin', $assoc );

		} else {

			return $this->delete_image_id();

		}

	}

	/**
	 * Delete Image ID
	 *
	 * @return  boolean
	 */
	public function delete_image_id() {

		$assoc = $this->get_data();

		if ( isset( $assoc[ $this->get_ttid() ] ) ) {
			unset( $assoc[ $this->get_ttid() ] );
			return update_option( 'taxonomy_image_plugin', $assoc );
		}

		return false;

	}

	/**
	 * Get Term Taxonomy ID
	 *
	 * @return  int  Term Taxonomy ID.
	 */
	private function get_ttid() {

		return $this->ttid;

	}

	/**
	 * Sanitize Image ID
	 *
	 * @param   integer  $image_id  Image ID.
	 * @return  integer             Image ID.
	 */
	private function sanitize_image_id( $image_id ) {

		return absint( $image_id );

	}

	/**
	 * Get Data
	 *
	 * @return  array  Associative array of term_taxonomy_ids and attachment IDs.
	 */
	private function get_data() {

		return taxonomy_image_plugin_get_associations();

	}

}
