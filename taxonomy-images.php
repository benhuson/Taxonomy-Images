<?php
/*
Plugin Name: Taxonomy Images BETA
Plugin URI: http://wordpress.mfields.org/plugins/taxonomy-images/
Description: The Taxonomy Images plugin enables you to associate images from your Media Library to categories, tags and taxonomies.
Version: 0.6 - ALPHA
Author: Michael Fields
Author URI: http://wordpress.mfields.org/
License: GPLv2

Copyright 2010  Michael Fields  michael@mfields.org

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

/* 2.9 Branch support */
if( !function_exists( 'taxonomy_exists' ) ) {
	function taxonomy_exists( $taxonomy ) {
		global $wp_taxonomies;
		return isset( $wp_taxonomies[$taxonomy] );
	}
}

define( 'TAXONOMY_IMAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TAXONOMY_IMAGE_PLUGIN_SLUG', 'taxonomy_images_plugin' );
define( 'TAXONOMY_IMAGE_PLUGIN_VERSION', '0.6' );
define( 'TAXONOMY_IMAGE_PLUGIN_PERMISSION', 'manage_categories' );

$taxonomy_image_plugin_image = array(
	'name' => 'detailwq',
	'size' => array( 30, 30, true )
	);

/**
 * Register custom image size with WordPress.
 * @global $taxonomy_image_plugin_image.
 * @access private
 * @since 2010-10-28
 */
function taxonomy_image_plugin_get_image_add_image_size() {
	global $taxonomy_image_plugin_image;
	add_image_size(
		$taxonomy_image_plugin_image['name'],
		$taxonomy_image_plugin_image['size'][0],
		$taxonomy_image_plugin_image['size'][1],
		$taxonomy_image_plugin_image['size'][2]
		);
}
add_action( 'init', 'taxonomy_image_plugin_get_image_add_image_size' );


/**
 * Create a button in the modal media window to associate the current image to the term.
 * 
 * @access private
 * @since 2010-10-28
 */
function taxonomy_image_plugin_add_image_to_taxonomy_button( $fields, $post ) {
	if(
		/* Newly uploaded image. */
		( isset( $_POST['fetch'] ) && 1 == $_POST['fetch'] ) ||
		
		/* Image from the Media Library tab. */
		( isset( $_GET[ TAXONOMY_IMAGE_PLUGIN_SLUG ] ) ) 
	) {
		$fields['image-size']['extra_rows']['taxonomy-image-plugin-button']['html'] = '<a rel="' . (int) $post->ID . '" class="button-primary taxonomy_image_plugin" href="#" onclick="return false;">' . __( 'Add Thumbnail to Taxonomy', 'taxonomy_image_plugin' ) . '</a>';
	}
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'taxonomy_image_plugin_add_image_to_taxonomy_button', 20, 2 );


/**
 * Return a url to a custom image size.
 * 
 * If size doesn't exist, attempt to create a resized version. 
 * 
 * @access private. 
 * @param int The database id of an image attachment.
 * @global $taxonomy_image_plugin_image.
 * @since 2010-10-28
 */
