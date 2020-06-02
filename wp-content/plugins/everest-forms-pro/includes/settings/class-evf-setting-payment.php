<?php
/**
 * EverestForms Payment Settings
 *
 * @package EverestForms\Admin
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EVF_Settings_Payment', false ) ) {
	return new EVF_Settings_Payment();
}

/**
 * EVF_Settings_Payment.
 */
class EVF_Settings_Payment extends EVF_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'payment';
		$this->label = __( 'Payments', 'everest-forms-pro' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$currencies       = evf_get_currencies();
		$currency_options = array();

		// Format currencies for select element.
		foreach ( $currencies as $code => $currency ) {
			$currency_options[ $code ] = sprintf( '%s (%s %s)', $currency['name'], $code, $currency['symbol'] );
		}

		$settings = apply_filters(
			'everest_forms_payment_settings',
			array(
				array(
					'title' => __( 'Payments', 'everest-forms-pro' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'payments_options',
				),

				array(
					'title'    => __( 'Currency', 'everest-forms-pro' ),
					'desc'     => __( 'This controls which currency gateways will take payments in.', 'everest-forms-pro' ),
					'id'       => 'everest_forms_currency',
					'default'  => 'USD',
					'type'     => 'select',
					'class'    => 'evf-enhanced-select',
					'desc_tip' => true,
					'options'  => $currency_options,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'payments_options',
				),
			)
		);

		return apply_filters( 'everest_forms_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		EVF_Admin_Settings::save_fields( $settings );
	}
}

return new EVF_Settings_Payment();
