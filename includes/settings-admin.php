<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Settings Admin
 */

namespace TaxonomyImages;

class Settings_Admin {

	/**
	 * Sanitize Settings
	 *
	 * This function is responsible for ensuring that
	 * all values within the 'taxonomy_image_plugin_settings'
	 * options are of the appropriate type.
	 *
	 * @param   array  Unknown.
	 * @return  array  Multi-dimensional array of sanitized settings.
	 *
	 * @internal  Private. Used by register_setting().
	 */
	public static function sanitize_settings( $dirty ) {

		$clean = array();

		if ( isset( $dirty['taxonomies'] ) ) {

			$taxonomies = get_taxonomies();

			foreach ( (array) $dirty['taxonomies'] as $taxonomy ) {
				if ( in_array( $taxonomy, $taxonomies ) ) {
					$clean['taxonomies'][] = $taxonomy;
				}
			}

		}

		// translators: Notice displayed on the custom administration page.
		$message = __( 'Image support for taxonomies successfully updated', 'taxonomy-images' );
		if ( empty( $clean ) ) {
			// translators: Notice displayed on the custom administration page.
			$message = __( 'Image support has been disabled for all taxonomies.', 'taxonomy-images' );
		}

		add_settings_error( 'taxonomy_image_plugin_settings', 'taxonomies_updated', esc_html( $message ), 'updated' );

		return $clean;

	}

}