function taxonomy_image_plugin_get_image( $id ) {
	global $taxonomy_image_plugin_image;

	/* Return url to custom intermediate size if it exists. */
	$img = image_get_intermediate_size( $id, $taxonomy_image_plugin_image['name'] );
	if( isset( $img['url'] ) ) {
		return $img['url'];
	}

	/* Detail image does not exist, attempt to create it. */
	$wp_upload_dir = wp_upload_dir();
	if( isset( $wp_upload_dir['basedir'] ) ) {

		/* Create path to original uploaded image. */
		$path = trailingslashit( $wp_upload_dir['basedir'] ) . get_post_meta( $id, '_wp_attached_file', true );
		if( is_file( $path ) ) {

			/* Attempt to create a new downsized version of the original image. */
			$new = image_resize( $path,
				$taxonomy_image_plugin_image['size'][0],
				$taxonomy_image_plugin_image['size'][1],
				$taxonomy_image_plugin_image['size'][2]
				);

			/* Image creation successful. Generate and cache image metadata. Return url. */
			if( !is_wp_error( $new ) ) {
				$meta = wp_generate_attachment_metadata( $id, $path );
				wp_update_attachment_metadata( $id, $meta );
				$img = image_get_intermediate_size( $id, $taxonomy_image_plugin_image['name'] );
				if( isset( $img['url'] ) ) {
					return $img['url'];
				}
			}
		}
	}

	/* Custom intermediate size cannot be created, try for thumbnail. */
	$img = image_get_intermediate_size( $id, 'thumbnail' );
	if( isset( $img['url'] ) ) {
		return $img['url'];
	}

	/* Thumbnail cannot be found, try fullsize. */
	$url = wp_get_attachment_url( $id );
	if( !empty( $url ) ) {
		return $url;
	}

	/*
	 * No images can be found. This is most likely caused by a user deleting an attachment
	 * before deleting it's association with a taxonomy.
	 */
	if( is_admin() ) {
		$associations = get_option( 'taxonomy_image_plugin' );
		foreach( $associations as $term => $img ) {
			if( $img === $id ) {
				unset( $associations[$term] );
			}
		}
		update_option( 'taxonomy_image_plugin', $associations );
	}

	/* This function has been called in a theme template and no image can be found. */
	return '';
}


/*
 * Remove the uri tab from the media upload box.
 * This plugin only supports associating images from the media library.
 * Leaving this tab will only confuse users.
 * @return array 
 */
function taxonomy_image_plugin_media_upload_remove_url_tab( $tabs ) {
	if( isset( $_GET[TAXONOMY_IMAGE_PLUGIN_SLUG] ) ) {
		unset( $tabs['type_url'] );
	}
	return $tabs;
}
add_filter( 'media_upload_tabs', 'taxonomy_image_plugin_media_upload_remove_url_tab' );


/*
 * Ensures that all key/value pairs are integers.
 * @param array An array of term_taxonomy_id/attachment_id pairs.
 * @return array 
 */
function taxonomy_image_plugin_sanitize_setting( $setting ) {
	$o = array();	
	foreach( (array) $setting as $key => $value ) {
		$o[ (int) $key ] = (int) $value;
	}
	return $o;
}


/**
 * JSON Respose.
 * Terminate script execution.
 * @param array Values to be encoded in JSON.
 * @return void
 */
function taxonomy_image_plugin_json_response( $response ) {
	header( 'Content-type: application/jsonrequest' );
	print json_encode( $response );
	exit;
}


/**
 * Register settings with WordPress.
 * @return void
 */
function taxonomy_image_plugin_register_setting() {
	register_setting( 'taxonomy_image_plugin', 'taxonomy_image_plugin', 'taxonomy_image_plugin_sanitize_setting' );
}
add_action( 'admin_init', 'taxonomy_image_plugin_register_setting' );


/**
 * Add a new association to the 'taxonomy_image_plugin' setting.
 * This is a callback for the wp_ajax_{$action} hook.
 * @return void
 */
