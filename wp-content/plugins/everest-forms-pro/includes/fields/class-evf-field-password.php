<?php
/**
 * Password field.
 *
 * @package EverestForms_Pro\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Password Class.
 */
class EVF_Field_Password extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Password', 'everest-forms-pro' );
		$this->type     = 'password';
		$this->icon     = 'evf-icon evf-icon-password';
		$this->order    = 70;
		$this->group    = 'advanced';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
					'required_field_message',
					'confirmation',
					'password_strength',
					'show_hide_password',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'placeholder',
					'confirmation_placeholder',
					'label_hide',
					'sublabel_hide',
					'default_value',
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
		add_filter( 'everest_forms_builder_field_option_class', array( $this, 'field_option_class' ), 10, 2 );
	}

	/**
	 * Confirmation field option.
	 *
	 * @param array $field Field Data.
	 */
	public function confirmation( $field ) {
		$confirm_field = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'confirmation',
				'value'   => isset( $field['confirmation'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Enable Password Confirmation', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check to enable password confirmation.', 'everest-forms-pro' ),
			),
			false
		);

		$args = array(
			'slug'    => 'confirmation',
			'content' => $confirm_field,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Password Meter field option.
	 *
	 * @param array $field Field Data.
	 */
	public function password_strength( $field ) {
		$class = isset( $field['password_strength'] ) && '1' === $field['password_strength'] ? 'everest-forms-visible' : 'everest-forms-hidden';

		$password_strength = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'password_strength',
				'value'   => isset( $field['password_strength'] ) ? $field['password_strength'] : '0',
				'desc'    => esc_html__( 'Enable Password Strength Meter', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check to enable password strength meter.', 'everest-forms-pro' ),
			),
			false
		);

		$password_bar_choice = $this->field_element(
			'radio',
			$field,
			array(
				'slug'    => 'password_bar',
				'default' => isset( $field['password_bar'] ) ? esc_attr( $field['password_bar'] ) : 'default',
				'desc'    => null,
				'options' => array(
					'default'  => esc_html__( 'Simple Text', 'everest-forms-pro' ),
					'progress' => esc_html__( 'Progress Bar', 'everest-forms-pro' ),
				),
			),
			false
		);

		$args = array(
			'slug'    => 'password_strength',
			'content' => $password_strength . '<div class="everest-forms-inner-options ' . $class . '">' . $password_bar_choice . '</div>',
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Show and Hide password.
	 *
	 * @param array $field Field Data.
	 */
	public function show_hide_password( $field ) {
		$fld = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'show_hide_password',
				'value'   => isset( $field['show_hide_password'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Enable Show and Hide Password', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check to enable password visibility toggle.', 'everest-forms-pro' ),
			),
			false
		);

		$args = array(
			'slug'    => 'show_hide_password',
			'content' => $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Confirmation Placeholder field option.
	 *
	 * @param array $field Field Data.
	 */
	public function confirmation_placeholder( $field ) {
		$lbl  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'confirmation_placeholder',
				'value'   => esc_html__( 'Confirmation Placeholder Text', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Enter text for the confirmation field placeholder.', 'everest-forms-pro' ),
			),
			false
		);
		$fld  = $this->field_element(
			'text',
			$field,
			array(
				'slug'  => 'confirmation_placeholder',
				'value' => ! empty( $field['confirmation_placeholder'] ) ? esc_attr( $field['confirmation_placeholder'] ) : '',
			),
			false
		);
		$args = array(
			'slug'    => 'confirmation_placeholder',
			'content' => $lbl . $fld,
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
		if ( isset( $field['password_strength'], $field['password_bar'] ) ) {
			if ( 'default' === $field['password_bar'] ) {
				$properties['inputs']['primary']['data']['strength'] = 'meter';
			} else {
				$properties['inputs']['primary']['data']['strength'] = 'progress';
			}
		}

		if ( empty( $field['confirmation'] ) ) {
			return $properties;
		}

		$form_id  = absint( $form_data['id'] );
		$field_id = $field['id'];

		// Password confirmation setting enabled.
		$props      = array(
			'inputs' => array(
				'primary'   => array(
					'block'    => array(
						'everest-forms-field-row-block',
						'everest-forms-one-half',
						'everest-forms-first',
					),
					'class'    => array(
						'everest-forms-field-password-primary',
					),
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => esc_html__( 'Password', 'everest-forms-pro' ),
					),
				),
				'secondary' => array(
					'attr'     => array(
						'name'        => "everest_forms[form_fields][{$field_id}][secondary]",
						'value'       => '',
						'placeholder' => ! empty( $field['confirmation_placeholder'] ) ? evf_string_translation( $form_id, $field_id, $field['confirmation_placeholder'], '-confirm-placeholder' ) : '',
					),
					'block'    => array(
						'everest-forms-field-row-block',
						'everest-forms-one-half',
					),
					'class'    => array(
						'input-text',
						'everest-forms-field-password-secondary',
					),
					'data'     => array(
						'rule-confirm' => '#' . $properties['inputs']['primary']['id'],
					),
					'id'       => "evf-{$form_id}-field_{$field_id}-secondary",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => esc_html__( 'Confirm Password', 'everest-forms-pro' ),
					),
					'value'    => '',
				),
			),
		);
		$properties = array_merge_recursive( $properties, $props );

		// Input Primary: adjust name.
		$properties['inputs']['primary']['attr']['name'] = "everest_forms[form_fields][{$field_id}][primary]";

		// Input Primary: remove size and error classes.
		$properties['inputs']['primary']['class'] = array_diff(
			$properties['inputs']['primary']['class'],
			array(
				'evf-error',
			)
		);

		// Input Primary: add error class if needed.
		if ( ! empty( $properties['error']['value']['primary'] ) ) {
			$properties['inputs']['primary']['class'][] = 'evf-error';
		}

		// Input Secondary: add error class if needed.
		if ( ! empty( $properties['error']['value']['secondary'] ) ) {
			$properties['inputs']['secondary']['class'][] = 'evf-error';
		}

		// Input Secondary: add required class if needed.
		if ( ! empty( $field['required'] ) ) {
			$properties['inputs']['secondary']['class'][] = 'evf-field-required';
		}

		return $properties;
	}

	/**
	 * Add class to field options wrapper to indicate if field confirmation is enabled.
	 *
	 * @param  array $class Field class.
	 * @param  array $field Field option data.
	 * @return array
	 */
	public function field_option_class( $class, $field ) {
		if ( 'password' === $field['type'] ) {
			if ( isset( $field['confirmation'] ) ) {
				$class[] = 'everest-forms-confirm-enabled';
			} else {
				$class[] = 'everest-forms-confirm-disabled';
			}
		}

		return $class;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {
		$placeholder         = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
		$confirm_placeholder = ! empty( $field['confirmation_placeholder'] ) ? esc_attr( $field['confirmation_placeholder'] ) : '';
		$confirm             = ! empty( $field['confirmation'] ) ? 'enabled' : 'disabled';
		$visibility_class    = ! empty( $field['show_hide_password'] ) ? '' : 'everest-forms-hidden';

		// Label.
		$this->field_preview_option( 'label', $field );
		?>
		<div class="everest-forms-confirm everest-forms-confirm-<?php echo esc_attr( $confirm ); ?>">
			<div class="everest-forms-confirm-primary">
				<div class="evf-field-password-input">
					<input type="password" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="widefat primary-input" disabled>
					<span class="dashicons dashicons-hidden toggle-password <?php echo esc_attr( $visibility_class ); ?>"></span>
				</div>
				<label class="everest-forms-sub-label"><?php esc_html_e( 'Password', 'everest-forms-pro' ); ?></label>
			</div>
			<div class="everest-forms-confirm-confirmation">
				<div class="evf-field-password-input">
					<input type="password" placeholder="<?php echo esc_attr( $confirm_placeholder ); ?>" class="widefat secondary-input" disabled>
					<span class="dashicons dashicons-hidden toggle-password <?php echo esc_attr( $visibility_class ); ?>" ></span>
				</div>
				<label class="everest-forms-sub-label"><?php esc_html_e( 'Confirm Password', 'everest-forms-pro' ); ?></label>
			</div>
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
		// Define data.
		$confirmation = ! empty( $field['confirmation'] );
		$primary      = $field['properties']['inputs']['primary'];
		$secondary    = ! empty( $field['properties']['inputs']['secondary'] ) ? $field['properties']['inputs']['secondary'] : array();

		// Standard password field.
		if ( ! $confirmation ) {
			echo '<div class="evf-field-password-input">';
			printf(
				'<input type="password" %s %s>',
				evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
				esc_attr( $primary['required'] )
			);
			if ( ! empty( $field['show_hide_password'] ) ) {
				printf( '<span toggle="#%s" class="dashicons dashicons-hidden toggle-password"></span>', esc_attr( $primary['id'] ) );
			}
			echo '</div>';
		} else {
			// Row wrapper.
			echo '<div class="everest-forms-field-row">';

			// Primary field.
			echo '<div ' . evf_html_attributes( false, $primary['block'] ) . '>';
			$this->field_display_sublabel( 'primary', 'before', $field );
			echo '<div class="evf-field-password-input primary">';
			printf(
				'<input type="password" %s %s>',
				evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
				esc_attr( $primary['required'] )
			);
			if ( ! empty( $field['show_hide_password'] ) ) {
				printf( '<span toggle="#%s" class="dashicons dashicons-hidden toggle-password"></span>', esc_attr( $primary['id'] ) );
			}
			echo '</div>';
			$this->field_display_sublabel( 'primary', 'after', $field );
			$this->field_display_error( 'primary', $field );
			echo '</div>';

			// Secondary field.
			echo '<div ' . evf_html_attributes( false, $secondary['block'] ) . '>';
			$this->field_display_sublabel( 'secondary', 'before', $field );
			echo '<div class="evf-field-password-input secondary">';
			printf(
				'<input type="password" %s %s>',
				evf_html_attributes( $secondary['id'], $secondary['class'], $secondary['data'], $secondary['attr'] ),
				esc_attr( $secondary['required'] )
			);
			if ( ! empty( $field['show_hide_password'] ) ) {
				printf( '<span toggle="#%s" class="dashicons dashicons-hidden toggle-password"></span>', esc_attr( $secondary['id'] ) );
			}
			echo '</div>';
			$this->field_display_sublabel( 'secondary', 'after', $field );
			$this->field_display_error( 'secondary', $field );
			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Validates field on form submit.
	 *
	 * @param string $field_id Field Id.
	 * @param array  $field_submit Submitted Data.
	 * @param array  $form_data All Form Data.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		$form_id            = $form_data['id'];
		$fields             = $form_data['form_fields'];
		$required           = evf_get_required_label();
		$conditional_status = isset( $form_data['form_fields'][ $field_id ]['conditional_logic_status'] ) ? $form_data['form_fields'][ $field_id ]['conditional_logic_status'] : 0;

		// Standard configuration, confirmation disabled.
		if ( empty( $fields[ $field_id ]['confirmation'] ) ) {

			// Required check.
			if ( ! empty( $fields[ $field_id ]['required'] ) && '1' !== $conditional_status && ( empty( $field_submit ) && '0' !== $field_submit ) ) {
				evf()->task->errors[ $form_id ][ $field_id ] = $required;
				update_option( 'evf_validation_error', 'yes' );
			}
		} else {

			// Required check.
			if ( ! empty( $fields[ $field_id ]['required'] ) && '1' !== $conditional_status && ( empty( $field_submit['primary'] ) && '0' !== $field_submit ) ) {
				evf()->task->errors[ $form_id ][ $field_id ]['primary'] = $required;
				update_option( 'evf_validation_error', 'yes' );
			}

			// Required check, secondary confirmation field.
			if ( ! empty( $fields[ $field_id ]['required'] ) && '1' !== $conditional_status && ( empty( $field_submit['secondary'] ) && '0' !== $field_submit ) ) {
				evf()->task->errors[ $form_id ][ $field_id ]['secondary'] = $required;
				update_option( 'evf_validation_error', 'yes' );
			}

			// Fields need to match.
			if ( isset( $field_submit['primary'] ) && isset( $field_submit['secondary'] ) ) {
				if ( $field_submit['primary'] !== $field_submit['secondary'] ) {
					evf()->task->errors[ $form_id ][ $field_id ]['secondary'] = esc_html__( 'Field values do not match.', 'everest-forms-pro' );
					update_option( 'evf_validation_error', 'yes' );
				}
			}
		}
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @param int    $field_id     Field ID.
	 * @param array  $field_submit Submitted field value.
	 * @param array  $form_data    Form data and settings.
	 * @param string $meta_key     Field meta key.
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {
		// Define data.
		if ( is_array( $field_submit ) ) {
			$value = ! empty( $field_submit['primary'] ) ? $field_submit['primary'] : '';
		} else {
			$value = ! empty( $field_submit ) ? $field_submit : '';
		}

		$name = ! empty( $form_data['form_fields'][ $field_id ]['label'] ) ? $form_data['form_fields'][ $field_id ]['label'] : '';

		// Set final field details.
		evf()->task->form_fields[ $field_id ] = array(
			'name'     => make_clickable( $name ),
			'value'    => sanitize_text_field( $value ),
			'id'       => absint( $field_id ),
			'type'     => $this->type,
			'meta_key' => $meta_key,
		);
	}
}
