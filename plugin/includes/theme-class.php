<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Theme
 *
 * Prints custom css to all public pages. If you do not
 * wish to have these styles included for you, please
 * insert the following code into your theme's functions.php
 * file:
 *
 * add_filter( 'taxonomy_images_disable_theme_css', '__return_true' );
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Theme {

	/**
	 * Enqueue Styles
	 *
	 * @internal  Private. Called via the `wp_enqueue_scripts` action.
	 */
	public function enqueue_styles() {

		if ( apply_filters( 'taxonomy_images_disable_theme_css', false ) ) {
			return;
		}

		wp_enqueue_style(
			'taxonomy-image-plugin-public',
			Plugin::plugin_url( 'plugin/assets/css/theme.css' ),
			array(),
			Plugin::VERSION,
			'screen'
		);

	}

}
