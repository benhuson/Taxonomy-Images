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

require_once( trailingslashit( dirname( __FILE__ ) ) . 'public-filters.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/term.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/term-legacy.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-field.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-control.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/image-admin-ajax.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/associations-legacy.php' );
require_once( trailingslashit( dirname( __FILE__ ) ) . 'deprecated.php' );

/**
 * Version Number.
 *
 * @return    string    The plugin's version number.
 * @access    private
 * @since     0.7
 * @alter     0.7.4
 */
function taxonomy_image_plugin_version() {
	return '1.0.dev';
}


/**
 * Get a url to a file in this plugin.
 *
 * @return    string
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_url( $file = '' ) {
	static $path = '';
	if ( empty( $path ) ) {
		$path = plugin_dir_url( __FILE__ );
	}
	return $path . $file;
}

// Register custom image size with WordPress.
add_action( 'init', array( 'TaxonomyImages\Image', 'add_image_size' ) );

/**
 * Load Plugin Text Domain.
 *
 * @access    private
 * @since     0.7.3
 */
function taxonomy_image_plugin_text_domain() {
	load_plugin_textdomain( 'taxonomy-images', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'taxonomy_image_plugin_text_domain' );

/**
 * Sanitize Settings.
 *
 * This function is responsible for ensuring that
 * all values within the 'taxonomy_image_plugin_settings'
 * options are of the appropriate type.
 *
 * @param     array     Unknown.
 * @return    array     Multi-dimensional array of sanitized settings.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_settings_sanitize( $dirty ) {
	$clean = array();
	if ( isset( $dirty['taxonomies'] ) ) {
		$taxonomies = get_taxonomies();
		foreach ( (array) $dirty['taxonomies'] as $taxonomy ) {
			if ( in_array( $taxonomy, $taxonomies ) )
				$clean['taxonomies'][] = $taxonomy;
		}
	}

	/* translators: Notice displayed on the custom administration page. */
	$message = __( 'Image support for taxonomies successfully updated', 'taxonomy-images' );
	if ( empty( $clean ) ) {
		/* translators: Notice displayed on the custom administration page. */
		$message = __( 'Image support has been disabled for all taxonomies.', 'taxonomy-images' );
	}

	add_settings_error( 'taxonomy_image_plugin_settings', 'taxonomies_updated', esc_html( $message ), 'updated' );

	return $clean;
}


/**
 * Register settings with WordPress.
 *
 * This plugin will store to sets of settings in the
 * options table. The first is named 'taxonomy_image_plugin'
 * and stores the associations between terms and images. The
 * keys in this array represent the term_taxonomy_id of the
 * term while the value represents the ID of the image
 * attachment.
 *
 * The second setting is used to store everything else. As of
 * version 0.7 it has one key named 'taxonomies' whichi is a
 * flat array consisting of taxonomy names representing a
 * black-list of registered taxonomies. These taxonomies will
 * NOT be given an image UI.
 *
 * @access    private
 */
function taxonomy_image_plugin_register_settings() {
	register_setting(
		'taxonomy_image_plugin',
		'taxonomy_image_plugin',
		array( 'TaxonomyImages\Associations_Legacy', 'sanitize' )
	);
	register_setting(
		'taxonomy_image_plugin_settings',
		'taxonomy_image_plugin_settings',
		'taxonomy_image_plugin_settings_sanitize'
	);
	add_settings_section(
		'taxonomy_image_plugin_settings',
		esc_html__( 'Settings', 'taxonomy-images' ),
		'__return_false',
		'taxonomy_image_plugin_settings'
	);
	add_settings_field(
		'taxonomy-images',
		esc_html__( 'Taxonomies', 'taxonomy-images' ),
		'taxonomy_image_plugin_control_taxonomies',
		'taxonomy_image_plugin_settings',
		'taxonomy_image_plugin_settings'
	);
}
add_action( 'admin_init', 'taxonomy_image_plugin_register_settings' );


