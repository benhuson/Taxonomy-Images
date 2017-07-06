<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image
 */

namespace TaxonomyImages;

add_action( 'init', array( 'TaxonomyImages\Image', 'add_image_size' ) );

class Image {

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
	 * @return  array  Image size data.
	 *
	 * @internal  Private.
	 */
	public static function get_image_size_data() {

		return array(
			'name' => 'detail',
			'size' => array( 150, 150, true )
		);

	}

}
