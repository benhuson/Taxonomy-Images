<?php

/**
 * Check Taxonomy Permissions.
 *
 * Allows a permission check to be performed on a term
 * when all you know is the term_taxonomy_id.
 *
 * @param     int       term_taxonomy_id
 * @return    bool      True if user can edit terms, False if not.
 *
 * @access    private
 */
function taxonomy_image_plugin_check_permissions( $tt_id ) {

	$t = new Taxonomy_Images_Term( $tt_id, true );

	return $t->current_user_can_edit();

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

	Taxonomy_Images_Public_Filters::please_use_filter( $function, $filter );

}

/**
 * Get Term Info.
 *
 * Returns term info by term_taxonomy_id.
 *
 * @deprecated
 *
 * @param     int       term_taxonomy_id
 * @return    array     Keys: term_id (int) and taxonomy (string).
 *
 * @access    private
 */
function taxonomy_image_plugin_get_term_info( $tt_id ) {

	$t = new Taxonomy_Images_Term( $tt_id, true );
	$term = $t->get_term();

	if ( $term ) {
		return array(
			'term_id'  => $term->term_id,
			'taxonomy' => $term->taxonomy
		);
	}

	return array();

}

/**
 * Version Number.
 *
 * @deprecated
 *
 * @return    string    The plugin's version number.
 * @access    private
 * @since     0.7
 * @alter     0.7.4
 */
function taxonomy_image_plugin_version() {

	return Taxonomy_Images_Config::get_version();

}

/**
 * Get a url to a file in this plugin.
 *
 * @deprecated
 *
 * @return    string
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_url( $file = '' ) {

	return Taxonomy_Images_Config::url( $file );

}

/**
 * Deprecated Shortcode.
 *
 * @deprecated  Deprecated since version 0.7
 *
 * @return  void
 * @access  private
 */
function taxonomy_images_plugin_shortcode_deprecated( $atts = array() ) {
	$o = '';
	$defaults = array(
		'taxonomy' => 'category',
		'size'     => 'detail',
		'template' => 'list'
	);

	extract( shortcode_atts( $defaults, $atts ) );

	/* No taxonomy defined return an html comment. */
	if ( ! taxonomy_exists( $taxonomy ) ) {
		$tax = strip_tags( trim( $taxonomy ) );
		return '<!-- taxonomy_image_plugin error: Taxonomy "' . esc_html( $taxonomy ) . '" is not defined.-->';
	}

	$terms = get_terms( $taxonomy );

	if ( ! is_wp_error( $terms ) ) {
		foreach( (array) $terms as $term ) {
			$url         = get_term_link( $term, $term->taxonomy );
			$title       = apply_filters( 'the_title', $term->name );
			$title_attr  = esc_attr( $term->name . ' (' . $term->count . ')' );
			$description = apply_filters( 'the_content', $term->description );

			$t = new Taxonomy_Images_Term( $term );
			$img_id = $t->get_image_id();

			$img = $img_id ? wp_get_attachment_image( $img_id, 'detail', false ) : '';

			if ( 'grid' == $template ) {
				$o .= "\n\t" . '<div class="taxonomy_image_plugin-' . $template . '">';
				$o .= "\n\t\t" . '<a style="float: left;" title="' . $title_attr . '" href="' . $url . '">' . $img . '</a>';
				$o .= "\n\t" . '</div>';
			} else {
				$o .= "\n\t\t" . '<a title="' . $title_attr . '" href="' . $url . '">' . $img . '</a>';;
				$o .= "\n\t\t" . '<h2 style="clear: none; margin-top: 0; padding-top: 0; line-height: 1em;"><a href="' . $url . '">' . $title . '</a></h2>';
				$o .= $description;
				$o .= "\n\t" . '<div style="clear: both; height: 1.5em;"></div>';
				$o .= "\n";
			}
		}
	}
	return $o;
}
add_shortcode( 'taxonomy_image_plugin', 'taxonomy_images_plugin_shortcode_deprecated' );


/**
 * This class has been left for backward compatibility with versions
 * of this plugin 0.5 and under. Please do not use any methods or
 * properties directly in your theme.
 *
 * @deprecated  Deprecated since version 0.5
 * @access      private
 */
class taxonomy_images_plugin {

	public $settings = array();

	public function __construct() {
		$this->settings = taxonomy_image_plugin_get_associations();
		add_action( 'taxonomy_image_plugin_print_image_html', array( &$this, 'print_image_html' ), 1, 3 );
	}

	public function get_thumb( $id ) {
		return taxonomy_image_plugin_get_image_src( $id );
	}

	public function print_image_html( $size = 'medium', $term_tax_id = false, $title = true, $align = 'none' ) {
		print $this->get_image_html( $size, $term_tax_id, $title, $align );
	}

	public function get_image_html( $size = 'medium', $term_tax_id = false, $title = true, $align = 'none' ) {
		$o = '';
		if ( false === $term_tax_id ) {
			global $wp_query;
			$obj = $wp_query->get_queried_object();
			if ( isset( $obj->term_taxonomy_id ) ) {
				$t = new Taxonomy_Images_Term( $obj );
			} else {
				return false;
			}
		} else {

			$t = new Taxonomy_Images_Term( $term_tax_id, true );

		}

		$attachment_id = $t->get_image_id();

		if ( $attachment_id ) {
			$alt           = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$attachment    = get_post( $attachment_id );
			/* Just in case an attachment was deleted, but there is still a record for it in this plugins settings. */
			if ( $attachment !== NULL ) {
				$o = get_image_tag( $attachment_id, $alt, '', $align, $size );
			}
		}
		return $o;
	}

}
$taxonomy_images_plugin = new taxonomy_images_plugin();
