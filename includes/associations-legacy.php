<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Associations (Legacy)
 *
 * This class provides an interface for term image
 * data stored as a seralized option.
 *
 * Hopefully this will be able to be deprecated at some point.
 */

namespace TaxonomyImages;

class Associations_Legacy {

	/**
	 * Sanitize Associations
	 *
	 * Ensures that all key/value pairs are positive integers.
	 * This filter will discard all zero and negative values.
	 *
	 * @internal  Private.
	 *
	 * @param   array  An array of term_taxonomy_id/attachment_id pairs.
	 * @return  array  Sanitized version of parameter.
	 */
	public static function sanitize( $associations ) {

		$o = array();

		foreach ( (array) $associations as $tt_id => $im_id ) {
			$tt_id = absint( $tt_id );
			$im_id = absint( $im_id );
			if ( 0 < $tt_id && 0 < $im_id ) {
				$o[ $tt_id ] = $im_id;
			}
		}

		return $o;

	}

}
