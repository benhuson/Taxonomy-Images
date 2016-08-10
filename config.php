<?php

/**
 * Config Class
 */
class Taxonomy_Images_Config {

	/**
	 * Version
	 *
	 * @var  string
	 */
	private static $version = '0.9.6';

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

}
