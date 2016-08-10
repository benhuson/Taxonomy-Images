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
	private static $version = '0.9.6';

	/**
	 * Set Plugin File
	 *
	 * @param  string  $plugin_file  The full path and filename of the main plugin file.
	 */
	public static function set_plugin_file( $plugin_file ) {

		self::$plugin_file = $plugin_file;

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

		return plugin_basename( self::$plugin_file );

	}

	/**
	 * Plugin Sub Directory
	 *
	 * @param   string  $file  Optional. File path to append.
	 * @return  string         Plugin folder name and filepath.
	 */
	public static function dirname( $file = '' ) {

		$dirname = dirname( self::basename() );

		if ( ! empty( $file ) ) {
			$dirname = trailingslashit( $dirname ) . $file;
		}

		return $dirname;

	}

	/**
	 * Plugin URL
	 *
	 * @param   string  $file  Optional. File path to append.
	 * @return  string         Plugin directory URL and filepath.
	 */
	public static function url( $file = '' ) {

		$path = plugin_dir_url( self::$plugin_file );

		if ( ! empty( $file ) ) {
			$path = trailingslashit( $path ) . $file;
		}

		return $path;

	}

}
