<?php
/**
 * Country field
 *
 * @package EverestForms_Pro\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Country Class.
 */
class EVF_Field_Country extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Country', 'everest-forms-pro' );
		$this->type     = 'country';
		$this->icon     = 'evf-icon evf-icon-flag';
		$this->order    = 120;
		$this->group    = 'advanced';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
					'required_field_message',
					'enable_country_flag',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'placeholder',
					'default_country',
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
		add_filter( 'everest_forms_html_field_value', array( $this, 'field_value' ), 10, 4 );
		add_filter( 'everest_forms_plaintext_field_value', array( $this, 'field_value' ), 10, 4 );
		add_filter( 'everest_forms_field_exporter_' . $this->type, array( $this, 'field_exporter' ) );
	}

	/**
	 * Enable country flag field option.
	 *
	 * @param array $field Field Data.
	 */
	public function enable_country_flag( $field ) {
		$value   = isset( $field['enable_country_flag'] ) ? $field['enable_country_flag'] : '0';
		$tooltip = esc_html__( 'Check this option to show country flag.', 'everest-forms-pro' );

		// Enabled country flag.
		$enable_country_flag = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'enable_country_flag',
				'value'   => $value,
				'desc'    => esc_html__( 'Enable Country Flag', 'everest-forms' ),
				'tooltip' => $tooltip,
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'enable_country_flag',
				'content' => $enable_country_flag,
			)
		);
	}

	/**
	 * Default country.
	 *
	 * @param array $field Field Data.
	 */
	public function default_country( $field ) {
		$lbl  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'default',
				'value'   => esc_html__( 'Default Country', 'everest-forms-pro' ),
				'tooltip' => sprintf( esc_html__( 'Enter the country to be displayed on frontend.', 'everest-forms-pro' ) ),
			),
			false
		);
		$fld  = $this->field_element(
			'select',
			$field,
			array(
				'slug'    => 'default',
				'value'   => ! empty( $field['default'] ) ? $field['default'] : '',
				'options' => array_merge(
					array(
						'' => esc_html__( '--- Select a country ---', 'everest-forms-pro' ),
					),
					evf_get_countries()
				),
			),
			false
		);
		$args = array(
			'slug'    => 'default',
			'content' => $lbl . $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Customize format for field value.
	 *
	 * @param  string $val       Field value.
	 * @param  array  $field_val Field settings.
	 * @param  array  $form_data Form data.
	 * @param  string $context   Value display context.
	 * @return string $val       Formatted country name.
	 */
	public function field_value( $val, $field_val, $form_data = array(), $context = '' ) {
		$countries = evf_get_countries();

		if ( is_serialized( $field_val ) || in_array( $context, array( 'email-plain', 'email-html', 'export-csv', 'export-pdf' ), true ) ) {
			$value = maybe_unserialize( $field_val );

			if ( isset( $value['type'], $value['country_code'] ) && $value['type'] === $this->type ) {
				$country_code = $value['country_code'];

				if ( isset( $countries[ $country_code ] ) ) {
					return $countries[ $country_code ];
				}
			}
		}

		return $val;
	}

	/**
	 * Filter callback for outputting formatted data.
	 *
	 * @param array $field Field Data.
	 */
	public function field_exporter( $field ) {
		$countries    = evf_get_countries();
		$country_code = false;

		if ( ! empty( $field['value']['country_code'] ) ) {
			$country_code = isset( $countries[ $field['value']['country_code'] ] ) ? $countries[ $field['value']['country_code'] ] : $field['value']['country_code'] . '.';
		}

		return array(
			'label' => ! empty( $field['name'] ) ? $field['name'] : ucfirst( str_replace( '_', ' ', $field['type'] ) ) . " - {$field['id']}",
			'value' => ! empty( $field['value']['country'] ) ? $field['value']['country'] : $country_code,
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {
		$countries = evf_get_countries();
		$default   = isset( $field['default'] ) ? $field['default'] : '';

		// Label.
		$this->field_preview_option( 'label', $field );

		// Field select element.
		echo '<select class="widefat" disabled>';

		// Optional placeholder.
		if ( empty( $default ) ) {
			printf( '<option value="" class="placeholder">%s</option>', ! empty( $field['placeholder'] ) ? esc_html( $field['placeholder'] ) : esc_html__( '--- Select a country ---', 'everest-forms-pro' ) );
		}

		foreach ( $countries as $country_code => $country_name ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $country_code ), selected( $country_code, $default, false ), esc_html( $country_name ) );
		}

		echo '</select>';

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
		// Setup and sanitize the necessary data.
		$primary       = $field['properties']['inputs']['primary'];
		$field         = apply_filters( 'everest_forms_select_field_display', $field, $field_atts, $form_data );
		$field_default = ! empty( $field['default'] ) ? esc_attr( $field['default'] ) : '';
		$countries     = evf_get_countries();

		// Enable country flag.
		if ( isset( $field['enable_country_flag'] ) ) {
			$primary['class'][] = esc_attr( 'evf-country-flag-selector' );

			if ( empty( $field['placeholder'] ) ) {
				$primary['data']['placeholder'] = esc_html__( '--- Select a country ---', 'everest-forms-pro' );
			}
		}

		// Primary select field.
		printf(
			'<select  %s %s>',
			evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $primary['required'] )
		);

		$field_placeholder = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';

		// Optional placeholder.
		if ( empty( $field_default ) ) {
			printf( '<option value="" class="placeholder" disabled selected>%s</option>', ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_html__( '--- Select a country ---', 'everest-forms-pro' ) );
		}

		foreach ( $countries as $country_code => $country_name ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $country_code ), selected( $country_code, $field_default, false ), esc_html( $country_name ) );

		}

		echo '</select>';
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @param string $field_id Field Id.
	 * @param array  $field_submit Submitted Field.
	 * @param array  $form_data All Form Data.
	 * @param string $meta_key Field Meta Key.
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {
		$name = ! empty( $form_data['form_fields'][ $field_id ]['label'] ) ? make_clickable( $form_data['form_fields'][ $field_id ]['label'] ) : '';

		// Set final field details.
		EVF()->task->form_fields[ $field_id ] = array(
			'name'     => $name,
			'value'    => array(
				'type'         => $this->type,
				'country_code' => sanitize_text_field( $field_submit ),
			),
			'id'       => $field_id,
			'type'     => $this->type,
			'meta_key' => $meta_key,
		);
	}
}
