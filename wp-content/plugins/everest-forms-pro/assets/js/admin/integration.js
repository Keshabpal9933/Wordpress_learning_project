/**
 * EverestFormsIntegration JS
 * global evfp_params
 */
 ;(function($) {
 	var s;
 	var EverestFormsIntegration = {

 		settings: {
 			form   : $('#everest-forms-builder-form'),
 			spinner: '<i class="evf-loading evf-loading-active" />'
 		},
		/**
		 * Start the engine.
		 *
		 */
		 init: function() {
		 	s = this.settings;
			// Document ready
			$(document).ready(EverestFormsIntegration.ready);
			if( $('.evf-connection-list-table tbody tr').length === 0 ){
				$('.toggle-switch').removeClass('connected');
			}

			$('.everest-forms-active-connections-list li').first().addClass('active-user');
			$('.evf-provider-connections div').first().addClass('active-connection');

			EverestFormsIntegration.bindUIActions();

			var conditional_cb = $( '.evf-provider-conditional input' );
			conditional_cb.each(function(index, el) {
				if( $(this).is(':checked') ){
					EverestFormsIntegration.getAllAvailableFields($(this).closest('.evf-provider-conditional'));
					var conditional_content = $(this).parent( 'p' ).siblings('.evf-conditional-container');
					conditional_content.fadeIn('slow');
					conditional_content.find('.evf-conditional-field-select').trigger('change');
				}
			});
		},
		ready: function() {

			s.formID = $('#everest-forms-builder-form').data('id');
		},

		/**
		 * Element bindings.
		 *
		 */
		 bindUIActions: function() {
		 	$(document).on('click', '.everest-forms-integration-connect-account', function(e) {
		 		EverestFormsIntegration.Connect(this,e);
		 	});
		 	$(document).on('click', '.everest-forms-integration-disconnect-account', function(e) {
		 		EverestFormsIntegration.disconnect(this,e);
		 	});
		 	$(document).on('click', '.everest-forms-connections-add', function(e) {
		 		EverestFormsIntegration.connectionAdd(this, e);
		 	});
		 	$(document).on('click', '.everest-forms-source-account-add button', function(e) {
		 		EverestFormsIntegration.accountAdd(this, e);
		 	});
		 	$(document).on('change', '.evf-provider-accounts select', function(e) {
		 		EverestFormsIntegration.accountSelect(this, e);
		 	});
		 	$(document).on('change', '.evf-provider-lists select', function(e) {
		 		EverestFormsIntegration.accountListSelect(this, e);
		 	});
		 	$(document).on('click', '.everest-forms-active-connections-list li a', function(e) {
		 		EverestFormsIntegration.selectActiveAccount(this, e);
		 	});
		 	$(document).on('click', '.toggle-remove', function(e) {
		 		EverestFormsIntegration.removeAccount(this, e);
		 	});
		 	$(document).on('change', '.evf-provider-conditional .evf-enable-conditional-logic', function(e) {
		 		EverestFormsIntegration.enableConditionLogic(this ,e);
		 	});
		 	$(document).on('change', '.evf-conditional-field-select', function(e) {
		 		EverestFormsIntegration.inputType(this ,e);
		 	});
		 },

		/**
		 * Connect integration account.
		 *
		 */
		 Connect: function(el, e) {
		 	e.preventDefault();
		 	var $this   = $( el ),
		 	apikey      = $('.evf-apikey').val(),
		 	label       = $('.evf-nickname').val(),
		 	$parent     = $this.closest( '.integration-connection-detail' ),
		 	data        = {
		 		action   : 'everest_forms_integration_connect',
		 		apikey   : apikey,
		 		label    : label,
		 		source   : $this.data( 'source' ),
		 		security : evfp_params.ajax_nonce
		 	};

		 	EverestFormsIntegration.inputToggle($this, 'disable');

		 	$.ajax({
		 		url: evfp_params.ajax_url,
		 		data: data,
		 		type: 'POST',

		 		success: function( response ) {
		 			if(response.success === true){
		 				EverestFormsIntegration.inputToggle($this, 'enable');
		 				$('.evf-connection-form :input').val('');
		 				$parent.find( '.evf-connection-list tbody' ).append( response.data.html );
		 				$parent.find('.integration-status').addClass( 'connected' );
		 			}else{
		 				EverestFormsIntegration.inputToggle($this, 'enable');
						var msg = evfp_params.provider_auth_error;
						if ( response.data.error_msg ) {
							msg += "\n" + response.data.error_msg; // jshint ignore:line
						}
						$.alert({
							title: false,
							content: msg,
							icon: 'dashicons dashicons-info',
							type: 'orange',
							buttons: {
								confirm: {
									text:  evfp_params.i18n_ok,
									btnClass: 'btn-confirm',
									keys: [ 'enter' ]
								}
							}
						});
					}
		 		}
		 	});

		 },
		/**
		 * Disconnect integration account.
		 *
		 */
		 disconnect: function(el, e) {
		 	e.preventDefault();
		 	var $this   = $( el ),
		 	apikey      = $('.evf-apikey').val(),
		 	label       = $('.evf-nickname').val(),
		 	$parent     = $this.closest( '.integration-connection-detail' ),
		 	data        = {
		 		action   : 'everest_forms_integration_disconnect',
		 		key      : $this.data( 'key' ),
		 		source   : $this.data( 'source' ),
		 		security : evfp_params.ajax_nonce
		 	};

		 	$.confirm({
		 		title: false,
		 		content: 'Are you sure you want to delete this connection?',
		 		backgroundDismiss: false,
		 		closeIcon: false,
		 		icon: 'dashicons dashicons-info',
		 		type: 'orange',
		 		buttons: {
		 			confirm: {
		 				text: evfp_params.i18n_ok,
		 				btnClass: 'btn-confirm',
		 				keys: [ 'enter' ],
		 				action: function(){
		 					$.post( evfp_params.ajax_url, data, function( res ) {
		 						if ( res.success ){
		 							$this.parent().parent().remove();
		 						} else {
		 							console.log( res );
		 						}
		 					}).fail( function( xhr ) {
		 						console.log( xhr.responseText );
		 					});
		 				}
		 			},
		 			cancel: {
		 				text: evfp_params.i18n_cancel,
		 				keys: [ 'esc' ]
		 			}
		 		}
		 	});
		 },

		 connectionAdd: function(el, e) {
		 	e.preventDefault();

		 	var $this        = $(el),
		 	source       = $this.data('source'),
		 	$connections = $this.closest('.everest-forms-panel-sidebar-content'),
		 	$container   = $this.parent(),
		 	type         = $this.data('type'),
		 	namePrompt   = evfp_params.i18n_prompt_connection,
		 	nameField    = '<input autofocus="" type="text" id="provider-connection-name" placeholder="'+evfp_params.i18n_prompt_placeholder+'">',
		 	nameError    = '<p class="error">'+evfp_params.i18n_error_name+'</p>',
		 	modalContent = namePrompt+nameField+nameError;

		 	modalContent = modalContent.replace(/%type%/g,type);
		 	$.confirm({
		 		title: false,
		 		content: modalContent,
		 		icon: 'dashicons dashicons-info',
		 		type: 'blue',
		 		backgroundDismiss: false,
		 		closeIcon: false,
		 		buttons: {
		 			confirm: {
		 				text: evfp_params.i18n_ok,
		 				btnClass: 'btn-confirm',
		 				keys: ['enter'],
		 				action: function() {
		 					var input = this.$content.find('input#provider-connection-name');
		 					var error = this.$content.find('.error');
		 					if (input.val() === '') {
		 						error.show();
		 						return false;
		 					} else {
		 						var name = input.val();

								// Disable button
								EverestFormsIntegration.inputToggle($this, 'disable');

								// Fire AJAX
								var data =  {
									action  : 'everest_forms_new_connection_add_'+source,
									source  : source,
									name    : name,
									id      : s.form.data('id'),
									security: evfp_params.ajax_nonce
								}
								$.ajax({
									url: evfp_params.ajax_url,
									data: data,
									type: 'POST',

									success: function( response ){
										EverestFormsIntegration.inputToggle($this, 'enable');
										$('.everest-form-add-connection-notice').remove();
										$connections.find('.evf-panel-content-section-'+source).find('.evf-provider-connections').append( response.data.html );
										$connections.find('.evf-provider-connection').removeClass('active-connection');
										$connections.find('.evf-provider-connection').last().addClass('active-connection');
										$this.parent().find('.everest-forms-active-connections-list li').removeClass('active-user');
										$this.closest('.everest-forms-active-connections.active').children('.everest-forms-active-connections-list').removeClass('empty-list');
										$this.parent().find('.everest-forms-active-connections-list').append( '<li class="active-user" data-connection-id= "'+response.data.connection_id+'"><a class="user-nickname" href="#">'+name+'</a><a href="#"><span class="toggle-remove">Remove</span></a></li>' );
										$('.everest-forms-panel-sidebar-section-'+ source ).siblings('.everest-forms-active-connections.active').children('.everest-forms-active-connections-list').children('.active-user').children('.user-nickname').trigger('click');
										var $connection = $connections.find('.evf-panel-content-section-'+source+ ' .evf-provider-connections .evf-provider-connection:last');
										if ($connection.find( '.evf-provider-accounts option:selected')) {
											$connection.find( '.evf-provider-accounts option:first').prop('selected', true);
											$connection.find('.evf-provider-accounts select').trigger('change');
										}
									}
								});
							}
						}
					},
					cancel: {
						text: evfp_params.i18n_cancel
					}
				}
			});
		 },


		/**
		 * Add and authorize Integration account.
		 *
		 */
		 accountAdd: function(el, e) {
		 	e.preventDefault();

		 	var $this       = $(el),
		 	source    = $this.data('source'),
		 	$connection = $this.closest('.evf-provider-connection'),
		 	$container  = $this.parent(),
		 	$fields     = $container.find(':input'),
		 	errors      = EverestFormsIntegration.requiredCheck($fields, $container);
			// Disable button
			EverestFormsIntegration.inputToggle($this, 'disable');

			// Bail if we have any errors
			if (errors) {
				$this.prop('disabled', false).find('i').remove();
				return false;
			}

			// Fire AJAX
			data = {
				action       : 'everest_forms_add_account_form_'+source,
				source       : source,
				connection_id: $connection.data('connection_id'),
				data         : EverestFormsIntegration.fakeSerialize($fields),
				security     : evfp_params.ajax_nonce
			}

			$.ajax({
				url: evfp_params.ajax_url,
				data: data,
				type: 'POST',

				success: function( response ) {
					if( response.success === true){
						$container.nextAll('.evf-connection-block').remove();
						$container.nextAll('.evf-conditional-block').remove();
						$container.after(response.data.html);
						$container.slideUp();
						$connection.find('.evf-provider-accounts select').trigger('change');
					}else{
						EverestFormsIntegration.inputToggle($this, 'enable');
						EverestFormsIntegration.errorDisplay(response.data.error, $container);
					}
				}
			});
		},


		/**
		 * Selecting a provider account
		 *
		 */
		 accountSelect: function(el, e) {
		 	e.preventDefault();

		 	var $this       = $(el),
		 	$connection = $this.closest('.evf-provider-connection'),
		 	$container  = $this.parent(),
		 	source      = $connection.data('provider');

			// Disable select, show loading
			EverestFormsIntegration.inputToggle($this, 'disable');

			// Remove any blocks that might exist as we prep for new account
			$container.nextAll('.evf-connection-block').remove();
			$container.nextAll('.evf-conditional-block').remove();

			if (!$this.val()) {
				// User selected to option to add new account
				$connection.find('.everest-forms-source-account-add input').val('');
				$('.everest-form-add-connection-notice').remove();
				$connection.find('.everest-forms-source-account-add').slideDown();
				EverestFormsIntegration.inputToggle($this, 'enable');

			} else {

				$connection.find('.everest-forms-source-account-add').slideUp();

				// Fire AJAX
				data = {
					action       : 'everest_forms_account_select_'+ source,
					source       : source,
					connection_id: $connection.data('connection_id'),
					account_id   : $this.find(':selected').val(),
					security     : evfp_params.ajax_nonce
				}
				$.ajax({
					url: evfp_params.ajax_url,
					data: data,
					type: 'POST',

					success: function( response ){
						if(response.success){
							EverestFormsIntegration.inputToggle($this, 'enable');
							$container.after(response.data.html);
							// Process first list found
							$connection.find('.evf-provider-lists option:first').prop('selected', true);
							$connection.find('.evf-provider-lists select').trigger('change');
						} else {
							EverestFormsIntegration.inputToggle($this, 'enable');
							$('.evf-alert-danger').remove();
							$('.evf-provider-connection.active-connection').append('<p class="evf-alert-danger evf-alert everest-forms-error-msg">'+ response.data.error +'</p>');
						}
					},
				});
			}
		},

		/**
		 * Selecting a provider account list.
		 *
		 */
		 accountListSelect: function(el, e) {
		 	e.preventDefault();

		 	var $this       = $(el),
		 	$connection = $this.closest('.evf-provider-connection'),
		 	$container  = $this.parent(),
		 	source    = $connection.data('provider');

			EverestFormsIntegration.inputToggle($this, 'disable');

			// Remove any blocks that might exist as we prep for new account
			$container.nextAll('.evf-connection-block').remove();
			$container.nextAll('.evf-conditional-block').remove();

			data = {
				action       : 'everest_forms_account_list_select_' + source,
				source       : source,
				connection_id: $connection.data('connection_id'),
				account_id   : $connection.find('.evf-provider-accounts option:selected').val(),
				list_id      : $this.find(':selected').val(),
				security     : evfp_params.ajax_nonce,
				form_id      : s.formID
			}

			$.ajax({
				url: evfp_params.ajax_url,
				data: data,
				type: 'POST',

				success: function( response ){
					EverestFormsIntegration.inputToggle($this, 'enable');
					$container.after(response.data.html);
				}
			});
		},

		selectActiveAccount: function(el, e) {
			e.preventDefault();

			var $this         = $(el),
			connection_id = $this.parent().data('connection-id'),
			active_block  = $('.evf-provider-connections').find('[data-connection_id="' + connection_id + '"]'),
			lengthOfActiveBlock = $(active_block).length;

			$('.evf-provider-connections').find('.evf-provider-connection').removeClass('active-connection');
			$this.parent().siblings().removeClass('active-user');
			$this.parent().addClass('active-user');

			if( lengthOfActiveBlock ){
				$( active_block ).addClass('active-connection');
			}

		},

		removeAccount: function(el, e) {
			e.preventDefault();

			var $this = $(el),
			connection_id = $this.parent().parent().data('connection-id'),
			active_block  = $('.evf-provider-connections').find('[data-connection_id="' + connection_id + '"]'),
			lengthOfActiveBlock = $(active_block).length,
			closestConnection = $this.closest('.everest-forms-active-connections-list'),
			checkConnection;
			$.confirm({
				title: false,
				content: "Are you sure you want to delete this connection?",
				backgroundDismiss: false,
				closeIcon: false,
				icon: 'dashicons dashicons-info',
				type: 'orange',
				buttons: {
					confirm: {
						text: evfp_params.i18n_ok,
						btnClass: 'btn-confirm',
						keys: ['enter'],
						action: function(){
							if( lengthOfActiveBlock ){
								var toBeRemoved = $this.parent().parent();
								active_block_after  = $('.evf-provider-connections').find('[data-connection_id="' + connection_id + '"]'),
								lengthOfActiveBlockAfter = $(active_block).length;
								if( toBeRemoved.prev().length ){
									toBeRemoved.prev().children('.user-nickname').trigger('click');
								}else {
									toBeRemoved.next().children('.user-nickname').trigger('click');
								}
								$( active_block ).remove();
								toBeRemoved.remove();
								checkConnection = $('.everest-forms-active-connections.active').children('.everest-forms-active-connections-list').children();
								if( 0 === checkConnection.length ) {
									closestConnection.addClass('empty-list');
									$('.evf-provider-connections').append('<div class="everest-form-add-connection-notice">Please add a connection.</div>');
								}
							}
						}
					},
					cancel: {
						text: evfp_params.i18n_cancel
					}
				}
			});
		},

		/**
		 * Show hide Conditional Logic Fields.
		 *
		 */
		enableConditionLogic: function( el, e) {
			var $this = $(el);
				if($this.is(':checked')){
					EverestFormsIntegration.getAllAvailableFields( $this.closest('.evf-provider-conditional') );
					$this.parent().siblings('.evf-conditional-container').fadeIn('slow');
					$this.parent().siblings('.evf-conditional-container').find('.evf-conditional-field-select').trigger('change');
				} else {
					$this.parent().siblings('.evf-conditional-container').fadeOut('slow');
				}
		},

		inputType: function( el, e) {
			e.preventDefault();
			var $this = $( el ),
				selected_option_id   = $this.find(':selected').data('field_id'),
				connection_id        = $this.parent().parent().data('con_id'),
				source               = $this.parent().parent().data('source'),
				$container = $('.everest-forms-panel-sidebar .everest-forms-tab-content').find('.everest-forms-field-option[data-field-id="' + selected_option_id + '"]').first(),
				options = $container.find('.everest-forms-field-option-group-inner .everest-forms-field-option-row-choices ul').find( 'input.label' ),
				conditional = 'undefined' != typeof evf_integration_data && 'undefined' != typeof evf_integration_data[source] && 'undefined' != typeof evf_integration_data[source][connection_id] ? evf_integration_data[source][connection_id]['conditional_logic'] : '' ,
				selected_option_type = $this.find(':selected').data('field_type');

				if ( 'zapier' === source ) {
					connection_id = 'zapier_connection';
				}

			switch (selected_option_type) {
				default:
					$this.parent().find('.evf-conditional-input').remove();
				var input_val =  conditional && 'undefined' !== typeof conditional.input_choice ? conditional.input_choice : '';
					$this.parent().append('<input class="evf-conditional-input" type="text" name="integrations['+source+']['+connection_id+'][conditional_logic][input_choice]" value="'+ input_val +'"></input>');

					break;

				case 'checkbox':
				case 'radio':
				case 'select':
					$this.parent().find('.evf-conditional-input').remove();
					$this.parent().append('<select class="evf-conditional-input" name="integrations['+source+']['+connection_id+'][conditional_logic][multiple_choice]"></select>');
					$(options).each(function(index, el) {
						var value    = $(el).val(),
							selected = '';
						if( conditional && value === conditional.multiple_choice ){
							selected = 'selected';
						}
						$this.parent().find('.evf-conditional-input').append('<option value="'+ value +'" '+selected+'>'+ value +'</option>');
					});

					break;

				case 'country' :
					$this.parent().find('.evf-conditional-input').remove();
					$this.parent().append('<select class="evf-conditional-input" name="integrations['+source+']['+connection_id+'][conditional_logic][country_choice]"></select>');
					options = $container.find('.everest-forms-field-option-group-advanced .everest-forms-field-option-row-default select').children();

					$(options).each(function(index, el) {
						var value    = $(el).val(),
							selected = '';
						if( conditional && value === conditional.country_choice ){
							selected = 'selected';
						}
						$this.parent().find('.evf-conditional-input').append('<option value="'+ value +'" '+selected+'>'+ $( el ).text() +'</option>');
					});

					break;

			}
		},

		getAllAvailableFields: function (  el ) {
			var connection_id = $(el).children('.evf-conditional-container').data('con_id'),
			    source        = $(el).children('.evf-conditional-container').data('source');
			$(el).parent().find('.evf-conditional-container .evf-conditional-wrapper .evf-conditional-field-select').empty();
			$('.evf-admin-row .evf-admin-grid .everest-forms-field').each( function(){
				var field_type  = $( this ).data('field-type'),
					conditional = 'undefined' != typeof evf_integration_data && 'undefined' != typeof evf_integration_data[source] && 'undefined' != typeof evf_integration_data[source][connection_id] ? evf_integration_data[source][connection_id]['conditional_logic'] : '' ,
					field_id    = $( this ).data('field-id'),
					field_label = $( this ).find('.label-title span').first().text(),
					selected = '',
					field_to_be_restricted=[];
					field_to_be_restricted = [
						'html',
						'title',
						'address',
						'image-upload',
						'file-upload',
						'date-time',
						'payment-multiple',
						'payment-single',
						'payment-checkbox',
						'payment-total',
					];
					if( conditional && field_id === conditional.field_select ){
						selected = 'selected';
					}

				if( $.inArray( field_type, field_to_be_restricted ) === -1 ){
					$(el).parent().find('.evf-conditional-container .evf-conditional-wrapper .evf-conditional-field-select').append('<option class="evf-conditional-fields" data-field_type="'+field_type+'" data-field_id="'+field_id+'" value="'+field_id+'" '+selected+'>'+field_label+'</option>');
				}
			});


		},

		/**
		 * Toggle input with loading indicator.
		 *
		 */
		inputToggle: function(el, status) {
			var $this = $(el);
			if (status == 'enable') {
				if ($this.is('select')) {
					$this.prop('disabled', false).next('i').remove();
				} else {
					$this.prop('disabled', false).find('i').remove();
				}
			} else if (status == 'disable'){
				if ($this.is('select')) {
					$this.prop('disabled', true).after(s.spinner);
				} else {
					$this.prop('disabled', true).prepend(s.spinner);
				}
			}
		},

		/**
		 * Display error.
		 *
		 */
		 errorDisplay: function(msg, location) {
		 	location.find('.everest-forms-error-msg').remove();
		 	location.prepend('<p class="evf-alert-danger evf-alert everest-forms-error-msg">'+msg+'</p>');
		 },

		/**
		 * Check for required fields.
		 *
		 */
		 requiredCheck: function(fields, location) {
		 	var error = false;

			// Remove any previous errors
			location.find('.evf-alert-required').remove();

			// Loop through input fields and check for values
			fields.each(function(index, el) {
				if ( $(el).hasClass('everest-forms-required') && $(el).val().length === 0 ) {
					$(el).addClass('everest-forms-error');
					error = true;
				} else {
					$(el).removeClass('everest-forms-error');
				}
			});
			if (error) {
				location.find('.everest-forms-error-msg').remove();
				location.prepend('<p class="evf-alert-danger evf-alert evf-alert-required">'+evfp_params.required_field+'</p>');
			}
			return error;
		},

		/**
		 * Psuedo serializing. Fake it until you make it.
		 *
		 */
		 fakeSerialize: function(els) {
		 	var fields = els.clone();

		 	fields.each(function(index, el){
		 		if ($(el).data('name')) {
		 			$(el).attr('name', $(el).data('name'));
		 		}
		 	});
		 	return fields.serialize();
		 }
		};
		EverestFormsIntegration.init();
	})(jQuery);