function taxonomy_image_plugin_create_association() {
	/* Vars */
	global $wpdb;
	$term_id = 0;
	$attachment_id = 0;
	$attachment_thumb_src = '';
	$response = array( 'message' => 'bad' );

	/* Check permissions */
	if( !current_user_can( TAXONOMY_IMAGE_PLUGIN_PERMISSION ) ) {
		wp_die( __( 'Sorry, you do not have the propper permissions to access this resource.', 'taxonomy_image_plugin' ) );
	}

	/* Nonce does not match */
	if( !isset( $_POST['wp_nonce'] ) ) {
		wp_die( __( 'Access Denied to this resource 1.', 'taxonomy_image_plugin' ) );
	}

	if( !wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-images-plugin-create-association' ) ) {
		wp_die( __( 'Access Denied to this resource 2.', 'taxonomy_image_plugin' ) );
	}

	/* Check value of $_POST['term_id'] */
	if( isset( $_POST['term_id'] ) ) {
		$term_taxonomy_id = (int) $_POST['term_id'];
		$response['term_id'] = $term_taxonomy_id;
	}

	/* Term does not exist - do not proceed. */	
	if( 0 === (int) $wpdb->get_var( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE `term_taxonomy_id` = {$term_taxonomy_id}" ) ) {
		wp_die( __( 'The term does not exist.', 'taxonomy_image_plugin' ) );
	}

	/* Query for $attachment_id */
	if( isset( $_POST['attachment_id'] ) ) {
		$attachment_id = (int) $_POST['attachment_id'];
	}

	/* Attachment does not exist - do not proceed */
	if( !is_object( get_post( $attachment_id ) ) ) {
		wp_die( __( 'The attachment does not exist.', 'taxonomy_image_plugin' ) );
	}

	$setting = get_option( 'taxonomy_image_plugin' );
	$setting[$term_taxonomy_id] = $attachment_id;
	update_option( 'taxonomy_image_plugin', $setting );

	$attachment_thumb_src = $this->get_thumb( $attachment_id );
	if( !empty( $attachment_thumb_src ) ) {
		$response['message'] = 'good'; /* No need to localize. */
		$response['attachment_thumb_src'] = $attachment_thumb_src;
	}

	/* Send JSON response and terminate script execution. */
	taxonomy_image_plugin_json_response( $response );
}
add_action( 'wp_ajax_taxonomy_images_remove_association', 'taxonomy_image_plugin_create_association' );


/**
 * Remove an association from the 'taxonomy_image_plugin' setting.
 * This is a callback for the wp_ajax_{$action} hook.
 * @return void
 */
function taxonomy_image_plugin_remove_association() {

	/* Check permissions */
	if ( !current_user_can( TAXONOMY_IMAGE_PLUGIN_PERMISSION ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why' => __( 'Access Denied: Permission.', 'taxonomy_image_plugin' ),
		) );
	}

	/* Nonce does not exist in $_POST. */
	if ( !isset( $_POST['wp_nonce'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why' => __( 'Access Denied: No nonce passed.', 'taxonomy_image_plugin' ),
		) );
	}

	/* Nonce does not match */
	if ( !wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-images-plugin-remove-association' ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why' => __( 'Access Denied: Nonce did not match.', 'taxonomy_image_plugin' ),
		) );
	}

	/* Check value of $_POST['term_id'] */
	if ( !isset( $_POST['term_id'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why' => __( 'term_id not sent.', 'taxonomy_image_plugin' ),
		) );
	}

	$term_id = (int) $_POST['term_id'];
	$associations = get_option( 'taxonomy_image_plugin' );
	
	if ( isset( $associations[$term_id] ) ) {
		unset( $associations[$term_id] );
	}

	update_option( 'taxonomy_image_plugin', $associations );
	
	/* Output */
	taxonomy_image_plugin_json_response( array( 'message' => 'good' ) );
}
add_action( 'wp_ajax_taxonomy_images_create_association', 'taxonomy_image_plugin_remove_association' );



####################################################################
#	CLASS STARTS HERE
####################################################################

