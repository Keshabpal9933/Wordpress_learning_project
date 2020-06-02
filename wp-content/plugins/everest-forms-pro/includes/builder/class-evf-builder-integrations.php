<?php
/**
 * EverestForms Builder Fields
 *
 * @package EverestForms_Pro\Admin
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EVF_Builder_Integrations', false ) ) {
	return new EVF_Builder_Integrations();
}

/**
 * EVF_Builder_Integrations class.
 */
class EVF_Builder_Integrations extends EVF_Builder_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id      = 'integrations';
		$this->label   = __( 'Integrations', 'everest-forms-pro' );
		$this->sidebar = true;

		parent::__construct();
	}

	/**
	 * Outputs the builder sidebar.
	 */
	public function output_sidebar() {
		$integrations = apply_filters( 'everest_forms_available_integrations', array() );

		if ( ! empty( $integrations ) ) {
			foreach ( $integrations as $integration ) {
				$this->add_sidebar_tab( $integration['name'], $integration['id'], $integration['icon'], $this->id );

				do_action( 'everest_forms_integration_connections_' . $integration['id'], $integration );
			}
		}
	}

	/**
	 * Outputs the builder content.
	 */
	public function output_content() {
		$providers_active = apply_filters( 'everest_forms_available_integrations', array() );

		if ( empty( $providers_active ) ) {
			echo '<div class="evf-panel-content-section evf-panel-content-section-info">';
			echo '<h5>' . esc_html__( 'Install Your Addons', 'everest-forms-pro' ) . '</h5>';
			echo '<p>' . sprintf(
				wp_kses(
					/* translators: %s - plugin admin area Addons page. */
					__( 'It seems you do not have any addons activated that needs integration. You can head over to the <a href="%s">Addons page</a> to install and activate the addon.', 'everest-forms-pro' ),
					array( 'a' => array( 'href' => array() ) )
				),
				esc_url( admin_url( 'admin.php?page=evf-addons' ) )
			) . '</p>';
			echo '</div>';
		} else {
			do_action( 'everest_forms_providers_panel_content', $this->form );
			wp_localize_script(
				'everest-forms-integrations-scripts',
				'evf_integration_data',
				isset( $this->form_data['integrations'] ) ? $this->form_data['integrations'] : array()
			);
		}
	}
}

return new EVF_Builder_Integrations();