/**
 * Admin Menu.
 *
 * Create the admin menu link for the settings page.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_images_settings_menu() {
	add_options_page(
		esc_html__( 'Taxonomy Images', 'taxonomy-images' ), // HTML <title> tag.
		esc_html__( 'Taxonomy Images', 'taxonomy-images' ), // Link text in admin menu.
		'manage_options',
		'taxonomy_image_plugin_settings',
		'taxonomy_image_plugin_settings_page'
	);
}
add_action( 'admin_menu', 'taxonomy_images_settings_menu' );


/**
 * Settings Page Template.
 *
 * This function in conjunction with others usei the WordPress
 * Settings API to create a settings page where users can adjust
 * the behaviour of this plugin. Please see the following functions
 * for more insight on the output generated by this function:
 *
 * taxonomy_image_plugin_control_taxonomies()
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_settings_page() {
	print "\n" . '<div class="wrap">';

	/* translators: Heading of the custom administration page. */
	print "\n" . '<h2>' . esc_html__( 'Taxonomy Images Plugin Settings', 'taxonomy-images' ) . '</h2>';
	print "\n" . '<div id="taxonomy-images">';
	print "\n" . '<form action="options.php" method="post">';

	settings_fields( 'taxonomy_image_plugin_settings' );
	do_settings_sections( 'taxonomy_image_plugin_settings' );

	/* translators: Button on the custom administration page. */
	print "\n" . '<div class="button-holder"><input class="button-primary" name="submit" type="submit" value="' . esc_attr__( 'Save Changes', 'taxonomy-images' ) . '" /></div>';
	print "\n" . '</div></form></div>';
}


/**
 * Taxonomy Checklist.
 *
 * @access    private
 */
function taxonomy_image_plugin_control_taxonomies() {
	$settings = get_option( 'taxonomy_image_plugin_settings' );
	$taxonomies = get_taxonomies( array(), 'objects' );
	foreach ( (array) $taxonomies as $taxonomy ) {
		if ( ! isset( $taxonomy->name ) ) {
			continue;
		}

		if ( ! isset( $taxonomy->label ) ) {
			continue;
		}

		if ( ! isset( $taxonomy->show_ui ) || empty( $taxonomy->show_ui ) ) {
			continue;
		}

		$id = 'taxonomy-images-' . $taxonomy->name;

		$checked = '';
		if ( isset( $settings['taxonomies'] ) && in_array( $taxonomy->name, (array) $settings['taxonomies'] ) ) {
			$checked = ' checked="checked"';
		}

		print "\n" . '<p><label for="' . esc_attr( $id ) . '">';
		print '<input' . $checked . ' id="' . esc_attr( $id ) . '" type="checkbox" name="taxonomy_image_plugin_settings[taxonomies][]" value="' . esc_attr( $taxonomy->name ) . '" />';
		print ' ' . esc_html( $taxonomy->label ) . '</label></p>';
	}
}

