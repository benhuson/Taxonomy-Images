<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Admin AJAX
 */

namespace TaxonomyImages;

add_action( 'wp_ajax_taxonomy_images_update_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'update_term_image' ) );
add_action( 'wp_ajax_taxonomy_images_delete_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'delete_term_image' ) );

class Image_Admin_AJAX {

	/**
	 * Update Term Image
	 *
	 * Handles the AJAX action to update a term image.
	 *
	 * @internal  Private. Called via the `wp_ajax_taxonomy_image_create_association` action.
	 */
	public static function update_term_image() {

		self::verify_nonce( 'taxonomy-image-plugin-create-association' );

		$term_id = self::get_posted_term_id();
		$image_id = self::get_posted_attachment_id();

		// Save as term meta
		$t = new Term_Image( $term_id );
		$updated = $t->update_image_id( $image_id );

		if ( $updated && ! is_wp_error( $updated ) ) {

			$img_admin = new Term_Image_Admin( $term_id );

			self::json_response( array(
				'status'               => 'good',
				'why'                  => esc_html__( 'Image successfully associated', 'taxonomy-images' ),
				'attachment_thumb_src' => $img_admin->get_url()
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

		self::verify_nonce( 'taxonomy-image-plugin-remove-association' );

		$term_id = self::get_posted_term_id();

		// Delete term meta
		$t = new Term_Image( $term_id );
		$deleted = $t->delete_image();

		if ( $deleted ) {

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
	 * Get Posted Term ID
	 *
	 * Exit if term ID not set or if no permission to edit.
	 *
	 * @return  integer  Term ID.
	 */
	private static function get_posted_term_id() {

		// Isset?
		if ( ! isset( $_POST['term_id'] ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'term_id not sent', 'taxonomy-images' ),
			) );

		}

		$term_id = absint( $_POST['term_id'] );

		// Empty?
		if ( empty( $term_id ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'term_id is empty', 'taxonomy-images' ),
			) );

		}

		// Permission?
		if ( ! self::check_permissions( $term_id ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'taxonomy-images' ),
			) );

		}

		return $term_id;

	}

	/**
	 * Get Posted Attachment ID
	 *
	 * @return  integer  Attachment ID.
	 */
	private static function get_posted_attachment_id() {

		if ( ! isset( $_POST['attachment_id'] ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image id not sent', 'taxonomy-images' )
			) );

		}

		$attachment_id = absint( $_POST['attachment_id'] );

		if ( empty( $attachment_id ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image id is not a positive integer', 'taxonomy-images' )
			) );

		}

		return $attachment_id;

	}

	/**
	 * Verify Nonce
	 *
	 * @param  string  $nonce  Nonce name.
	 */
	private static function verify_nonce( $nonce ) {

		// Isset?
		if ( ! isset( $_POST['wp_nonce'] ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'No nonce included.', 'taxonomy-images' ),
			) );

		}

		// Verified?
		if ( ! wp_verify_nonce( $_POST['wp_nonce'], $nonce ) ) {

			self::json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Nonce did not match', 'taxonomy-images' ),
			) );

		}

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
	 * Check Taxonomy Permissions
	 *
	 * Check edit permissions based on a term_id.
	 *
	 * @param   integer  term_id  Term ID.
	 * @return  boolean           True if user can edit terms, False if not.
	 */
	private static function check_permissions( $term_id ) {

		$term = get_term( $term_id );

		if ( $term && ! is_wp_error( $term ) ) {

			if ( empty( $term->taxonomy ) ) {
				return false;
			}

			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( isset( $taxonomy->cap->edit_terms ) ) {
				return current_user_can( $taxonomy->cap->edit_terms );
			}

		}

		return false;

	}

}
