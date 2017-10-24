<?php

/*
Plugin Name:          Taxonomy Images
Plugin URI:           https://github.com/benhuson/Taxonomy-Images
Description:          Associate images from your media library to categories, tags and custom taxonomies.
Version:              0.9.6
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

require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/config.php' );

Taxonomy_Images_Config::set_version( '0.9.6' );
Taxonomy_Images_Config::set_plugin_file( __FILE__ );

require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/term.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/public-filters.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/functions.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/deprecated.php' );

// Register custom image size with WordPress.
add_action( 'init', 'taxonomy_image_plugin_add_image_size' );

// Load Plugin Text Domain.
add_action( 'init', 'taxonomy_image_plugin_text_domain' );

// Modal Button.
add_filter( 'attachment_fields_to_edit', 'taxonomy_image_plugin_modal_button', 20, 2 );

// Register settings with WordPress.
add_action( 'admin_init', 'taxonomy_image_plugin_register_settings' );

// Admin Menu.
add_action( 'admin_menu', 'taxonomy_images_settings_menu' );

// Create an association.
add_action( 'wp_ajax_taxonomy_image_create_association', 'taxonomy_image_plugin_create_association' );

// Remove an association.
add_action( 'wp_ajax_taxonomy_image_plugin_remove_association', 'taxonomy_image_plugin_remove_association' );

// Get a list of user-defined associations.
add_action( 'init', 'taxonomy_image_plugin_get_associations' );

// Dynamically create hooks for each taxonomy.
add_action( 'admin_init', 'taxonomy_image_plugin_add_dynamic_hooks' );

// Custom javascript for modal media box.
add_action( 'admin_print_scripts-media-upload-popup', 'taxonomy_image_plugin_media_upload_popup_js' );

// Custom javascript for wp-admin/edit-tags.php.
add_action( 'admin_print_scripts-edit-tags.php', 'taxonomy_image_plugin_edit_tags_js' );

// Custom styles.
add_action( 'admin_print_styles-edit-tags.php', 'taxonomy_image_plugin_css_admin' );  // Pre WordPress 4.5
add_action( 'admin_print_styles-term.php', 'taxonomy_image_plugin_css_admin' );       // WordPress 4.5+
add_action( 'admin_print_styles-media-upload-popup', 'taxonomy_image_plugin_css_admin' );

// Thickbox styles.
add_action( 'admin_print_styles-edit-tags.php', 'taxonomy_image_plugin_css_thickbox' );

// Public Styles.
add_action( 'wp_enqueue_scripts', 'taxonomy_image_plugin_css_public' );

// Activation.
register_activation_hook( __FILE__, 'taxonomy_image_plugin_activate' );

// Cache Images.
add_action( 'template_redirect', 'taxonomy_image_plugin_cache_queried_images' );

// Plugin Meta Links.
add_filter( 'plugin_row_meta', 'taxonomy_images_plugin_row_meta', 10, 2 );

// Enqueue Admin Scripts.
add_action( 'admin_enqueue_scripts', 'taxonomy_images_admin_enqueue_scripts' );
