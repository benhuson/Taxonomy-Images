<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term Image Admin Control
 */

namespace TaxonomyImages;

class Term_Image_Admin_Control extends Term_Image_Admin {

	/**
	 * Get rendered control.
	 *
	 * @param   string  $size  Pass `large` for larger image control size.
	 * @return  string         HTML output.
	 */
	public function get_rendered( $size = '' ) {

		$term = $this->get_term();

		// Return if term not valid...
		if ( ! $term ) {
			return '';
		}

		$term_id = $this->get_term_id();

		$name = strtolower( $this->get_taxonomy_singular_name() );

		$attachment_id = $this->get_image_id();

		$hide = $attachment_id ? '' : ' hide';

		$size_class = 'large' == $size ? ' taxonomy-image-thumbnail-large' : '';

		// Nonces
		$nonce = wp_create_nonce( 'taxonomy-image-plugin-create-association' );
		$nonce_remove = wp_create_nonce( 'taxonomy-image-plugin-remove-association' );

		// Control Attributes
		$common_attributes = array(
			'data-term-id="' . $term_id . '"',
			'data-attachment-id="' . $attachment_id . '"'
		);

		// Update Attributes
		$update_attributes = wp_parse_args( $common_attributes, array(
			'data-nonce="' . $nonce . '"',
			'href="' . esc_url( admin_url( 'media-upload.php' ) . '?type=image&tab=library&post_id=0&TB_iframe=true' ) . '"'
		) );

		// Edit Image Link Attributes
		$edit_attributes = wp_parse_args( $update_attributes, array(
			'class="taxonomy-image-thumbnail' . $size_class . '"',
			'title="' . esc_attr( sprintf( __( 'Associate an image with the %1$s named &#8220;%2$s&#8221;.', 'taxonomy-images' ), $name, $term->name ) ) . '"'
		) );

		// Add Image Link Attributes
		$add_attributes = wp_parse_args( $update_attributes, array(
			'class="control upload"',
			'title="' . esc_attr( sprintf( __( 'Upload a new image for this %s.', 'taxonomy-images' ), $name ) ) . '"'
		) );

		// Remove Image Link Attributes
		$remove_attributes = wp_parse_args( $common_attributes, array(
			'data-nonce="' . $nonce_remove . '"',
			'class="control remove' . $hide . '"',
			'href="#"',
			'title="' . esc_attr( sprintf( __( 'Remove image from this %s.', 'taxonomy-images' ), $name ) ) . '"',
			'id="' . esc_attr( 'remove-' . $term_id ) . '"'
		) );

		// Control
		$o  = '<div id="' . esc_attr( 'taxonomy-image-control-' . $term_id ) . '" class="taxonomy-image-control hide-if-no-js">';
		$o .= '<a ' . implode( ' ', $edit_attributes ) . '><img id="' . esc_attr( 'taxonomy_image_plugin_' . $term_id ) . '" src="' . esc_url( $this->get_url() ) . '" alt="" /></a>';
		$o .= '<a ' . implode( ' ', $add_attributes ) . '>' . esc_html__( 'Upload', 'taxonomy-images' ) . '</a>';
		$o .= '<a ' . implode( ' ', $remove_attributes ) . '>' . esc_html__( 'Delete', 'taxonomy-images' ) . '</a>';
		$o .= '</div>';

		return $o;

	}

	/**
	 * Get Taxonomy Singular Name
	 *
	 * @return  string
	 */
	private function get_taxonomy_singular_name() {

		$tax = $this->get_taxonomy();

		if ( ! empty( $tax ) ) {

			$taxonomy = get_taxonomy( $tax );

			if ( isset( $taxonomy->labels->singular_name ) ) {
				return $taxonomy->labels->singular_name;
			}

		}

		return _x( 'Term', 'taxonomy singular name', 'taxonomy-images' );

	}

}
