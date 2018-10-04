<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Legacy Hooks
 *
 * All functions defined in this plugin should be considered
 * private meaning that they are not to be used in any other
 * WordPress extension including plugins and themes.
 *
 * This file contains custom filters for the legacy version
 * of this plugin..
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Legacy_Hooks {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Setup Hooks
	 */
	public function setup_hooks() {

		add_filter( 'taxonomy-images-get-terms', array( $this, 'get_terms' ), 10, 2 );
		add_filter( 'taxonomy-images-get-the-terms', array( $this, 'get_the_terms' ), 10, 2 );
		add_filter( 'taxonomy-images-list-the-terms', array( $this, 'list_the_terms' ), 10, 2 );

		add_filter( 'taxonomy-images-queried-term-image', array( $this, 'queried_term_image' ), 10, 2 );
		add_filter( 'taxonomy-images-queried-term-image-data', array( $this, 'queried_term_image_data' ), 10, 2 );
		add_filter( 'taxonomy-images-queried-term-image-id', array( $this, 'queried_term_image_id' ) );
		add_filter( 'taxonomy-images-queried-term-image-object', array( $this, 'queried_term_image_object' ) );
		add_filter( 'taxonomy-images-queried-term-image-url', array( $this, 'queried_term_image_url' ), 10, 2 );

	}

	/**
	 * Get Terms
	 *
	 * This function adds a custom property (image_id) to each
	 * object returned by WordPress core function get_terms().
	 * This property will be set for all term objects. In cases
	 * where a term has an associated image, "image_id" will
	 * contain the value of the image object's ID property. If
	 * no image has been associated, this property will contain
	 * integer with the value of zero.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_terms
	 *
	 * Recognized Arguments:
	 *
	 * cache_images (bool) If true, all images will be added to
	 * WordPress object cache. If false, caching will not occur.
	 * Defaults to true. Optional.
	 *
	 * having_images (bool) If true, the returned array will contain
	 * only terms that have associated images. If false, all terms
	 * of the taxonomy will be returned. Defaults to true. Optional.
	 *
	 * taxonomy (string) Name of a registered taxonomy to
	 * return terms from. Defaults to "category". Optional.
	 *
	 * term_args (array) Arguments to pass as the second
	 * parameter of get_terms(). Defaults to an empty array.
	 * Optional.
	 *
	 * @param   mixed  Default value for apply_filters() to return. Unused.
	 * @param   array  Named arguments. Please see above for explantion.
	 * @return  array  List of term objects.
	 *
	 * @access  private  Use the 'taxonomy-images-get-terms' filter.
	 * @since   0.7
	 */
	public function get_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-get-terms';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'cache_images'  => true,
			'having_images' => true,
			'taxonomy'      => 'category',
			'term_args'     => array(),
		) );

		$args['taxonomy'] = explode( ',', $args['taxonomy'] );
		$args['taxonomy'] = array_map( 'trim', $args['taxonomy'] );

		// @todo  Check if taxonomy supported in settings

		$terms = get_terms( $args['taxonomy'], $args['term_args'] );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$image_ids = array();
		$terms_with_images = array();

		foreach ( (array) $terms as $key => $term ) {
			$terms[ $key ]->image_id = 0;

			$t = new Term_Image( $term->term_id );
			$img = $t->get_image_id();

			if ( $img ) {
				$terms[ $key ]->image_id = $img;
				$image_ids[] = $img;
				if ( ! empty( $args['having_images'] ) ) {
					$terms_with_images[] = $terms[ $key ];
				}
			}

		}

		$image_ids = array_unique( $image_ids );

		if ( ! empty( $args['cache_images'] ) ) {
			$images = array();
			if ( ! empty( $image_ids ) ) {
				$images = get_children( array( 'include' => implode( ',', $image_ids ) ) );
			}
		}

		if ( ! empty( $terms_with_images ) ) {
			return $terms_with_images;
		}

		return $terms;

	}

	/**
	 * Get the Terms
	 *
	 * This function adds a custom property (image_id) to each
	 * object returned by WordPress core function get_the_terms().
	 * This property will be set for all term objects. In cases
	 * where a term has an associated image, "image_id" will
	 * contain the value of the image object's ID property. If
	 * no image has been associated, this property will contain
	 * integer with the value of zero.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_the_terms
	 *
	 * Recognized Arguments:
	 *
	 * having_images (bool) If true, the returned array will contain
	 * only terms that have associated images. If false, all terms
	 * of the taxonomy will be returned. Defaults to true. Optional.
	 *
	 * post_id (int) The post to retrieve terms from. Defaults
	 * to the ID property of the global $post object. Optional.
	 *
	 * taxonomy (string) Name of a registered taxonomy to
	 * return terms from. Defaults to "category". Optional.
	 *
	 * @param   mixed  Default value for apply_filters() to return. Unused.
	 * @param   array  Named arguments. Please see above for explantion.
	 * @return  array  List of term objects. Empty array if none were found.
	 *
	 * @access  private   Use the 'taxonomy-images-get-the-terms' filter.
	 * @since   0.7
	 */
	public function get_the_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-get-the-terms';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'having_images' => true,
			'post_id'       => 0,
			'taxonomy'      => 'category',
		) );

		// @todo  Check if taxonomy supported in settings

		if ( empty( $args['post_id'] ) ) {
			$args['post_id'] = get_the_ID();
		}

		$terms = get_the_terms( $args['post_id'], $args['taxonomy'] );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		if ( empty( $terms ) ) {
			return array();
		}

		$terms_with_images = array();

		foreach ( (array) $terms as $key => $term ) {
			$terms[ $key ]->image_id = 0;

			$t = new Term_Image( $term->term_id );
			$img = $t->get_image_id();

			if ( $img ) {
				$terms[ $key ]->image_id = $img;
				if ( ! empty( $args['having_images'] ) ) {
					$terms_with_images[] = $terms[ $key ];
				}
			}

		}

		if ( ! empty( $terms_with_images ) ) {
			return $terms_with_images;
		}

		return $terms;

	}

	/**
	 * List the Terms
	 *
	 * Lists all terms associated with a given post that
	 * have associated images. Terms without images will
	 * not be included.
	 *
	 * Recognized Arguments:
	 *
	 * after (string) Text to append to the output.
	 * Defaults to: '</ul>'. Optional.
	 *
	 * after_image (string) Text to append to each image in the
	 * list. Defaults to: '</li>'. Optional.
	 *
	 * before (string) Text to preppend to the output.
	 * Defaults to: '<ul class="taxonomy-images-the-terms">'.
	 * Optional.
	 *
	 * before_image (string) Text to prepend to each image in the
	 * list. Defaults to: '<li>'. Optional.
	 *
	 * image_size (string) Any registered image size. Values will
	 * vary from installation to installation. Image sizes defined
	 * in core include: "thumbnail", "medium" and "large". "fullsize"
	 * may also be used to get the unmodified image that was uploaded.
	 * Optional. Defaults to "thumbnail".
	 *
	 * post_id (int) The post to retrieve terms from. Defaults
	 * to the ID property of the global $post object. Optional.
	 *
	 * taxonomy (string) Name of a registered taxonomy to
	 * return terms from. Defaults to "category". Optional.
	 *
	 * @param   mixed   Default value for apply_filters() to return. Unused.
	 * @param   array   Named arguments. Please see above for explantion.
	 * @return  string  HTML markup.
	 *
	 * @access  private  Use the 'taxonomy-images-list-the-terms' filter.
	 * @since   0.7
	 */
	public function list_the_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-list-the-terms';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'after'        => '</ul>',
			'after_image'  => '</li>',
			'before'       => '<ul class="taxonomy-images-the-terms">',
			'before_image' => '<li>',
			'image_size'   => 'thumbnail',
			'post_id'      => 0,
			'taxonomy'     => 'category',
		) );

		$args['having_images'] = true;

		// @todo  Check if taxonomy supported in settings

		$terms = apply_filters( 'taxonomy-images-get-the-terms', '', $args );

		if ( empty( $terms ) ) {
			return '';
		}

		$output = '';

		foreach( $terms as $term ) {

			if ( ! isset( $term->image_id ) ) {
				continue;
			}

			$image = wp_get_attachment_image( $term->image_id, $args['image_size'] );

			if ( ! empty( $image ) ) {
				$output .= $args['before_image'] . '<a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '">' . $image .'</a>' . $args['after_image'];
			}

		}

		if ( ! empty( $output ) ) {
			return $args['before'] . $output . $args['after'];
		}

		return '';

	}

	/**
	 * Queried Term Image
	 *
	 * Prints html markup for the image associated with
	 * the current queried term.
	 *
	 * Recognized Arguments:
	 *
	 * after (string) - Text to append to the image's HTML.
	 *
	 * before (string) - Text to prepend to the image's HTML.
	 *
	 * image_size (string) - May be any image size registered with
	 * WordPress. If no image size is specified, 'thumbnail' will be
	 * used as a default value. In the event that an unregistered size
	 * is specified, this function will return an empty string.
	 *
	 * Designed to be used in archive templates including
	 * (but not limited to) archive.php, category.php, tag.php,
	 * taxonomy.php as well as derivatives of these templates.
	 *
	 * @param   mixed   Default value for apply_filters() to return. Unused.
	 * @param   array   Named array of arguments.
	 * @return  string  HTML markup for the associated image.
	 *
	 * @access  private  Use the 'taxonomy-images-queried-term-image' filter.
	 * @since   0.7
	 */
	public function queried_term_image( $default = '', $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'after'      => '',
			'attr'       => array(),
			'before'     => '',
			'image_size' => 'thumbnail',
		) );

		$image_id = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		if ( ! empty( $image_id ) ) {

			$html = wp_get_attachment_image( $image_id, $args['image_size'], false, $args['attr'] );

			if ( ! empty( $html ) ) {
				return $args['before'] . $html . $args['after'];
			}

		}

		return '';

	}

	/**
	 * Queried Term Image Data
	 *
	 * Returns a url to the image associated with the current queried
	 * term. In the event that no image is found an empty string will
	 * be returned.
	 *
	 * Designed to be used in archive templates including
	 * (but not limited to) archive.php, category.php, tag.php,
	 * taxonomy.php as well as derivatives of these templates.
	 *
	 * Recognized Arguments
	 *
	 * image_size (string) - May be any image size registered with
	 * WordPress. If no image size is specified, 'thumbnail' will be
	 * used as a default value. In the event that an unregistered size
	 * is specified, this function will return an empty array.
	 *
	 * @param   mixed  Default value for apply_filters() to return. Unused.
	 * @param   array  Named Arguments.
	 * @return  array  Image data: url, width and height.
	 *
	 * @access  private  Use the 'taxonomy-images-queried-term-image-data' filter.
	 * @since   0.7
	 * @alter   0.7.2
	 */
	public function queried_term_image_data( $default, $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image-data';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'image_size' => 'thumbnail',
		) );

		$image_id = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		if ( empty( $image_id ) ) {
			return array();
		}

		$data = image_get_intermediate_size( $image_id, $args['image_size'] );

		if ( empty( $data ) ) {

			$src = wp_get_attachment_image_src( $image_id, 'full' );

			if ( isset( $src[0] ) ) {
				$data['url'] = $src[0];
			}

			if ( isset( $src[1] ) ) {
				$data['width'] = $src[1];
			}

			if ( isset( $src[2] ) ) {
				$data['height'] = $src[2];
			}

		}

		if ( ! empty( $data ) ) {
			return $data;
		}

		return array();

	}

	/**
	 * Queried Term Image ID
	 *
	 * Designed to be used in archive templates including
	 * (but not limited to) archive.php, category.php, tag.php,
	 * taxonomy.php as well as derivatives of these templates.
	 *
	 * Returns an integer representing the image attachment's ID.
	 * In the event that an image has been associated zero will
	 * be returned.
	 *
	 * This function should never be called directly in any file
	 * however it may be access in any template file via the
	 * 'taxonomy-images-queried-term-image-id' filter.
	 *
	 * @param   mixed  Default value for apply_filters() to return. Unused.
	 * @return  int    Image attachment's ID.
	 *
	 * @access  private  Use the 'taxonomy-images-queried-term-image-id' filter.
	 * @since   0.7
	 */
	public function queried_term_image_id( $default = 0 ) {

		$filter = 'taxonomy-images-queried-term-image-id';

		$this->check_current_filter( __FUNCTION__, $filter );

		$obj = get_queried_object();

		if ( is_a( $obj, 'WP_Term' ) ) {

			// @todo  Check if taxonomy supported in settings

			$t = new Term_Image( $obj->term_id );
			return absint( $t->get_image_id() );

		}

		return 0;

	}

	/**
	 * Queried Term Image Object
	 *
	 * Returns all data stored in the WordPress posts table for
	 * the image associated with the term in object form. In the
	 * event that no image is found an empty object will be returned.
	 *
	 * Designed to be used in archive templates including
	 * (but not limited to) archive.php, category.php, tag.php,
	 * taxonomy.php as well as derivatives of these templates.
	 *
	 * This function should never be called directly in any file
	 * however it may be access in any template file via the
	 * 'taxonomy-images-queried-term-image' filter.
	 *
	 * @param   mixed     Default value for apply_filters() to return. Unused.
	 * @return  stdClass  WordPress Post object.
	 *
	 * @access  private  Use the 'taxonomy-images-queried-term-image-object' filter.
	 * @since   0.7
	 */
	public function queried_term_image_object( $default ) {

		$filter = 'taxonomy-images-queried-term-image-object';

		$this->check_current_filter( __FUNCTION__, $filter );

		$image_id = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		$image = new \stdClass;

		if ( ! empty( $image_id ) ) {
			$image = get_post( $image_id );
		}

		return $image;

	}

	/**
	 * Queried Term Image URL
	 *
	 * Returns a url to the image associated with the current queried
	 * term. In the event that no image is found an empty string will
	 * be returned.
	 *
	 * Designed to be used in archive templates including
	 * (but not limited to) archive.php, category.php, tag.php,
	 * taxonomy.php as well as derivatives of these templates.
	 *
	 * Recognized Arguments
	 *
	 * image_size (string) - May be any image size registered with
	 * WordPress. If no image size is specified, 'thumbnail' will be
	 * used as a default value. In the event that an unregistered size
	 * is specified, this function will return an empty string.
	 *
	 * @param   mixed   Default value for apply_filters() to return. Unused.
	 * @param   array   Named Arguments.
	 * @return  string  Image URL.
	 *
	 * @access  private  Use the 'taxonomy-images-queried-term-image-url' filter.
	 * @since   0.7
	 */
	public function queried_term_image_url( $default = '', $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image-url';

		$this->check_current_filter( __FUNCTION__, $filter );

		$args = wp_parse_args( $args, array(
			'image_size' => 'thumbnail',
		) );

		$data = apply_filters( 'taxonomy-images-queried-term-image-data', array(), $args );

		if ( isset( $data['url'] ) ) {
			return $data['url'];
		}

		return '';

	}

	/**
	 * Check Current Filter
	 *
	 * Check that the user is not directly calling a function instead
	 * of using supported filters.
	 *
	 * @param  string  $function  Name of function called.
	 * @param  string  $filter    Name of filter to use instead.
	 */
	private function check_current_filter( $function, $filter ) {

		if ( current_filter() !== $filter ) {
			$this->please_use_filter( $function, $filter );
		}

	}

	/**
	 * Please Use Filter
	 *
	 * Report to user that they are directly calling a function instead
	 * of using supported filters.
	 *
	 * @todo  Log PHP error.
	 *
	 * @param  string  $function  Name of function called.
	 * @param  string  $filter    Name of filter to use instead.
	 */
	private function please_use_filter( $function, $filter ) {

		$error = sprintf( esc_html__( 'The %1$s has been called directly. Please use the %2$s filter instead.', 'taxonomy-images' ),
			'<code>' . esc_html( $function . '()' ) . '</code>',
			'<code>' . esc_html( $filter ) . '</code>'
		);

	}

}
