<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Cache
 */

namespace TaxonomyImages;

add_action( 'template_redirect', array( 'TaxonomyImages\Cache', 'cache_queried_images' ) );

class Cache {

	/**
	 * Cache Queried Images
	 *
	 * Cache all term images associated with posts in
	 * the main WordPress query.
	 *
	 * @param  array  Post objects.
	 *
	 * @internal  Private. Called via the `template_redirect` action.
	 */
	public static function cache_queried_images() {

		global $posts;

		self::cache_images( $posts );

	}

	/**
	 * Cache Images
	 *
	 * Sets the WordPress object cache for all term images
	 * associated to the posts in the provided array. This
	 * function has been created to minimize queries when
	 * using this plugins get_the_terms() style function.
	 *
	 * @param  array  Post objects.
	 */
	private static function cache_images( $posts ) {

		$term_ids = array();

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
					if ( ! isset( $term->term_id ) ) {
						continue;
					}
					$term_ids[] = $term->term_id;
				}

			}

		}

		$term_ids = array_filter( array_unique( $term_ids ) );
		$image_ids = array();

		foreach ( $term_ids as $term_id ) {

			$t = new Term( $term_id );
			$image_id = $t->get_image_id();

			if ( empty( $image_id ) ) {
				continue;
			}

			$image_ids[] = $image_id;

		}

		if ( empty( $image_ids ) ) {
			return;
		}

		$images = get_posts( array(
			'include'   => $image_ids,
			'post_type' => 'attachment'
		) );

	}

}