// Handle AJAX Updates
add_action( 'wp_ajax_taxonomy_images_update_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'update_term_image' ) );
add_action( 'wp_ajax_taxonomy_images_delete_term_image', array( 'TaxonomyImages\Image_Admin_AJAX', 'delete_term_image' ) );

// Load a list of user-defined associations.
add_action( 'init', array( 'TaxonomyImages\Associations_Legacy', 'get' ) );

/**
 * Dynamically create hooks for each taxonomy.
 *
 * Adds hooks for each taxonomy that the user has given
 * an image interface to via settings page. These hooks
 * enable the image interface on wp-admin/edit-tags.php.
 *
 * @access    private
 * @since     0.4.3
 * @alter     0.7
 */
function taxonomy_image_plugin_add_dynamic_hooks() {
	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( ! isset( $settings['taxonomies'] ) ) {
		return;
	}

	foreach ( $settings['taxonomies'] as $taxonomy ) {
		add_filter( 'manage_' . $taxonomy . '_custom_column', 'taxonomy_image_plugin_taxonomy_rows', 15, 3 );
		add_filter( 'manage_edit-' . $taxonomy . '_columns',  'taxonomy_image_plugin_taxonomy_columns' );
		add_action( $taxonomy . '_edit_form_fields',          'taxonomy_image_plugin_edit_tag_form', 10, 2 );
	}
}
add_action( 'admin_init', 'taxonomy_image_plugin_add_dynamic_hooks' );


/**
 * Edit Term Columns.
 *
 * Insert a new column on wp-admin/edit-tags.php.
 *
 * @see taxonomy_image_plugin_add_dynamic_hooks()
 *
 * @param     array     A list of columns.
 * @return    array     List of columns with "Images" inserted after the checkbox.
 *
 * @access    private
 * @since     0.4.3
 */
function taxonomy_image_plugin_taxonomy_columns( $original_columns ) {
	$new_columns = $original_columns;
	array_splice( $new_columns, 1 );
	$new_columns['taxonomy_image_plugin'] = esc_html__( 'Image', 'taxonomy-images' );
	return array_merge( $new_columns, $original_columns );
}


/**
 * Edit Term Rows.
 *
 * Create image control for each term row of wp-admin/edit-tags.php.
 *
 * @see taxonomy_image_plugin_add_dynamic_hooks()
 *
 * @param     string    Row.
 * @param     string    Name of the current column.
 * @param     int       Term ID.
 * @return    string    HTML image control.
 *
 * @access    private
 * @since     2010-11-08
 */
function taxonomy_image_plugin_taxonomy_rows( $row, $column_name, $term_id ) {

	global $taxonomy;

	if ( 'taxonomy_image_plugin' === $column_name ) {

		$term = get_term( $term_id, $taxonomy );

		$control = new TaxonomyImages\Image_Admin_Control( $term );

		return $row . $control->get_rendered();
	}

	return $row;

}

/**
 * Edit Term Control.
 *
 * Create image control for wp-admin/edit-tag-form.php.
 * Hooked into the '{$taxonomy}_edit_form_fields' action.
 *
 * @param     stdClass  Term object.
 * @param     string    Taxonomy slug.
 *
 * @access    private
 * @since     2010-11-08
 */
function taxonomy_image_plugin_edit_tag_form( $term, $taxonomy ) {

	$field = new TaxonomyImages\Image_Admin_Field( $term );
	$control = new TaxonomyImages\Image_Admin_Control( $term );

	$taxonomy = get_taxonomy( $taxonomy );

	?>
	<tr class="form-field hide-if-no-js">
		<th scope="row" valign="top">
			<label for="description"><?php print esc_html__( 'Featured Image', 'taxonomy-images' ) ?></label>
		</th>
		<td>
			<?php echo $control->get_rendered(); ?>
			<div class="clear"></div>
			<?php $field->the_description( '<span class="description">', '</span>' ); ?>
		</td>
	</tr>
	<?php

}

/**
 * Custom styles.
 *
 * @since     0.7
 * @access    private
 */
function taxonomy_image_plugin_css_admin() {
	if ( false == taxonomy_image_plugin_is_screen_active() && current_filter() != 'admin_print_styles-media-upload-popup' ) {
		return;
	}

	wp_enqueue_style(
		'taxonomy-image-plugin-edit-tags',
		taxonomy_image_plugin_url( 'css/admin.css' ),
		array(),
		taxonomy_image_plugin_version(),
		'screen'
	);
}
add_action( 'admin_print_styles-edit-tags.php', 'taxonomy_image_plugin_css_admin' );  // Pre WordPress 4.5
add_action( 'admin_print_styles-term.php', 'taxonomy_image_plugin_css_admin' );       // WordPress 4.5+

/**
 * Public Styles.
 *
 * Prints custom css to all public pages. If you do not
 * wish to have these styles included for you, please
 * insert the following code into your theme's functions.php
 * file:
 *
 * add_filter( 'taxonomy-images-disable-public-css', '__return_true' );
 *
 * @since     0.7
 * @access    private
 */
function taxonomy_image_plugin_css_public() {
	if ( apply_filters( 'taxonomy-images-disable-public-css', false ) ) {
		return;
	}

	wp_enqueue_style(
		'taxonomy-image-plugin-public',
		taxonomy_image_plugin_url( 'css/style.css' ),
		array(),
		taxonomy_image_plugin_version(),
		'screen'
	);
}
add_action( 'wp_enqueue_scripts', 'taxonomy_image_plugin_css_public' );


/**
 * Activation.
 *
 * Two entries in the options table will created when this
 * plugin is activated in the event that they do not exist.
 *
 * 'taxonomy_image_plugin_settings' (array) A multi-dimensional array
 * of user-defined settings. As of version 0.7, only one key is used:
 * 'taxonomies' which is a whitelist of registered taxonomies having ui
 * that support the custom image ui provided by this plugin.
 *
 * @access    private
 * @alter     0.7
 */
function taxonomy_image_plugin_activate() {

	TaxonomyImages\Associations_Legacy::create_option();

	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( false === $settings ) {
		add_option( 'taxonomy_image_plugin_settings', array(
			'taxonomies' => array()
		) );
	}
}
register_activation_hook( __FILE__, 'taxonomy_image_plugin_activate' );


/**
 * Is Screen Active?
 *
 * @return    bool
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_is_screen_active() {
	$screen = get_current_screen();
	if ( ! isset( $screen->taxonomy ) ) {
		return false;
	}

	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( ! isset( $settings['taxonomies'] ) ) {
		return false;
	}

	if ( in_array( $screen->taxonomy, $settings['taxonomies'] ) ) {
		return true;
	}

	return false;
}


/**
 * Cache Images
 *
 * Sets the WordPress object cache for all term images
 * associated to the posts in the provided array. This
 * function has been created to minimize queries when
 * using this plugins get_the_terms() style function.
 *
 * @param     array          Post objects.
 *
 * @access    private
 * @since     1.1
 */
function taxonomy_image_plugin_cache_images( $posts ) {
	$assoc = TaxonomyImages\Associations_Legacy::get();
	if ( empty( $assoc ) ) {
		return;
	}

	$tt_ids = array();
	foreach ( (array) $posts as $post ) {
		if ( ! isset( $post->ID ) || ! isset( $post->post_type ) ) {
			continue;
		}

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) ) {
			continue;
		}

		foreach ( $taxonomies as $taxonomy ) {
			$the_terms = get_the_terms( $post->ID, $taxonomy );
			foreach ( (array) $the_terms as $term ) {
				if ( ! isset( $term->term_taxonomy_id ) ) {
					continue;
				}
				$tt_ids[] = $term->term_taxonomy_id;
			}
		}
	}
	$tt_ids = array_filter( array_unique( $tt_ids ) );

	$image_ids = array();
	foreach ( $tt_ids as $tt_id ) {
		if ( ! isset( $assoc[ $tt_id ] ) ) {
			continue;
		}

		if ( in_array( $assoc[ $tt_id ], $image_ids ) ) {
			continue;
		}

		$image_ids[] = $assoc[ $tt_id ];
	}

	if ( empty( $image_ids ) ) {
		return;
	}

	$images = get_posts( array(
		'include'   => $image_ids,
		'post_type' => 'attachment'
	) );
}


