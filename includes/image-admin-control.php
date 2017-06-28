<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Image Admin Control
 */

namespace TaxonomyImages;

class Image_Admin_Control {

	/**
	 * Term
	 *
	 * @var  WP_Term
	 */
	private $term = null;

	/**
	 * Constructor
	 *
	 * @param  WP_Term  $term  Term object.
	 */
	public function __construct( $term ) {

		if ( is_a( $term, 'WP_Term' ) ) {
			$this->term = $term;
		}

	}

	/**
	 * Get rendered control.
	 *
	 * @return  string  HTML output.
	 */
	public function get_rendered() {

		// Return if term not valid...
		if ( ! $this->term ) {
			return '';
		}

		$tt_id = $this->term->term_taxonomy_id;

		$name = strtolower( $this->get_taxonomy_singular_name() );

		$attachment_id = $this->get_image_id();

		$hide = $attachment_id ? '' : ' hide';

		$img = taxonomy_image_plugin_get_image_src( $attachment_id );

		// Nonces
		$nonce = wp_create_nonce( 'taxonomy-image-plugin-create-association' );
		$nonce_remove = wp_create_nonce( 'taxonomy-image-plugin-remove-association' );

		// Strings
		$str_associate  = sprintf( __( 'Associate an image with the %1$s named &#8220;%2$s&#8221;.', 'taxonomy-images' ), $name, $this->term->name );
		$str_upload_new = sprintf( __( 'Upload a new image for this %s.', 'taxonomy-images' ), $name );
		$str_remove     = sprintf( __( 'Remove image from this %s.', 'taxonomy-images' ), $name );

		// URLs
		$media_library_url = admin_url( 'media-upload.php' ) . '?type=image&tab=library&post_id=0&TB_iframe=true';
		$media_type_url    = admin_url( 'media-upload.php' ) . '?type=image&tab=type&post_id=0&TB_iframe=true';

		// Control
		$o  = '<div id="' . esc_attr( 'taxonomy-image-control-' . $tt_id ) . '" class="taxonomy-image-control hide-if-no-js">';
		$o .= '<a class="taxonomy-image-thumbnail" data-tt-id="' . $tt_id . '" data-attachment-id="' . $attachment_id . '" data-nonce="' . $nonce . '" href="' . esc_url( $media_library_url ) . '" title="' . esc_attr( $str_associate ) . '"><img id="' . esc_attr( 'taxonomy_image_plugin_' . $tt_id ) . '" src="' . esc_url( $img ) . '" alt="" /></a>';
		$o .= '<a class="control upload" data-tt-id="' . $tt_id . '" data-attachment-id="' . $attachment_id . '" data-nonce="' . $nonce . '" href="' . esc_url( $media_type_url ) . '" title="' . esc_attr( $str_upload_new ) . '">' . esc_html__( 'Upload.', 'taxonomy-images' ) . '</a>';
		$o .= '<a class="control remove' . $hide . '" data-tt-id="' . $tt_id . '" data-nonce="' . $nonce_remove . '" href="#" id="' . esc_attr( 'remove-' . $tt_id ) . '" rel="' . esc_attr( $tt_id ) . '" title="' . esc_attr( $str_remove ) . '">' . esc_html__( 'Delete', 'taxonomy-images' ) . '</a>';
		$o .= '<input type="hidden" class="tt_id" name="' . esc_attr( 'tt_id-' . $tt_id ) . '" value="' . esc_attr( $tt_id ) . '" />';
		$o .= '<input type="hidden" class="image_id" name="' . esc_attr( 'image_id-' . $tt_id ) . '" value="' . esc_attr( $attachment_id ) . '" />';

		if ( isset( $this->term->name ) && isset( $this->term->slug ) ) {
			$o .= '<input type="hidden" class="term_name" name="' . esc_attr( 'term_name-' . $this->term->slug ) . '" value="' . esc_attr( $this->term->name ) . '" />';
		}

		$o .= '</div>';

		return $o;

	}

	/**
	 * Get Taxonomy Singular Name
	 *
	 * @return  string
	 */
	private function get_taxonomy_singular_name() {

		if ( $this->term ) {

			$taxonomy = get_taxonomy( $this->term->taxonomy );

			if ( isset( $taxonomy->labels->singular_name ) ) {
				return $taxonomy->labels->singular_name;
			}

		}

		return _x( 'Term', 'taxonomy singular name', 'taxonomy-images' );

	}

	/**
	 * Get Image ID
	 *
	 * @return  integer  Attachment ID.
	 */
	private function get_image_id() {

		if ( $this->term ) {

			$associations = taxonomy_image_plugin_get_associations();

			if ( isset( $associations[ $this->term->term_taxonomy_id ] ) ) {
				return (int) $associations[ $this->term->term_taxonomy_id ];
			}

		}

		return 0;

	}

}
