<?php
/**
 * Payment total field.
 *
 * @package EverestForms_Pro\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Payment_Total class.
 */
class EVF_Field_Payment_Total extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Total', 'everest-forms-pro' );
		$this->type     = 'payment-total';
		$this->icon     = 'evf-icon evf-icon-total';
		$this->order    = 60;
		$this->group    = 'payment';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
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
		// Input Primary: initial total is always zero.
		$properties['inputs']['primary']['attr']['value'] = '0';

		// Input Primary: add class for targeting calculations.
		$properties['inputs']['primary']['class'][] = 'evf-payment-total';

		// Input Primary: add data attribute if total is required.
		if ( ! empty( $field['required'] ) ) {
			$properties['inputs']['primary']['data']['rule-required-payment'] = true;
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

		// Primary field.
		echo '<div>' . evf_format_amount( 0, true ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

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
		$primary = $field['properties']['inputs']['primary'];
		$type    = ! empty( $field['required'] ) ? 'text' : 'hidden';
		$style   = ! empty( $field['required'] ) ? 'style="position:absolute!important;clip:rect(0,0,0,0)!important;height:1px!important;width:1px!important;border:0!important;overflow:hidden!important;padding:0!important;margin:0!important;" readonly ' : '';

		// This displays the total the user sees.
		echo '<div class="evf-payment-total">' . evf_sanitize_amount( evf_format_amount( 0, true ) ) . '</div>';

		// Hidden input for processing.
		printf(
			'<input type="%s" %s %s>',
			esc_attr( $type ),
			evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
			$style // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}

	/**
	 * Validates field on form submit.
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Field value submitted by a user.
	 * @param array  $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && ( empty( $field_submit ) || evf_sanitize_amount( $field_submit ) <= 0 ) ) {
			evf()->process->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Payment is required.', 'everest-forms-pro' );
		}
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $field_id     Field ID.
	 * @param mixed  $field_submit Submitted field value.
	 * @param array  $form_data    Form data and settings.
	 * @param string $meta_key     Field meta key.
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {
		$name   = ! empty( $form_data['form_fields'][ $field_id ]['label'] ) ? $form_data['form_fields'][ $field_id ]['label'] : '';
		$amount = evf_sanitize_amount( $field_submit );

		// Set final field details.
		EVF()->task->form_fields[ $field_id ] = array(
			'name'       => make_clickable( $name ),
			'value'      => evf_format_amount( $amount, true ),
			'amount'     => evf_format_amount( $amount ),
			'amount_raw' => $amount,
			'id'         => $field_id,
			'type'       => $this->type,
			'meta_key'   => $meta_key,
		);
	}
}
