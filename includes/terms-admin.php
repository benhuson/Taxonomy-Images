<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Terms Admin
 */

namespace TaxonomyImages;

add_action( 'admin_init', array( 'TaxonomyImages\Terms_Admin', 'add_admin_fields' ) );

class Terms_Admin {

	/**
	 * Dynamically add admin fields for each taxonomy
	 *
	 * Adds hooks for each taxonomy that the user has given
	 * an image interface to via settings page. These hooks
	 * enable the image interface on wp-admin/edit-tags.php.
	 *
	 * @internal  Private. Called via the `admin_init` action.
	 */
	public static function add_admin_fields() {

		$settings = get_option( 'taxonomy_image_plugin_settings' );

		if ( ! isset( $settings['taxonomies'] ) ) {
			return;
		}

		foreach ( $settings['taxonomies'] as $taxonomy ) {
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( get_class(), 'taxonomy_columns' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( get_class(), 'term_row' ), 15, 3 );
			add_action( $taxonomy . '_edit_form_fields', array( get_class(), 'edit_form' ), 10, 2 );
		}

	}

	/**
	 * Edit Taxonomy Columns
	 *
	 * Insert a new column on wp-admin/edit-tags.php.
	 *
	 * @param   array  A list of columns.
	 * @return  array  List of columns with "Images" inserted after the checkbox.
	 *
	 * @internal  Private. Called via the `manage_edit-{$taxonomy}_columns` filter.
	 */
	public static function taxonomy_columns( $original_columns ) {

		$new_columns = $original_columns;
		array_splice( $new_columns, 1 );
		$new_columns['taxonomy_image_plugin'] = esc_html__( 'Image', 'taxonomy-images' );

		return array_merge( $new_columns, $original_columns );

	}

	/**
	 * Edit Term Row
	 *
	 * Create image control for each term row of wp-admin/edit-tags.php.
	 *
	 * @param   string   Row.
	 * @param   string   Name of the current column.
	 * @param   integer  Term ID.
	 * @return  string   HTML image control.
	 *
	 * @internal  Private.  Called via the `manage_{$taxonomy}_custom_column` filter.
	 */
	public static function term_row( $row, $column_name, $term_id ) {

		if ( 'taxonomy_image_plugin' === $column_name ) {

			$term = get_term( $term_id );

			$control = new Image_Admin_Control( $term );

			return $row . $control->get_rendered();

		}

		return $row;

	}

	/**
	 * Edit Term Form
	 *
	 * Create image control for `wp-admin/term.php`.
	 *
	 * @param  WP_Term  Term object.
	 * @param  string   Taxonomy slug.
	 *
	 * @internal  Private. Called via the `{$taxonomy}_edit_form_fields` action.
	 */
	public static function edit_form( $term, $taxonomy ) {

		$field = new Image_Admin_Field( $term );
		$control = new Image_Admin_Control( $term );

		?>
		<tr class="form-field hide-if-no-js">
			<th scope="row" valign="top">
				<label for="description"><?php print esc_html__( 'Featured Image', 'taxonomy-images' ); ?></label>
			</th>
			<td>
				<?php echo $control->get_rendered(); ?>
				<div class="clear"></div>
				<?php $field->the_description( '<span class="description">', '</span>' ); ?>
			</td>
		</tr>
		<?php

	}

}
