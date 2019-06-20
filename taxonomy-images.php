<?php

/*
Plugin Name:          Taxonomy Images
Plugin URI:           https://github.com/benhuson/Taxonomy-Images
Description:          Associate images from your media library to categories, tags and custom taxonomies.
Version:              1.0
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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! defined( 'TAXONOMY_IMAGES_FILE' ) ) {
	define( 'TAXONOMY_IMAGES_FILE', __FILE__ );
}

require_once( trailingslashit( dirname( TAXONOMY_IMAGES_FILE ) ) . 'plugin/includes/supported-class.php' );

if ( Taxonomy_Images_Supported::plugin_supported() ) {

	// Load term meta plugin version which requires PHP 5.3 and taxonomy meta support
	require_once( trailingslashit( dirname( TAXONOMY_IMAGES_FILE ) ) . 'plugin/plugin.php' );

} else {

	// Load legacy plugin version
	require_once( trailingslashit( dirname( TAXONOMY_IMAGES_FILE ) ) . 'legacy/plugin.php' );

}
