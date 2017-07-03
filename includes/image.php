<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image
 */

namespace TaxonomyImages;

class Image {

	/**
	 * Image ID
	 *
	 * @var  integer
	 */
	protected $image_id = 0;

	/**
	 * Constructor
	 *
	 * @param  integer  $image_id  Attachment ID.
	 */
	public function __construct( $image_id ) {

		$this->image_id = absint( $image_id );

	}

	/**
	 * Get Image URL
	 * 
	 * Return a URI to a custom image size.
	 * 
	 * If size doesn't exist, attempt to create a resized version.
	 * The output of this function should be escaped before printing to the browser.
	 *
	 * @internal  Private.
	 *
	 * @return  string  URI of custom image on success. Otherwise empty string.
	 */
	public function get_url() {

		$detail = self::get_image_size_data();

		// Return url to custom intermediate size if it exists.
		$img = image_get_intermediate_size( $this->image_id, $detail['name'] );
		if ( isset( $img['url'] ) ) {
			return $img['url'];
		}

		// Detail image does not exist, attempt to create it.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $wp_upload_dir['basedir'] ) ) {

			// Create path to original uploaded image.
			$path = trailingslashit( $wp_upload_dir['basedir'] ) . get_post_meta( $this->image_id, '_wp_attached_file', true );

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

						$meta = wp_generate_attachment_metadata( $this->image_id, $path );
						wp_update_attachment_metadata( $this->image_id, $meta );
						$img = image_get_intermediate_size( $this->image_id, $detail['name'] );

						if ( isset( $img['url'] ) ) {
							return $img['url'];
						}

					}

				}

			}

		}

		// Custom intermediate size cannot be created, try for thumbnail.
		$img = image_get_intermediate_size( $this->image_id, 'thumbnail' );
		if ( isset( $img['url'] ) ) {
			return $img['url'];
		}

		// Thumbnail cannot be found, try fullsize.
		$url = wp_get_attachment_url( $this->image_id );
		if ( ! empty( $url ) ) {
			return $url;
		}

		/**
		 * No image can be found.
		 * This is most likely caused by a user deleting an attachment before deleting it's association with a taxonomy.
		 * If we are in the administration panels:
		 * - Delete the association.
		 * - Return uri to default.png.
		 */
		if ( is_admin() ) {
			$assoc = taxonomy_image_plugin_get_associations();
			foreach ( $assoc as $term => $img ) {
				if ( $img === $this->image_id ) {
					unset( $assoc[ $term ] );
				}
			}
			update_option( 'taxonomy_image_plugin', $assoc );
			return taxonomy_image_plugin_url( 'default.png' );
		}

		/**
		 * No image can be found.
		 * Return path to blank-image.png.
		 */
		return taxonomy_image_plugin_url( 'blank.png' );

	}

	/**
	 * Add Image Size
	 *
	 * Register custom image size with WordPress.
	 *
	 * @internal  Private. Called via the `init` action.
	 */
	public static function add_image_size() {

		$detail = self::get_image_size_data();

		add_image_size(
			$detail['name'],
			$detail['size'][0],
			$detail['size'][1],
			$detail['size'][2]
		);

	}

	/**
	 * Get Image Size Data
	 *
	 * Configuration data for the "detail" image size.
	 *
	 * @internal  Private.
	 *
	 * @return  array  Image size data.
	 */
	public static function get_image_size_data() {

		return array(
			'name' => 'detail',
			'size' => array( 150, 150, true )
		);

	}

}
