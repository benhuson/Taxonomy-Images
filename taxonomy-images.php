<?php

/*
Plugin Name:          Taxonomy Images
Plugin URI:           https://github.com/benhuson/Taxonomy-Images
Description:          Associate images from your media library to categories, tags and custom taxonomies.
Version:              1.0.dev
Author:               Michael Fields, Ben Huson
Author URI:           https://github.com/benhuson
License:              GNU General Public License v2 or later
License URI:          http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2010-2011  Michael Fields  michael@mfields.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Required versions
$taxonomy_images_has_php_version = version_compare( PHP_VERSION, '5.3.0', '>' );
$taxonomy_images_has_wp_version = version_compare( $wp_version, '4.4', '>=' );

/**
 * Plugin Not Supported Notice
 */
function taxonomy_images_plugin_not_supported_notice() {

	$upgrade = array();
	$requires = array();
	$notice = '<div class="error"><p>' . __( '%1$s requires %2$s to function properly. Please upgrade %3$s or deactivate %1$s.', 'taxonomy-images' ) . '</p></div>';

	if ( ! $taxonomy_images_has_php_version ) {
		$upgrade[] = __( 'PHP', 'taxonomy-images' );
		$requires[] = sprintf( _x( 'PHP %s', 'version', 'taxonomy-images' ), '5.3+' );
	}

	if ( ! $taxonomy_images_has_wp_version ) {
		$upgrade[] = __( 'WordPress', 'taxonomy-images' );
		$requires[] = sprintf( _x( 'WordPress %s', 'version', 'taxonomy-images' ), '4.4+' );
	}

	printf( $notice, __( 'Taxonomy Images', 'taxonomy-images' ), implode( ' and ', $requires ), implode( ' and ', $upgrade ) );

}

// Requires PHP 5.3+ and WordPress 4.4+
if ( ! $taxonomy_images_has_php_version || ! $taxonomy_images_has_wp_version ) {
	add_action( 'admin_notices', 'taxonomy_images_plugin_not_supported_notice' );
	return;
}

require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/plugin.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/term.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-field.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-control.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/public-filters.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/cache.php' );

if ( is_admin() ) {

	// Admin & AJAX
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

		// AJAX only
		require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-ajax.php' );

	} else {

		// Admin only
		require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/terms-admin.php' );
		require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/settings-admin.php' );

	}

} else {

	// Front-end Only
	require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/public-css.php' );

}

TaxonomyImages\Plugin::set_base_file( __FILE__ );

register_activation_hook( TaxonomyImages\Plugin::file(), array( 'TaxonomyImages\Plugin', 'activate' ) );