/**
 * Cache Images
 *
 * Cache all term images associated with posts in
 * the main WordPress query.
 *
 * @param     array          Post objects.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_cache_queried_images() {
	global $posts;
	taxonomy_image_plugin_cache_images( $posts );
}
add_action( 'template_redirect', 'taxonomy_image_plugin_cache_queried_images' );


/**
 * Check Taxonomy
 *
 * Wrapper for WordPress core functions taxonomy_exists().
 * In the event that an unregistered taxonomy is passed a
 * E_USER_NOTICE will be generated.
 *
 * @param     string         Taxonomy name as registered with WordPress.
 * @param     string         Name of the current function or filter.
 * @return    bool           True if taxonomy exists, False if not.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_check_taxonomy( $taxonomy, $filter ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		trigger_error( sprintf( esc_html__( 'The %1$s argument for %2$s is set to %3$s which is not a registered taxonomy. Please check the spelling and update the argument.', 'taxonomy-images' ),
			'<var>' . esc_html__( 'taxonomy', 'taxonomy-images' ) . '</var>',
			'<code>' . esc_html( $filter ) . '</code>',
			'<strong>' . esc_html( $taxonomy ) . '</strong>'
		) );
		return false;
	}

	$settings = get_option( 'taxonomy_image_plugin_settings' );

	if ( ! isset( $settings['taxonomies'] ) ) {
		trigger_error( sprintf( esc_html__( 'No taxonomies have image support. %1$s', 'taxonomy-images' ), taxonomy_images_plugin_settings_page_link() ) );
		return false;
	}

	if ( ! in_array( $taxonomy, (array) $settings['taxonomies'] ) ) {
		trigger_error( sprintf( esc_html__( 'The %1$s taxonomy does not have image support. %2$s', 'taxonomy-images' ),
			'<strong>' . esc_html( $taxonomy ) . '</strong>',
			taxonomy_images_plugin_settings_page_link()
		) );
		return false;
	}

	return true;
}


/**
 * Please Use Filter.
 *
 * Report to user that they are directly calling a function
 * instead of using supported filters. A E_USER_NOTICE will
 * be generated.
 *
 * @param     string         Name of function called.
 * @param     string         Name of filter to use instead.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_please_use_filter( $function, $filter ) {
	trigger_error( sprintf( esc_html__( 'The %1$s has been called directly. Please use the %2$s filter instead.', 'taxonomy-images' ),
		'<code>' . esc_html( $function . '()' ) . '</code>',
		'<code>' . esc_html( $filter ) . '</code>'
	) );
}


/**
 * Plugin Meta Links.
 *
 * Add a link to this plugin's setting page when it
 * displays in the table on wp-admin/plugins.php.
 *
 * @param     array          List of links.
 * @param     string         Current plugin being displayed in plugins.php.
 * @return    array          Potentially modified list of links.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_images_plugin_row_meta( $links, $file ) {
	static $plugin_name = '';

	if ( empty( $plugin_name ) ) {
		$plugin_name = plugin_basename( __FILE__ );
	}

	if ( $plugin_name != $file ) {
		return $links;
	}

	$link = taxonomy_images_plugin_settings_page_link( esc_html__( 'Settings', 'taxonomy-images' ) );
	if ( ! empty( $link ) ) {
		$links[] = $link;
	}

	$links[] = '<a href="http://wordpress.mfields.org/donate/">' . esc_html__( 'Donate', 'taxonomy-images' ) . '</a>';

	return $links;
}
add_filter( 'plugin_row_meta', 'taxonomy_images_plugin_row_meta', 10, 2 );


/**
 * Settings Page Link.
 *
 * @param     array     Localized link text.
 * @return    string    HTML link to settings page.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_images_plugin_settings_page_link( $link_text = '' ) {
	if ( empty( $link_text ) ) {
		$link_text = __( 'Manage Settings', 'taxonomy-images' );
	}

	$link = '';
	if ( current_user_can( 'manage_options' ) ) {
		$link = '<a href="' . esc_url( add_query_arg( array( 'page' => 'taxonomy_image_plugin_settings' ), admin_url( 'options-general.php' ) ) ) . '">' . esc_html( $link_text ) . '</a>';
	}

	return $link;
}

/**
 * Enqueue Admin Scripts
 *
 * @since  0.9
 */
function taxonomy_images_admin_enqueue_scripts() {

	if ( false == taxonomy_image_plugin_is_screen_active() ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'taxonomy-images-media-modal',
		taxonomy_image_plugin_url( 'js/media-modal.js' ),
		array( 'jquery' ),
		taxonomy_image_plugin_version()
	);

	wp_localize_script( 'taxonomy-images-media-modal', 'TaxonomyImagesMediaModal', array(
		'wp_media_post_id'     => 0,
		'attachment_id'        => 0,
		'uploader_title'       => __( 'Featured Image', 'taxonomy-images' ),
		'uploader_button_text' => __( 'Set featured image', 'taxonomy-images' ),
		'default_img_src'      => taxonomy_image_plugin_url( 'default.png' )
	) );

}
add_action( 'admin_enqueue_scripts', 'taxonomy_images_admin_enqueue_scripts' );
