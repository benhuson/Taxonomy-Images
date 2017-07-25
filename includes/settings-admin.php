<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Settings Admin
 */

namespace TaxonomyImages;

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
	 * Register settings
	 *
	 * This plugin will store to sets of settings in the
	 * options table. The first is named 'taxonomy_image_plugin'
	 * and stores the associations between terms and images. The
	 * keys in this array represent the term_taxonomy_id of the
	 * term while the value represents the ID of the image
	 * attachment.
	 *
	 * The second setting is used to store everything else. As of
	 * version 0.7 it has one key named 'taxonomies' which is a
	 * flat array consisting of taxonomy names representing a
	 * black-list of registered taxonomies. These taxonomies will
	 * NOT be given an image UI.
	 *
	 * @internal  Private. Called via the `admin_init` action.
	 */
	public static function register_settings() {

		register_setting(
			'taxonomy_image_plugin_settings',
			'taxonomy_image_plugin_settings',
			array( get_class(), 'sanitize_settings' )
		);

		add_settings_section(
			'taxonomy_image_plugin_settings',
			esc_html__( 'Settings', 'taxonomy-images' ),
			'__return_false',
			'taxonomy_image_plugin_settings'
		);

		add_settings_field(
			'taxonomy-images',
			esc_html__( 'Taxonomies', 'taxonomy-images' ),
			array( get_class(), 'taxonomies_setting_field' ),
			'taxonomy_image_plugin_settings',
			'taxonomy_image_plugin_settings'
		);

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

	/**
	 * Get Settings Page Link
	 *
	 * @param   array   Localized link text.
	 * @return  string  HTML link to settings page.
	 */
	private static function get_settings_page_link( $link_text = '' ) {

		if ( empty( $link_text ) ) {
			$link_text = __( 'Manage Settings', 'taxonomy-images' );
		}

		if ( current_user_can( 'manage_options' ) ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'page' => 'taxonomy_image_plugin_settings' ), admin_url( 'options-general.php' ) ) ), esc_html( $link_text ) );
		}

		return '';
	}

	/**
	 * Plugin Meta Links
	 *
	 * Add a link to this plugin's setting page when it
	 * displays in the table on wp-admin/plugins.php.
	 *
	 * @param   array   List of links.
	 * @param   string  Current plugin being displayed in plugins.php.
	 * @return  array   Potentially modified list of links.
	 *
	 * @internal  Private. Called via the `plugin_row_meta` filter.
	 */
	public static function plugin_row_meta( $links, $file ) {

		static $plugin_name = '';

		if ( empty( $plugin_name ) ) {
			$plugin_name = Plugin::basename();
		}

		if ( $plugin_name != $file ) {
			return $links;
		}

		$link = self::get_settings_page_link( esc_html__( 'Settings', 'taxonomy-images' ) );
		if ( ! empty( $link ) ) {
			$links[] = $link;
		}

		return $links;

	}

}
