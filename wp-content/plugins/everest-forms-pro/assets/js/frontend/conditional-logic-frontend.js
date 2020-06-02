/**
 * EverestFormsConditionalFrontend JS
 */
(function($) {

	var EverestFormsConditionalFrontend = {

		/**
		 * Start the engine.
		 */
		init: function() {
			$(document).ready(EverestFormsConditionalFrontend.ready);

			EverestFormsConditionalFrontend.bindUIActions();
		},

		ready: function() {

			$('.everest-form').each(function() {
				EverestFormsConditionalFrontend.conditionalLogicAction($(this));
			});
		},

		bindUIActions: function() {

			$(document).on('change keyup', '.everest-forms-trigger-conditional input, .everest-forms-trigger-conditional select, .everest-forms-trigger-conditional textarea', function() {
				EverestFormsConditionalFrontend.conditionalLogicAction($(this));
			});
		},

		conditionalLogicAction: function(el) {
			var $this = $(el),
				$form = $this.closest('.everest-form'),
				formID = $form.attr('data-formid'),
				allFields = $form.find('.evf-frontend-row').find('.evf-frontend-grid').find('.input-text');
				var el = $form.find('.evf-submit-container ').find('.evf-submit')[0];
				allFields.push(el);
				var myObj = {};
			myObj.conditional_rules = [];

			allFields.each(function(index, el) {
				var field_id = $(this).attr('id');
				if ('undefined' !== typeof $(this).attr('conditional_rules') && '' != $(this).attr('conditional_rules')) {
					if (EverestFormsConditionalFrontend.IsJsonString($(this).attr('conditional_rules')) === true) {
						myObj.conditional_rules[field_id] = JSON.parse($(this).attr('conditional_rules'));
					}
				}

			});
			var fields = myObj.conditional_rules;

			for (var fieldID in fields) {
				var field = fields[fieldID].conditionals,
					action = fields[fieldID].conditional_option,
					field_required = fields[fieldID].required,
					matchCondition = false;

				// Groups
				for (var groupID in field) {

					var group = field[groupID],
						conditionGroup = true;

					// Rules
					for (var ruleID in group) {

						var rule = group[ruleID],
							val = '',
							conditionalRuleMatch = false,
							left = '',
							right = '';

						if (!rule.field) {
							continue;
						}
						var targetInput = $('[conditional_id="' + group[ruleID].field + '"]');

						if( targetInput.length === 0 ) {
							continue;
						}

						var type = targetInput.get(0).type;

						if ( rule.operator === 'empty' || rule.operator === 'not_empty' ) {
							rule.value = '';
							if ( type === 'radio' || type === 'checkbox' || type === 'payment-multiple' || type === 'payment-checkbox' || type === 'rating' || type === 'likert' || type === 'scale-rating' ) {
								$check = $form.find( '#evf-'+formID+'-field_'+group[ruleID].field+'-container input:checked' );
								if ( $check.length ) {
									left = true;
								}
							} else {
								left = $form.find( '#evf-'+formID+'-field_'+group[ruleID].field ).val();
								if ( ! left  ) {
									left = '';
								}
							}

						} else {
							if (type == "radio" || type == "checkbox") {
							var check = $form.find( '#evf-'+formID+'-field_'+group[ruleID].field+'-container input:checked' );
							if ( check.length ) {
								$.each( check, function() {
									var value =  $( this ).val();
									if ( type === 'checkbox' ) {
										if ( rule.value === value ) {
											left = value;
										}
									} else {
										left = value;
									}
								});
							}
						} else {
							if( type === "tel" ) {
								left = targetInput.val().replace(/[()-\s]/g,'');
							} else {
								left = targetInput.val();
							}
						}
					}
						right = group[ruleID].value;

						switch (rule.operator) {
							case 'is':
								conditionalRuleMatch = (left === right);
								break;
							case 'is_not':
								conditionalRuleMatch = (left !== right);
								break;
							case 'empty':
								conditionalRuleMatch = ( left.length === 0 );
								break;
							case 'not_empty':
								conditionalRuleMatch = ( left.length > 0 );
								break;
							case 'greater_than' :
								left      = left.replace( /[^0-9.]/g, '' );
								conditionalRuleMatch = ( '' !== left ) && ( EverestFormsConditionalFrontend.convertToFlot( left ) > EverestFormsConditionalFrontend.convertToFlot( right ) );
								break;
							case 'less_than' :
								left      = left.replace( /[^0-9.]/g, '' );
								conditionalRuleMatch = ( '' !== left ) && ( EverestFormsConditionalFrontend.convertToFlot( left ) < EverestFormsConditionalFrontend.convertToFlot( right ) );
								break;
						}

						if (!conditionalRuleMatch) {
							conditionGroup = false;
						break;
						}

					}

					if (conditionGroup) {
						matchCondition = true;
					}

				}
				var submitButton = $('form.everest-form').find('.evf-submit-container ').find('button[id="' + fieldID + '"]');
				if ( $('.evf-field-container').find('.everest-forms-part').length ) {
					if ( submitButton.length > 0 && '""' !== submitButton.attr('conditional_rules') ){
						setTimeout( function() {
							if ( ( matchCondition && action === 'disable') ) {
								submitButton.triggerHandler( 'evf-conditional-logic-submit', 'disable' );
							} else if ( (matchCondition && action === 'hide') || (!matchCondition && action === 'show') ) {
								submitButton.triggerHandler( 'evf-conditional-logic-submit', 'hide' );
							} else {
								submitButton.triggerHandler( 'evf-conditional-logic-submit', 'show' );
							}
						}, 1 );
					}
				} else {
					if ( submitButton.length > 0 && '""' !== submitButton.attr('conditional_rules') ){
						if ( ( matchCondition && action === 'disable') ) {
							submitButton.prop('disabled', true);
						} else if ( (matchCondition && action === 'hide') || (!matchCondition && action === 'show') ) {
							submitButton.prop('disabled', false);
							submitButton.closest(".evf-submit-container").hide();
							submitButton.closest(".evf-submit-container").find( 'button' ).attr( 'disabled', 'disabled' );
						} else {
							submitButton.prop('disabled', false);
							submitButton.closest(".evf-submit-container").show();
							submitButton.closest(".evf-submit-container").find( 'button' ).removeAttr( 'disabled' );
						}
					}
				}

				var single_field = $('form.everest-form').find('.evf-frontend-row').find('.evf-frontend-grid').find('.input-text[id="' + fieldID + '"]');
				if ((matchCondition && action === 'hide') || (!matchCondition && action !== 'hide')) {
					$( document.body ).trigger('conditional_hide', [single_field.closest(".evf-field")]);
					single_field.closest(".evf-field").hide();
					single_field.closest(".evf-field").find( 'input, select' ).attr( 'disabled', 'disabled' );
					single_field.removeAttr("required");
				} else {
					$( document.body ).trigger('conditional_show', [single_field.closest(".evf-field")]);
					single_field.closest(".evf-field").show();
					single_field.closest(".evf-field").find( 'input, select' ).removeAttr( 'disabled' );

					if ( '1' === field_required && ! single_field.is( '.evf-conditional-logic-holder' ) ) {
						single_field.attr( 'required', 'required' );
					}
				}
			}
		},

		IsJsonString: function(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		},

		convertToFlot: function ( val ) {
			return ( parseFloat( val ) || 0 );
		}
	};

	EverestFormsConditionalFrontend.init();

})(jQuery);
