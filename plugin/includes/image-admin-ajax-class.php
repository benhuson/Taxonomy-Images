<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Admin AJAX
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Image_Admin_AJAX {

	/**
	 * Update Term Image
	 *
	 * Handles the AJAX action to update a term image.
	 *
	 * @internal  Private. Called via the `wp_ajax_taxonomy_image_create_association` action.
	 */
	public function update_term_image() {

		$this->verify_nonce( 'taxonomy-image-plugin-create-association' );

		$term_id = $this->get_posted_term_id();
		$image_type = $this->get_posted_image_type();
		$image_id = $this->get_posted_attachment_id();

		// Save as term meta
		$t = new Term_Image( $term_id, $image_type );
		$updated = $t->update_image_id( $image_id );

		if ( $updated && ! is_wp_error( $updated ) ) {

			$img_admin = new Term_Image_Admin_Control( $term_id, $image_type );

			$this->json_response( array(
				'status'               => 'good',
				'why'                  => esc_html__( 'Image successfully associated', 'taxonomy-images' ),
				'attachment_thumb_src' => $img_admin->get_image_url()
			) );

		} else {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Association could not be created', 'taxonomy-images' )
			) );

		}

		// Don't know why, but something didn't work.
		$this->json_response();

	}

	/**
	 * Delete Term Image
	 *
	 * Handles the AJAX action to remove a term image.
	 *
	 * @internal  Private. Called via the `wp_ajax_taxonomy_image_plugin_remove_association` action.
	 */
	public function delete_term_image() {

		$this->verify_nonce( 'taxonomy-image-plugin-remove-association' );

		$term_id = $this->get_posted_term_id();
		$image_type = $this->get_posted_image_type();

		// Delete term meta
		$t = new Term_Image( $term_id, $image_type );
		$deleted = $t->delete_image();

		if ( $deleted ) {

			$this->json_response( array(
				'status' => 'good',
				'why'    => esc_html__( 'Association successfully removed', 'taxonomy-images' )
			) );

		} else {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Association could not be removed', 'taxonomy-images' )
			) );

		}

		// Don't know why, but something didn't work.
		$this->json_response();

	}

	/**
	 * Get Posted Term ID
	 *
	 * Exit if term ID not set or if no permission to edit.
	 *
	 * @return  integer  Term ID.
	 */
	private function get_posted_term_id() {

		if ( ! isset( $_POST['term_id'] ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'term_id not set', 'taxonomy-images' ),
			) );

		}

		$term_id = absint( $_POST['term_id'] );

		// Empty?
		if ( empty( $term_id ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'term_id is empty', 'taxonomy-images' ),
			) );

		}

		// Permission?
		if ( ! $this->check_permissions( $term_id ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'taxonomy-images' ),
			) );

		}

		return $term_id;

	}

	/**
	 * Get Posted Image Type
	 *
	 * @return  string  Image type.
	 */
	private function get_posted_image_type() {

		if ( ! isset( $_POST['image_type'] ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'image_type not set', 'taxonomy-images' ),
			) );

		}

		return sanitize_key( $_POST['image_type'] );

	}

	/**
	 * Get Posted Attachment ID
	 *
	 * @return  integer  Attachment ID.
	 */
	private function get_posted_attachment_id() {

		if ( ! isset( $_POST['attachment_id'] ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image ID not sent', 'taxonomy-images' )
			) );

		}

		$attachment_id = absint( $_POST['attachment_id'] );

		if ( empty( $attachment_id ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'Image ID is not a positive integer', 'taxonomy-images' )
			) );

		}

		return $attachment_id;

	}

	/**
	 * Verify Nonce
	 *
	 * @param  string  $nonce  Nonce name.
	 */
	private function verify_nonce( $nonce ) {

		if ( ! isset( $_POST['wp_nonce'] ) ) {

			$this->json_response( array(
				'status' => 'bad',
				'why'    => esc_html__( 'No nonce included', 'taxonomy-images' ),
			) );

		}

		if ( ! wp_verify_nonce( $_POST['wp_nonce'], $nonce ) ) {

			$this->json_response( array(
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
	private function json_response( $args ) {

		/* translators: An ajax request has failed for an unknown reason. */
		$response = wp_parse_args( $args, array(
			'status'               => 'bad',
			'why'                  => esc_html__( 'Unknown error encountered', 'taxonomy-images' ),
			'attachment_thumb_src' => ''
		) );

		header( 'Content-type: application/jsonrequest' );
		echo json_encode( $response );
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
	private function check_permissions( $term_id ) {

		$term = get_term( $term_id );

		if ( $term && ! is_wp_error( $term ) ) {

			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( isset( $taxonomy->cap->edit_terms ) ) {
				return current_user_can( $taxonomy->cap->edit_terms );
			}

		}

		return false;

	}

}
