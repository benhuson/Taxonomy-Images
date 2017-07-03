<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Admin AJAX
 */

namespace TaxonomyImages;

class Image_Admin_AJAX {

	/**
	 * Update Term Image
	 *
	 * Handles the AJAX action to update a term image.
	 *
	 * @internal  Private. Called via the `wp_ajax_taxonomy_image_create_association` action.
	 */
	public static function update_term_image() {

		if ( ! isset( $_POST['tt_id'] ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'tt_id not sent', 'taxonomy-images' ),
			) );
		}

		$tt_id = absint( $_POST['tt_id'] );

		if ( empty( $tt_id ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'tt_id is empty', 'taxonomy-images' ),
			) );
		}

		if ( ! self::check_permissions( $tt_id ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'taxonomy-images' ),
			) );
		}

		if ( ! isset( $_POST['wp_nonce'] ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'No nonce included.', 'taxonomy-images' ),
			) );
		}

		if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-image-plugin-create-association' ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Nonce did not match', 'taxonomy-images' ),
			) );
		}

		if ( ! isset( $_POST['attachment_id'] ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image id not sent', 'taxonomy-images' )
			) );
		}

		$image_id = absint( $_POST['attachment_id'] );

		if ( empty( $image_id ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image id is not a positive integer', 'taxonomy-images' )
			) );
		}

		// @todo  Deprecate. Here for backwards-compatibility.
		$assoc = taxonomy_image_plugin_get_associations();
		$assoc[ $tt_id ] = $image_id;

		// Save as term meta
		$t = new Term_Legacy( $tt_id );
		$t->update_image_id( $image_id );

		// @todo  Make this work primarily for term meta.
		if ( update_option( 'taxonomy_image_plugin', taxonomy_image_plugin_sanitize_associations( $assoc ) ) ) {

			self::json_response( array(
				'status'               => 'good',
				'why'                  => esc_html__( 'Image successfully associated', 'taxonomy-images' ),
				'attachment_thumb_src' => taxonomy_image_plugin_get_image_src( $image_id )
			) );

		} else {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Association could not be created', 'taxonomy-images' )
			) );

		}

		// Don't know why, but something didn't work.
		self::json_response();

	}

	/**
	 * Delete Term Image
	 *
	 * Handles the AJAX action to remove a term image.
	 *
	 * @internal  Private. Called via the `wp_ajax_taxonomy_image_plugin_remove_association` action.
	 */
	public static function delete_term_image() {

		if ( ! isset( $_POST['tt_id'] ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'tt_id not sent', 'taxonomy-images' ),
			) );
		}

		$tt_id = absint( $_POST['tt_id'] );

		if ( empty( $tt_id ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'tt_id is empty', 'taxonomy-images' ),
			) );
		}

		if ( ! self::check_permissions( $tt_id ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'taxonomy-images' ),
			) );
		}

		if ( ! isset( $_POST['wp_nonce'] ) ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'No nonce included', 'taxonomy-images' ),
			) );
		}

		if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-image-plugin-remove-association') ) {
			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Nonce did not match', 'taxonomy-images' ),
			) );
		}

		// @todo  Deprecate. Here for backwards-compatibility.
		$assoc = taxonomy_image_plugin_get_associations();

		if ( ! isset( $assoc[ $tt_id ] ) ) {
			self::json_response( array(
				'status' => 'good',
				'why'    => esc_html__( 'Nothing to remove', 'taxonomy-images' )
			) );
		}

		unset( $assoc[ $tt_id ] );

		// Delete term meta
		$t = new Term_Legacy( $tt_id );
		$t->delete_image();

		// @todo  Make this work primarily for term meta.
		if ( update_option( 'taxonomy_image_plugin', $assoc ) ) {

			self::json_response( array(
				'status' => 'good',
				'why'    => esc_html__( 'Association successfully removed', 'taxonomy-images' )
			) );

		} else {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Association could not be removed', 'taxonomy-images' )
			) );

		}

		// Don't know why, but something didn't work.
		self::json_response();

	}

	/**
	 * JSON Response
	 *
	 * Terminates script execution and provides a JSON response.
	 *
	 * @param  array  Associative array of values to be encoded in JSON.
	 */
	private static function json_response( $args ) {

		/* translators: An ajax request has failed for an unknown reason. */
		$response = wp_parse_args( $args, array(
			'status'               => 'bad',
			'why'                  => esc_html__( 'Unknown error encountered', 'taxonomy-images' ),
			'attachment_thumb_src' => ''
		) );

		header( 'Content-type: application/jsonrequest' );
		print json_encode( $response );
		exit;

	}

	/**
	 * Check Taxonomy Permissions.
	 *
	 * Allows a permission check to be performed on a term
	 * when all you know is the term_taxonomy_id.
	 *
	 * @param   integer  term_taxonomy_id
	 * @return  bool     True if user can edit terms, False if not.
	 */
	private static function check_permissions( $tt_id ) {

		$term_legacy = new Term_Legacy( $tt_id );

		$tax = $term_legacy->get_taxonomy();
		if ( empty( $tax ) ) {
			return false;
		}

		$taxonomy = get_taxonomy( $tax );

		if ( isset( $taxonomy->cap->edit_terms ) ) {
			return current_user_can( $taxonomy->cap->edit_terms );
		}

		return false;

	}

}
