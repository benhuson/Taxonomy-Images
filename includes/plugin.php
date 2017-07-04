<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Plugin
 */

namespace TaxonomyImages;

add_action( 'init', array( 'TaxonomyImages\Plugin', 'load_textdomain' ) );

class Plugin {

	/**
	 * Plugin `__FILE__`
	 */
	private static $file = '';

	/**
	 * Version Number
	 *
	 * @return  string  The plugin's version number.
	 */
	public static function version() {

		return '1.0.dev';

	}

	/**
	 * Plugin File
	 *
	 * @return  string
	 */
	public static function file() {

		return self::$file;

	}

	/**
	 * Plugin Basename (relative to plugin root)
	 *
	 * @return  string
	 */
	public static function basename() {

		return plugin_basename( self::$file );

	}

	/**
	 * Set Plugin Basename
	 *
	 * @internal  Private. Only used for initially defining the plugin base file.
	 *
	 * @param  string  $file  Base `__FILE__` of the plugin.
	 */
	public static function set_basename( $file ) {

		self::$file = $file;

	}

	/**
	 * Get the base URL to this plugin folder
	 *
	 * @return  string
	 */
	private static function plugin_dir_url() {

		return trailingslashit( plugin_dir_url( self::$file ) );

	}

	/**
	 * Get a URL to a file within this plugin folder
	 *
	 * @return  string
	 */
	public static function plugin_url( $file = '' ) {

		return self::plugin_dir_url() . $file;

	}

	/**
	 * Load Plugin Text Domain
	 *
	 * @internal  Private. Called via the `init` action.
	 */
	public static function load_textdomain() {

		load_plugin_textdomain( 'taxonomy-images', false, dirname( self::basename() ) . '/languages/' );

	}

	/**
	 * Activate
	 *
	 * Called by the plugin activation hook.
	 *
	 * Two entries in the options table will created when this
	 * plugin is activated in the event that they do not exist.
	 *
	 * 'taxonomy_image_plugin_settings' (array) A multi-dimensional array
	 * of user-defined settings. As of version 0.7, only one key is used:
	 * 'taxonomies' which is a whitelist of registered taxonomies having ui
	 * that support the custom image ui provided by this plugin.
	 *
	 * @internal  Private. Only used when activating the plugin.
	 */
	public static function activate() {

		Associations_Legacy::create_option();

		$settings = get_option( 'taxonomy_image_plugin_settings' );

		if ( false === $settings ) {
			add_option( 'taxonomy_image_plugin_settings', array(
				'taxonomies' => array()
			) );
		}

	}

}
