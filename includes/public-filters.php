<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Public Filters Interface
 *
 * All functions defined in this plugin should be considered
 * private meaning that they are not to be used in any other
 * WordPress extension including plugins and themes. Direct
 * use of functions defined herein constitutes unsupported use
 * and is strongly discouraged. This file contains custom filters
 * which enable extension authors to interact with this plugin in
 * a responsible manner.
 */

namespace TaxonomyImages;

add_action( 'plugins_loaded', array( 'TaxonomyImages\Public_Filters', 'setup_filters' ) );
add_action( 'the_content', array( 'TaxonomyImages\Public_Filters', 'debug_content' ) );

class Public_Filters {

	/**
	 * Setup Filters
	 *
	 * @internal  Private. Called via the `plugins_loaded` action.
	 */
	public static function setup_filters() {

		add_filter( 'taxonomy-images-get-terms',      array( get_class(), 'get_terms' ), 10, 2 );
		add_filter( 'taxonomy-images-get-the-terms',  array( get_class(), 'get_the_terms' ), 10, 2 );
		add_filter( 'taxonomy-images-list-the-terms', array( get_class(), 'list_the_terms' ), 10, 2 );

		add_filter( 'taxonomy-images-queried-term-image',        array( get_class(), 'queried_term_image' ), 10, 2 );
		add_filter( 'taxonomy-images-queried-term-image-data',   array( get_class(), 'queried_term_image_data' ), 10, 2 );
		add_filter( 'taxonomy-images-queried-term-image-id',     array( get_class(), 'queried_term_image_id' ) );
		add_filter( 'taxonomy-images-queried-term-image-object', array( get_class(), 'queried_term_image_object' ) );
		add_filter( 'taxonomy-images-queried-term-image-url',    array( get_class(), 'queried_term_image_url' ), 10, 2 );

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
	 * @see  http://codex.wordpress.org/Function_Reference/get_terms
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
	 * @internal  Private. Use the 'taxonomy-images-get-terms' filter.
	 */
	public static function get_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-get-terms';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$args = wp_parse_args( $args, array(
			'cache_images'  => true,
			'having_images' => true,
			'taxonomy'      => 'category',
			'term_args'     => array(),
		) );

		$args['taxonomy'] = explode( ',', $args['taxonomy'] );
		$args['taxonomy'] = array_map( 'trim', $args['taxonomy'] );

		foreach ( $args['taxonomy'] as $taxonomy ) {
			if ( ! self::check_taxonomy( $taxonomy, $filter ) ) {
				return array();
			}
		}

		$terms = get_terms( $args['taxonomy'], $args['term_args'] );
		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$image_ids = array();
		$terms_with_images = array();

		foreach ( (array) $terms as $key => $term ) {
			$terms[ $key ]->image_id = 0;
			$t = new Term( $term->term_id );
			$i = $t->get_image_id();
			if ( ! empty( $i ) ) {
				$terms[ $key ]->image_id = $i;
				$image_ids[] = $i;
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
	 * @see  http://codex.wordpress.org/Function_Reference/get_the_terms
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
	 * @internal  Private.  Use the 'taxonomy-images-get-the-terms' filter.
	 */
	public static function get_the_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-get-the-terms';
		if ( $filter !== current_filter() ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$args = wp_parse_args( $args, array(
			'having_images' => true,
			'post_id'       => 0,
			'taxonomy'      => 'category',
		) );

		if ( ! self::check_taxonomy( $args['taxonomy'], $filter ) ) {
			return array();
		}

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
			$t = new Term( $term->term_id );
			$i = $t->get_image_id();
			if ( ! empty( $i ) ) {
				$terms[ $key ]->image_id = $i;
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
	 * @internal  Private. Use the 'taxonomy-images-list-the-terms' filter.
	 */
	public static function list_the_terms( $default, $args = array() ) {

		$filter = 'taxonomy-images-list-the-terms';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

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

		if ( ! self::check_taxonomy( $args['taxonomy'], $filter ) ) {
			return '';
		}

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
	 * @internal  Private. Use the 'taxonomy-images-queried-term-image' filter.
	 */
	public static function queried_term_image( $default, $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$args = wp_parse_args( $args, array(
			'after'      => '',
			'attr'       => array(),
			'before'     => '',
			'image_size' => 'thumbnail',
		) );

		$ID = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		if ( empty( $ID ) ) {
			return '';
		}

		$html = wp_get_attachment_image( $ID, $args['image_size'], false, $args['attr'] );

		if ( empty( $html ) ) {
			return '';
		}

		return $args['before'] . $html . $args['after'];

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
	 * @internal  Private. Use the 'taxonomy-images-queried-term-image-id' filter.
	 */
	public static function queried_term_image_id( $default ) {

		$filter = 'taxonomy-images-queried-term-image-id';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$obj = get_queried_object();

		// Return early if we are not in a term archive.
		if ( ! isset( $obj->term_id ) ) {
			trigger_error( sprintf( esc_html__( '%1$s is not a property of the current queried object. This usually happens when the %2$s filter is used in an unsupported template file. This filter has been designed to work in taxonomy archives which are traditionally served by one of the following template files: category.php, tag.php or taxonomy.php. Learn more about %3$s.', 'taxonomy-images' ),
				'<code>' . esc_html__( 'term_id', 'taxonomy-images' ) . '</code>',
				'<code>' . esc_html( $filter ) . '</code>',
				'<a href="http://codex.wordpress.org/Template_Hierarchy">' . esc_html( 'template hierarchy', 'taxonomy-images' ) . '</a>'
			) );
			return 0;
		}

		if ( ! self::check_taxonomy( $obj->taxonomy, $filter ) ) {
			return 0;
		}

		$t = new Term( $obj->term_id );

		return $t->get_image_id();

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
	 * @internal  Private. Use the 'taxonomy-images-queried-term-image-object' filter.
	 */
	public static function queried_term_image_object( $default ) {

		$filter = 'taxonomy-images-queried-term-image-object';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$ID = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		$image = new \stdClass;
		if ( ! empty( $ID ) ) {
			$image = get_post( $ID );
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
	 * @internal  Private. Use the 'taxonomy-images-queried-term-image-url' filter.
	 */
	public static function queried_term_image_url( $default, $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image-url';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$args = wp_parse_args( $args, array(
			'image_size' => 'thumbnail',
		) );

		$data = apply_filters( 'taxonomy-images-queried-term-image-data', array(), $args );

		$url = '';
		if ( isset( $data['url'] ) ) {
			$url = $data['url'];
		}

		return $url;

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
	 * @internal  Private. Use the 'taxonomy-images-queried-term-image-data' filter.
	 */
	public static function queried_term_image_data( $default, $args = array() ) {

		$filter = 'taxonomy-images-queried-term-image-data';
		if ( current_filter() !== $filter ) {
			self::please_use_filter( __FUNCTION__, $filter );
		}

		$args = wp_parse_args( $args, array(
			'image_size' => 'thumbnail',
		) );

		$ID = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

		if ( empty( $ID ) ) {
			return array();
		}

		$data = array();

		if ( in_array( $args['image_size'], array( 'full', 'fullsize' ) ) ) {
			$src = wp_get_attachment_image_src( $ID, 'full' );

			if ( isset( $src[0] ) ) {
				$data['url'] = $src[0];
			}
			if ( isset( $src[1] ) ) {
				$data['width'] = $src[1];
			}
			if ( isset( $src[2] ) ) {
				$data['height'] = $src[2];
			}

		} else {
			$data = image_get_intermediate_size( $ID, $args['image_size'] );
		}

		if ( ! empty( $data ) ) {
			return $data;
		}

		return array();

	}

	/**
	 * Please Use Filter
	 *
	 * Report to user that they are directly calling a function
	 * instead of using supported filters. A E_USER_NOTICE will
	 * be generated.
	 *
	 * @param  string  Name of function called.
	 * @param  string  Name of filter to use instead.
	 */
	private static function please_use_filter( $function, $filter ) {

		trigger_error( sprintf( esc_html__( 'The %1$s has been called directly. Please use the %2$s filter instead.', 'taxonomy-images' ),
			'<code>' . esc_html( $function . '()' ) . '</code>',
			'<code>' . esc_html( $filter ) . '</code>'
		) );

	}

	/**
	 * Check Taxonomy
	 *
	 * Wrapper for WordPress core functions taxonomy_exists().
	 * In the event that an unregistered taxonomy is passed a
	 * E_USER_NOTICE will be generated.
	 *
	 * @param   string   Taxonomy name as registered with WordPress.
	 * @param   string   Name of the current function or filter.
	 * @return  boolean  True if taxonomy exists, False if not.
	 */
	private static function check_taxonomy( $taxonomy, $filter ) {

		// Taxonomy doesn't exist
		if ( ! taxonomy_exists( $taxonomy ) ) {

			trigger_error( sprintf( esc_html__( 'The %1$s argument for %2$s is set to %3$s which is not a registered taxonomy. Please check the spelling and update the argument.', 'taxonomy-images' ),
				'<var>' . esc_html__( 'taxonomy', 'taxonomy-images' ) . '</var>',
				'<code>' . esc_html( $filter ) . '</code>',
				'<strong>' . esc_html( $taxonomy ) . '</strong>'
			) );

			return false;

		}

		$settings = get_option( 'taxonomy_image_plugin_settings' );

		// No taxonomies have image support
		if ( ! isset( $settings['taxonomies'] ) ) {

			trigger_error( esc_html__( 'No taxonomies have image support.', 'taxonomy-images' ) );

			return false;

		}

		// Taxonomy does not have image support
		if ( ! in_array( $taxonomy, (array) $settings['taxonomies'] ) ) {

			trigger_error( sprintf( esc_html__( 'The %1$s taxonomy does not have image support.', 'taxonomy-images' ),
				'<strong>' . esc_html( $taxonomy ) . '</strong>'
			) );

			return false;

		}

		return true;

	}

	/**
	 * Debug Content
	 *
	 * Adds example public filter output to post content.
	 *
	 * @param   string  $content  Content.
	 * @return  string            Debug content.
	 */
	public static function debug_content( $content ) {

		// Debug?
		if ( ! Plugin::debug() ) {
			return $content;
		}

		/**
		 * Get Terms
		 *
		 * Returns terms with `image_id` property.
		 */	
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-get-terms</h2>';
		$content .= '<pre>$terms = apply_filters( \'taxonomy-images-get-terms\', array() );</pre>';
		$content .= '<pre>' . print_r( apply_filters( 'taxonomy-images-get-terms', array() ), true ) . '</pre>';

		/**
		 * Get The Terms
		 *
		 * Returns terms from the current post with the `image_id` property.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-get-the-terms</h2>';
		$content .= '<pre>$terms = apply_filters( \'taxonomy-images-get-the-terms\', array() );</pre>';
		$content .= '<pre>' . print_r( apply_filters( 'taxonomy-images-get-the-terms', array() ), true ) . '</pre>';
		
		/**
		 * List The Terms
		 *
		 * Return html markup representing the images associated with
		 * terms from the current post.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-list-the-terms</h2>';
		$content .= '<pre>echo apply_filters( \'taxonomy-images-list-the-terms\', \'\', array(' . "\n\t" . '\'having_images\' => true,' . "\n\t" . '\'image_size\' => \'thumbnail\'' . "\n" . ') );</pre>';
		$content .= apply_filters( 'taxonomy-images-list-the-terms', '', array( 'having_images' => false, 'image_size' => 'thumbnail' ) );

		/**
		 * Queried Term Image
		 *
		 * Return html markup representing the image associated with the
		 * currently queried term. In the event that no associated image
		 * exists, the filter should return an empty object.
		 *
		 * In the event that the Taxonomy Images plugin is not installed
		 * apply_filters() will return it's second parameter.
		 *
		 * This example shows custom attributes added to the <img /> tag
		 * and content to add before and after the image.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-queried-term-image</h2>';
		$content .= '<pre>echo apply_filters( \'taxonomy-images-queried-term-image\', \'\', array(' . "\n\t" . '\'before\' => \'&lt;div style="padding: 20px; background-color: grey;"&gt;\',' . "\n\t" . '\'after\' => \'&lt;/div&gt;\',' . "\n\t" . '\'image_size\' => \'medium\'' . "\n\t'attr' => array(\n\t\t'alt' => 'Custom alternative text',\n\t\t'title' => 'Custom Title',\n\t\t'class' => 'my-class my-other-class'\n\t)\n" . ' ) );</pre>';
		$content .= apply_filters( 'taxonomy-images-queried-term-image', '', array(
			'before'     => '<div style="padding: 20px; background-color: grey;">',
			'after'      => '</div>',
			'image_size' => 'medium',
			'attr' => array(
				'alt'   => 'Custom alternative text',
				'title' => 'Custom Title',
				'class' => 'my-class my-other-class'
			)
		) );

		/**
		 * Queried Term Image Data
		 *
		 * Return an array of data about the image associated with the current
		 * queried term. In the event that no associated image exists, the filter
		 * should return an empty string.
		 *
		 * In the event that the Taxonomy Images plugin is not installed
		 * apply_filters() will return it's second parameter.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-queried-term-image-data</h2>';
		$content .= '<pre>$term_image_data = apply_filters( \'taxonomy-images-queried-term-image-data\', array(), array(' . "\n\t" . '\'image_size\' => \'medium\'' . "\n" . ') );</pre>';
		$content .= '<pre>' . print_r( apply_filters( 'taxonomy-images-queried-term-image-data', array(), array(
			'image_size' => 'medium'
		) ), true ) . '</pre>';

		/**
		 * Queried Term Image ID
		 *
		 * Return the id of the image associated with the currently
		 * queried term. In the event that no associated image exists,
		 * the filter should return zero.
		 *
		 * In the event that the Taxonomy Images plugin is not installed
		 * apply_filters() will return it's second parameter.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-queried-term-image-id</h2>';
		$content .= '<pre>$term_image_id = apply_filters( \'taxonomy-images-queried-term-image-id\', \'0\' );</pre>';
		$content .= '<pre>' . apply_filters( 'taxonomy-images-queried-term-image-id', '0' ) . '</pre>';

		/**
		 * Queried Term Image Object
		 *
		 * Return an object representing the image associated with the
		 * currently queried term. In the event that no associated image
		 * exists, the filter should return an empty object.
		 *
		 * In the event that the Taxonomy Images plugin is not installed
		 * apply_filters() will return it's second parameter.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-queried-term-image-object</h2>';
		$content .= '<pre>$term_image_obj = apply_filters( \'taxonomy-images-queried-term-image-object\', null );</pre>';
		$content .= '<pre>' . print_r( apply_filters( 'taxonomy-images-queried-term-image-object', null ), true ) . '</pre>';

		/**
		 * Queried Term Image URL
		 *
		 * Return a url to the image associated with the current queried
		 * term. In the event that no associated image exists, the filter
		 * should return an empty string.
		 *
		 * In the event that the Taxonomy Images plugin is not installed
		 * apply_filters() will return it's second parameter.
		 */
		$content .= '<hr />';
		$content .= '<h2>Filter: taxonomy-images-queried-term-image-url</h2>';
		$content .= '<pre>$term_image_url = apply_filters( \'taxonomy-images-queried-term-image-url\', \'\', array(' . "\n\t" . '\'image_size\' => \'medium\'' . "\n" . ') );</pre>';
		$content .= '<pre>' . apply_filters( 'taxonomy-images-queried-term-image-url', '', array(
			'image_size' => 'medium'
		) ) . '</pre>';

		return $content;

	}

}
