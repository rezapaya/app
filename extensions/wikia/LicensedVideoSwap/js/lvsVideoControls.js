/**
 * Handle clicks on play buttons so they play the video
 */

define( 'lvs.videocontrols', ['wikia.videoBootstrap', 'wikia.nirvana', 'jquery'], function( VideoBootstrap, nirvana, $ ) {
	"use strict";

	var videoInstances = [];

	function setVerticalAlign( $element, video ) {
return; // TODO: once height is set dynamically, let this function run.
		var videoHeight = video.height,
			wrapperHeight = $element.height(),
			topMargin = ( wrapperHeight - videoHeight ) / 2;

		$element.data( 'height', wrapperHeight ).height( wrapperHeight - topMargin ).css( 'padding-top', topMargin );
	}

	// remove vertical alignment css
	function removeVerticalAlign( $element ) {
return; // TODO: once height is set dynamically, let this function run.
		var height = $element.data( 'height' );
		if ( height ) {
			$element.height( height ).css( 'padding-top', 0 );
		}
	}

	function init( $container ) {
		var videoWidth = $container.find( '.grid-3' ).width();

		$container.on( 'click', '.video', function(e) {
			e.preventDefault();

			var $this = $( this ),
				fileTitle = decodeURIComponent( $this.children( 'img' ).attr( 'data-video-key' ) ),
				$element,
				$thumbList,
				$row = $this.closest( '.row' );

			$row.find( '.swap-button' ).attr( 'data-video-swap', fileTitle );

			if ( $this.hasClass( 'thumb' ) ) {
				// one of the thumbnails was clicked
				$element = $this.closest( '.row' ).find( '.premium .video-wrapper' );
				$thumbList = $this.closest( 'ul' );

				// put outline around the thumbnail that was clicked
				$thumbList.find( '.selected' ).removeClass( 'selected' );
				$this.addClass( 'selected' );
			} else {
				// Large image was clicked
				$element = $this.parent();
			}


			nirvana.sendRequest({
				controller: 'VideoHandler',
				method: 'getEmbedCode',
				data: {
					fileTitle: fileTitle,
					width: videoWidth,
					autoplay: 1
				},
				callback: function( data ) {
					// Remove styles of previous video
					removeVerticalAlign( $element );

					var videoInstance = new VideoBootstrap( $element[0], data.embedCode, 'licensedVideoSwap' );

					setVerticalAlign( $element, videoInstance );

					// Update swap button so it contains the dbkey of the video to swap
					$row.find( '.swap-button' ).attr( 'data-video-swap', fileTitle );

					videoInstances.push( videoInstance );
				}
			});
		});

		$container.on( 'contentReset', function() {
			// All video instances will have been wiped away with the html reset
			videoInstances = [];
		});
	}

	function reset() {
		var i,
			len = videoInstances.length,
			vb;

		for ( i = 0; i < len; i++ ) {
			vb = videoInstances[ i ];

			// revert to original html
			removeVerticalAlign( $( vb.element ) );
			vb.resetToThumb();

		}
	}

	return {
		init: init,
		reset: reset
	}
});