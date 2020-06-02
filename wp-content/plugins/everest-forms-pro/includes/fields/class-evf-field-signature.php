<?php
/**
 * Signature Field
 *
 * @package EverestForms_Pro\Fields
 * @since   1.2.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Signature Class.
 */
class EVF_Field_Signature extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Signature', 'everest-forms-pro' );
		$this->type     = 'signature';
		$this->icon     = 'evf-icon evf-icon-signature';
		$this->order    = 100;
		$this->group    = 'advanced';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
					'required_field_message',
					'option_display',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'label_hide',
					'css',
				),
			),
		);

		$this->defaults_option_value = array(
			'background_color' => 'rgb(255, 255, 255)',
			'pen_color'        => 'rgba(0,0,0,1)',
			'image_format'     => 'png',
		);

		parent::__construct();
	}

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_action( 'everest_forms_frontend_output', array( $this, 'enqueue_frontend_scripts' ), 10, 1 );
		add_filter( 'everest_forms_process_after_filter', array( $this, 'signature_upload' ), 10, 3 );
		add_filter( 'everest_forms_html_field_value', array( $this, 'render_image_file' ), 10, 4 );
		add_filter( 'everest_forms_plaintext_field_value', array( $this, 'plaintext_field_value' ), 10, 4 );
		add_filter( 'everest_forms_field_exporter_' . $this->type, array( $this, 'field_exporter' ) );
	}

	/**
	 * Frontend Enqueue scripts for signature field.
	 */
	public function enqueue_frontend_scripts() {
		$data   = $this->defaults_option_value;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts.
		wp_enqueue_script( 'signature-pad', plugins_url( '/assets/js/signature_pad/signature_pad.umd.js', EFP_PLUGIN_FILE ), EFP_VERSION, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script( 'everest-forms-signature', plugins_url( "/assets/js/frontend/signature{$suffix}.js", EFP_PLUGIN_FILE ), array( 'signature-pad' ), EFP_VERSION, true );
		wp_localize_script( 'everest-forms-signature', 'evf_signature_params', $data );
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

		echo "<canvas style='width:100%;height:100px;max-width:100%;max-height:100%;'></canvas>";

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Option display in sidebar.
	 *
	 * @param array $field Field Data.
	 */
	public function option_display( $field ) {
		$file_format   = $this->defaults_option_value['image_format'];
		$field_options = sprintf( '<input type="hidden"  name="form_fields[%s][signature_file_format]" value="%s" />', $field['id'], $file_format );

		// Field option row (markup) including label and input.
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'signature',
				'content' => $field_options,
			)
		);
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
		$primary           = $field['properties']['inputs']['primary'];
		$conditional_id    = isset( $field['properties']['inputs']['primary']['attr']['conditional_id'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_id'] : '';
		$conditional_rules = isset( $field['properties']['inputs']['primary']['attr']['conditional_rules'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_rules'] : '';
		printf( '<div id="everest_form_signature_canvas_%s" class="everest_form_signature_canvas-wrap" data-image-format="%s" data-form-id="%s" data-field-id="%s" >', esc_html( $field['id'] ), esc_html( $field['signature_file_format'] ), esc_html( $form_data['id'] ), esc_html( $field['id'] ) );
		printf( "<canvas id='evf-signature-canvas-%s' class='evf-signature-canvas' style='width:%s;height:200px;max-width:%s;max-height:%s;' ></canvas>", esc_html( $field['id'] ), '100%', '100%', '100%' );
		printf(
			'<input type="hidden" id="evf-signature-img-input-%s" class="input-text" name="everest_forms[form_fields][%s][signature_image]" conditional_rules="%s" conditional_id="%s" value="" %s / > ',
			esc_html( $field['id'] ),
			esc_html( $field['id'] ),
			esc_attr( $conditional_rules ),
			esc_attr( $conditional_id ),
			esc_attr( $primary['required'] )
		);
		printf( ' <a href="JavaScript:void(0);" title="%s" style="text-decoration: none;" id="everest-form-signature-reset-%s" class="evf-signature-reset"><span class="dashicons dashicons-no-alt"></span> </a> ', esc_attr__( 'Clear Signature', 'everest-forms-pro' ), esc_html( $field['id'] ) );
		printf( '</div>' );
	}

	/**
	 * Validates signature field.
	 *
	 * @param int   $field_id Field Id.
	 * @param array $field_submit Submitted Field.
	 * @param array $form_data Form Data.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		$field_type     = isset( $form_data['form_fields'][ $field_id ]['type'] ) ? $form_data['form_fields'][ $field_id ]['type'] : '';
		$field_required = isset( $form_data['form_fields'][ $field_id ]['required'] ) ? $form_data['form_fields'][ $field_id ]['required'] : '';

		if ( empty( $field_required ) ) {
			return;
		}

		$file        = isset( $field_submit['signature_image'] ) ? $field_submit['signature_image'] : '';
		$file_format = $form_data['form_fields'][ $field_id ]['signature_file_format'];
		$check_str   = "data:image/{$file_format};base64";

		if ( empty( $file ) ) {
			$validation_text = evf_get_required_label();
			update_option( 'evf_validation_error', 'yes' );
		} elseif ( false === strpos( $file, $check_str ) ) {
			$validation_text = esc_html__( 'Invalid signature image format.', 'everest-forms-pro' );
			update_option( 'evf_validation_error', 'yes' );
		}

		if ( isset( $validation_text ) ) {
			EVF()->task->errors[ $form_data['id'] ][ $field_id ] = apply_filters( 'everest_forms_type_validation', $validation_text );
			update_option( 'evf_validation_error', 'yes' );
		}
	}

	/**
	 * Function to convert blob into image file and save it to entry.
	 *
	 * @param array $form_fields Form fields Data.
	 * @param array $entry       Form Entry Data.
	 * @param array $form_data   Form Data Object.
	 *
	 * @return $form_fields Form Fields Data.
	 */
	public function signature_upload( $form_fields, $entry, $form_data ) {
		$img_num = 1;
		foreach ( $form_fields as $key => $field ) {

			if ( isset( $field['type'] ) && 'signature' === $field['type'] ) {

				$img_num++;

				// Define data.
				$uploads                    = wp_upload_dir();
				$form_id                    = absint( $form_data['id'] );
				$evf_uploads_root           = trailingslashit( $uploads['basedir'] ) . 'everest_forms_uploads';
				$signature_upload_directory = trailingslashit( $evf_uploads_root . '/' . $form_id ) . 'signature';

				// Check for form upload directory destination.
				if ( ! file_exists( $signature_upload_directory ) ) {
					wp_mkdir_p( $signature_upload_directory );
				}

				// Check if the index.html exists in the root uploads director, if not create it.
				if ( ! file_exists( trailingslashit( $signature_upload_directory ) . 'index.html' ) ) {
					file_put_contents( trailingslashit( $signature_upload_directory ) . 'index.html', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				}

				// Create Image from blob and save it.
				$data_uri          = $field['value'];
				$file_format       = isset( $form_data['form_fields'][ $field['id'] ]['signature_file_format'] ) ? $form_data['form_fields'][ $field['id'] ]['signature_file_format'] : 'png';
				$check_file_format = "data:image/{$file_format};base64,";
				if ( false !== strpos( $field['value'], $check_file_format ) ) {
					$encoded_image = str_replace( $check_file_format, '', $data_uri );
					$decoded_image = base64_decode( $encoded_image ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
					$file          = trailingslashit( $signature_upload_directory ) . 'signature_' . time() . '-' . $img_num . ".{$file_format}";
					file_put_contents( $file, $decoded_image ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
					$form_fields[ $key ]['value'] = $file;
				}
			}
		}
		return $form_fields;
	}

	/**
	 * Display Image file on entries
	 *
	 * @param array  $val Entry Value.
	 * @param array  $field_val Field Value.
	 * @param array  $form_data Form Data.
	 * @param string $context   Field Context.
	 *
	 * @return $val Return Signature Image Tag.
	 */
	public function render_image_file( $val, $field_val, $form_data = array(), $context = '' ) {
		$uploads = wp_upload_dir();

		if ( ! is_array( $field_val ) && false !== strpos( $field_val, $uploads['basedir'] ) && 'image/png' === mime_content_type( $field_val ) ) {
			$img_url = trailingslashit( content_url() ) . str_replace( str_replace( 'uploads', '', $uploads['basedir'] ), '', $field_val );
			$file    = $uploads['basedir'] . str_replace( '/uploads/', '/', str_replace( content_url(), '', $img_url ) );

			if ( in_array( $context, array( 'email-html', 'export-csv', 'export-pdf' ), true ) ) {
				if ( 'export-pdf' === $context ) {
					$val = sprintf( '<img src="%s" style="width:200px;height=100px;" />', $file );
					return $val;
				} else {
					$val = sprintf( '<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>', esc_url( $img_url ), __( 'Signature', 'everest-forms-pro' ) );
				}
			} else {
				$val = sprintf( '<img src="%s" style="width:300px;height=150px;max-height:%s;max-width:%s;"  />', esc_url( $img_url ), '100%', '100%' );
			}
			return $val;
		} else {
			return $val;
		}
	}

	/**
	 * Customize format for Plain field value.
	 *
	 * @param  string $val       Value of the field in plain format.
	 * @param  array  $field_val Field value object.
	 * @param  array  $form_data Form data object.
	 * @param  string $context   Context string.
	 * @return string $val       Value returned.
	 */
	public function plaintext_field_value( $val, $field_val, $form_data = array(), $context = '' ) {
		$uploads = wp_upload_dir();
		if ( ! is_array( $field_val ) && strpos( $field_val, $uploads['basedir'] ) !== false && 'image/png' === mime_content_type( $field_val ) ) {
			if ( 'email-plain' === $context ) {
				$img_url = trailingslashit( content_url() ) . str_replace( str_replace( 'uploads', '', $uploads['basedir'] ), '', $field_val );
				return esc_url( $img_url ) . "\r\n\r\n";
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
		$value = '';

		if ( ! empty( $field['value'] ) ) {
			$path      = file_get_contents( $field['value'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$signature = tempnam( sys_get_temp_dir(), 'prefix' );
			file_put_contents( $signature, $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

			$value = sprintf(
				'<img src="%s" style="width:150px;height:80px;max-height:200px;max-width:100px;"/>',
				$signature
			);
		}

		return array(
			'label' => ! empty( $field['name'] ) ? $field['name'] : ucfirst( str_replace( '_', ' ', $field['type'] ) ) . " - {$field['id']}",
			'value' => ! empty( $value ) ? $value : false,
		);
	}
}
