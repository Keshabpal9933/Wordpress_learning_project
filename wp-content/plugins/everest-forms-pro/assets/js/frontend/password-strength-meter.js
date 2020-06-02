/* global wp, pwsL10n */
( function( $ ) {
	'use strict';

	var evf_password_strength_meter = {
		$document: $( document ),

		// Can define the blacklist here.
		blacklist: [],

		// Begin event listening routine.
		init: function() {
			evf_password_strength_meter.$document.ready( function() {
				// For textual as well as bar style visual indicator.
				evf_password_strength_meter.$document.on( 'keyup change', '*[data-strength="meter"], *[data-strength="progress"]', function () {
					evf_password_strength_meter.password_functor( $( this ) );
				});
			});
		},

		// Returns the html for initial text password strength indicator.
		bar_init: function ( id ) {
			return '<div id="pass-strength-result-' + id + '" class="everest-forms-pass-strength everest-forms-progress-bar very-weak"><div class="everest-forms-progress-bar-indicator"></div></div>';
		},

		// Returns the html for initial progress bar style indicator.
		txt_init: function ( id ) {
			return '<div id="pass-strength-result-' + id + '" class="everest-forms-pass-strength very-weak">' + pwsL10n.short + '</div>';
		},

		// Maps the events to the right functions to be able to check passwords.
		password_functor: function( el ) {
			var $this  = el,
				el_id  = $this.attr( 'id' ),
				data   = $this.data( 'strength' ),
				status = $( '#pass-strength-result-' + el_id );

			if ( 1 !== status.length ) {
				var $el_to_append = $this.closest( '.evf-field-password-input' ),
					to_append     = 'progress' === data ? evf_password_strength_meter.bar_init( el_id ) : evf_password_strength_meter.txt_init( el_id );

				if ( 0 === $this.val().length ) {
					return 0;
				}

				if ( $el_to_append.next().is( $( '.everest-forms-field-sublabel' ) ) ) {
					// Append after the input control.
					$el_to_append.next().after( to_append );
				} else {
					// Append after the sublabel.
					$el_to_append.after( to_append );
				}

				status = $( '#password-strength' + el_id );
			}

			// Delegate the data to decide how to manip field, and the element itself with the password.
			evf_password_strength_meter.password_level( $this.val(), status, data );
		},

		// Manipulates the password strength Div element based on the password strength passed.
		password_level: function ( password, el, data ) {
			// The score is determined by wp's inherent function, from the pwsL10n library.
			var score = wp.passwordStrength.meter( password, evf_password_strength_meter.blacklist, password ),
				bar   = '<div class="everest-forms-progress-bar-indicator"></div>';

			// Since both types share the same archetype classes, we remove them all.
			el.removeClass( 'weak medium strong very-weak' );

			switch ( score ) {
				case 2:
					el.addClass( 'weak' ).html( ( 'progress' !== data ) ? pwsL10n.bad : bar );
					break;
				case 3:
					el.addClass( 'medium' ).html( ( 'progress' !== data ) ? pwsL10n.good : bar );
					break;
				case 4:
					el.addClass( 'strong' ).html( ( 'progress' !== data ) ? pwsL10n.strong : bar );
					break;
				default:
					if ( 0 === password.length ) {
						el.remove();
						break;
					} else {
						el.addClass( 'very-weak' ).html( ( 'progress' !== data ) ? pwsL10n.short : bar );
						break;
					}
			}

			return true;
		}
	};

	// Initialize.
	evf_password_strength_meter.init();
})( jQuery );