if( !class_exists( 'taxonomy_images_plugin' ) ) {
	/**
	* Category Thumbs
	* @author Michael Fields <michael@mfields.org>
	* @copyright Copyright (c) 2009, Michael Fields.
	* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
	* @package Plugins
	* @filesource
	*/
	class taxonomy_images_plugin {
		public $settings = array();
		public $locale = 'taxonomy_image_plugin';
		private $current_taxonomy = false;
		public function __construct() {
			/* Set Properties */
			$this->settings = get_option( 'taxonomy_image_plugin' );

			/* Plugin Activation Hooks */
			register_activation_hook( __FILE__, array( &$this, 'activate' ) );

			/* General Hooks. */
			
			add_action( 'admin_head', array( &$this, 'set_current_taxonomy' ), 10 );
			add_action( 'wp_head', array( &$this, 'set_current_taxonomy' ), 10 );

			/* Media Upload Thickbox Hooks. */
			add_action( 'admin_print_scripts-media-upload-popup', array( &$this, 'media_upload_popup_js' ), 2000 );
			

			/* Category Admin Hooks. */
			add_action( 'admin_print_scripts-categories.php', array( &$this, 'scripts' ) );
			add_action( 'admin_print_styles-categories.php', array( &$this, 'styles' ) );

			/* 3.0 and beyond. Dynamically create hooks. */
			add_action( 'admin_init', array( &$this, 'admin_init' ) );

			/* 2.9 Support - hook into taxonomy terms administration panel. */
			add_filter( 'manage_categories_custom_column', array( &$this, 'category_rows' ), 15, 3 );
			add_filter( 'manage_categories_columns', array( &$this, 'category_columns' ) );
			add_filter( 'manage_edit-tags_columns', array( &$this, 'category_columns' ) );

			/* Styles and Scripts */
			add_action( 'admin_print_scripts-edit-tags.php', array( &$this, 'edit_tags_js' ) );
			add_action( 'admin_print_styles-edit-tags.php', array( &$this, 'edit_tags_css' ) );

			/* Custom Actions for front-end. */
			add_action( 'taxonomy_image_plugin_print_image_html', array( &$this, 'print_image_html' ), 1, 3 );
			add_shortcode( 'taxonomy_image_plugin', array( &$this, 'list_term_images_shortcode' ) );
		}
		public function activate() {
			add_option( 'taxonomy_image_plugin', array() );
		}
		public function media_upload_popup_js() {
			if( isset( $_GET[ TAXONOMY_IMAGE_PLUGIN_SLUG ] ) ) {
				wp_enqueue_script( 'taxonomy-images-media-upload-popup', TAXONOMY_IMAGE_PLUGIN_URL . 'media-upload-popup.js', array( 'jquery' ), TAXONOMY_IMAGE_PLUGIN_VERSION );
				$term_id = (int) $_GET[ TAXONOMY_IMAGE_PLUGIN_SLUG ];
				wp_localize_script( 'taxonomy-images-media-upload-popup', 'taxonomyImagesPlugin', array (
					'attr' => TAXONOMY_IMAGE_PLUGIN_SLUG . '=' . $term_id, // RED FLAG!!!!!!!!!!!!
					'nonce' => wp_create_nonce( 'taxonomy-images-plugin-create-association' ),					
					'locale' => 'taxonomy_image_plugin',
					'term_id' => $term_id,
					'attr_slug' => TAXONOMY_IMAGE_PLUGIN_SLUG
					) );
			}
		}
		public function edit_tags_js() {
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'taxonomy-images-edit-tags', TAXONOMY_IMAGE_PLUGIN_URL . 'edit-tags.js', array( 'jquery' ), TAXONOMY_IMAGE_PLUGIN_VERSION );
			wp_localize_script( 'taxonomy-images-edit-tags', 'taxonomyImagesPlugin', array (
				'nonce_remove' => wp_create_nonce( 'taxonomy-images-plugin-remove-association' ),
				'img_src' => TAXONOMY_IMAGE_PLUGIN_URL . 'default-image.png'
				) );
		}
		public function edit_tags_css() {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'taxonomy-images-edit-tags', TAXONOMY_IMAGE_PLUGIN_URL . 'admin.css', array(), TAXONOMY_IMAGE_PLUGIN_VERSION, 'screen' );
		}
		
		/**
		 * Dynamically create hooks for the columns and rows of edit-tags.php
		 * @since 0.4.3
		 * @uses $wp_taxonomies
		 * @return void
		 */
		public function admin_init() {
			global $wp_taxonomies;
			foreach( $wp_taxonomies as $taxonomy => $taxonomies ) {
				add_filter( 'manage_' . $taxonomy . '_custom_column', array( &$this, 'category_rows' ), 10, 3 );
				add_filter( 'manage_edit-' . $taxonomy . '_columns', array( &$this, 'category_columns' ), 10, 3 );
			}
		}
		public function list_term_images_shortcode( $atts = array() ) {
			$o = '';
			$defaults = array(
				'taxonomy' => 'category',
				'size' => 'detail',
				'template' => 'list'
				);

			extract( shortcode_atts( $defaults, $atts ) );

			/* No taxonomy defined return an html comment. */
			if( !taxonomy_exists( $taxonomy ) ) {
				$tax = strip_tags( trim( $taxonomy ) );
				return '<!-- taxonomy_image_plugin error: Taxonomy "' . $taxonomy . '" is not defined.-->';
			}

			$terms = get_terms( $taxonomy );

			if( !is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$open = '';
					$close = '';
					$img_tag = '';
					$url = get_term_link( $term, $term->taxonomy );
					$img = $this->get_image_html( $size, $term->term_taxonomy_id, true, 'left' );
					$title = apply_filters( 'the_title', $term->name );
					$title_attr = esc_attr( $term->name . ' (' . $term->count . ')' );
					$description = apply_filters( 'the_content', $term->description );
					if( $template === 'grid' ) {
						$o.= "\n\t" . '<div class="taxonomy_image_plugin-' . $template . '">';
						$o.= "\n\t\t" . '<a style="float:left;" title="' . $title_attr . '" href="' . $url . '">' . $img . '</a>';
						$o.= "\n\t" . '</div>';
					}
					else {
						$o.= "\n\t\t" . '<a title="' . $title_attr . '" href="' . $url . '">' . $img . '</a>';;
						$o.= "\n\t\t" . '<h2 style="clear:none;margin-top:0;padding-top:0;line-height:1em;"><a href="' . $url . '">' . $title . '</a></h2>';
						$o.= $description;
						$o.= "\n\t" . '<div style="clear:both;height:1.5em"></div>';
						$o.= "\n";
					}
				}
			}
			return $o;
		}
		public function set_current_taxonomy() {
			if( is_admin() ) {
				global $hook_suffix;
				if( $hook_suffix === 'categories.php' ) {
					$this->current_taxonomy = 'category';
				}
				if( $hook_suffix === 'edit-tags.php' && isset( $_GET['taxonomy'] ) ) {
					$this->current_taxonomy = ( get_taxonomy( $_GET['taxonomy'] ) ) ? $_GET['taxonomy'] : false;
				}
			}
			else {
				global $wp_query;
				$obj = $wp_query->get_queried_object();
				if( isset( $obj->taxonomy ) ) {
					$this->current_taxonomy = $obj->taxonomy;
				}
			}
		}
		public function category_rows( $c, $column_name, $term_id ) {
			if( $column_name === 'custom' ) {
				global $taxonomy_image_plugin_taxonomy;
				$name = __( 'taxonomy', 'taxonomy_images_plugin' );
				if( isset( $taxonomy_image_plugin_taxonomy->labels->singular_name ) ) {
					$name = strtolower( $taxonomy_image_plugin_taxonomy->labels->singular_name );
				}
				$term_tax_id = $this->term_tax_id( (int) $term_id );
				$href_library = admin_url( 'media-upload.php' ) . '?type=image&amp;tab=library&amp;' . TAXONOMY_IMAGE_PLUGIN_SLUG . '=' . $term_tax_id. '&amp;post_id=0&amp;TB_iframe=true';
				$href_upload = admin_url( 'media-upload.php' ) . '?type=image&amp;tab=type&amp;' . TAXONOMY_IMAGE_PLUGIN_SLUG . '=' . $term_tax_id. '&amp;post_id=0&amp;TB_iframe=true';;
				$id = 'taxonomy_image_plugin' . '_' . $term_tax_id;
				$attachment_id = ( isset( $this->settings[ $term_tax_id ] ) ) ? (int) $this->settings[ $term_tax_id ] : false;
				$img = ( $attachment_id ) ? $this->get_thumb( $attachment_id ) : TAXONOMY_IMAGE_PLUGIN_URL . 'default-image.png';
				$text = array(
					esc_attr__( 'Please enable javascript to activate the taxonomy images plugin.', 'taxonomy_image_plugin' ),
					esc_attr__( 'Upload.', 'taxonomy_image_plugin' ),
					sprintf( esc_attr__( 'Upload a new image for this %s.', 'taxonomy_image_plugin' ), $name ),
					esc_attr__( 'Media Library.', 'taxonomy_image_plugin' ),
					sprintf( esc_attr__( 'Change the image for this %s.', 'taxonomy_image_plugin' ), $name ),
					esc_attr__( 'Delete', 'taxonomy_image_plugin' ),
					sprintf( esc_attr__( 'Remove image from this %s.', 'taxonomy_image_plugin' ), $name ),
					);
				$class = array(
					'remove' => '',
					);
				if( !$attachment_id ) {
					$class['remove'] = ' hide';
				}
				$img_src = TAXONOMY_IMAGE_PLUGIN_URL . 'no-javascript.png';
				return <<<EOF
{$c}
<img class="hide-if-js" src="{$img_src}" alt="{$text[0]}" />
<div id="taxonomy-image-control-{$term_tax_id}" class="taxonomy-image-control hide-if-no-js">
	<a class="thickbox taxonomy-image-thumbnail" onclick="return false;" href="{$href_library}" title="{$text[4]}"><img id="{$id}" src="{$img}" alt="" /></a>
	<a class="upload control thickbox" onclick="return false;" href="{$href_upload}" title="{$text[2]}">{$text[1]}</a>
	<span id="remove-{$term_tax_id}" rel="{$term_tax_id}" class="delete control{$class['remove']}" title="{$text[6]}">{$text[5]}</span>
</div>
EOF;
			}
		}
		public function category_columns( $original_columns ) {
			global $taxonomy, $taxonomy_image_plugin_taxonomy;
			$taxonomy_image_plugin_taxonomy = get_taxonomy( $taxonomy );
			$new_columns = $original_columns;
			array_splice( $new_columns, 1 ); /* isolate the checkbox column */
			$new_columns['custom'] = __( 'Image', 'taxonomy_image_plugin' ); /* Add custom column */
			return array_merge( $new_columns, $original_columns ); 
		}
		public function term_tax_id( $term ) {
			if( empty( $this->current_taxonomy ) ) {
				return false;
			}
			$data = get_term( $term, $this->current_taxonomy );
			if( isset( $data->term_taxonomy_id ) && !empty( $data->term_taxonomy_id ) ) {
				return $data->term_taxonomy_id;
			}
			else {
				return false;
			}
		}
		/*
		 * USED ONLY IN CUSTOM_ACTION.
		 */
		public function print_image_html( $size = 'medium', $term_tax_id = false, $title = true, $align = 'none' ) {
			print $this->get_image_html( $size, $term_tax_id, $title, $align );
		}
		/*
		 * USED ONLY IN THE SHORTCODE + print wrapper function.
		 * @uses $wp_query
		 */
		public function get_image_html( $size = 'medium', $term_tax_id = false, $title = true, $align = 'none' ) { // Left for backward compatibility with version < 0.6
			$o = '';
			if( !$term_tax_id ) {
				global $wp_query;
				$mfields_queried_object = $wp_query->get_queried_object();
				$term_tax_id = $mfields_queried_object->term_taxonomy_id;
			}

			$term_tax_id = (int) $term_tax_id;

			if( isset( $this->settings[ $term_tax_id ] ) ) {
				$attachment_id = (int) $this->settings[ $term_tax_id ];
				$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				$attachment = get_post( $attachment_id ); /* Just in case an attachment was deleted, but there is still a record for it in this plugins settings. */
				if( $attachment !== NULL ) {
					$o = get_image_tag( $attachment_id, $alt, '', $align, $size );
				}
			}
			return $o;
		}
		/*
		* @param $id (int) Attachment ID
		*/
		public function get_thumb( $id ) { // Left for backward compatibility with version < 0.6
			return taxonomy_image_plugin_get_image( $id );
		}
			}
	$taxonomy_images_plugin = new taxonomy_images_plugin();
}