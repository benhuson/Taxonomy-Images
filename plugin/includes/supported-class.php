<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Supported
 *
 * This class is not namespaced as it needs to be
 * backwards-compatible with earlier versions of PHP.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Taxonomy_Images_Supported {

	/**
	 * Plugin Supported?
	 *
	 * @return  boolean
	 */
	public static function plugin_supported() {

		return self::php_version_supported() && self::wp_version_supported() && self::is_supported_by_default();

	}

	/**
	 * PHP Version Supported?
	 *
	 * 5.3+ required for namespace support.
	 *
	 * @return  boolean
	 */
	public static function php_version_supported() {

		return version_compare( PHP_VERSION, '5.3.0', '>' );

	}

	/**
	 * WP Version Supported?
	 *
	 * WordPress 4.4 required for term meta support.
	 *
	 * @return  boolean
	 */
	public static function wp_version_supported() {

		global $wp_version;

		return version_compare( $wp_version, '4.4', '>=' );

	}

	/**
	 * Is Supported By Default?
	 *
	 * Used to disable term meta by default until production-ready.
	 * Can use filter in the meantime to add support.
	 *
	 * @return  boolean
	 */
	public static function is_supported_by_default() {

		return apply_filters( 'taxonomy_images/use_term_meta', false );

	}

}
