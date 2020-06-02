<?php
/**
 * EverestForms Builder Fields
 *
 * @package EverestForms_Pro\Admin
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EVF_Builder_Payments', false ) ) {
	return new EVF_Builder_Payments();
}

/**
 * EVF_Builder_Payments class.
 */
class EVF_Builder_Payments extends EVF_Builder_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id      = 'payments';
		$this->label   = __( 'Payments', 'everest-forms-pro' );
		$this->sidebar = true;

		parent::__construct();
	}

	/**
	 * Outputs the builder sidebar.
	 */
	public function output_sidebar() {
		$payments = apply_filters( 'everest_forms_available_payments', array() );

		if ( ! empty( $payments ) ) {
			foreach ( $payments as $payment ) {
				$this->add_sidebar_tab( $payment['name'], $payment['id'], $payment['icon'], $this->id );

				do_action( 'everest_forms_payment_connections_' . $payment['id'], $payment );
			}
		}
	}

	/**
	 * Outputs the builder content.
	 */
	public function output_content() {
		$payment_settings = apply_filters( 'everest_forms_available_payments', array() );

		if ( empty( $payment_settings ) ) {
			echo '<div class="evf-panel-content-section evf-payment-setting-content evf-panel-content-section-info">';
			echo '<h5>' . esc_html__( 'Install Your Addons', 'everest-forms-pro' ) . '</h5>';
			echo '<p>' . sprintf(
				wp_kses(
					/* translators: %s - plugin admin area Addons page. */
					__(
						'It seems you do not have any addons activated that needs payment. You can head over to the <a href="%s">Addons page</a> to install and activate the addon.',
						'everest-forms-pro'
					),
					array( 'a' => array( 'href' => array() ) )
				),
				esc_url( admin_url( 'admin.php?page=evf-addons' ) )
			) . '</p>';
			echo '</div>';
		} else {
			do_action( 'everest_forms_payments_panel_content', $this->form );
		}
	}
}

return new EVF_Builder_Payments();
