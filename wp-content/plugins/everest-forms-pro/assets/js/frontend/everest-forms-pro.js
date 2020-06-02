/* global everest_forms_params */
jQuery( function ( $ ) {

	// everest_forms_params is required to continue, ensure the object exists.
	if ( typeof everest_forms_params === 'undefined' ) {
		return false;
	}

	var getEnhancedSelectFormatString = function() {
		return {
			'language': {
				noResults: function() {
					return everest_forms_params.i18n_no_countries;
				}
			}
		};
	};

	var everest_forms_pro = {
		init: function() {
			$( document ).ready( everest_forms_pro.ready );

			var hidden_type = $('.evf-single-item-price-hidden input[type=hidden]').length;
			var user_defined_type = $('.evf-field-payment-single input[type=text]').length;
			var single_amount = $('.evf-single-item-price').find("span").length;

			if ( hidden_type !== 0 && user_defined_type == 0 && single_amount == 0 ) {
					$('.evf-single-item-price-hidden input[type=hidden]').closest('.evf-field-payment-single').hide();
					$('.evf-field-payment-total').hide();
			} else {
					$('.evf-single-item-price-hidden input[type=hidden]').closest('.evf-field-payment-single').hide();
					$('.evf-field-payment-total').show();
			}

			this.init_range_sliders();
			this.load_validation();
			everest_forms_pro.bindUIActions();
		},

		ready: function() {
			everest_forms_pro.loadPayments();
			everest_forms_pro.loadPhoneField();
			everest_forms_pro.loadCountryFlags();
		},

		/**
		 * Initialize Range Slider Fields.
		 *
		 * @since 1.3.3
		 */
		init_range_sliders: function() {
			// Show Range Slider Fields.
			$( '.evf-field.evf-field-range-slider' ).show();

			if ( $().ionRangeSlider && $( '.evf-field-range-slider' ).length ) {
				// Initialize range slider.
				$( '.evf-field-range-slider .evf-field-primary-input' ).ionRangeSlider({
					onFinish: function( elements ) {
						var $field = elements.input.closest( '.evf-field' );

						everest_forms_pro.setPrefixPostfixTexts( null, null, $field );
					}
				})

				// Slider value change handler.
				.on( 'change', function() {
					var new_value = $( this ).val();

					// Update slider input.
					$( this ).closest( '.evf-field-range-slider' ).find( '.evf-slider-input' ).val( new_value );

					// Update slider handle/highlight/track color.
					everest_forms_pro.setSliderColors( $( this ).closest( '.evf-field' ) );
				});

				// Slider input value change handler.
				$( '.evf-field-range-slider .evf-slider-input' )
				.on( 'input', function() {
					var new_value = $( this ).val(),
						$field = $( this ).closest( '.evf-field' );

					$( this ).closest( '.evf-field-range-slider' ).find( '.evf-field-primary-input' ).data( 'ionRangeSlider' ).update({ from: new_value });
					everest_forms_pro.setPrefixPostfixTexts( null, null, $field );
				});

				// Slider Reset icon handler.
				$( '.evf-field-range-slider .evf-range-slider-reset-icon' ).on( 'click', function( e ) {
					var $field = $( this ).closest( '.evf-field' ),
						default_value = $field.find( '.evf-field-primary-input' ).data( 'default' );

					// Update slider to default value.
					$field.find( '.evf-field-primary-input' ).data( 'ionRangeSlider' ).update({ from: default_value });

					// Update slider handle color.
					everest_forms_pro.setSliderColors( $( this ).closest( '.evf-field' ) );
					everest_forms_pro.setPrefixPostfixTexts( null, null, $field );
				});

				// Setup sliders according to the options.
				$( '.evf-field.evf-field-range-slider' ).each( function() {
					var $field = $( this ).closest( '.evf-field' );

					// Set slider handle/highlight/track color.
					everest_forms_pro.setSliderColors( this );

					// Use text prefix/postfix.
					everest_forms_pro.setPrefixPostfixTexts( null, null, $field );
				});
			}
		},

		/**
		 * Sets configured texts as prefix and postfix for a Range Slider field.
		 *
		 * @since 1.3.3
		 */
		setPrefixPostfixTexts: function ( field_id, form_id, $field ) {
			var provided_selector_params = ( field_id && '' !== field_id && form_id && '' !== form_id ),
				provided_field = ( null !== $field && undefined !== $field );

			if ( provided_selector_params || provided_field ) {
				var $field = provided_field ? $field : $( '#evf-' + form_id + '-field_' + field_id + '-container' ),
					$primary_input = $field.find( '.evf-field-primary-input' ),
					use_text_prefix_postfix = $primary_input.data( 'use-text-prefix-postfix' ),
					prefix_text = $primary_input.data( 'prefix-text' ),
					postfix_text = $primary_input.data( 'postfix-text' );

				if ( true === use_text_prefix_postfix ) {
					$field.find( 'span.irs-min' ).html( prefix_text );
					$field.find( 'span.irs-max' ).html( postfix_text );
				}
			}
		},

		/**
		 * Sets colors for a Range Slider field's handle, highlight and track.
		 *
		 * @since 1.3.3
		 */
		setSliderColors: function ( element ) {
			var $primary_input = $( element ).find( '.evf-field-primary-input' ),
				highlight_color = $primary_input.data( 'highlight_color' ),
				track_color = $primary_input.data( 'track_color' );

			everest_forms_pro.setSliderHandleColor( element );
			$( element ).find( '.irs-bar' ).css( 'background', highlight_color );
			$( element ).find( '.irs-line' ).css( 'background', track_color );
		},

		/**
		 * Set a Range Slider field's handle color.
		 *
		 * @since 1.3.3
		 */
		setSliderHandleColor: function ( element ) {
			if ( element ) {
				var $field = $( element ),
					field_id = $field.attr( 'id' ),
					skin = $field.find( '.evf-field-primary-input' ).data( 'skin' ),
					handle_color = $field.find( '.evf-field-primary-input' ).data( 'handle_color' ),
					style = '';

				switch ( skin ) {
					case 'flat':
						$field.find( '.irs-handle i' ).first().css( 'background-color', handle_color );
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						style = '#' + field_id +' .irs-single:before { border-top-color: ' + handle_color + '!important; }';
						break;

					case 'big':
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						$field.find( '.irs-single' ).css( 'background', handle_color );
						$field.find( '.irs-handle' ).css( 'background-color', handle_color );
						$field.find( '.irs-handle' ).css( 'background', handle_color );
						break;

					case 'modern':
						$field.find( '.irs-handle i' ).css( 'background', handle_color );
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						style = '#' + field_id +' .irs-single:before { border-top-color: ' + handle_color + '!important; }';
						break;

					case 'sharp':
						$field.find( '.irs-handle' ).css( 'background-color', handle_color );
						$field.find( '.irs-handle i' ).first().css( 'border-top-color', handle_color );
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						style = '#' + field_id +' .irs-single:before { border-top-color: ' + handle_color + '!important; }';
						break;

					case 'round':
						$field.find( '.irs-handle' ).css( 'border-color', handle_color );
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						style = '#' + field_id +' .irs-single:before { border-top-color: ' + handle_color + '!important; }';
						break;

					case 'square':
						$field.find( '.irs-handle' ).css( 'border-color', handle_color );
						$field.find( '.irs-single' ).css( 'background-color', handle_color );
						style = '#' + field_id +' .irs-single:before { border-top-color: ' + handle_color + '!important; }';
						break;
				}

				$( 'body' ).find( '.evf-range-slider-handle-style-tag-' + field_id ).remove();
				$( 'body' ).append( '<style class="evf-range-slider-handle-style-tag-' + field_id + '" >' + style + '</style>' );
			}
		},

		load_validation: function() {
			if ( typeof $.fn.validate === 'undefined' ) {
				return false;
			}

			// Validate method for file extensions.
			$.validator.addMethod( 'extension', function( value, element, param ) {
				param = typeof param === 'string' ? param.replace( /,/g, '|' ) : 'png|jpe?g|gif';
				return this.optional( element ) || value.match( new RegExp( '\\.(' + param + ')$', 'i' ) );
			}, everest_forms_params.i18n_messages_fileextension );

			// Validate method for file size.
			$.validator.addMethod( 'maxsize', function( value, element, param ) {
				var maxSize = param,
					optionalValue = this.optional( element ),
					i, len, file;

				if ( optionalValue ) {
					return optionalValue;
				}

				if ( element.files && element.files.length ) {
					i = 0;
					len = element.files.length;
					for ( ; i < len; i++ ) {
						file = element.files[i];
						if ( file.size > maxSize ) {
							return false;
						}
					}
				}

				return true;
			}, everest_forms_params.i18n_messages_filesize );

			// Validate Smart Phone Field.
			if ( typeof $.fn.intlTelInput !== 'undefined' ) {
				$.validator.addMethod( 'smart-phone-field', function( value, element ) {
					return this.optional( element ) || $( element ).intlTelInput( 'isValidNumber' );
				}, everest_forms_params.i18n_messages_phone );
			}
		},

		/**
		 * Payments: Do various payment-related tasks on load.
		 */
		loadPayments: function() {
			// Update Total field(s) with latest calculation.
			$( '.evf-payment-total' ).each( function( index, el ) {
				everest_forms_pro.amountTotal( this );
			} );
		},

		/**
		 * Load phone field.
		 *
		 * @since 1.2.9
		 */
		loadPhoneField: function() {
			var inputOptions = {};

			// Only continue if intlTelInput library exists.
			if ( typeof $.fn.intlTelInput === 'undefined' ) {
				return false;
			}

			// Determine the country by IP if storing user details is enabled.
			if ( 'yes' !== everest_forms_params.disable_user_details ) {
				inputOptions.geoIpLookup = everest_forms_pro.currentIpToCountry;
			}

			// Try an alternative solution if storing user details is disabled.
			if ( 'yes' === everest_forms_params.disable_user_details ) {
				var lang = this.getFirstBrowserLanguage(),
					countryCode = lang.indexOf( '-' ) > -1 ? lang.split( '-' ).pop() : '';
			}

			// Make sure the library recognizes browser country code to avoid console error.
			if ( countryCode ) {
				var countryData = window.intlTelInputGlobals.getCountryData();

				countryData = countryData.filter( function( country ) {
					return country.iso2 === countryCode.toLowerCase();
				} );
				countryCode = countryData.length ? countryCode : '';
			}

			// Set default country.
			inputOptions.initialCountry = 'yes' === everest_forms_params.disable_user_details && countryCode ? countryCode : 'auto';

			$( '.evf-smart-phone-field' ).each( function( i, el ) {
				var $el = $( el );

				// Hidden input allows to include country code into submitted data.
				inputOptions.hiddenInput = $el.closest( '.evf-field-phone' ).data( 'field-id' );
				inputOptions.utilsScript = everest_forms_pro_params.plugin_url + 'assets/js/intlTelInput/utils.js';

				$el.intlTelInput( inputOptions );

				// Change name of the phone field.
				var field_name     = $el.attr( 'name' ),
					field_new_name = field_name + '[phone_field]';

				$el.attr( 'name', field_new_name );
				$el.blur( function() {
					if ( $el.intlTelInput( 'isValidNumber' ) ) {
						$el.siblings( 'input[type="hidden"]' ).val( $el.intlTelInput( 'getNumber' ) );
					}
				} );
			} );
		},
		loadCountryFlags: function() {
			// Only continue if SelectWoo library exists.
			if ( 'undefined' !== typeof $.fn.selectWoo ) {
				$( 'select.evf-country-flag-selector:visible' ).each( function() {
					var countryUrl   = "https://lipis.github.io/flag-icon-css/flags/4x3/",
						select2_args = $.extend({
							placeholder: $( this ).attr( 'placeholder' ) || '',
							templateResult: function( country ) {
								if ( ! country.id ) {
									return country.text;
								}
								return $( '<div class="iti__flag-box"><div class="iti__flag iti__' + country.id.toLowerCase() + '"></div></div><span class="iti__country-name">' + country.text + '</span>' );
							},
							templateSelection: function( country ) {
								if ( ! country.id ) {
									return country.text;
								}
								return $( '<div class="iti__flag-box"><div class="iti__flag iti__' + country.id.toLowerCase() + '"></div></div><span class="iti__country-name">' + country.text + '</span>' );
							}
						}, getEnhancedSelectFormatString() );

					$( this ).selectWoo( select2_args );
				});
			}
		},

		/**
		 * Get user browser preferred language.
		 *
		 * @since 1.2.9
		 *
		 * @returns {String} Language code.
		 */
		getFirstBrowserLanguage: function() {
			var nav = window.navigator,
				browserLanguagePropertyKeys = [ 'language', 'browserLanguage', 'systemLanguage', 'userLanguage' ],
				i,
				language;

			// Support for HTML 5.1 "navigator.languages".
			if ( Array.isArray( nav.languages ) ) {
				for ( i = 0; i < nav.languages.length; i++ ) {
					language = nav.languages[ i ];
					if ( language && language.length ) {
						return language;
					}
				}
			}

			// Support for other well known properties in browsers.
			for ( i = 0; i < browserLanguagePropertyKeys.length; i++ ) {
				language = nav[ browserLanguagePropertyKeys[ i ] ];
				if ( language && language.length ) {
					return language;
				}
			}

			return '';
		},

		/**
		 * Asynchronously fetches country code using current IP
		 * and executes a callback with the relevant country code.
		 *
		 * @since 1.2.9
		 *
		 * @param {Function} callback Executes once the fetch is completed.
		 */
		currentIpToCountry: function( callback ) {
			$.get( 'https://ipapi.co/json' ).always( function( resp ) {
				var countryCode = ( resp && resp.country ) ? resp.country : '';

				if ( ! countryCode ) {
					var lang = everest_forms_pro.getFirstBrowserLanguage();
					countryCode = lang.indexOf( '-' ) > -1 ? lang.split( '-' ).pop() : '';
				}

				callback( countryCode );
			} );
		},

		/**
		 * Element bindings.
		 */
		bindUIActions: function() {

			// Payments: Update Total field(s) when latest calculation.
			$( document ).on( 'change input', '.evf-payment-price', function() {
				everest_forms_pro.amountTotal(this, true);
			} );

			$( document.body ).on( 'conditional_show conditional_hide', function( e, fieldWrapper ) {
				var payment_field = $( fieldWrapper ).find( '.evf-payment-price' );

				if ( $( payment_field ).length ) {
					everest_forms_pro.amountTotal( payment_field, true );
				}
			} );

			// Payments: Restrict user input payment fields
			$( document ).on( 'input', '.evf-payment-user-input', function() {
				var $this  = $( this ),
					amount = $this.val();
				$this.val( amount.replace( /[^0-9.,]/g, '' ) );
			} );

			// Payments: Sanitize/format user input amounts
			$( document ).on( 'focusout', '.evf-payment-user-input', function() {
				var $this     = $(this),
					amount    = $this.val(),
					sanitized = everest_forms_pro.amountSanitize( amount ),
					formatted = everest_forms_pro.amountFormat( sanitized );
				$this.val( formatted );
			} );

			// Rating field: hover effect.
			$( '.everest-forms-field-rating' ).hover(
				function() {
					$( this ).parent().find( '.everest-forms-field-rating' ).removeClass( 'selected hover' );
					$( this ).prevAll().andSelf().addClass( 'hover' );
				},
				function() {
					$( this ).parent().find( '.everest-forms-field-rating' ).removeClass( 'selected hover' );
					$( this ).parent().find( 'input:checked' ).parent().prevAll().andSelf().addClass( 'selected' );
				}
			);

			// Rating field: toggle.
			$( document ).on( 'change', '.everest-forms-field-rating input', function() {

				var $this  = $( this ),
					$wrap  = $this.closest( '.everest-forms-field-rating-container' ),
					$items = $wrap.find( '.everest-forms-field-rating' );

				$items.removeClass( 'hover selected' );
				$this.parent().prevAll().andSelf().addClass( 'selected' );
			} );

			// Rating field: preselect the selected rating.
			$( document ).ready( function () {
				$( '.everest-forms-field-rating input:checked' ).change();
			} );

			$( document ).on( 'click', '.toggle-password', function() {
				var $this = $(this),
					input = $( $this.attr( 'toggle' ) );

				$this.toggleClass( 'dashicons-visibility' );

				if ( 'password' === input.attr( 'type' ) ) {
					input.attr( 'type', 'text' );
				} else {
					input.attr( 'type', 'password' );
				}
			});
		},

		/**
		 * Payments: Calculate total.
		 */
		amountTotal: function( el, validate ) {
			validate = validate || false;

			var $form                = $( el ).closest( '.everest-form' ),
				total                = 0,
				totalFormatted       = 0,
				totalFormattedSymbol = 0,
				currency             = everest_forms_pro.getCurrency();

			$form.find( '.evf-field .evf-payment-price:enabled, .evf-field .evf-single-item-price-hidden input[type="hidden"]:enabled' ).each( function( index, el ) {
				var $this = $(this),
				amount =0;

				if ( 'text' === $this.attr( 'type' ) || 'hidden' === $this.attr( 'type' ) ) {
					amount = $this.val();
				} else if ( ( 'radio' === $this.attr( 'type' ) || 'checkbox' === $this.attr( 'type' ) ) && $this.is( ':checked' ) ) {
					amount = $this.data('amount');
				} else if ( $this.is( 'select' ) && $this.find( 'option:selected' ).length > 0 ) {
					amount = $this.find( 'option:selected' ).data( 'amount' );
				}

				if ( ! everest_forms_pro.empty( amount ) ) {
					amount = everest_forms_pro.amountSanitize( amount );
					total  = Number( total ) + Number( amount );
				}
			});

			if ( $form.find( '.evf-payment-quantity' ).length ) {
				$form.find( '.evf-payment-quantity' ).each(function( index, el ) {
					if ( 0 < $( el).val() ) {
						form_id       = $form.data( 'formid' );
						map_field_id  = $(el).data('map_field');
						$mapped_field = $( '#evf-' + form_id + '-field_' + map_field_id );

						if ( ( $mapped_field.is( '.evf-payment-price' ) && $mapped_field.is( ':enabled' ) ) || $mapped_field.find( '.evf-payment-price' ).is( ':enabled' ) ) {
							var map_field_amount = $mapped_field.val().replace( ',', '.' ),
								quantity        = $( el ).val(),
								amount;

							if ( $mapped_field.closest( '.evf-field' ).is( '.evf-field-payment-multiple' ) ) {
								if ( $mapped_field.find( '.evf-payment-price:checked' ).length ) {
									amount = $mapped_field.find( '.evf-payment-price:checked' ).data( 'amount' );

									if ( ! isNaN( parseFloat( amount ) ) ) {
										map_field_amount = parseFloat( amount );
										total = total - map_field_amount;
										total += quantity * map_field_amount;
									}
								}
							} else if ( $mapped_field.closest( '.evf-field' ).is( '.evf-field-payment-checkbox' ) ) {
								$mapped_field.find( '.evf-payment-price:checked' ).each( function() {
									amount = $( this ).data( 'amount' );

									if ( ! isNaN( parseFloat( amount ) ) ) {
										map_field_amount = parseFloat( amount );
										total -= map_field_amount;
										total += quantity * map_field_amount;
									}
								} );
							} else {
								if ( ! isNaN( parseFloat( map_field_amount ) ) ) {
									map_field_amount = parseFloat( map_field_amount );
									total -= map_field_amount;
									total += quantity * map_field_amount;
								}
							}
						}
					}
				} );
			}

			totalFormatted = everest_forms_pro.amountFormat( total );

			if ( 'left' === currency.symbol_pos ) {
				totalFormattedSymbol = currency.symbol + ' ' + totalFormatted;
			} else {
				totalFormattedSymbol = totalFormatted + ' ' + currency.symbol;
			}

			$form.find( '.evf-payment-total' ).each( function() {
				if ( 'hidden' === $( this ).attr( 'type' ) || 'text' === $( this ).attr( 'type' ) ) {
					$( this ).val( totalFormattedSymbol );
					if ( 'text' === $( this ).attr( 'type' ) && validate && $form.data( 'validator' ) ) {
						$( this ).valid();
					}
				} else {
					$( this ).text( totalFormattedSymbol );
				}
			} );
		},

		/**
		 * Sanitize amount and convert to standard format for calculations.
		 */
		amountSanitize: function(amount) {
			var currency = everest_forms_pro.getCurrency();
				amount   = amount.toString().replace(/[^0-9.,]/g,'');

			if ( ',' === currency.decimal_sep && ( -1 !== amount.indexOf(currency.decimal_sep) ) ) {
				if ( '.' === currency.thousands_sep && -1 !== amount.indexOf(currency.thousands_sep) ) {
					amount = amount.replace(currency.thousands_sep,'');
				} else if( '' === currency.thousands_sep && -1 !== amount.indexOf('.') ) {
					amount = amount.replace('.','');
				}
				amount = amount.replace(currency.decimal_sep,'.');
			} else if ( ',' === currency.thousands_sep && ( -1 !== amount.indexOf(currency.thousands_sep) ) ) {
				amount = amount.replace(currency.thousands_sep,'');
			}

			return everest_forms_pro.numberFormat( amount, 2, '.', '' );
		},

		/**
		 * Format amount.
		 */
		amountFormat: function(amount) {
			var currency = everest_forms_pro.getCurrency();
			amount = String(amount);

			// Format the amount
			if ( ',' === currency.decimal_sep  && ( -1 !== amount.indexOf(currency.decimal_sep) ) ) {
				var sepFound = amount.indexOf(currency.decimal_sep),
					whole    = amount.substr(0, sepFound),
					part     = amount.substr(sepFound+1, amount.strlen-1);

				amount = whole + '.' + part;
			}

			// Strip comma(,) from the amount(if it is set as the thousands separator).
			if ( currency.thousands_sep === ',' && ( amount.indexOf(currency.thousands_sep) !== -1 ) ) {
				amount = amount.replace(',','');
			}

			if ( everest_forms_pro.empty( amount ) ) {
				amount = 0;
			}

			return everest_forms_pro.numberFormat( amount, 2, currency.decimal_sep, currency.thousands_sep );
		},

		/**
		 * Get site currency settings.
		 */
		getCurrency: function() {
			var currency = {
				code: 'USD',
				thousands_sep: ',',
				decimal_sep: '.',
				symbol: '$',
				symbol_pos: 'left'
			};

			// Backwards compatibility.
			if ( 'undefined' !== typeof evf_settings.currency_code ) {
				currency.code = evf_settings.currency_code;
			}

			if ( 'undefined' !== typeof evf_settings.currency_thousands ) {
				currency.thousands_sep = evf_settings.currency_thousands;
			}

			if ( 'undefined' !== typeof evf_settings.currency_decimal ) {
				currency.decimal_sep = evf_settings.currency_decimal;
			}

			if ( 'undefined' !== typeof evf_settings.currency_symbol ) {
				currency.symbol = evf_settings.currency_symbol;
			}

			if ( 'undefined' !== typeof evf_settings.currency_symbol_pos ) {
				currency.symbol_pos = evf_settings.currency_symbol_pos;
			}

			return currency;
		},

		/**
		 * Format number.
		 * @link http://locutus.io/php/number_format/
		 */
		numberFormat: function (number, decimals, decimalSep, thousandsSep) {
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n = !isFinite(+number) ? 0 : +number;
			var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
			var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
			var dec = (typeof decimalSep === 'undefined') ? '.' : decimalSep;
			var s;
			var toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);

				return '' + (Math.round(n * k) / k).toFixed(prec)
			};

			// @TODO: for IE parseFloat(0.55).toFixed(0) = 0;
			s = ( prec ? toFixedFix( n, prec ) : '' + Math.round(n) ).split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}

			if ((s[1] || '').length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1).join('0');
			}

			return s.join(dec);
		},

		/**
		 * Empty check similar to PHP.
		 *
		 * @link http://locutus.io/php/empty/
		 */
		empty: function(mixedVar) {
			var undef;
			var key;
			var i;
			var len;
			var emptyValues = [undef, null, false, 0, '', '0'];

			for (i = 0, len = emptyValues.length; i < len; i++) {
				if (mixedVar === emptyValues[i]) {
					return true
				}
			}

			if ( 'object' === typeof mixedVar ) {
				for ( key in mixedVar ) {
					if ( mixedVar.hasOwnProperty(key) ) {
						return false;
					}
				}

				return true;
			}

			return false;
		}
	};

	everest_forms_pro.init(jQuery);
});
