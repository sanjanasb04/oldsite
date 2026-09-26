/******/ (() => { // webpackBootstrap
/*!********************************!*\
  !*** ./assets/src/js/lph5p.js ***!
  \********************************/
jQuery( document ).ready( function() {
	if ( jQuery( '.learn_press_h5p_content' ).hasClass( 'interactivevideo' ) ) {
		jQuery( '#complete_h5p_button' ).hide();
	}

	if ( typeof H5P !== 'undefined' && H5P.externalDispatcher ) {
		H5P.externalDispatcher.on( 'xAPI', onXapi );
	}
} );

function onXapi( event ) {
	if ( ! lpH5pSettings.ajax_url || ! lpH5pSettings.conditional_h5p ) {
		return;
	}

	if ( ( event.getVerb() === 'completed' || event.getVerb() === 'answered' ) && ! event.getVerifiedStatementValue( [ 'context', 'contextActivities', 'parent' ] ) ) {
		if ( lpH5pSettings.h5p_button_complete === 'yes' ) {
			const elBtnCompleteH5P = document.querySelector( '#complete_h5p_button' );
			elBtnCompleteH5P.style.display = 'inline-block';
		} else {
			submitH5P();
		}
	}
}

const submitH5P = () => {
	// Call ajax to add new section
	const callBack = {
		success: ( response ) => {
			const { status, message, data } = response;
			if ( 'success' === status ) {
				window.location.reload();
			} else {
				alert( message );
			}
		},
		error: ( error ) => {
			alert( error );
		},
		completed: () => {

		},
	};

	const dataSend = {
		action: 'user_submit_h5p_when_complete',
		lp_h5p_id: lpH5pSettings.id,
		course_id: lpH5pSettings.course_id,
		args: {
			id_url: 'submit_h5p_when_complete',
		},
	};
	window.lpAJAXG.fetchAJAX( dataSend, callBack );
}

/******/ })()
;
//# sourceMappingURL=lph5p.js.map