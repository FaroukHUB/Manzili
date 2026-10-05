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

	function initVideoPicker( buttonId, removeId, inputId ) {
		var button = document.getElementById( buttonId );
		var remove = document.getElementById( removeId );
		var input  = document.getElementById( inputId );
		if ( ! button ) return;
		var frame;
		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media( {
				title:    'Choisir une vidéo',
				button:   { text: 'Utiliser cette vidéo' },
				multiple: false,
				library:  { type: 'video' }
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				input.value = att.id;
				button.textContent = 'Changer la vidéo';
				remove.style.display = 'inline-block';
				var preview = button.closest( '.clo-img-picker' ).querySelector( 'video' );
				if ( preview ) {
					preview.src = att.url;
				} else {
					var v = document.createElement( 'video' );
					v.src = att.url; v.controls = true; v.muted = true;
					v.style.cssText = 'max-width:120px;max-height:80px;border:1px solid #ccc;';
					button.closest( '.clo-img-picker' ).insertBefore( v, button.parentNode );
				}
			} );
			frame.open();
		} );
		remove.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			input.value = '';
			button.textContent = 'Choisir une vidéo';
			remove.style.display = 'none';
			var preview = button.closest( '.clo-img-picker' ).querySelector( 'video' );
			if ( preview ) preview.remove();
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initImagePicker( 'clo-img1-pick', 'clo-img1-remove', 'clo_image_1_id', 'clo-img1-preview' );
		initImagePicker( 'clo-img2-pick', 'clo-img2-remove', 'clo_image_2_id', 'clo-img2-preview' );
		initImagePicker( 'clo-img3-pick', 'clo-img3-remove', 'clo_image_3_id', 'clo-img3-preview' );
		initVideoPicker( 'clo-video-pick', 'clo-video-remove', 'clo_video_id' );
	} );
}() );
