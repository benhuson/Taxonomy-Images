<?php

/**
 * Config Class
 */
class Taxonomy_Images_Config {

	/**
	 * Plugin File
	 *
	 * @var  string
	 */
	private static $plugin_file = __FILE__;

	/**
	 * Version
	 *
	 * @var  string
	 */
	private static $version = '';

	/**
	 * Basename
	 *
	 * @var  string
	 */
	private static $basename = null;

	/**
	 * Dirname
	 *
	 * @var  string
	 */
	private static $dirname = null;

	/**
	 * URL
	 *
	 * @var  string
	 */
	private static $url = null;

	/**
	 * Set Plugin File
	 *
	 * @param  string  $plugin_file  The full path and filename of the main plugin file.
	 */
	public static function set_plugin_file( $plugin_file ) {

		self::$plugin_file = $plugin_file;

	}

	/**
	 * Set Version
	 *
	 * @return  string  Version string.
	 */
	public static function set_version( $version ) {

		self::$version = $version;

	}

	/**
	 * Get Version
	 *
	 * @return  string  Version string.
	 */
	public static function get_version() {

		return self::$version;

	}

	/**
	 * Check if a WordPress feature is supported
	 *
	 * @param   string   $feature  Feature.
	 * @return  boolean
	 */
	public static function supports( $feature ) {

		switch ( $feature ) {

			/**
			 * Media Modal Supported?
			 *
			 * @see  WordPress 3.5 Blog Post
			 *       https://wordpress.org/news/2012/12/elvin/
			 *
			 * @see  WordPress JavaScript wp.media
			 *       https://codex.wordpress.org/Javascript_Reference/wp.media
			 */
			case 'media_modal':
				return version_compare( get_bloginfo( 'version' ), 3.5 ) >= 0;

		}

		return false;

	}

	/**
	 * Plugin Basename
	 *
	 * @return  string  Plugin basename.
	 */
	public static function basename() {

		// Get and cache basename
		if ( is_null( self::$basename ) ) {
			self::$basename = plugin_basename( self::$plugin_file );
		}

		return self::$basename;

	}

	/**
	 * Plugin Sub Directory
	 *
	 * @param   string  $file  Optional. File path to append.
	 * @return  string         Plugin folder name and filepath.
	 */
	public static function dirname( $file = '' ) {

		// Get and cache dirname
		if ( is_null( self::$dirname ) ) {
			self::$dirname = dirname( self::basename() );
		}

		// Add file path
		if ( ! empty( $file ) ) {
			return trailingslashit( self::$dirname ) . $file;
		}

		return self::$dirname;

	}

	/**
	 * Plugin URL
	 *
	 * @param   string  $file  Optional. File path to append.
	 * @return  string         Plugin directory URL and filepath.
	 */
	public static function url( $file = '' ) {

		// Get and cache URL
		if ( is_null( self::$url ) ) {
			self::$url = plugin_dir_url( self::$plugin_file );
		}

		// Add file path
		if ( ! empty( $file ) ) {
			return trailingslashit( self::$url ) . $file;
		}

		return self::$url;

	}

}
