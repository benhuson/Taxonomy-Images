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
	 * @internal  Private. Only used for initially defined plugin base file.
	 *
	 * @param  string  $file  Base `__FILE__` of the plugin.
	 */
	public static function set_basename( $file ) {

		self::$file = $file;

	}

}
