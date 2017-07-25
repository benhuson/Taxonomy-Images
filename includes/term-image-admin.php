<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term Image Admin
 */

namespace TaxonomyImages;

class Term_Image_Admin extends Term_Image {

	/**
	 * Get Image URL
	 * 
	 * Return a URI to a custom preview image size for display in admin.
	 * 
	 * If size doesn't exist, attempt to create a resized version.
	 * The output of this function should be escaped before printing to the browser.
	 *
	 * @return  string  URI of custom image on success. Otherwise empty string.
	 *
	 * @internal  Private.
	 */
	public function get_url() {

		$detail = Image::get_image_size_data();

		// Return url to custom intermediate size if it exists.
		$img = $this->get_image_url( $detail['name'] );
		if ( ! empty( $img ) ) {
			return $img;
		}

		// Try to create image and return URL.
		$img = $this->generate_image_url();
		if ( ! empty( $img ) ) {
			return $img;
		}

		// Custom intermediate size cannot be created, try for thumbnail.
		$img = $this->get_image_url( 'thumbnail' );
		if ( ! empty( $img ) ) {
			return $img;
		}

		// Thumbnail cannot be found, try fullsize.
		$img = $this->get_image_url();
		if ( ! empty( $img ) ) {
			return $img;
		}

		/**
		 * No image can be found.
		 * This is most likely caused by a user deleting an attachment before deleting it's association with a taxonomy.
		 * If we are in the admin delete the association and return URL to default image.
		 */
		if ( is_admin() ) {
			$this->delete_image();
			return Plugin::plugin_url( 'images/default.png' );
		}

		// Otherwise return path to blank image.
		return Plugin::plugin_url( 'images/blank.png' );

	}

	/**
	 * Get Image URL
	 *
	 * @param   string  $size  Image size. Return fullsize image if empty.
	 * @return  string         Image URL.
	 */
	private function get_image_url( $size = '' ) {

		// Fullsize
		if ( empty( $size ) ) {
			return wp_get_attachment_url( $this->get_image_id() );
		}

		$img = image_get_intermediate_size( $this->get_image_id(), $size );

		if ( isset( $img['url'] ) ) {
			return $img['url'];
		}

		return '';

	}

	/**
	 * Generate custom image size URL
	 *
	 * Only use this method if detail image does not exist and needs creating.
	 *
	 * @return  string  URL.
	 */
	private function generate_image_url() {

		$detail = Image::get_image_size_data();

		// Detail image does not exist, attempt to create it.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $wp_upload_dir['basedir'] ) ) {

			$image_id = $this->get_image_id();

			// Create path to original uploaded image.
			$path = trailingslashit( $wp_upload_dir['basedir'] ) . get_post_meta( $image_id, '_wp_attached_file', true );

			if ( is_file( $path ) ) {

				// Attempt to create a new downsized version of the original image
				$new = wp_get_image_editor( $path );

				// Image editor instance OK
				if ( ! is_wp_error( $new ) ) {

					$resized = $new->resize(
						$detail['size'][0],
						$detail['size'][1],
						absint( $detail['size'][2] )
					);

					// Image resize successful. Generate and cache image metadata. Return url.
					if ( ! is_wp_error( $resized ) ) {

						$path = $new->generate_filename();
						$new->save( $path );

						$meta = wp_generate_attachment_metadata( $image_id, $path );
						wp_update_attachment_metadata( $image_id, $meta );
						$img = image_get_intermediate_size( $image_id, $detail['name'] );

						if ( isset( $img['url'] ) ) {
							return $img['url'];
						}

					}

				}

			}

		}

		return '';

	}

}
