<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Plugin
 */

namespace TaxonomyImages;

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
	 * Plugin Directory
	 */
	private static $plugin_dir = '';

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
		self::setup();

	}

	/**
	 * Set Plugin Base File
	 *
	 * @internal  Private. Only used for initially defining the plugin base file.
	 *
	 * @param  string  $file  Base `__FILE__` of the plugin.
	 */
	private static function set_base_file( $file ) {

		self::$file = $file;

	}

	/**
	 * Setup Plugin Files, Actions & Filters
	 */
	private static function setup() {

		// AJAX, Admin & Front-end
		self::require_plugin_file( 'includes/term-image.php' );
		self::require_plugin_file( 'includes/image-type.php' );
		self::require_plugin_file( 'includes/image-types.php' );
		self::require_plugin_file( 'includes/image.php' );

		add_action( 'init', array( get_class(), 'load_textdomain' ) );
		add_action( 'init', array( 'TaxonomyImages\Image_Types', 'register_image_types' ) );
		add_action( 'init', array( 'TaxonomyImages\Image', 'add_image_size' ) );

		if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// AJAX only
			self::require_plugin_file( 'includes/term-image-admin.php' );
			self::require_plugin_file( 'includes/image-admin-ajax.php' );

			add_action( 'wp_ajax_taxonomy_images_update_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'update_term_image' ) );
			add_action( 'wp_ajax_taxonomy_images_delete_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'delete_term_image' ) );

		} else {

			// Admin & Front-end
			self::require_plugin_file( 'includes/public-filters.php' );
			self::require_plugin_file( 'includes/cache.php' );

			add_action( 'template_redirect', array( 'TaxonomyImages\Cache', 'cache_queried_images' ) );

			if ( is_admin() ) {

				// Admin only
				self::require_plugin_file( 'includes/term-image-admin.php' );
				self::require_plugin_file( 'includes/term-image-admin-control.php' );
				self::require_plugin_file( 'includes/terms-admin.php' );
				self::require_plugin_file( 'includes/settings-admin.php' );

				add_action( 'admin_init', array( 'TaxonomyImages\Terms_Admin', 'add_admin_fields' ) );
				add_action( 'admin_enqueue_scripts', array( 'TaxonomyImages\Terms_Admin', 'enqueue_scripts' ) );
				add_action( 'admin_print_styles-edit-tags.php', array( 'TaxonomyImages\Terms_Admin', 'enqueue_styles' ) );  // Pre WordPress 4.5
				add_action( 'admin_print_styles-term.php', array( 'TaxonomyImages\Terms_Admin', 'enqueue_styles' ) );       // WordPress 4.5+
				add_action( 'admin_menu', array( 'TaxonomyImages\Settings_Admin', 'settings_menu' ) );
				add_action( 'admin_init', array( 'TaxonomyImages\Settings_Admin', 'register_settings' ) );
				add_filter( 'plugin_row_meta', array( 'TaxonomyImages\Settings_Admin', 'plugin_row_meta' ), 10, 2 );

			} else {

				// Front-end Only
				self::require_plugin_file( 'includes/public-css.php' );

				add_action( 'wp_enqueue_scripts', array( 'TaxonomyImages\Public_CSS', 'enqueue_styles' ) );

			}

		}

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
	 * Get the base path to this plugin folder
	 *
	 * @return  string
	 */
	private static function plugin_dir() {

		if ( empty( self::$plugin_dir ) ) {
			self::$plugin_dir = trailingslashit( dirname( self::$file ) );
		}

		return self::$plugin_dir;

	}

	/**
	 * Get a path to a file within this plugin folder
	 *
	 * @return  string
	 */
	private static function plugin_file( $file = '' ) {

		return self::plugin_dir() . $file;

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
	 * Require plugin file
	 */
	private static function require_plugin_file( $file ) {

		require_once( self::plugin_file( $file ) );

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
