<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Settings Admin
 */

namespace TaxonomyImages;

add_action( 'admin_menu', array( 'TaxonomyImages\Settings_Admin', 'settings_menu' ) );

class Settings_Admin {

	/**
	 * Admin Menu
	 *
	 * Create the admin menu link for the settings page.
	 *
	 * @internal  Private. Called via the `admin_menu` action.
	 */
	public static function settings_menu() {

		add_options_page(
			esc_html__( 'Taxonomy Images', 'taxonomy-images' ),  // HTML <title> tag.
			esc_html__( 'Taxonomy Images', 'taxonomy-images' ),  // Link text in admin menu.
			'manage_options',
			'taxonomy_image_plugin_settings',
			array( get_class(), 'settings_page' )
		);

	}

	/**
	 * Settings Page
	 *
	 * This function in conjunction with others use the WordPress
	 * Settings API to create a settings page where users can adjust
	 * the behaviour of this plugin. Please see the following functions
	 * for more insight on the output generated by this function:
	 *
	 * taxonomy_image_plugin_control_taxonomies()
	 *
	 * @internal  Private. Used by add_options_page().
	 */
	public static function settings_page() {

		echo '<div class="wrap">';

		// translators: Heading of the custom administration page.
		echo '<h2>' . esc_html__( 'Taxonomy Images Plugin Settings', 'taxonomy-images' ) . '</h2>';
		echo '<div id="taxonomy-images">';
		echo '<form action="options.php" method="post">';

		settings_fields( 'taxonomy_image_plugin_settings' );
		do_settings_sections( 'taxonomy_image_plugin_settings' );

		// translators: Button on the custom administration page.
		echo '<div class="button-holder"><input class="button-primary" name="submit" type="submit" value="' . esc_attr__( 'Save Changes', 'taxonomy-images' ) . '" /></div>';
		echo '</div></form></div>';

	}

	/**
	 * Taxonomies Setting Field
	 *
	 * @internal  Private. Called by add_settings_field().
	 */
	public static function taxonomies_setting_field() {

		$settings = get_option( 'taxonomy_image_plugin_settings' );
		$taxonomies = get_taxonomies( array(), 'objects' );

		foreach ( (array) $taxonomies as $taxonomy ) {

			if ( ! isset( $taxonomy->name ) || ! isset( $taxonomy->label ) || ! isset( $taxonomy->show_ui ) || empty( $taxonomy->show_ui ) ) {
				continue;
			}

			$id = 'taxonomy-images-' . $taxonomy->name;
			$checked = checked( isset( $settings['taxonomies'] ) && in_array( $taxonomy->name, (array) $settings['taxonomies'] ), true, false );

			printf( '<p><label for="%1$s"><input%2$s id="%1$s" type="checkbox" name="taxonomy_image_plugin_settings[taxonomies][]" value="%3$s" /> %4$s</label></p>', esc_attr( $id ), $checked, esc_attr( $taxonomy->name ), esc_html( $taxonomy->label ) );

		}

	}

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
