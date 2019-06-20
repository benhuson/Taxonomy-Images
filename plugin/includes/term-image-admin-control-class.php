<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Term Image Admin Control
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Term_Image_Admin_Control extends Term_Image {

	/**
	 * Get Rendered Control
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

		// Control Attributes
		$edit_attributes = $this->get_control_edit_attributes( $size );
		$add_attributes = $this->get_control_add_attributes();
		$remove_attributes = $this->get_control_remove_attributes();

		// Control
		$control  = '<div id="' . esc_attr( 'taxonomy-image-control-' . $this->get_type() . '-' . $term_id ) . '" class="taxonomy-image-control hide-if-no-js">';
		$control .= $this->get_rendered_control_link( $edit_attributes, '<img id="' . esc_attr( 'taxonomy_image_plugin_' . $term_id ) . '" src="' . esc_url( $this->get_image_url() ) . '" alt="" />' );
		$control .= $this->get_rendered_control_link( $add_attributes, esc_html_x( 'Add', 'taxonomy image', 'taxonomy-images' ) );
		$control .= $this->get_rendered_control_link( $remove_attributes, esc_html_x( 'Remove', 'taxonomy image', 'taxonomy-images' ) );
		$control .= '</div>';

		return $control;

	}

	/**
	 * Get Rendered Control Link
	 *
	 * @param   array   $attributes  HTML link attributes.
	 * @param   string  $content     HTML link content.
	 * @return  string               HTML link.
	 */
	private function get_rendered_control_link( $attributes, $content ) {

		return '<a ' . $this->get_rendered_attributes( $attributes ) . '>' . $content . '</a>';

	}

	/**
	 * Get Rendered Attributes
	 *
	 * @param   array   $attributes  Attributes.
	 * @return  string               HTML formatted attributes.
	 */
	private function get_rendered_attributes( $attributes ) {

		$html_attributes = array();

		foreach ( $attributes as $key => $attribute ) {
			$html_attributes[] = $key . '="' . $attribute . '"';
		}

		return implode( ' ', $html_attributes );

	}

	/**
	 * Get Control Remove Attributes
	 *
	 * Attributes for HTML remove image control.
	 *
	 * @return  array  HTML attributes.
	 */
	private function get_control_remove_attributes() {

		$hide = $this->get_image_id() ? '' : ' hide';
		$name = strtolower( $this->get_taxonomy_singular_name() );
		$term = $this->get_term();

		return wp_parse_args( $this->get_control_attributes(), array(
			'data-nonce' => wp_create_nonce( 'taxonomy-image-plugin-remove-association' ),
			'class'      => 'control remove' . $hide,
			'href'       => '#',
			'title'      => esc_attr( sprintf( _x( 'Remove featured image from the &#8220;%1$s&#8221; %2$s.', 'term name and taxonomy', 'taxonomy-images' ), $term->name, $name ) ),
			'id'         => esc_attr( 'remove-' . $this->get_term_id() )
		) );

	}

	/**
	 * Get Control Add Attributes
	 *
	 * Attributes for HTML add image control.
	 *
	 * @return  array  HTML attributes.
	 */
	private function get_control_add_attributes() {

		return wp_parse_args( $this->get_control_update_attributes(), array(
			'class' => 'control upload'
		) );

	}

	/**
	 * Get Control Edit Attributes
	 *
	 * Attributes for HTML edit image control.
	 *
	 * @return  array  HTML attributes.
	 */
	private function get_control_edit_attributes( $size ) {

		$size_class = 'large' == $size ? ' taxonomy-image-thumbnail-large' : '';

		return wp_parse_args( $this->get_control_update_attributes(), array(
			'class' => 'taxonomy-image-thumbnail' . $size_class
		) );

	}

	/**
	 * Get Control Update Attributes
	 *
	 * Attributes common to HTML controls that add/update images.
	 *
	 * @return  array  HTML attributes.
	 */
	private function get_control_update_attributes() {

		$name = strtolower( $this->get_taxonomy_singular_name() );
		$term = $this->get_term();

		return wp_parse_args( $this->get_control_attributes(), array(
			'data-nonce' => wp_create_nonce( 'taxonomy-image-plugin-create-association' ),
			'href'       => esc_url( admin_url( 'media-upload.php' ) . '?type=image&tab=library&post_id=0&TB_iframe=true' ),
			'title'      => esc_attr( sprintf( _x( 'Set featured image for the &#8220;%1$s&#8221; %2$s.', 'term name and taxonomy', 'taxonomy-images' ), $term->name, $name ) )
		) );

	}

	/**
	 * Get Control Attributes
	 *
	 * Base level attributes common to all HTML controls
	 * for adding/deleting/updating images.
	 *
	 * @return  array  HTML attributes.
	 */
	private function get_control_attributes() {

		return array(
			'data-term-id'       => $this->get_term_id(),
			'data-image-type'    => $this->get_type(),
			'data-attachment-id' => $this->get_image_id()
		);

	}

	/**
	 * Get Image URL
	 * 
	 * Return a URI to a custom preview image size for display in admin.
	 * The output of this function should be escaped before printing to the browser.
	 *
	 * @return  string  URI of custom image on success. Otherwise empty string.
	 *
	 * @internal  Private. Also used for rendering admin control in AJAX.
	 */
	public function get_image_url() {

		/**
		 * Get the admin preview sized image URL
		 * The core Media Library list gets 60 x 60px images, but we try for slightly higher res.
		 */
		$thumb = wp_get_attachment_image_src( $this->get_image_id(), array( 75, 75 ) );

		if ( $thumb ) {
			return $thumb[0];
		}

		/**
		 * No image can be found.
		 * This is most likely caused by a user deleting an attachment before deleting it's association with a taxonomy.
		 * If we are in the admin delete the association and return URL to default image.
		 */
		if ( is_admin() ) {
			$this->delete_image();
		}

		// Otherwise return path to placeholder image.
		return $this->get_placeholder_url();

	}

	/**
	 * Get Placeholder URL
	 *
	 * Overrides placeholder URL with admin placeholder image.
	 *
	 * @return  string  Placeholder image URL.
	 */
	protected function get_placeholder_url() {

		return Plugin::plugin_url( 'plugin/assets/images/default.png' );

	}

}
