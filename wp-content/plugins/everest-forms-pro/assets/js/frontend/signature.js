/**
 * Script to handle signature field activities
 */
'use strict';

var emptySignature;
var signatureCans = document.getElementsByClassName( "evf-signature-canvas" );
var signaturePads = Array();
var ratio = Math.max(window.devicePixelRatio || 1, 1);

if ( signatureCans != "undefined" ) {
	for ( var i in signatureCans) {
		if ( i >= 0 ) {
			var signatureCanvasParent = signatureCans[i].parentNode;
			var formId = signatureCanvasParent.getAttribute( "data-form-id" );
			var fieldId = signatureCanvasParent.getAttribute( "data-field-id" );

			// This part causes the canvas to be cleared
			signatureCans[i].width = signatureCans[i].offsetWidth * ratio;
			signatureCans[i].height = signatureCans[i].offsetHeight * ratio;
			signatureCans[i].getContext("2d").scale(ratio, ratio);

			signaturePads[fieldId] = new SignaturePad( signatureCans[i], {
				penColor: evf_signature_params.pen_color,
				backgroundColor: evf_signature_params.background_color,
				throttle: 0,
				onEnd: function() {
					var canvParent = this._ctx.canvas.parentNode;
					var fieldId = canvParent.getAttribute( "data-field-id" );
					var fileFormat = "image/" + canvParent.getAttribute( "data-image-format" );
					jQuery( "#evf-signature-img-input-" + fieldId ).val( this.toDataURL( fileFormat ) ).trigger( 'input change' ).valid();
					jQuery(canvParent).closest('.evf-field-signature').removeClass('everest-forms-invalid').addClass('everest-forms-validated');
				}
			});

			document.getElementById( "everest-form-signature-reset-" + fieldId ).addEventListener( 'click', function() {
				var fieldId = this.getAttribute( 'id' ).replace( 'everest-form-signature-reset-', '' );
				document.getElementById( 'evf-signature-img-input-' + fieldId ).value = '';
				signaturePads[fieldId].clear();
			});
		}
	}

	// Add event listerner on submit of form
	signaturePads.map(function(signaturePad, i) {
		// Add event listener to the reset button.
		document
			.getElementById("everest-form-signature-reset-" + i)
			.addEventListener("click", function() {
				document
					.getElementById("evf-signature-img-input-" + i)
					.setAttribute("value", "");
				signaturePad.clear();
			});
	});

	// Add event listener to window resize for canvas adjustment
	window.addEventListener("resize", function() {
		var ratio = Math.max( window.devicePixelRatio || 1, 1 );
		var allCanvases = document.getElementsByClassName(
			"evf-signature-canvas"
		);
		for ( var a in allCanvases ) {
			if (a >= 0) {
				var ctx = allCanvases[a].getContext( "2d" );
				allCanvases[a].width = allCanvases[a].offsetWidth * ratio;
				allCanvases[a].height = allCanvases[a].offsetHeight * ratio;
				ctx.fillStyle = evf_signature_params.background_color;
				ctx.scale(ratio, ratio);
				ctx.clearRect(
					0,
					0,
					allCanvases[a].width,
					allCanvases[a].height
				);
				ctx.fillRect(0, 0, allCanvases[a].width, allCanvases[a].height);
			}
		}
	});
}

// Reset signature pad for multipart
(function( $ ) {
	$( document ).ready( function() {

		$( '.everest-forms-part-button' ).on( 'evf-init-part-change', function() {
			var allCans = $( ".evf-signature-canvas" );

			// Ref: https://stackoverflow.com/questions/27176983/dispatchevent-not-working-in-ie11
			if ( 'function' === typeof Event ) {
				window.dispatchEvent( new Event( 'resize' ) );
			} else {
				var event = document.createEvent( 'Event' );

				event.initEvent( 'resize', true, true );
				window.dispatchEvent( event );
			}

			if ( allCans.length > 1 ) {
				$( ".evf-signature-canvas" ).each( function() {
					var fieldId = $( this ).parent().attr( 'data-field-id' );
					var canvas  = document.getElementById( 'evf-signature-canvas-' + fieldId );
					var ctx     = canvas.getContext( '2d' );
					var sigImg  = new Image();
					var imgVal  = $('#evf-signature-img-input-' + fieldId).val();
					if ( imgVal.length > 0 ) {
						sigImg.src = imgVal;
						ctx.drawImage(sigImg, 0, 0);
					}
				});
			}

		});
	});
}( jQuery ));
