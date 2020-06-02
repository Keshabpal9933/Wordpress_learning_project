<?php
/**
 * Payment Radio field.
 *
 * @package EverestForms\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Payment_Radio Class.
 */
class EVF_Field_Payment_Radio extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Multiple Choice', 'everest-forms-pro' );
		$this->type     = 'payment-multiple';
		$this->icon     = 'evf-icon evf-icon-multiple-choices-radio';
		$this->order    = 20;
		$this->group    = 'payment';
		$this->defaults = array(
			1 => array(
				'label'   => esc_html__( 'First Choice', 'everest-forms-pro' ),
				'value'   => '10.00',
				'image'   => '',
				'default' => '',
			),
			2 => array(
				'label'   => esc_html__( 'Second Choice', 'everest-forms-pro' ),
				'value'   => '20.00',
				'image'   => '',
				'default' => '',
			),
			3 => array(
				'label'   => esc_html__( 'Third Choice', 'everest-forms-pro' ),
				'value'   => '30.00',
				'image'   => '',
				'default' => '',
			),
		);
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'choices',
					'choices_images',
					'description',
					'required',
					'required_field_message',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'input_columns',
					'label_hide',
					'css',
				),
			),
		);

		parent::__construct();
	}

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_filter( 'everest_forms_html_field_value', array( $this, 'html_field_value' ), 10, 4 );
		add_filter( 'everest_forms_field_properties_' . $this->type, array( $this, 'field_properties' ), 5, 3 );
	}

	/**
	 * Return images, if any, for HTML supported values.
	 *
	 * @since 1.3.0
	 *
	 * @param string $value     Field value.
	 * @param array  $field     Field settings.
	 * @param array  $form_data Form data and settings.
	 * @param string $context   Value display context.
	 *
	 * @return string
	 */
	public function html_field_value( $value, $field, $form_data = array(), $context = '' ) {
		if ( is_serialized( $field ) || in_array( $context, array( 'email-html', 'export-pdf' ), true ) ) {
			$field_value = maybe_unserialize( $field );
			$field_type  = isset( $field_value['type'] ) ? sanitize_text_field( $field_value['type'] ) : 'payment-multiple';

			if ( $field_type === $this->type ) {
				if (
					'entry-table' !== $context
					&& ! empty( $field_value['label'] )
					&& ! empty( $field_value['image'] )
					&& apply_filters( 'everest_forms_payment_multiple_field_html_value_images', true, $context )
				) {
					return sprintf(
						'<span style="max-width:200px;display:block;margin:0 0 5px 0;"><img src="%s" style="max-width:100%%;display:block;margin:0;"></span>%s',
						esc_url( $field_value['image'] ),
						esc_html( $field_value['label'] )
					);
				} elseif ( isset( $field_value['label'] ) ) {
					return esc_html( $field_value['label'] );
				}
			}
		}

		return $value;
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array of additional field properties.
	 */
	public function field_properties( $properties, $field, $form_data ) {
		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = $field['id'];
		$choices  = $field['choices'];

		// Remove primary input.
		unset( $properties['inputs']['primary'] );

		// Set input container (ul) properties.
		$properties['input_container'] = array(
			'class' => array(),
			'data'  => array(),
			'attr'  => array(),
			'id'    => "evf-{$form_id}-field_{$field_id}",
		);

		// Set input properties.
		foreach ( $choices as $key => $choice ) {
			// BW compatibility for choice value.
			if ( ! empty( $field['amount'][ $key ]['value'] ) ) {
				$choice['value'] = $field['amount'][ $key ]['value'];
			}

			$properties['inputs'][ $key ] = array(
				'container' => array(
					'attr'  => array(),
					'class' => array( "choice-{$key}" ),
					'data'  => array(),
					'id'    => '',
				),
				'label'     => array(
					'attr'  => array(
						'for' => "evf-{$form_id}-field_{$field_id}_{$key}",
					),
					'class' => array( 'everest-forms-field-label-inline' ),
					'data'  => array(),
					'id'    => '',
					'text'  => sprintf( '%s - %s', evf_string_translation( $form_id, $field_id, $choice['label'], '-choice-' . $key ), evf_format_amount( evf_sanitize_amount( $choice['value'] ), true ) ),
				),
				'attr'      => array(
					'name'  => "everest_forms[form_fields][{$field_id}]",
					'value' => $key,
				),
				'class'     => array( 'input-text', 'evf-payment-price' ),
				'data'      => array(
					'amount' => evf_format_amount( evf_sanitize_amount( $choice['value'] ) ),
				),
				'id'        => "evf-{$form_id}-field_{$field_id}_{$key}",
				'image'     => isset( $choice['image'] ) ? $choice['image'] : '',
				'required'  => ! empty( $field['required'] ) ? 'required' : '',
				'default'   => isset( $choice['default'] ),
			);
		}

		// Required class for validation.
		if ( ! empty( $field['required'] ) ) {
			$properties['input_container']['class'][] = 'evf-field-required';
		}

		// Custom properties if enabled image choices.
		if ( ! empty( $field['choices_images'] ) ) {
			$properties['input_container']['class'][] = 'everest-forms-image-choices';

			foreach ( $properties['inputs'] as $key => $inputs ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'everest-forms-image-choices-item';
			}
		}

		// Add selected class for choices with defaults.
		foreach ( $properties['inputs'] as $key => $inputs ) {
			if ( ! empty( $inputs['default'] ) ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'everest-forms-selected';
			}
		}

		return $properties;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {
		// Label.
		$this->field_preview_option( 'label', $field );

		// Choices.
		$this->field_preview_option( 'choices', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field Data.
	 * @param array $field_atts Field attributes.
	 * @param array $form_data All Form Data.
	 */
	public function field_display( $field, $field_atts, $form_data ) {
		// Define data.
		$container = $field['properties']['input_container'];
		$choices   = $field['properties']['inputs'];

		// List.
		printf( '<ul %s>', evf_html_attributes( $container['id'], $container['class'], $container['data'], $container['attr'] ) );

		foreach ( $choices as $key => $choice ) {
			if ( empty( $choice['container'] ) ) {
				continue;
			}

			// Conditional logic.
			$choice['attr']['conditional_id'] = $choices['primary']['attr']['conditional_id'];
			if ( isset( $choices['primary']['attr']['conditional_rules'] ) ) {
				$choice['attr']['conditional_rules'] = $choices['primary']['attr']['conditional_rules'];
			}

			printf( '<li %s>', evf_html_attributes( $choice['container']['id'], $choice['container']['class'], $choice['container']['data'], $choice['container']['attr'] ) );

			if ( ! empty( $field['choices_images'] ) ) {
				// Make image choices keyboard-accessible.
				$choice['label']['attr']['tabindex'] = 0;

				// Image choices.
				printf( '<label %s>', evf_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] ) );

				if ( ! empty( $choice['image'] ) ) {
					printf(
						'<span class="everest-forms-image-choices-image"><img src="%s" alt="%s"%s></span>',
						esc_url( $choice['image'] ),
						esc_attr( $choice['label']['text'] ),
						! empty( $choice['label']['text'] ) ? ' title="' . esc_attr( $choice['label']['text'] ) . '"' : ''
					);
				}

				echo '<br>';

				$choice['attr']['tabindex'] = '-1';

				printf( '<input type="radio" %s %s %s>', evf_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ), esc_attr( $choice['required'] ), checked( '1', $choice['default'], false ) );
				echo '<span class="everest-forms-image-choices-label">' . wp_kses_post( $choice['label']['text'] ) . '</span>';
				echo '</label>';
			} else {
				// Normal display.
				printf( '<input type="radio" %s %s %s>', evf_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ), esc_attr( $choice['required'] ), checked( '1', $choice['default'], false ) );
				printf( '<label %s>%s</label>', evf_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] ), wp_kses_post( $choice['label']['text'] ) );
			}

			echo '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Validates field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id Field Id.
	 * @param array $field_submit Submitted Field.
	 * @param array $form_data Form Data.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) ) {
			evf()->task->errors[ $form_data['id'] ][ $field_id ] = evf_get_required_label();
			update_option( 'evf_validation_error', 'yes' );
		}

		// Validate that the option selected is real.
		if ( ! empty( $field_submit ) && empty( $form_data['form_fields'][ $field_id ]['choices'][ $field_submit ] ) ) {
			evf()->task->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Invalid payment option', 'everest-forms-pro' );
			update_option( 'evf_validation_error', 'yes' );
		}
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_id Field Id.
	 * @param array  $field_submit Submitted Field.
	 * @param array  $form_data All Form Data.
	 * @param string $meta_key Field Meta Key.
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {
		$field        = $form_data['form_fields'][ $field_id ];
		$name         = make_clickable( $field['label'] );
		$value_raw    = sanitize_text_field( $field_submit );
		$amount       = 0;
		$value        = '';
		$choice_label = '';
		$image        = '';

		// BW compatibility for choice value for amount.
		if ( ! empty( $field['amount'][ $field_submit ]['value'] ) ) {
			$field['choices'][ $field_submit ]['value'] = $field['amount'][ $field_submit ]['value'];
		}

		if ( ! empty( $field_submit ) && ! empty( $field['choices'][ $field_submit ]['value'] ) ) {
			$amount = evf_sanitize_amount( $field['choices'][ $field_submit ]['value'] );
			$value  = evf_format_amount( $amount, true );

			if ( ! empty( $field['choices'][ $field_submit ]['label'] ) ) {
				$choice_label = sanitize_text_field( $field['choices'][ $field_submit ]['label'] );
				$value        = $choice_label . ' - ' . $value;
			}

			if ( ! empty( $field['choices_images'] ) ) {
				$image = ! empty( $field['choices'][ $field_submit ]['image'] ) ? esc_url_raw( $field['choices'][ $field_submit ]['image'] ) : '';
			}
		}

		evf()->task->form_fields[ $field_id ] = array(
			'id'           => $field_id,
			'type'         => $this->type,
			'value'        => array(
				'name'     => $name,
				'type'     => $this->type,
				'label'    => $value,
				'amount'   => evf_format_amount( $amount ),
				'currency' => get_option( 'everest_forms_currency', 'USD' ),
				'image'    => $image,
			),
			'meta_key'     => $meta_key,
			'amount_raw'   => $amount,
			'value_raw'    => $value_raw,
			'value_choice' => $choice_label,
		);
	}
}
