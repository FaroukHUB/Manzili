/* CLO product meta box — image pickers via wp.media */
( function () {
	'use strict';

	function initImagePicker( buttonId, removeId, inputId, previewId ) {
		var button  = document.getElementById( buttonId );
		var remove  = document.getElementById( removeId );
		var input   = document.getElementById( inputId );
		var preview = document.getElementById( previewId );

		if ( ! button ) {
			return;
		}

		var frame;

		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title:    button.dataset.title || 'Choisir une image',
				button:   { text: 'Utiliser cette image' },
				multiple: false,
				library:  { type: 'image' }
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				input.value = attachment.id;
				preview.src = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;
				preview.style.display = 'block';
				remove.style.display  = 'inline-block';
			} );

			frame.open();
		} );

		remove.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			input.value            = '';
			preview.src            = '';
			preview.style.display  = 'none';
			remove.style.display   = 'none';
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initImagePicker( 'clo-img1-pick', 'clo-img1-remove', 'clo_image_1_id', 'clo-img1-preview' );
		initImagePicker( 'clo-img2-pick', 'clo-img2-remove', 'clo_image_2_id', 'clo-img2-preview' );
		initImagePicker( 'clo-img3-pick', 'clo-img3-remove', 'clo_image_3_id', 'clo-img3-preview' );
	} );
}() );
