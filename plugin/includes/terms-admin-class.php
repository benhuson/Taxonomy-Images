<?php

/**
 * @package     Taxonomy Images
 * @subpackage  Terms Admin
 */

namespace Plugins\Taxonomy_Images;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Terms_Admin {

	/**
	 * Image Types Class
	 *
	 * @var  Image_Types|null
	 */
	private $image_types = null;

	/**
	 * Constructor
	 *
	 * @param  Image_Types  $image_types  Instance of class.
	 */
	public function __construct( $image_types ) {

		$this->image_types = $image_types;

	}

	/**
	 * Term Image Column ID
	 *
	 * @var  string
	 */
	private $term_image_column_id = 'taxonomy_image_plugin';

	/**
	 * Dynamically add admin fields for each taxonomy
	 *
	 * Adds hooks for each taxonomy that the user has given
	 * an image interface to via settings page. These hooks
	 * enable the image interface on wp-admin/edit-tags.php.
	 *
	 * @internal  Private. Called via the `admin_init` action.
	 */
	public function add_admin_fields() {

		$settings = get_option( 'taxonomy_image_plugin_settings' );

		if ( ! isset( $settings['taxonomies'] ) ) {
			return;
		}

		foreach ( $settings['taxonomies'] as $taxonomy ) {
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'taxonomy_columns' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'term_row' ), 15, 3 );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form' ), 10, 2 );
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
	public function taxonomy_columns( $original_columns ) {

		$new_columns = $original_columns;
		array_splice( $new_columns, 1 );
		$new_columns[ $this->term_image_column_id ] = esc_html__( 'Image', 'taxonomy-images' );

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
	public function term_row( $row, $column_name, $term_id ) {

		if ( $this->term_image_column_id === $column_name ) {

			$control = new Term_Image_Admin_Control( $term_id );

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
	public function edit_form( $term, $taxonomy ) {

		$image_types = $this->image_types->get_image_types( $taxonomy );

		foreach ( $image_types as $image_type ) {

			$control = new Term_Image_Admin_Control( $term->term_id, $image_type->get_id() );

			?>
			<tr class="form-field hide-if-no-js">
				<th scope="row" valign="top">
					<label for="description"><?php printf( esc_html__( '%s Image', 'taxonomy-images' ), esc_html( $image_type->get_label() ) ); ?></label>
				</th>
				<td><?php echo $control->get_rendered( 'large' ); ?></td>
			</tr>
			<?php

		}

	}

	/**
	 * Enqueue Admin Scripts
	 *
	 * @internal  Private. Called via the `admin_enqueue_scripts` action.
	 */
	public function enqueue_scripts() {

		if ( ! $this->is_term_admin_screen() ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'taxonomy-images-media-modal',
			Plugin::plugin_url( 'plugin/assets/js/media-modal.js' ),
			array( 'jquery' ),
			Plugin::VERSION
		);

		wp_localize_script( 'taxonomy-images-media-modal', 'TaxonomyImagesMediaModal', array(
			'wp_media_post_id'     => 0,
			'attachment_id'        => 0,
			'uploader_title'       => __( 'Featured Image', 'taxonomy-images' ),
			'uploader_button_text' => __( 'Set featured image', 'taxonomy-images' ),
			'default_img_src'      => Plugin::plugin_url( 'plugin/assets/images/default.png' )
		) );

	}

	/**
	 * Enqueue Admin Styles
	 *
	 * @internal  Private. Called via the `admin_print_styles-{$page}` action.
	 */
	public function enqueue_styles() {

		if ( ! $this->is_term_admin_screen() ) {
			return;
		}

		wp_enqueue_style(
			'taxonomy-image-plugin-edit-tags',
			Plugin::plugin_url( 'plugin/assets/css/admin.css' ),
			array(),
			Plugin::VERSION,
			'screen'
		);

	}

	/**
	 * Is Term Admin Screen?
	 *
	 * @return  boolean
	 */
	private function is_term_admin_screen() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! isset( $screen->taxonomy ) ) {
			return false;
		}

		$settings = get_option( 'taxonomy_image_plugin_settings' );
		if ( ! isset( $settings['taxonomies'] ) ) {
			return false;
		}

		if ( in_array( $screen->taxonomy, $settings['taxonomies'] ) ) {
			return true;
		}

		return false;

	}

}
