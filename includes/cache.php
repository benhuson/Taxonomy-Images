<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Cache
 */

namespace TaxonomyImages;

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

		$assoc = Associations_Legacy::get();

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

			if ( ! isset( $assoc[ $tt_id ] ) || in_array( $assoc[ $tt_id ], $image_ids ) ) {
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

}
