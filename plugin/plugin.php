<?php

/**
 * @package     Taxononomy Images
 * @subpackage  Plugin
 *
 * Requires WordPress 4.4+ (for term meta support)
 * Requires PHP 5.3+ (for namespace support)
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'load' ) );

class Plugin {

	/**
	 * Plugin Version
	 *
	 * @var  string
	 */
	const VERSION = '0.9.6';

	/**
	 * Plugin Directory
	 *
	 * Used to cache string or subsequent queries.
	 *
	 * @var  string
	 */
	private static $plugin_dir = '';

	/**
	 * Basename
	 *
	 * Used to cache string or subsequent queries.
	 *
	 * @var  string
	 */
	private static $basename = null;

	/**
	 * Plugin Directory URL
	 *
	 * Used to cache string or subsequent queries.
	 *
	 * @var  string
	 */
	private static $plugin_dir_url = '';

	/**
	 * Image Types Class
	 *
	 * @var  Image_Types|null
	 */
	private static $image_types = null;

	/**
	 * Image Admin AJAX Class
	 *
	 * Manages AJAX saving, updating and deleting of term images.
	 *
	 * @var  Image_Admin_AJAX|null
	 */
	private static $ajax = null;

	/**
	 * Cache Class
	 *
	 * Manages caching of term image data.
	 *
	 * @var  Cache|null
	 */
	private static $cache = null;

	/**
	 * Upgrade Class
	 *
	 * @var  Upgrade|null
	 */
	private static $upgrade = null;

	/**
	 * Settings Admin Class
	 *
	 * @var  Settings_Admin|null
	 */
	private static $settings_admin = null;

	/**
	 * Terms Admin Class
	 *
	 * @var  Terms_Admin|null
	 */
	private static $terms_admin = null;

	/**
	 * Theme Class
	 *
	 * @var  Theme|null
	 */
	private static $theme = null;

	/**
	 * Legacy Class
	 *
	 * @var  Legacy|null
	 */
	private static $legacy = null;

	/**
	 * Load
	 *
	 * Require plugin files depending on the context: AJAX, Admin, Public.
	 * Set up all API action/filter hooks.
	 *
	 * @internal  Private. Called via the `plugins_loaded` action.
	 */
	public static function load() {

		/**
		 * Legacy Migration & Compatibility
		 */

		self::require_plugin_files( array(
			'plugin/includes/legacy-class.php',
			'plugin/includes/legacy-hooks.php'
		) );

		$legacy_hooks = new Legacy_Hooks();
		$legacy_hooks->setup_hooks();

		self::$legacy = new Legacy();

		/**
		 * AJAX, Admin & Front-end
		 */

		self::require_plugin_files( array(
			'plugin/includes/term-image-class.php',
			'plugin/includes/image-type-class.php',
			'plugin/includes/image-types-class.php'
		) );

		add_action( 'init', array( get_class(), 'load_textdomain' ) );

		self::$image_types = new Image_Types();

		add_action( 'init', array( self::$image_types, 'register_image_types' ) );

		if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			/**
			 * AJAX only
			 */

			self::require_plugin_files( array(
				'plugin/includes/term-image-admin-control-class.php',
				'plugin/includes/image-admin-ajax-class.php'
			) );

			self::$ajax = new Image_Admin_AJAX();

			add_action( 'wp_ajax_taxonomy_images_update_term_image', array( self::$ajax, 'update_term_image' ) );
			add_action( 'wp_ajax_taxonomy_images_delete_term_image', array( self::$ajax, 'delete_term_image' ) );

		} else {

			/**
			 * Admin & Front-end
			 */

			self::require_plugin_files( array(
				'plugin/includes/cache-class.php'
			) );

			self::$cache = new Cache();

			add_action( 'template_redirect', array( self::$cache, 'cache_queried_images' ) );

			if ( is_admin() ) {

				/**
				 * Admin only
				 */

				self::require_plugin_files( array(
					'plugin/includes/upgrade-class.php',
					'plugin/includes/term-image-admin-control-class.php',
					'plugin/includes/terms-admin-class.php',
					'plugin/includes/settings-admin-class.php'
				) );

				self::$upgrade = new Upgrade();

				add_action( 'admin_init', array( self::$upgrade, 'upgrade_if_required' ) );

				self::$settings_admin = new Settings_Admin();

				add_action( 'admin_menu', array( self::$settings_admin, 'settings_menu' ) );
				add_action( 'admin_init', array( self::$settings_admin, 'register_settings' ) );
				add_filter( 'plugin_row_meta', array( self::$settings_admin, 'plugin_row_meta' ), 10, 2 );

				self::$terms_admin = new Terms_Admin( self::$image_types );

				add_action( 'admin_init', array( self::$terms_admin, 'add_admin_fields' ) );
				add_action( 'admin_enqueue_scripts', array( self::$terms_admin, 'enqueue_scripts' ) );
				add_action( 'admin_print_styles-edit-tags.php', array( self::$terms_admin, 'enqueue_styles' ) );  // Pre WordPress 4.5
				add_action( 'admin_print_styles-term.php', array( self::$terms_admin, 'enqueue_styles' ) );       // WordPress 4.5+

			} else {

				/**
				 * Front-end Only
				 */

				self::require_plugin_file( 'plugin/includes/theme-class.php' );

				self::$theme = new Theme();

				add_action( 'wp_enqueue_scripts', array( self::$theme, 'enqueue_styles' ) );

			}

		}

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
	 * Require plugin files
	 *
	 * @param  array  $files  File paths relative to plugin folder.
	 */
	private static function require_plugin_files( $files ) {

		foreach ( $files as $file ) {
			require_once( self::plugin_file( $file ) );
		}

	}

	/**
	 * Require plugin file
	 *
	 * @param  string  $file  File path relative to plugin folder.
	 */
	private static function require_plugin_file( $file ) {

		require_once( self::plugin_file( $file ) );

	}

	/**
	 * Get a path to a file within this plugin folder
	 *
	 * @param   string  $file  File path relative to plugin directory.
	 * @return  string         Absolute file path.
	 */
	private static function plugin_file( $file = '' ) {

		return self::plugin_dir() . $file;

	}

	/**
	 * Get the base URL to this plugin folder
	 *
	 * @return  string  Absolute path to plugin directory.
	 */
	private static function plugin_dir_url() {

		// Get and cache plugin directory URL
		if ( empty( self::$plugin_dir_url ) ) {
			self::$plugin_dir_url = trailingslashit( plugin_dir_url( self::file() ) );
		}

		return self::$plugin_dir_url;

	}

	/**
	 * Get a URL to a file within this plugin folder
	 *
	 * @param   string  $file  File path relative to plugin directory.
	 * @return  string         URL for file path.
	 */
	public static function plugin_url( $file = '' ) {

		return self::plugin_dir_url() . $file;

	}

	/**
	 * Get the base path to this plugin folder
	 *
	 * @return  string  Plugin directory name.
	 */
	private static function plugin_dir() {

		// Get and cache plugin directory
		if ( empty( self::$plugin_dir ) ) {
			self::$plugin_dir = trailingslashit( dirname( self::file() ) );
		}

		return self::$plugin_dir;

	}

	/**
	 * Plugin Basename
	 *
	 * @return  string  Plugin basename.
	 */
	public static function basename() {

		// Get and cache basename
		if ( is_null( self::$basename ) ) {
			self::$basename = plugin_basename( self::file() );
		}

		return self::$basename;

	}

	/**
	 * Plugin File
	 *
	 * @return  string  Plugin base file.
	 */
	public static function file() {

		return TAXONOMY_IMAGES_FILE;

	}

}
