/**
 * EverestFormsProBuilder JS
 */
( function ( $, params, data ) {
	var EverestFormsProBuilder = {

		init: function() {
			EverestFormsProBuilder.bindUIActions();
			EverestFormsProBuilder.checkEnabledPayments();

			$(function() {
				EverestFormsProBuilder.initializeRangeSliderFields();

				$( document.body ).on( 'evf_field_drop_complete', function( e, field_type, dragged_field_id ) {

					// Process the new field data if it is a Range Slider field.
					if ( 'range-slider' === field_type ) {
						// Initialize the dropped field as an Range Slider field.
						EverestFormsProBuilder.initializeRangeSliderField( dragged_field_id );

						// Show slider input by default.
						$( '#everest-forms-field-option-' + dragged_field_id ).find( '.evf-show-slider-input' ).attr( 'checked', 'checked' );
						$( '#everest-forms-field-' + dragged_field_id ).find( '.evf-slider-input-wrapper' ).show();
					}
				});

				$( document.body ).on( 'evf_render_node_complete', function( e, field_type, new_key, clonedField, clonedOption ) {

					// Process the cloned data if it is a Range Slider field.
					if ( 'range-slider' === field_type ) {
						var html = '';

						clonedField.find( '.irs' ).remove();
						clonedField.find( '.evf-slider .evf-range-slider-preview' ).hide();

						// Purify Handle Color Picker.
						html = clonedOption.find( '.everest-forms-field-option-row-handle_color .wp-picker-input-wrap label' ).html();
						clonedOption.find( '.everest-forms-field-option-row-handle_color .wp-picker-container' ).remove();
						clonedOption.find( '.everest-forms-field-option-row-handle_color' ).append( html );

						// Purify Highlight Color Picker.
						html = clonedOption.find( '.everest-forms-field-option-row-highlight_color .wp-picker-input-wrap label' ).html();
						clonedOption.find( '.everest-forms-field-option-row-highlight_color .wp-picker-container' ).remove();
						clonedOption.find( '.everest-forms-field-option-row-highlight_color' ).append( html );

						// Purify Track Color Picker.
						html = clonedOption.find( '.everest-forms-field-option-row-track_color .wp-picker-input-wrap label' ).html();
						clonedOption.find( '.everest-forms-field-option-row-track_color .wp-picker-container' ).remove();
						clonedOption.find( '.everest-forms-field-option-row-track_color' ).append( html );

						EverestFormsProBuilder.initializeRangeSliderField( new_key );
					}
				});
			});
		},

		/**
		 * Initialize All Range Slider Fields.
		 *
		 * @since 1.3.3
		 */
		initializeRangeSliderFields: function() {
			// Min value change handler.
			$( document.body ).on( 'change', '.everest-forms-field-option .evf-range-slider-skin', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Min value change handler.
			$( document.body ).on( 'input', '.everest-forms-field-option .everest-forms-field-option-row-min_value .evf-input-number', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Max value change handler.
			$( document.body ).on( 'input', '.everest-forms-field-option .everest-forms-field-option-row-max_value .evf-input-number', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Min/Max values change handler (This is for the two column Min/Max option UI).
			$( document.body ).on( 'input', '.everest-forms-field-option .everest-forms-field-option-row-min_max_values .evf-input-number', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Show Grid option change handler.
			$( document.body ).on( 'change', '.everest-forms-field-option .evf-range-slider-show-grid', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Show slider prefix/postfix option change handler.
			$( document.body ).on( 'change', '.everest-forms-field-option .evf-show-slider-prefix-postfix', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Default value option change handler.
			$( document.body ).on( 'input', '.everest-forms-field-option .everest-forms-field-option-row-default_value input', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Use Text Prefix/Postfix option change handler (checkbox).
			$( document.body ).on( 'change', '.everest-forms-field-option .evf-use-text-prefix-postfix', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Text Prefix/Postfix option change handler (input).
			$( document.body ).on( 'input', '.everest-forms-field-option .evf-input-prefix-text, .everest-forms-field-option .evf-input-postfix-text', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Slider input visibility option change handler.
			$( document.body ).on( 'change', '.everest-forms-field-option .evf-show-slider-input', EverestFormsProBuilder.updateRangeSliderBasicOptions );

			// Initialize Range Slider Fields.
			$( '.everest-forms-field.everest-forms-field-range-slider' ).each( function( e ) {
				var field_id = $( this ).data( 'field-id' );
				EverestFormsProBuilder.initializeRangeSliderField( field_id );
			});
		},

		/**
		 * Initialize a Range Slider Field.
		 *
		 * @since 1.3.3
		 */
		initializeRangeSliderField: function( field_id ) {
			var $field = $( '#everest-forms-field-' + field_id );

			// Initialize the field as an IonRangeSlider field.
			$field.find( '.evf-range-slider-preview' ).ionRangeSlider();

			// Slider handle/highlight/track color change handler.
			EverestFormsProBuilder.initializeSliderHandleColorOption( field_id );
			EverestFormsProBuilder.initializeSliderHighlightColorOption( field_id );
			EverestFormsProBuilder.initializeSliderTrackColorOption( field_id );
			EverestFormsProBuilder.updateRangeSliderBasicOptions( field_id );
			EverestFormsProBuilder.updateRangeSliderColors( field_id );
		},

		/**
		 * Initialize Range Slider Handle Color Picker.
		 *
		 * @since 1.3.3
		 */
		initializeSliderHandleColorOption: function( field_id ) {
			var $field_options_container = $( '#everest-forms-field-option-' + field_id );

			// Initialize color picker for Handle Color option.
			$field_options_container.find( '.evf-range-slider-handle-color' )
			.wpColorPicker({
				change: function( event, ui ) {
					var new_color = $( event.target ).val(),
						field_id = $( this ).closest( '.everest-forms-field-option-row' ).data( 'field-id' ),
						current_skin = $field_options_container.find( '.evf-range-slider-skin' ).val();

					EverestFormsProBuilder.setSliderHandleColor( field_id, new_color, current_skin );
				}
			});
		},

		/**
		 * Initialize Range Slider Highlight Color Picker.
		 *
		 * @since 1.3.3
		 */
		initializeSliderHighlightColorOption: function( field_id ) {
			var $field = $( '#everest-forms-field-' + field_id ),
				$field_options_container = $( '#everest-forms-field-option-' + field_id );

			$field_options_container.find( '.evf-range-slider-highlight-color' )
			.wpColorPicker({
				change: function( event, ui ) {
					var new_color = $( event.target ).val();

					if ( new_color ) {
						$field.find( '.irs-bar' ).css( 'background', new_color );
					}
				}
			});
		},

		/**
		 * Initialize Range Slider Track Color Picker.
		 *
		 * @since 1.3.3
		 */
		initializeSliderTrackColorOption: function( field_id ) {
			var $field = $( '#everest-forms-field-' + field_id ),
				$field_options_container = $( '#everest-forms-field-option-' + field_id );

			$field_options_container.find( '.evf-range-slider-track-color' )
			.wpColorPicker({
				change: function( event, ui ) {
					var new_color = $( event.target ).val();

					if ( new_color ) {
						$field.find( '.irs-line' ).css( 'background', new_color );
					}
				}
			});
		},

		/**
		 * Update a Range Slider field's Handle/Highlight/Track colors.
		 *
		 * @since 1.3.3
		 */
		updateRangeSliderColors: function ( field_id ) {
			var $field = $( '#everest-forms-field-' + field_id ),
				$field_options_container = $( '#everest-forms-field-option-' + field_id ),
				skin = $field_options_container.find( '.evf-range-slider-skin' ).val(),
				handle_color = $field_options_container.find( '.evf-range-slider-handle-color' ).val(),
				highlight_color = $field_options_container.find( '.evf-range-slider-highlight-color' ).val(),
				track_color = $field_options_container.find( '.evf-range-slider-track-color' ).val();

			// Set handle color for the Slider field.
			EverestFormsProBuilder.setSliderHandleColor( field_id, handle_color, skin );

			// Set Current Highlight Color.
			$field.find( '.irs-bar' ).css( 'background', highlight_color );

			// Set Current Track Color.
			$field.find( '.irs-line' ).css( 'background', track_color );
		},

		/**
		 * Set a Range Slider's handle color.
		 *
		 * @since 1.3.3
		 */
		setSliderHandleColor: function ( field_id, color, skin ) {
			if ( '' !== field_id ) {
				var $field = $( '#everest-forms-field-' + field_id ),
					style = '';

				switch ( skin ) {
					case 'flat':
						$field.find( '.irs-handle i' ).first().css( 'background-color', color );
						$field.find( '.irs-single' ).css( 'background-color', color );
						style = '#everest-forms-field-' + field_id +' .irs-single:before { border-top-color: ' + color + '!important; }';
						break;

					case 'big':
						$field.find( '.irs-single' ).css( 'background-color', color );
						$field.find( '.irs-single' ).css( 'background', color );
						$field.find( '.irs-handle' ).css( 'background-color', color );
						$field.find( '.irs-handle' ).css( 'background', color );
						break;

					case 'modern':
						$field.find( '.irs-handle i' ).css( 'background', color );
						$field.find( '.irs-single' ).css( 'background-color', color );
						style = '#everest-forms-field-' + field_id +' .irs-single:before { border-top-color: ' + color + '!important; }';
						break;

					case 'sharp':
						$field.find( '.irs-handle' ).css( 'background-color', color );
						$field.find( '.irs-handle i' ).first().css( 'border-top-color', color );
						$field.find( '.irs-single' ).css( 'background-color', color );
						style = '#everest-forms-field-' + field_id +' .irs-single:before { border-top-color: ' + color + '!important; }';
						break;

					case 'round':
						$field.find( '.irs-handle' ).css( 'border-color', color );
						$field.find( '.irs-single' ).css( 'background-color', color );
						style = '#everest-forms-field-' + field_id +' .irs-single:before { border-top-color: ' + color + '!important; }';
						break;

					case 'square':
						$field.find( '.irs-handle' ).css( 'border-color', color );
						$field.find( '.irs-single' ).css( 'background-color', color );
						style = '#everest-forms-field-' + field_id +' .irs-single:before { border-top-color: ' + color + '!important; }';
						break;
				}

				$( 'body' ).find( '.evf-range-slider-handle-style-tag-' + field_id ).remove();
				$( 'body' ).append( '<style class="evf-range-slider-handle-style-tag-' + field_id + '" >' + style + '</style>' );
			}
		},

		/**
		 * Update a Range Slider field's basic options like min/max value, default value, step, show/hide prefix/postfix, skin, grid etc.
		 * The options like handle color, highlight color and track color are not handled by this function as it needs a unique approach.
		 *
		 * @since 1.3.3
		 */
		updateRangeSliderBasicOptions: function( field_id ) {
			var field_id = ( 'string' === typeof field_id ) ? field_id : $( '.everest-forms-field-option:visible' ).data( 'field-id' ),
				$field = $( '#everest-forms-field-' + field_id ),
				$field_option_section = $( '#everest-forms-field-option-' + field_id ),
				min_value = parseFloat( $( '#everest-forms-field-option-' + field_id + '-min_value' ).val() ),
				max_value = parseFloat( $( '#everest-forms-field-option-' + field_id + '-max_value' ).val() ),
				new_skin = $( '#everest-forms-field-option-' + field_id + '-skin' ).val(),
				default_value = parseFloat( $( '#everest-forms-field-option-' + field_id + '-default_value' ).val() ),
				show_grid_option = $( '#everest-forms-field-option-' + field_id + '-show_grid' ).is( ':checked' ),
				show_prefix_postfix_option = $( '#everest-forms-field-option-' + field_id + '-show_prefix_postfix' ).is( ':checked' ),
				is_text_prefix_postfix_enabled = $( '#everest-forms-field-option-' + field_id + '-use_text_prefix_postfix' ).is( ':checked' ),
				show_slider_input = $( '#everest-forms-field-option-' + field_id + '-show_slider_input' ).is( ':checked' ),
				slider_options = {};

			if ( max_value > min_value ) {
				if ( '' !== min_value ) {
					slider_options.min = min_value;
				}
				if ( '' !== max_value ) {
					slider_options.max = max_value;
				}
			} else {
				$( '#everest-forms-field-option-' + field_id + '-max_value' ).val( min_value + 1 );
			}
			$( '#everest-forms-field-option-' + field_id + '-max_value' ).attr( 'min', min_value + 1 );

			if ( '' !== new_skin ) {
				slider_options.skin = new_skin;
			} else {
				slider_options.skin = 'round';
			}
			if ( '' !== new_skin ) {
				slider_options.skin = new_skin;
			}
			if ( show_grid_option ) {
				slider_options.grid = true;
			} else {
				slider_options.grid = false;
			}
			if ( show_prefix_postfix_option ) {
				slider_options.hide_min_max = false;
				$( '#everest-forms-field-option-row-' + field_id + '-use_text_prefix_postfix' ).show();
				$( '#everest-forms-field-option-' + field_id ).find( '.evf-prefix-postfix-warning-message' ).show();
			} else {
				slider_options.hide_min_max = true;
				$( '#everest-forms-field-option-row-' + field_id + '-use_text_prefix_postfix' ).hide();
				$( '#everest-forms-field-option-' + field_id ).find( '.evf-prefix-postfix-warning-message' ).hide();
			}
			if ( '' !== default_value ) {
				slider_options.from = default_value;
			}

			// Slider input visibility update.
			if ( show_slider_input ) {
				$field.find( '.evf-slider-input-wrapper' ).show();
			} else {
				$field.find( '.evf-slider-input-wrapper' ).hide();
			}

			// Update the slider field with the specified options.
			$field.find( 'input.evf-range-slider-preview' ).data( 'ionRangeSlider' ).update( slider_options );
			$field.find( '.evf-slider-input' ).val( default_value );

			// Set prefix/postfix texts.
			if ( show_prefix_postfix_option && is_text_prefix_postfix_enabled ) {
				var prefix_text = $field_option_section.find( '.evf-input-prefix-text' ).val(),
					postfix_text = $field_option_section.find( '.evf-input-postfix-text' ).val();

				// Update Use Text Prefix/Postfix option.
				$field.find( 'span.irs-min' ).html( prefix_text );
				$field.find( 'span.irs-max' ).html( postfix_text );
				$field_option_section.find( '.evf-range-slider-prefix-postfix-texts' ).show();
			} else {
				// Update Use Text Prefix/Postfix option.
				$field_option_section.find( '.evf-range-slider-prefix-postfix-texts' ).hide();
			}

			// Show/Hide prefix/postfix warning message.
			if ( ! show_prefix_postfix_option || ( show_prefix_postfix_option && is_text_prefix_postfix_enabled ) ) {
				$field_option_section.find( '.evf-prefix-postfix-warning-message' ).hide();
			} else {
				$field_option_section.find( '.evf-prefix-postfix-warning-message' ).show();
			}

			// Update Range Slider Colors.
			EverestFormsProBuilder.updateRangeSliderColors( field_id );
		},

		/**
		 * Element bindings
		 */
		bindUIActions: function() {
			$builder = $( '#everest-forms-builder' );

			// Real-time updates for Password Strength Meter option.
			$builder.on( 'change', '.everest-forms-field-option-row-password_strength input', function(e) {
				$( this ).parent().find( '.everest-forms-inner-options' ).toggleClass( 'everest-forms-visible everest-forms-hidden' );
			});

			// Rating point validation error tips.
			$( document.body )

				.on( 'blur', '.evf-number-of-stars[type=number]', function() {
					$( '.evf_error_tip' ).fadeOut( '100', function() { $( this ).remove(); } );
				})

				.on( 'change click', '.evf-number-of-stars[type=number]', function(e) {
					var number_of_stars = parseInt( $( this ).val(), 10 );

					if ( number_of_stars > 100 ) {
						$( this ).val('100');
						EverestFormsProBuilder.livePreviewNumberOfRating( $( this) );
					}
				})

				.on( 'keyup click', '.evf-number-of-stars[type=number]', function() {
					var number_of_stars = parseInt( $( this ).val(), 10 );

					if ( number_of_stars > 100 ) {
						$( document.body ).triggerHandler( 'evf_add_error_tip', [ $( this ), 'i18n_field_rating_greater_than_max_value_error', params ] );
					} else {
						$( document.body ).triggerHandler( 'evf_remove_error_tip', [ $( this ), 'i18n_field_rating_greater_than_max_value_error' ] );
					}
				});

			// Live effect for Rating field Number of Stars option.
			$( document ).on( 'keyup mouseup', '.everest-forms-field-option-row-number_of_stars input', function() {
				EverestFormsProBuilder.livePreviewNumberOfRating( this );
			});

			$( document ).on( 'input', '.everest-forms-field-map-table .key-source', function() {
				EverestFormsProBuilder.updateUserMetakey( this );
			});

			$( document ).on( 'click', '.everest-forms-addable-list .add', function() {
				EverestFormsProBuilder.userMetaAdd( this );
			});

			$( document ).on( 'click', '.everest-forms-addable-list .remove', function() {
				EverestFormsProBuilder.userMetaRemove( this );
			});

			// Live effect for Rating field icon option.
			$( document ).on( 'change', '.everest-forms-field-option-row-rating-icon input[type=radio]', function() {

				var $this      = $( this ),
					value      = $this.val(),
					id         = $this.parent().data( 'field-id' ),
					icon_color = $( '#everest-forms-field-'+id +' .rating-icon' ).find('svg').first().css('fill');
					$icons     = $( '#everest-forms-field-'+id +' .rating-icon' ),
					iconClass  = '<svg width="32" height="32" viewBox="0 0 32 32" style="fill:' + icon_color + '"><path d="M20.33 11.45L16 2.69l-4.33 8.76L2 12.86l7 6.82-1.65 9.64L16 24.77l8.65 4.55L23 19.68l7-6.82-9.67-1.41z"/></svg>';
					if ( 'heart' === value ) {
						iconClass = '<svg width="32" height="32" viewBox="0 0 32 32" style="fill:' + icon_color + '"><path d="M27.66 16.94L16 28 4.34 16.94a7.31 7.31 0 0 1 0-10.72A8.21 8.21 0 0 1 10 4a6.5 6.5 0 0 1 5 2l1 1s.88-.89 1-1a6.5 6.5 0 0 1 5-2 8.21 8.21 0 0 1 5.66 2.22 7.31 7.31 0 0 1 0 10.72z"/></svg>';
					} else if ( 'thumb' === value ) {
						iconClass = '<svg width="32" height="32" viewBox="0 0 32 32" style="fill:' + icon_color + '"><path d="M30 14.88a3.42 3.42 0 0 0-3.36-3.36h-4.85l.14-.42a2.42 2.42 0 0 1 .2-.39c.08-.14.14-.24.17-.31.21-.4.37-.72.48-1a7.39 7.39 0 0 0 .33-1.05A5.71 5.71 0 0 0 23 4a3.48 3.48 0 0 0-3-2 1.61 1.61 0 0 0-1.43.89C18.34 3.13 17 7 17 7a5.44 5.44 0 0 1-1 2c-.57.75-2.6 3-3.2 3.71s-1.05 1-1.33 1C10 13.74 10 15.71 10 16v9c0 .3 0 2.2 1.52 2.2a12.7 12.7 0 0 1 2.76.77A15.6 15.6 0 0 0 21 30a8.9 8.9 0 0 0 5.74-1.92C30 25 30 15.88 30 14.88zM5 14a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0v-7a3 3 0 0 0-3-3zm0 11a1 1 0 1 1 1-1 1 1 0 0 1-1 1z"/></svg>';
					} else if ( 'smiley' === value ) {
						iconClass = '<svg width="32" height="32" viewBox="0 0 32 32" style="fill:' + icon_color + '"><path d="M16 2a14 14 0 1 0 14 14A14 14 0 0 0 16 2zm4 8a2 2 0 1 1-2 2 2 2 0 0 1 2-2zm-8 0a2 2 0 1 1-2 2 2 2 0 0 1 2-2zm4 14a9.23 9.23 0 0 1-8.16-4.89l1.32-.71a7.76 7.76 0 0 0 13.68 0l1.32.71A9.23 9.23 0 0 1 16 24z"/></svg>';
					} else if ( 'bulb' === value ) {
						iconClass = '<svg width="32" height="32" viewBox="0 0 32 32" style="fill:' + icon_color + '"><path d="M16 2.25A9.76 9.76 0 0 0 6.25 12c0 3.21 2 5.68 3.52 7.48A6.28 6.28 0 0 1 11.25 23a.76.76 0 0 0 .75.75h8a.74.74 0 0 0 .74-.64 10 10 0 0 1 1.53-3.69c.24-.35.49-.7.75-1.06 1.28-1.77 2.73-3.79 2.73-6.36A9.76 9.76 0 0 0 16 2.25zM20 25.25h-8a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5zM19 28.25h-6a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5z"/></svg>';
					}

				$icons.html( iconClass );
			});

			// Live effect for Rating field icon color option.
			$( '.everest-forms-field-option-row-icon_color input.colorpicker' ).wpColorPicker({
				change: function( event ) {
					var $this     = $( this ),
						value     = $this.val(),
						id        = $this.closest( '.everest-forms-field-option-row' ).data( 'field-id' ),
						$icons    = $( '#everest-forms-field-'+id +' .rating-icon svg' );

					$icons.css( 'fill', value );
				}
			});

			$( document ).on( 'change', '.everest-forms-field-option-row-show_hide_password input', function() {
				var id = $( this ).parent().data( 'field-id' );

				if ( this.checked ) {
					$( '#everest-forms-field-' + id ).find( '.toggle-password' ).removeClass( 'everest-forms-hidden' );
				} else {
					$( '#everest-forms-field-' + id ).find( '.toggle-password' ).addClass( 'everest-forms-hidden' );
				}
			} );

			// Enable disable payments.
			$builder.on( 'change', '#everest-forms-panel-field-paymentsstripe-enable_stripe', function() {
				EverestFormsProBuilder.checkEnabledPayments();
			} );

			// Real-time updates for Single Item field "Item Price" option
			$builder.on( 'input', '.everest-forms-field-option-row-item_price input', function(e) {
				var $this      = $( this ),
					value      = $this.val(),
					id         = $this.parent().data( 'field-id' ),
					sanitized  = EverestFormsProBuilder.amountSanitize( value ),
					formatted  = EverestFormsProBuilder.amountFormat( sanitized ),
					singleItem;

				if ( 'right' === data.currency_symbol_pos ) {
					singleItem = formatted + ' ' + data.currency_symbol;
				} else {
					singleItem = data.currency_symbol + ' ' + formatted;
				}

				$( '#everest-forms-field-' + id ).find( '.widefat' ).val( formatted );
				$( '#everest-forms-field-' + id ).find( '.price' ).text( singleItem );
			});

			// Restrict user money input fields.
			$builder.on( 'input', '.evf-money-input', function(event) {
				var $this = $( this ),
					amount = $this.val(),
					start  = $this[0].selectionStart,
					end    = $this[0].selectionEnd;
				$this.val( amount.replace( /[^0-9.,]/g, '' ) );
				$this[0].setSelectionRange(start,end);
			});

			// Format user money input fields.
			$builder.on( 'focusout', '.evf-money-input', function(event) {
				var $this     = $(this),
					amount    = $this.val(),
					sanitized = EverestFormsProBuilder.amountSanitize( amount ),
					formatted = EverestFormsProBuilder.amountFormat( sanitized );
				$this.val( formatted );
			});
		},

		/**
		 * Sanitize amount and convert to standard format for calculations.
		 *
		 * @since 1.3.0
		 */
		amountSanitize: function(amount) {
			amount = amount.replace( /[^0-9.,]/g, '' );

			if ( ',' === data.currency_decimal && ( -1 !== amount.indexOf( data.currency_decimal ) ) ) {
				if ( '.' === data.currency_thousands && -1 !== amount.indexOf( data.currency_thousands ) ) {;
					amount = amount.replace( data.currency_thousands, '' );
				} else if ( '' === data.currency_thousands && -1 !== amount.indexOf( '.' ) ) {
					amount = amount.replace( '.', '' );
				}
				amount = amount.replace( data.currency_decimal, '.' );
			} else if ( ',' === data.currency_thousands && ( -1 !== amount.indexOf( data.currency_thousands ) ) ) {
				amount = amount.replace( data.currency_thousands, '' );
			}

			return EverestFormsProBuilder.numberFormat( amount, 2, '.', '' );
		},

		/**
		 * Format amount.
		 *
		 * @since 1.3.0
		 */
		amountFormat: function( amount ) {
			amount = String( amount );

			// Format the amount.
			if ( ',' === data.currency_decimal && ( -1 !== amount.indexOf( data.currency_decimal ) ) ) {
				var sepFound = amount.indexOf( data.currency_decimal );
					whole    = amount.substr( 0, sepFound );
					part     = amount.substr( sepFound+1, amount.strlen - 1 );
					amount   = whole + '.' + part;
			}

			// Strip ',' from the amount (if set as the thousands separator).
			if ( ',' === data.currency_thousands && ( -1 !== amount.indexOf( data.currency_thousands ) ) ) {
				amount = amount.replace( ',', '' );
			}

			if ( ! amount ) {
				amount = 0;
			}

			return EverestFormsProBuilder.numberFormat( amount, 2, data.currency_decimal, data.currency_thousands );
		},

		/**
		 * Format number.
		 *
		 * @link http://locutus.io/php/number_format/
		 * @since 1.3.0
		 */
		numberFormat: function ( number, decimals, decimalSep, thousandsSep ) {
			number   = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n    = ! isFinite( +number ) ? 0 : +number;
			var prec = ! isFinite( +decimals ) ? 0 : Math.abs(decimals);
			var sep  = ( 'undefined' === typeof thousandsSep ) ? ',' : thousandsSep;
			var dec  = ( 'undefined' === typeof decimalSep ) ? '.' : decimalSep;
			var s    = '';

			var toFixedFix = function ( n, prec ) {
				var k = Math.pow( 10, prec );
				return '' + ( Math.round(n * k) / k ).toFixed( prec )
			};

			// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
			s = ( prec ? toFixedFix( n, prec ) : '' + Math.round(n) ).split( '.' );
			if ( s[0].length > 3 ) {
				s[0] = s[0].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep )
			}
			if ( (s[1] || '' ).length < prec ) {
				s[1]  = s[1] || '';
				s[1] += new Array( prec - s[1].length + 1 ).join( '0' );
			}

			return s.join( dec )
		},

		livePreviewNumberOfRating : function( el ) {
			var $this  = $( el ),
			value  = $this.val();
			var id    = $this.parent().data( 'field-id' ),
				icons = $( '#everest-forms-field-'+id +' .rating-icon' ).first();
			if ( value <= 100 ) {
				$( '#everest-forms-field-'+id +' .rating-icon' ).remove();
				for ( var $i = 1; $i <= value; $i++ ) {
					$( '#everest-forms-field-'+id +'').append( icons.clone() );
				}
			}
		},

		// Field user meta map table, update user meta key source
		updateUserMetakey : function( el ) {
			var $this = $( el );
				value = $this.val(),
				$destination = $this.parent().parent().find('.key-destination'),
				name  = $destination.data('name');

			if ( value ) {
				$destination.attr('name', name.replace('{source}', value.replace(/[^0-9a-zA-Z_-]/gi, '')));
			}
		},

		userMetaAdd : function( el ) {
			var $this   = $(el),
			$row    = $this.closest('li'),
			li_length = $this.closest('ul').find('li').length,
			choice  = $row.clone().insertAfter($row);

			choice.find('input').val('');
			choice.find('select :selected').prop('selected', false);
			choice.find('.key-destination').attr('name','');
			if ( 'undefined' !== typeof $this.closest('ul').attr('data-tax') ) {
				var tax = $this.closest('ul').attr('data-tax'),
				next_id = li_length+1;
				choice.find('.key').find('.everest-forms-'+tax+'-map-select').attr('name','settings[post_tax_'+tax+']['+next_id+']');
				choice.find('.field').find('.everest-forms-'+tax+'-field-map-options').attr('name','settings[post_tax_'+tax+'_value]['+next_id+']');
			}
		},

		userMetaRemove: function( el ) {
			var $this = $(el),
				$row = $this.closest('li'),
				$ul = $this.closest('ul'),
				total = $ul.find('li').length;

			if ( total > '1' ) {
				$row.remove();
			}
		},

		/**
		 * Check for enabled payment on payment section
		 *
		 * @since 1.3.0
		 */
		checkEnabledPayments: function() {
			if ( $( '#everest-forms-panel-field-paymentsstripe-enable_stripe' ).prop( 'checked' ) ) {
				$( '#everest-forms-add-fields-credit-card' ).removeClass( 'enable-stripe-model' );
			} else {
				$( '#everest-forms-add-fields-credit-card' ).addClass( 'enable-stripe-model' );
			}
		},
	}
	EverestFormsProBuilder.init(jQuery);
})( jQuery, everest_forms_builder, evf_data );
