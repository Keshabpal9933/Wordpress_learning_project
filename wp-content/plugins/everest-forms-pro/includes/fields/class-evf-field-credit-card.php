<?php
/**
 * Credit card field
 *
 * @package EverestForms_Pro\Fields
 * @since   1.2.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Credit_Card Class.
 */
class EVF_Field_Credit_Card extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Credit Card', 'everest-forms-pro' );
		$this->type     = 'credit-card';
		$this->icon     = 'evf-icon evf-icon-payment';
		$this->order    = 50;
		$this->group    = 'payment';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
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
		add_action( 'admin_print_footer_scripts', array( $this, 'builder_footer_scripts' ) );
		// add_filter( 'everest_forms_field_new_required', array( $this, 'field_default_required' ), 5, 3 ); @codingStandardsIgnoreLine
	}

	/**
	 * Restrict the users to add the field inside the
	 * form builder if not supporting payment gateway is active.
	 */
	public function builder_footer_scripts() {
		if ( apply_filters( 'everest_forms_field_credit_card_enable', false ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(function($){
				$( '#everest-forms-add-fields-credit-card' ).remove();
			});
		</script>
		<?php
	}

	/**
	 * Field should default to being required.
	 *
	 * @since 1.3.0
	 *
	 * @param bool  $required Required status, true is required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 */
	public function field_default_required( $required, $field ) {
		if ( 'credit-card' === $field['type'] ) {
			return true;
		}

		return $required;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {
		$this->field_preview_option( 'label', $field );
		?>
		<div class="everest-forms-credit-card-cardnumber">
			<div class="everest-forms-card-icon">
				<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 21"><g fill="none" fill-rule="evenodd"><g id="unknown" class="Icon-fill"><g id="card" transform="translate(0 2)"><path id="shape" d="M26.58 19H2.42A2.4 2.4 0 0 1 0 16.62V2.38A2.4 2.4 0 0 1 2.42 0h24.16A2.4 2.4 0 0 1 29 2.38v14.25A2.4 2.4 0 0 1 26.58 19zM10 5.83c0-.46-.35-.83-.78-.83H3.78c-.43 0-.78.37-.78.83v3.34c0 .46.35.83.78.83h5.44c.43 0 .78-.37.78-.83V5.83z" opacity=".2"></path><path id="shape" d="M25 15h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0H4c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1z" opacity=".3"></path></g></g></g></svg>
			</div>
			<input class="card-number" type="text" placeholder="Card Number" disabled>
			<input class="card-expiration" type="text" placeholder="MM / YY" disabled>
			<input class ="card-cvc" type="text" placeholder="CVC" disabled>
		</div>
		<?php
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
		$credit_card_gateways = apply_filters( 'everest_forms_credit_card_gateway', array() );
		$conditional_rules    = isset( $field['properties']['inputs']['primary']['attr']['conditional_rules'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_rules'] : '';
		$conditional_id       = isset( $field['properties']['inputs']['primary']['attr']['conditional_id'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_id'] : '';
		$form_id              = isset( $form_data['id'] ) ? $form_data['id'] : 0;
		if ( ! empty( $credit_card_gateways ) ) {
			foreach ( $credit_card_gateways as $gateway => $value ) {

				// @codingStandardsIgnoreStart
				printf(
					'<div id="everest_forms_%s_gateway_%s" data-gateway="%s" class="input-text" data-form-id="%s" conditional_rules="%s" conditional_id="%s"></div><label id="card-errors" class="evf-error" role="alert"></label>',
					$gateway,
					$form_id,
					$gateway,
					$form_id,
					esc_attr( $conditional_rules ),
					esc_attr( $conditional_id )
				);
				// @codingStandardsIgnoreEnd
			}
		}
	}
}
