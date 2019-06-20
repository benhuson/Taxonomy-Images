<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Upgrade
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Upgrade {

	/**
	 * Upgrade If Required
	 *
	 * Check if the plugin version has changed and if so
	 * run any upgrade routines.
	 *
	 * @internal  Private. Called via the `admin_init` action.
	 */
	public function upgrade_if_required() {

		$current_version = get_option( 'taxonomy_images_version' );

		if ( empty( $current_version ) || version_compare( Plugin::VERSION, $current_version, '>' ) ) {
			$this->upgrade( $current_version, Plugin::VERSION );
		}

	}

	/**
	 * Upgrade
	 *
	 * @param  string  $from  Upgrade from version.
	 * @param  string  $to    Upgrade to version.
	 */
	private function upgrade( $from, $to ) {

		// Clean Install
		if ( empty( $from ) ) {

			$default_settings = array(
				'taxonomies' => array()
			);
			add_option( 'taxonomy_image_plugin_settings', $default_settings );

		}

		// Update version
		update_option( 'taxonomy_images_version', $to );

	}

}
