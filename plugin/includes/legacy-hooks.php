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
	protected function setup_hooks() {

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

		return default;

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

		return default;

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

		return default;

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
	public function queried_term_image( $default, $args = array() ) {

		return default;

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

		return default;

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
	public function queried_term_image_id( $default ) {

		return default;

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

		return default;

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
	public function queried_term_image_url( $default, $args = array() ) {

		return default;

	}

}
