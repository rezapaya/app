/**
 * Controls for swap button and keep button in LicensedVideoSwap
 */
define( 'lvs.swapkeep', [
	'wikia.querystring',
	'lvs.commonajax',
	'lvs.videocontrols',
	'jquery',
	'wikia.nirvana'
], function( QueryString, commonAjax, videoControls, $, nirvana ) {
	"use strict";

	var $parent,
		$overlay,
		$row,
		$button,
		isSwap,
		currTitle,
		newTitle,
		qs,
		sort,
		page,
		$container;

	function doRequest(){
		// Add loading graphic
		commonAjax.startLoadingGraphic();

		qs = new QueryString();
		sort = qs.getVal( 'sort', 'recent' );
		page = qs.getVal( 'currentPage', 1);

		var data = {
			videoTitle: currTitle,
			sort: sort,
			currentPage: page
		};

		if ( isSwap ) {
			data.newTitle = newTitle;
		}

		nirvana.sendRequest({
			controller: 'LicensedVideoSwapSpecialController',
			method: isSwap ? 'swapVideo' : 'keepVideo',
			data: data,
			callback: function( data ) {
				commonAjax.success( $container, data);
			},
			onErrorCallback: function() {
				commonAjax.failure();
			}
		});
	}

	function confirmModal() {
		videoControls.reset();

		var currTitleText =  currTitle.replace(/_/g, ' ' ),
			newTitleText,
			title,
			msg;

		if ( isSwap ) {
			newTitleText = newTitle.replace(/_/g, ' ' );
			title = $.msg( 'lvs-confirm-swap-title' );
			msg = $.msg( 'lvs-confirm-swap-message', currTitleText, newTitleText );
		} else {
			title = $.msg( 'lvs-confirm-keep-title' );
			msg = $.msg( 'lvs-confirm-keep-message', currTitleText );
		}

		$.confirm({
			title: title,
			content: msg,
			onOk: function() {
				doRequest();
			},
			width: 700
		});
	}

	function init( $elem ) {
		// Event listener for interacting with buttons
		$container = $elem;
		$container.on( 'mouseover mouseout click', '.swap-button, .keep-button', function( e ) {
			$button = $( this );

			$parent = $button.parent();
			$overlay = $parent.siblings( '.swap-arrow' );
			$row = $button.closest( '.row' );
			isSwap = $button.is( '.swap-button' );

			if ( isSwap ) {
				// swap button hovered
				if ( e.type == 'mouseover' ) {
					$overlay.fadeIn( 100 );
				} else if ( e.type == 'mouseout' ) {
					$overlay.fadeOut( 100 );
					// swap button clicked
				} else if ( e.type == 'click' ) {
					// Get both titles - current/non-premium video and video to swap it out with
					newTitle = decodeURIComponent( $button.attr( 'data-video-swap' ) );
					currTitle = decodeURIComponent( $row.find( '.keep-button' ).attr( 'data-video-keep' ) );
					confirmModal();
				}
				// Keep button clicked
			} else if ( e.type == 'click' ) {
				currTitle = decodeURIComponent( $row.find( '.keep-button' ).attr( 'data-video-keep' ) );
				// no new title b/c we're keeping the current video
				newTitle = '';
				confirmModal();
			}
		});
	}

	return {
		init: init
	};
});