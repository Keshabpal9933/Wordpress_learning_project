<?php
/**
 * Payment Single Item field.
 *
 * @package EverestForms\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Payment_Single Field.
 */
class EVF_Field_Payment_Single extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Single Item', 'everest-forms-pro' );
		$this->type     = 'payment-single';
		$this->icon     = 'evf-icon evf-icon-single-item';
		$this->order    = 10;
		$this->group    = 'payment';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'item_price',
					'item_format',
					'required',
					'required_field_message',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'placeholder',
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
		add_filter( 'everest_forms_field_properties_' . $this->type, array( $this, 'field_properties' ), 5, 3 );
	}

	/**
	 * Item price field option.
	 *
	 * @param array $field Field data.
	 */
	public function item_price( $field ) {
		$item_price       = ! empty( $field['item_price'] ) ? evf_format_amount( evf_sanitize_amount( $field['item_price'] ) ) : '';
		$item_price_label = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'item_price',
				'value'   => esc_html__( 'Item Price', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Please Enter price of your item, without a currency symbol.', 'everest-forms-pro' ),
			),
			false
		);
		$item_price_input = $this->field_element(
			'text',
			$field,
			array(
				'slug'        => 'item_price',
				'value'       => $item_price,
				'class'       => 'evf-money-input',
				'placeholder' => evf_format_amount( 0 ),
			),
			false
		);

		$args = array(
			'slug'    => 'item_price',
			'content' => $item_price_label . $item_price_input,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Item format field option.
	 *
	 * @param array $field Field data.
	 */
	public function item_format( $field ) {
		$item_type          = ! empty( $field['item_type'] ) ? esc_attr( $field['item_type'] ) : '';
		$item_format_label  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'item_type',
				'value'   => esc_html__( 'Item Type', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Please select the item type.', 'everest-forms-pro' ),
			),
			false
		);
		$item_format_select = $this->field_element(
			'select',
			$field,
			array(
				'slug'    => 'item_type',
				'value'   => $item_type,
				'options' => array(
					'single' => esc_html__( 'Pre Defined', 'everest-forms-pro' ),
					'user'   => esc_html__( 'User Defined', 'everest-forms-pro' ),
					'hidden' => esc_html__( 'Hidden', 'everest-forms-pro' ),
				),
			),
			false
		);

		$args = array(
			'slug'    => 'item_price',
			'content' => $item_format_label . $item_format_select,
		);
		$this->field_element( 'row', $field, $args );
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
		$form_id  = absint( $form_data['id'] );
		$field_id = $field['id'];

		// Set options container (<select>) properties.
		$properties['input_container'] = array(
			'class' => array( 'evf-payment-price' ),
			'data'  => array(),
			'id'    => "evf-{$form_id}-field_{$field_id}",
		);

		$properties['inputs']['primary']['class'][] = 'evf-payment-price';

		// User format data and class.
		$item_type = ! empty( $field['item_type'] ) ? $field['item_type'] : 'single';
		if ( 'user' === $item_type ) {
			$properties['inputs']['primary']['class'][]               = 'evf-payment-user-input';
			$properties['inputs']['primary']['data']['rule-currency'] = '["$",false]';
		}

		// Price.
		$field_value                                      = ! empty( $field['item_price'] ) ? evf_sanitize_amount( $field['item_price'] ) : '';
		$properties['inputs']['primary']['attr']['value'] = ! empty( $field_value ) ? evf_format_amount( $field_value, true ) : '';

		// Pre defined and hidden format should hide the input field.
		if ( ! empty( $field['item_type'] ) && 'hidden' === $field['item_type'] ) {
			$properties['container']['class'][] = 'evf-field-hidden';
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
		$item_price  = ! empty( $field['item_price'] ) ? evf_format_amount( evf_sanitize_amount( $field['item_price'] ), true ) : evf_format_amount( 0, true );
		$placeholder = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : evf_format_amount( 0 );
		$item_type   = ! empty( $field['item_type'] ) ? esc_html( $field['item_type'] ) : 'single';
		$value       = ! empty( $field['item_price'] ) ? evf_format_amount( evf_sanitize_amount( $field['item_price'] ) ) : '';

		echo '<div class="format-selected-' . esc_attr( $item_type ) . ' format-selected">';

			$this->field_preview_option( 'label', $field );

			echo '<p class="item-price">';
				printf(
					/* translators: %s - item price. */
					esc_html__( 'Price: %s', 'everest-forms-pro' ),
					'<span class="price">' . esc_html( $item_price ) . '</span>'
				);
			echo '</p>';

			printf(
				'<input type="text" placeholder="%s" class="widefat primary-input" value="%s" disabled>',
				esc_attr( $placeholder ),
				esc_attr( $value )
			);

			$this->field_preview_option( 'description', $field );

			echo '<p class="item-price-hidden">';
				esc_html_e( 'Note: You have selected hidden type which will not visible in the form.', 'everest-forms-pro' );
			echo '</p>';

		echo '</div>';
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
		$primary   = $field['properties']['inputs']['primary'];
		$item_type = ! empty( $field['item_type'] ) ? $field['item_type'] : 'single';

		switch ( $item_type ) {
			case 'single':
			case 'hidden':
				if ( 'single' === $item_type ) {
					$primary['attr']['value'] = $field['item_price'];
					echo '<div class="evf-single-item-price">';
					printf(
						/* translators: %s - item price. */
						esc_html__( 'Price: %s', 'everest-forms-pro' ),
						'<span class="evf-payment-price">' . esc_html( evf_format_amount( evf_sanitize_amount( $field['item_price'] ), true ) ) . '</span>'
					);
					echo '</div>';
				}

				// Primary price field.
				printf(
					'<input type="hidden" %s>',
					evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] )
				);
				break;
			case 'user':
				printf(
					'<input type="text" %s>',
					evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] )
				);
				break;
		}
	}

	/**
	 * Validates field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Field data submitted by a user.
	 * @param array  $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		// Basic required check.
		if ( empty( $field_submit ) && ! empty( $form_data['form_fields'][ $field_id ]['required'] ) ) {
			evf()->task->errors[ $form_data['id'] ][ $field_id ] = evf_get_required_label();
			update_option( 'evf_validation_error', 'yes' );
			return;
		}

		// If field format is not user provided, validate the amount posted.
		if ( ! empty( $field_submit ) && 'user' !== $form_data['form_fields'][ $field_id ]['item_type'] ) {
			$price  = evf_sanitize_amount( $form_data['form_fields'][ $field_id ]['item_price'] );
			$submit = evf_sanitize_amount( $field_submit );

			if ( $price !== $submit ) {
				evf()->task->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Amount mismatch', 'everest-forms-pro' );
				update_option( 'evf_validation_error', 'yes' );
			}
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
		$field = $form_data['form_fields'][ $field_id ];
		$name  = ! empty( $field['label'] ) ? make_clickable( $field['label'] ) : '';

		// Only trust the value if the field is user format.
		if ( 'user' === $field['item_type'] ) {
			$amount = evf_sanitize_amount( $field_submit );
		} else {
			$amount = evf_sanitize_amount( $field['item_price'] );
		}

		evf()->task->form_fields[ $field_id ] = array(
			'name'       => $name,
			'value'      => evf_format_amount( $amount, true ),
			'amount'     => evf_format_amount( $amount ),
			'amount_raw' => $amount,
			'currency'   => get_option( 'everest_forms_currency', 'USD' ),
			'id'         => $field_id,
			'type'       => $this->type,
			'meta_key'   => $meta_key,
		);
	}
}
