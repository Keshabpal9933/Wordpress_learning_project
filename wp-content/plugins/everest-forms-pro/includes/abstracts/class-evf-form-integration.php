<?php
/**
 * Abstract EVF_APP_Integration class.
 *
 * @package EverestForms/Classes
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Form_Integration class.
 */
abstract class EVF_Form_Integration {

	/**
	 * Integration ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Integration name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Integration icon.
	 *
	 * @var  mixed
	 */
	public $icon = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'everest_forms_available_integrations', array( $this, 'register_integration' ) );
	}

	/**
	 * Register integration.
	 *
	 * @param  array $integrations List of integrations.
	 * @return array of registered integrations.
	 */
	public function register_integration( $integrations ) {
		$integrations[ $this->id ] = array(
			'id'   => $this->id,
			'icon' => $this->icon,
			'name' => $this->name,
		);

		return $integrations;
	}

	/**
	 * Conditional Logic facilitator function.
	 *
	 * @param string $connection_id Defines the connection for the conditional logic.
	 * @param array  $connection    Parameter var for conditional.
	 * @param array  $form_data     Form data.
	 */
	public function conditional_logic( $connection_id = '', $connection = array(), $form_data = '' ) {

		$selected_logic           = ! empty( $connection['conditional_logic']['status'] ) ? $connection['conditional_logic']['status'] : '';
		$selected_field_select    = ! empty( $connection['conditional_logic']['field_select'] ) ? $connection['conditional_logic']['field_select'] : '';
		$selected_condition       = ! empty( $connection['conditional_logic']['condition'] ) ? $connection['conditional_logic']['condition'] : '';
		$selected_input_choice    = ! empty( $connection['conditional_logic']['input_choice'] ) ? $connection['conditional_logic']['input_choice'] : '';
		$selected_multiple_choice = ! empty( $connection['conditional_logic']['multiple_choice'] ) ? $connection['conditional_logic']['multiple_choice'] : '';
		$selected_country_choice  = ! empty( $connection['conditional_logic']['country_choice'] ) ? $connection['conditional_logic']['country_choice'] : '';

		$output                  = '<div class="everest-forms-panel-field evf-provider-conditional evf-connection-block">';
			$output             .= sprintf(
				'<label for="%s_conditional_logic"><input id="%s_conditional_logic" class="evf-enable-conditional-logic" type="checkbox" value="1" name="integrations[%s][%s][conditional_logic][status]" %s>%s</label>',
				esc_attr( $this->id ),
				esc_attr( $this->id ),
				esc_attr( $connection_id ),
				checked( ! empty( $connection['conditional_logic']['status'] ), true, false ),
				esc_attr( $connection_id ),
				__( 'Use conditional logic', 'everest-forms-pro' )
			);
			$output             .= '<div class="evf-conditional-container survey-option everest-forms-border-container" data-con_id="' . $connection_id . '" data-source="' . $this->id . '">';
				$output         .= '<h4 class="everest-forms-border-container-title">' . __( 'Conditional Rules', 'everest-forms-pro' ) . '</h4>';
				$output         .= '<div class="evf-logic"><p>Send data only if the following matches.</p>';
				$output         .= '</div>';
				$output         .= '<div class="evf-conditional-wrapper">';
					$output     .= sprintf( '<select class="evf-conditional-field-select" name="integrations[%s][%s][conditional_logic][field_select]">', $this->id, $connection_id );
					$output     .= '</select>';
					$output     .= sprintf( '<select class="evf-conditional-condition" name="integrations[%s][%s][conditional_logic][condition]">', $this->id, $connection_id );
						$output .= '<option value = "is"  ' . selected( $selected_condition, 'is', false ) . '> is </option>';
						$output .= '<option value = "is_not" ' . selected( $selected_condition, 'is_not', false ) . '> is not </option>';
					$output     .= '</select>';
				$output         .= '</div>';
			$output             .= '</div>';
		$output                 .= '</div>';

		return $output;
	}
}
