
// Media Modal Frame
var taxonomy_images_file_frame;

( function( $ ) {

	$( document ).ready( function() {

		// Store the old id (not sure if this is application when editing a term)
		TaxonomyImagesMediaModal.termID = 0;
		TaxonomyImagesMediaModal.imageType = '';

		// When the remove icon is clicked...
		$( '.wp-list-table, .form-table' ).on( 'click', '.taxonomy-image-control a.remove', function( event ) {

			event.preventDefault();

			var term_id = $( this ).data( 'term-id' );
			var image_type = $( this ).data( 'image-type' );

			$.ajax( {
				url      : ajaxurl,
				type     : 'POST',
				dataType : 'json',
				data     : {
					'action'     : 'taxonomy_images_delete_term_image',
					'wp_nonce'   : $( this ).data( 'nonce' ),
					'term_id'    : $( this ).data( 'term-id' ),
					'image_type' : $( this ).data( 'image-type' )
				},
				cache     : false,
				success   : function ( response ) {
					if ( 'good' === response.status ) {

						selector = $( '#taxonomy-image-control-' + image_type + '-' + term_id );

						/* Update the image on the screen below */
						selector.find( '.taxonomy-image-thumbnail img' ).attr( 'src', TaxonomyImagesMediaModal.default_img_src );

						selector.find( 'a.taxonomy-image-thumbnail' ).data( 'attachment-id', 0 );
						selector.find( 'a.upload' ).data( 'attachment-id', 0 );

						/* Show delete control on the screen below */
						selector.find( '.remove' ).addClass( 'hide' );

					}
					else if ( 'bad' === response.status ) {
						alert( response.why );
					}
				}
			} );

		} );

		// When image or upload icon clicked...
		$( '.wp-list-table, .form-table' ).on( 'click', '.taxonomy-image-control a.upload, .taxonomy-image-control a.taxonomy-image-thumbnail', function( event ) {

			event.preventDefault();

			button = $( this );

			TaxonomyImagesMediaModal.termID = $( this ).data( 'term-id' );
			TaxonomyImagesMediaModal.imageType = $( this ).data( 'image-type' );
			TaxonomyImagesMediaModal.attachment_id = $( this ).data( 'attachment-id' );
			TaxonomyImagesMediaModal.nonce = $( this ).data( 'nonce' );

			// If the media frame already exists, reopen it.
			if ( taxonomy_images_file_frame ) {

				// Set the post ID to the term being edited and open
				taxonomy_images_file_frame.open();
				return;

			} else {

				// Set the wp.media post id so the uploader grabs the term ID being edited
				TaxonomyImagesMediaModal.termID = $( this ).data( 'term-id' );
				TaxonomyImagesMediaModal.imageType = $( this ).data( 'image-type' );

			}

			// Create the media frame.
			taxonomy_images_file_frame = wp.media.frames.taxonomy_images_file_frame = wp.media( {
				title    : TaxonomyImagesMediaModal.uploader_title,
				button   : { text : TaxonomyImagesMediaModal.uploader_button_text },
				library  : { type: 'image' },
				multiple : false
			} );

			// Pre-select selected attachment
			wp.media.frames.taxonomy_images_file_frame.on( 'open', function() {
				var selection = wp.media.frames.taxonomy_images_file_frame.state().get( 'selection' );
				var selected_id = TaxonomyImagesMediaModal.attachment_id;
				if ( selected_id > 0 ) {
					attachment = wp.media.attachment( selected_id );
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				}
			} );

			// When an image is selected, run a callback.
			taxonomy_images_file_frame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader
				attachment = taxonomy_images_file_frame.state().get( 'selection' ).first().toJSON();

				var term_id = TaxonomyImagesMediaModal.termID;
				var image_type = TaxonomyImagesMediaModal.imageType;
				var attachment_id = attachment.id;

				// Do something with attachment.id and/or attachment.url here
				$.ajax( {
					url      : ajaxurl,
					type     : 'POST',
					dataType : 'json',
					data     : {
						'action'        : 'taxonomy_images_update_term_image',
						'wp_nonce'      : TaxonomyImagesMediaModal.nonce,
						'attachment_id' : attachment.id,
						'term_id'       : parseInt( TaxonomyImagesMediaModal.termID ),
						'image_type'    : TaxonomyImagesMediaModal.imageType 
					},
					success  : function ( response ) {
						if ( 'good' === response.status ) {
							var parent_id = button.parent().attr( 'id' );

							/* Set state of all other buttons. */
							$( '.taxonomy-image-modal-control' ).each( function( i, e ) {
								if ( parent_id == $( e ).attr( 'id' ) ) {
									return true;
								}
								$( e ).find( '.create-association' ).show();
								$( e ).find( '.remove-association' ).hide();
							} );

							selector = $( '#taxonomy-image-control-' + image_type + '-' + term_id );

							/* Update the image on the screen below */
							selector.find( '.taxonomy-image-thumbnail img' ).attr( 'src', response.attachment_thumb_src );

							selector.find( 'a.taxonomy-image-thumbnail' ).data( 'attachment-id', attachment_id );
							selector.find( 'a.upload' ).data( 'attachment-id', attachment_id );

							/* Show delete control on the screen below */
							$( selector ).find( '.remove' ).each( function ( i, e ) {
								$( e ).removeClass( 'hide' );
							} );

						}
						else if ( 'bad' === response.status ) {
							alert( response.why );
						}
					}
				} );

			} );

			// Finally, open the modal
			taxonomy_images_file_frame.open();

		} );

	} );

} )( jQuery );
