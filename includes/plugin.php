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
	 * Plugin Basename
	 */
	private static $basename = '';

	/**
	 * Plugin Directory URL
	 */
	private static $plugin_dir_url = '';

	/**
	 * Version Number
	 *
	 * @return  string  The plugin's version number.
	 */
	public static function version() {

		return '1.0.dev';

	}

	/**
	 * Load Plugin
	 */
	public static function load( $base_file ) {

		self::set_base_file( $base_file );

	}

	/**
	 * Set Plugin Base File
	 *
	 * @internal  Private. Only used for initially defining the plugin base file.
	 *
	 * @param  string  $file  Base `__FILE__` of the plugin.
	 */
	public static function set_base_file( $file ) {

		self::$file = $file;

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

		if ( empty( self::$basename ) ) {
			self::$basename = plugin_basename( self::$file );
		}

		return self::$basename;

	}

	/**
	 * Get the base URL to this plugin folder
	 *
	 * @return  string
	 */
	private static function plugin_dir_url() {

		if ( empty( self::$plugin_dir_url ) ) {
			self::$plugin_dir_url = trailingslashit( plugin_dir_url( self::$file ) );
		}

		return self::$plugin_dir_url;

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
	 * @todo  Check for plugin update on `admin_init` to run this method.
	 *
	 * @internal  Private. Only used when activating the plugin.
	 */
	public static function activate() {

		// @todo  Any upgrade migrations here?

	}

	/**
	 * Debug
	 *
	 * Should debug information be displayed?
	 */
	public static function debug() {

		return apply_filters( 'taxonomy-images-debug', false );

	}

}
