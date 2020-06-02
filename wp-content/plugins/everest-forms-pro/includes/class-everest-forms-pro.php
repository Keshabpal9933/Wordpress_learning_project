<?php
/**
 * EverestForms Pro setup
 *
 * @package EverestForms_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main EverestForms Pro Class.
 *
 * @class EverestForms_Pro
 */
final class EverestForms_Pro {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.3.4.1';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with Everest Forms is installed.
		if ( defined( 'EVF_VERSION' ) && version_compare( EVF_VERSION, '1.6.0', '>=' ) ) {
			$this->define_constants();
			$this->includes();

			// Hooks.
			add_action( 'everest_forms_init', array( $this, 'plugin_updater' ) );
			add_filter( 'everest_forms_get_builder_pages', array( $this, 'load_builder_pages' ) );
			add_filter( 'everest_forms_get_settings_pages', array( $this, 'load_settings_pages' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_action( 'everest_forms_shortcode_scripts', array( $this, 'shortcode_scripts' ) );
			add_filter( 'everest_forms_builder_strings', array( $this, 'form_builder_strings' ) );
			add_filter( 'everest_forms_get_script_data', array( $this, 'form_script_data' ), 10, 2 );
			add_filter( 'everest_forms_get_settings_validation', array( $this, 'validation_settings' ) );

			// Entry actions.
			add_action( 'admin_init', array( $this, 'process_actions' ) );
			add_action( 'everest_forms_view_entries_notices', array( $this, 'entry_notices' ) );

			// Entry processing and setup.
			add_filter( 'everest_forms_entry_statuses', array( $this, 'entry_statuses' ) );
			add_filter( 'everest_forms_entry_bulk_actions', array( $this, 'entry_bulk_actions' ) );
			add_filter( 'everest_forms_entries_table_columns', array( $this, 'entries_table_columns' ), 10, 2 );
			add_action( 'everest_forms_entry_table_column_value', array( $this, 'entries_table_column_value' ), 10, 3 );
			add_action( 'everest_forms_after_entry_details_hndle', array( $this, 'add_starred_icon' ) );
			add_action( 'everest_forms_after_entry_details', array( $this, 'entry_details_actions' ), 10, 3 );
			add_filter( 'everest_forms_hidden_entry_fields', array( $this, 'entry_hidden_fields' ), 20 );
			add_action( 'everest_forms_after_entry_details', array( $this, 'payment_details_inside_entry' ), 10, 2 );
			add_action( 'everest_forms_process_validate_payment-single', array( $this, 'payment_single_validation' ), 10, 4 );
			add_action( 'everest_forms_process_validate_payment-multiple', array( $this, 'payment_multiple_validation' ), 10, 4 );

			// Row meta.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 20, 2 );

			// AJAX events.
			add_action( 'wp_ajax_everest_forms_entry_star', array( $this, 'ajax_entry_star' ) );
			add_action( 'wp_ajax_everest_forms_entry_read', array( $this, 'ajax_entry_read' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'everest_forms_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/everest-forms-pro/everest-forms-pro-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/everest-forms-pro-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'everest-forms-pro' );

		load_textdomain( 'everest-forms-pro', WP_LANG_DIR . '/everest-forms-pro/everest-forms-pro-' . $locale . '.mo' );
		load_plugin_textdomain( 'everest-forms-pro', false, plugin_basename( dirname( EFP_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		/**
		 * Abstract classes.
		 */
		include_once EFP_ABSPATH . 'includes/abstracts/class-evf-form-integration.php';
		include_once EFP_ABSPATH . 'includes/abstracts/class-evf-email-marketing.php';
		include_once EFP_ABSPATH . 'includes/abstracts/class-evf-form-fields-upload.php';
		include_once EFP_ABSPATH . 'includes/abstracts/class-evf-payments.php';

		/**
		 * Core classes.
		 */
		include_once EFP_ABSPATH . 'includes/payments/functions.php';
		include_once EFP_ABSPATH . 'includes/class-evf-conditional-logics.php';

		/**
		 * Additional fields.
		 */
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-html.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-title.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-phone.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-hidden.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-address.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-country.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-password.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-file-upload.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-image-upload.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-signature.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-rating.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-range-slider.php';

		/**
		 * Payment fields.
		 */
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-payment-single.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-payment-radio.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-payment-checkbox.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-payment-quantity.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-payment-total.php';
		include_once EFP_ABSPATH . 'includes/fields/class-evf-field-credit-card.php';
	}

	/**
	 * Plugin Updater.
	 */
	public function plugin_updater() {
		if ( class_exists( 'EVF_Plugin_Updater' ) ) {
			return EVF_Plugin_Updater::updates( EFP_PLUGIN_FILE, 3441, EFP_VERSION );
		}
	}

	/**
	 * Define EVF Constants.
	 */
	private function define_constants() {
		$this->define( 'EFP_ABSPATH', dirname( EFP_PLUGIN_FILE ) . '/' );
		$this->define( 'EFP_PLUGIN_BASENAME', plugin_basename( EFP_PLUGIN_FILE ) );
		$this->define( 'EFP_VERSION', self::VERSION );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load builder page.
	 *
	 * @param  array $builder Builder page.
	 * @return array of builder page.
	 */
	public function load_builder_pages( $builder ) {
		$builder[] = include_once EFP_ABSPATH . 'includes/builder/class-evf-builder-integrations.php';
		$builder[] = include_once EFP_ABSPATH . 'includes/builder/class-evf-builder-payments.php';

		return $builder;
	}

	/**
	 * Load settings page.
	 *
	 * @param  array $settings Settings page.
	 * @return array of settings page.
	 */
	public function load_settings_pages( $settings ) {
		$settings[] = include_once EFP_ABSPATH . 'includes/settings/class-evf-setting-payment.php';

		return $settings;
	}

	/**
	 * Admin Enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Range Slider Scripts.
		wp_register_style( 'ion-range-slider', plugins_url( '/assets/css/rangeSlider.css', EFP_PLUGIN_FILE ), array(), '2.3.1' );
		wp_register_script( 'ion-range-slider', plugins_url( '/assets/js/ion-range-slider/ion.rangeSlider' . $suffix . '.js', EFP_PLUGIN_FILE ), array( 'jquery' ), '2.3.1', true );

		// Register admin scripts.
		wp_register_style( 'everest-forms-pro-admin', plugins_url( '/assets/css/admin.css', EFP_PLUGIN_FILE ), array(), EFP_VERSION );
		wp_register_script( 'everest-forms-entries-scripts', plugins_url( "/assets/js/admin/everest-forms-pro-entries{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery' ), EFP_VERSION, true );
		wp_register_script( 'everest-forms-builder-scripts', plugins_url( "/assets/js/admin/everest-forms-pro-builder{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery', 'ion-range-slider' ), EFP_VERSION, true );
		wp_register_script( 'everest-forms-integrations-scripts', plugins_url( "/assets/js/admin/integration{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery', 'jquery-confirm' ), EFP_VERSION, true );
		wp_register_script( 'everest-forms-conditionals-scripts', plugins_url( "/assets/js/admin/conditional-logic{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery', 'jquery-confirm' ), EFP_VERSION, true );

		// Add RTL support for admin styles.
		wp_style_add_data( 'everest-forms-pro-admin', 'rtl', 'replace' );

		// Admin styles for EVF pages only.
		if ( in_array( $screen_id, evf_get_screen_ids(), true ) ) {
			wp_enqueue_style( 'everest-forms-pro-admin' );
			wp_localize_script(
				'everest-forms-payment-scripts',
				'evfpayment_payment_params',
				array(
					'i18n_payment_option_label' => __( 'Payment Options', 'everest-forms-pro' ),
					'i18n_only_paypal_gateway'  => __( 'Paypal is selected as payment gateway.', 'everest-forms-pro' ),
					'i18n_only_stripe_gateway'  => __( 'Stripe is selected as payment gateway.', 'everest-forms-pro' ),
					'i18n_empty_gateways'       => __( 'Please enable payment gateways.', 'everest-forms-pro' ),
				)
			);

			wp_enqueue_script( 'everest-forms-conditionals-scripts' );
			wp_enqueue_script( 'everest-forms-integrations-scripts' );
			wp_localize_script(
				'everest-forms-conditionals-scripts',
				'evf_conditional_rules',
				array(
					'i18n_remove_rule'         => esc_html__( 'Remove existing rules?', 'everest-forms-pro' ),
					'i18n_remove_rule_message' => esc_html__( 'Payment rule is mutually exclusive. Do you wish to remove other rules?', 'everest-forms-pro' ),
				)
			);

			wp_localize_script(
				'everest-forms-integrations-scripts',
				'evfp_params',
				array(
					'admin_url'               => admin_url(),
					'ajax_url'                => admin_url( 'admin-ajax.php', 'relative' ),
					'i18n_ok'                 => esc_html__( 'OK', 'everest-forms-pro' ),
					'i18n_close'              => esc_html__( 'Close', 'everest-forms-pro' ),
					'i18n_cancel'             => esc_html__( 'Cancel', 'everest-forms-pro' ),
					'ajax_nonce'              => wp_create_nonce( 'process-ajax-nonce' ),
					'form_id'                 => isset( $_GET['form_id'] ) ? wp_unslash( $_GET['form_id'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'i18n_confirm_save'       => esc_html__( 'We need to save your progress to continue to the Marketing panel. Is that OK?', 'everest-forms-pro' ),
					'i18n_confirm_connection' => esc_html__( 'Are you sure you want to delete this connection?', 'everest-forms-pro' ),
					'i18n_prompt_connection'  => esc_html__( 'Enter a %type% nickname', 'everest-forms-pro' ),
					'i18n_prompt_placeholder' => esc_html__( 'Eg: Newsletter Optin', 'everest-forms-pro' ),
					'i18n_error_name'         => esc_html__( 'You must provide a connection nickname', 'everest-forms-pro' ),
					'i18n_required_field'     => esc_html__( 'Field required', 'everest-forms-pro' ),
					'provider_auth_error'     => esc_html__( 'Could not authenticate with the provider.', 'everest-forms-pro' ),
					'required_field'          => esc_html__( 'Fields are required.', 'everest-forms-pro' ),
				)
			);

			if ( 'everest-forms_page_evf-entries' === $screen_id ) {
				wp_enqueue_script( 'everest-forms-entries-scripts' );
				wp_localize_script(
					'everest-forms-entries-scripts',
					'everest_forms_entries',
					array(
						'nonce'        => wp_create_nonce( 'everest-forms-entry' ),
						'ajax_url'     => admin_url( 'admin-ajax.php', 'relative' ),
						'entry_star'   => esc_html__( 'Star entry', 'everest-forms-pro' ),
						'entry_unstar' => esc_html__( 'Unstar entry', 'everest-forms-pro' ),
						'entry_read'   => esc_html__( 'Mark entry read', 'everest-forms-pro' ),
						'entry_unread' => esc_html__( 'Mark entry unread', 'everest-forms-pro' ),
					)
				);
			}

			// EverestForms builder pages.
			if ( in_array( $screen_id, array( 'everest-forms_page_evf-builder' ), true ) ) {
				wp_enqueue_style( 'ion-range-slider' );
				wp_enqueue_script( 'everest-forms-builder-scripts' );
				wp_localize_script(
					'everest-forms-builder-scripts',
					'everest_forms_builder',
					array(
						'i18n_field_rating_greater_than_max_value_error' => esc_html__( 'Please enter in a value less than 100.', 'everest-forms-pro' ),
					)
				);
			}
		}
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function shortcode_scripts( $atts ) {
		$form_data = evf()->form->get( $atts['id'], array( 'content_only' => true ) );

		if ( ! empty( $form_data['form_fields'] ) ) {
			$is_phone = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type'         => 'phone',
					'phone_format' => 'smart',
				)
			);

			$is_country = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type'                => 'country',
					'enable_country_flag' => 1,
				)
			);

			$is_address = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type'                => 'address',
					'enable_country_flag' => 1,
				)
			);

			$is_password = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type'              => 'password',
					'password_strength' => 1,
				)
			);

			$is_file_upload = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type' => 'file-upload',
				)
			);

			$is_image_upload = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type' => 'image-upload',
				)
			);

			if ( ! empty( $is_phone ) || ! empty( $is_country ) || ! empty( $is_address ) ) {
				wp_enqueue_style( 'jquery-intl-tel-input' );

				if ( ! empty( $is_phone ) ) {
					wp_enqueue_script( 'jquery-intl-tel-input' );
				}

				if ( ! empty( $is_country ) || ! empty( $is_address ) ) {
					wp_enqueue_style( 'select2' );
					wp_enqueue_script( 'selectWoo' );
				}
			}

			if ( ! empty( $is_password ) ) {
				wp_enqueue_script( 'evf-password-strength-meter' );
			}

			if ( ! empty( $is_file_upload ) || ! empty( $is_image_upload ) ) {
				wp_enqueue_script( 'everest-forms-file-upload' );
			}
		}
	}

	/**
	 * Append additional strings for form builder.
	 *
	 * @since 1.3.0
	 *
	 * @param array $strings List of strings.
	 *
	 * @return array
	 */
	public function form_builder_strings( $strings ) {
		$currency   = get_option( 'everest_forms_currency', 'USD' );
		$currencies = evf_get_currencies();

		$strings['currency']            = sanitize_text_field( $currency );
		$strings['currency_name']       = sanitize_text_field( $currencies[ $currency ]['name'] );
		$strings['currency_decimal']    = sanitize_text_field( $currencies[ $currency ]['decimal_separator'] );
		$strings['currency_thousands']  = sanitize_text_field( $currencies[ $currency ]['thousands_separator'] );
		$strings['currency_symbol']     = sanitize_text_field( $currencies[ $currency ]['symbol'] );
		$strings['currency_symbol_pos'] = sanitize_text_field( $currencies[ $currency ]['symbol_pos'] );

		return $strings;
	}

	/**
	 * Frontend Enqueue scripts.
	 */
	public function frontend_enqueue_scripts() {
		$strings = array();
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'everest-forms-pro-frontend', plugins_url( '/assets/css/everest-forms-pro-frontend.css', EFP_PLUGIN_FILE ), array(), EFP_VERSION );

		wp_enqueue_script( 'everest-forms-pro', plugins_url( "/assets/js/frontend/everest-forms-pro{$suffix}.js", EFP_PLUGIN_FILE ), array( 'everest-forms' ), EFP_VERSION, true );
		wp_enqueue_script( 'conditional-logic-builder', plugins_url( "/assets/js/frontend/conditional-logic-frontend{$suffix}.js", EFP_PLUGIN_FILE ), array( 'everest-forms' ), EFP_VERSION, true );
		wp_localize_script(
			'everest-forms-pro',
			'everest_forms_pro_params',
			array(
				'plugin_url' => plugin_dir_url( EFP_PLUGIN_FILE ),
			)
		);

		// Range Slider Scripts.
		wp_register_style( 'ion-range-slider', plugins_url( '/assets/css/rangeSlider.css', EFP_PLUGIN_FILE ), array(), EFP_VERSION );
		wp_register_script( 'ion-range-slider', plugins_url( "/assets/js/ion-range-slider/ion.rangeSlider{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery' ), '2.3.1', true );

		// Smart phone field scripts.
		wp_register_style( 'jquery-intl-tel-input', plugins_url( '/assets/css/intlTelInput.css', EFP_PLUGIN_FILE ), array(), EFP_VERSION );
		wp_register_script( 'jquery-intl-tel-input', plugins_url( "/assets/js/intlTelInput/jquery.intlTelInput{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery' ), '16.0.7', true );

		// Password strength meter scripts.
		wp_register_script( 'evf-password-strength-meter', plugins_url( "/assets/js/frontend/password-strength-meter{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery', 'password-strength-meter' ), EFP_VERSION, true );

		// File and image upload field scripts.
		wp_register_script( 'dropzone', plugins_url( "/assets/js/dropzone/dropzone{$suffix}.js", EFP_PLUGIN_FILE ), array( 'jquery' ), '5.5.0', true );
		wp_register_script( 'everest-forms-file-upload', plugins_url( "/assets/js/frontend/everest-forms-file-upload{$suffix}.js", EFP_PLUGIN_FILE ), array( 'dropzone', 'wp-util' ), EFP_VERSION, true );
		wp_localize_script(
			'everest-forms-file-upload',
			'everest_forms_upload_parms',
			array(
				'url'             => admin_url( 'admin-ajax.php' ),
				'errors'          => array(
					'file_not_uploaded' => esc_html__( 'This file was not uploaded.', 'everest-forms-pro' ),
					'file_limit'        => esc_html__( 'File limit has been reached ({fileLimit}).', 'everest-forms-pro' ),
					'file_extension'    => get_option( 'everest_forms_fileextension_validation' ),
					'file_size'         => get_option( 'everest_forms_filesize_validation' ),
					'post_max_size'     => sprintf(
						/* translators: %s: Max upload size */
						esc_html__( 'File exceeds the upload limit allowed (%s).', 'everest-forms-pro' ),
						evf_max_upload()
					),
				),
				'loading_message' => esc_html__( 'Do not submit the form until the upload process is finished', 'everest-forms-pro' ),
			)
		);

		if ( function_exists( 'evf_get_currencies' ) ) {
			$currency                       = get_option( 'everest_forms_currency', 'USD' );
			$currencies                     = evf_get_currencies();
			$strings['currency_code']       = $currency;
			$strings['currency_thousands']  = $currencies[ $currency ]['thousands_separator'];
			$strings['currency_decimal']    = $currencies[ $currency ]['decimal_separator'];
			$strings['currency_symbol']     = $currencies[ $currency ]['symbol'];
			$strings['currency_symbol_pos'] = $currencies[ $currency ]['symbol_pos'];
		}
		$strings = apply_filters( 'everest_forms_frontend_strings', $strings );

		foreach ( (array) $strings as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}
			$strings[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo 'var evf_settings = ' . wp_json_encode( $strings ) . "\n";
		echo "/* ]]> */\n";
		echo "</script>\n";

		do_action( 'everest_forms_footer_end' );
	}

	/**
	 * Load Gutenberg block scripts.
	 */
	public function enqueue_block_editor_assets() {
		if ( has_block( 'everest-forms/form-selector' ) ) {
			wp_enqueue_style( 'everest-forms-pro-frontend', plugins_url( '/assets/css/everest-forms-pro-frontend.css', EFP_PLUGIN_FILE ), array(), EFP_VERSION );
		}
	}

	/**
	 * Add new settings to the validation settings page.
	 *
	 * @since  1.0.0
	 * @param  mixed $settings array of settings.
	 * @return mixed
	 */
	public function validation_settings( $settings ) {
		$new_settings = array(
			array(
				'title'    => __( 'Phone Number', 'everest-forms-pro' ),
				'desc'     => __( 'Enter the message for valid phone number.', 'everest-forms-pro' ),
				'default'  => __( 'Please enter a valid phone number.', 'everest-forms-pro' ),
				'css'      => 'min-width: 350px;',
				'id'       => 'everest_forms_phone_validation',
				'type'     => 'text',
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'File Extension', 'everest-forms-pro' ),
				'desc'     => __( 'Enter the message for the allowed file extensions.', 'everest-forms-pro' ),
				'default'  => __( 'File type is not allowed.', 'everest-forms-pro' ),
				'css'      => 'min-width: 350px;',
				'id'       => 'everest_forms_fileextension_validation',
				'type'     => 'text',
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'File Size', 'everest-forms-pro' ),
				'desc'     => __( 'Enter the message for the max file size allowed.', 'everest-forms-pro' ),
				'default'  => __( 'File exceeds max size allowed.', 'everest-forms-pro' ),
				'id'       => 'everest_forms_filesize_validation',
				'css'      => 'min-width: 350px;',
				'type'     => 'text',
				'desc_tip' => true,
			),
		);

		// Add new settings to the existing ones.
		foreach ( $settings as $key => $setting ) {
			if ( isset( $setting['id'] ) && 'everest_forms_number_validation' === $setting['id'] ) {
				array_splice( $settings, $key + 1, 0, $new_settings );
				break;
			}
		}

		return $settings;
	}

	/**
	 * Add additional bulk actions.
	 *
	 * @param array $actions Bulk actions.
	 */
	public function entry_bulk_actions( $actions ) {
		return array_merge(
			array(
				'star'   => esc_html__( 'Star', 'everest-forms-pro' ),
				'unstar' => esc_html__( 'Unstar', 'everest-forms-pro' ),
				'read'   => esc_html__( 'Mark Read', 'everest-forms-pro' ),
				'unread' => esc_html__( 'Mark Unread', 'everest-forms-pro' ),
			),
			$actions
		);
	}

	/**
	 * Add additional entry statues.
	 *
	 * @param array $statuses Entry statuses.
	 */
	public function entry_statuses( $statuses ) {
		$position     = array_search( 'trash', array_keys( $statuses ), true );
		$new_statuses = array(
			'unread'  => esc_html__( 'Unread', 'everest-forms-pro' ),
			'starred' => esc_html__( 'Starred', 'everest-forms-pro' ),
		);

		return array_merge( array_slice( $statuses, 0, $position ), $new_statuses, array_slice( $statuses, $position ) );
	}

	/**
	 * Entries table column.
	 *
	 * @param array $columns Columns.
	 * @param array $form_data Forms data.
	 */
	public function entries_table_columns( $columns, $form_data ) {
		$new_columns    = array();
		$paypal_enabled = isset( $form_data['payments']['paypal']['enable_paypal'] ) ? '1' === $form_data['payments']['paypal']['enable_paypal'] : false;
		$stripe_enabled = isset( $form_data['payments']['stripe']['enable_stripe'] ) ? '1' === $form_data['payments']['stripe']['enable_stripe'] : false;

		if ( empty( $_GET['status'] ) || ( isset( $_GET['status'] ) && 'trash' !== $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$new_columns['indicators'] = '';
			$columns                   = array_merge( array_slice( $columns, 0, 1 ), $new_columns, array_slice( $columns, 1 ) );
		}

		if ( $paypal_enabled || $stripe_enabled ) {
			$new_columns['status'] = esc_html__( 'Status', 'everest-forms-pro' );
			$pos                   = array_search( 'date', array_keys( $columns ), true );
			$columns               = array_merge( array_slice( $columns, 0, $pos ), $new_columns, array_slice( $columns, $pos ) );
		}

		return $columns;
	}

	/**
	 * Renders the columns.
	 *
	 * @param  string $value Entry value.
	 * @param  object $entry Entry object.
	 * @param  string $column_name Column Name.
	 * @return string
	 */
	public function entries_table_column_value( $value, $entry, $column_name ) {
		if ( isset( $entry->meta ) ) {
			$payment_meta = $entry->meta;

			if ( isset( $payment_meta['meta'] ) ) {
				$payment_details = json_decode( $payment_meta['meta'] );
			}
		}

		switch ( $column_name ) {
			case 'total':
				if ( isset( $payment_details->payment_total ) ) {
					$amount = evf_sanitize_amount( $payment_details->payment_total, $payment_details->payment_currency );
					$total  = evf_format_amount( $amount, true, $payment_details->payment_currency );
					$value  = $total;
				} else {
					$value = '<span class="na">&mdash;</span>';
				}
				break;
			case 'status':
				if ( ! empty( $payment_meta['status'] ) ) {
					$dollar_icon = plugins_url( '/assets/img/icon-dollar.png', EFP_PLUGIN_FILE );
					$value       = ucfirst( $payment_meta['status'] ) . '<img src="' . $dollar_icon . '" alt="Payment">';
				} else {
					$value = '-';
				}
				break;
			case 'indicators':
				// Stars.
				$star_action = empty( $entry->starred ) ? 'star' : 'unstar';
				$star_title  = empty( $entry->starred ) ? esc_html__( 'Star entry', 'everest-forms-pro' ) : esc_html__( 'Unstar entry', 'everest-forms-pro' );
				$value       = '<a href="#" class="indicator-star ' . $star_action . '" data-id="' . absint( $entry->entry_id ) . '" title="' . esc_attr( $star_title ) . '"><span class="dashicons dashicons-star-filled"></span></a>';

				// Viewed.
				$read_action = empty( $entry->viewed ) ? 'read' : 'unread';
				$read_title  = empty( $entry->viewed ) ? esc_html__( 'Mark entry read', 'everest-forms-pro' ) : esc_html__( 'Mark entry unread', 'everest-forms-pro' );
				$value      .= '<a href="#" class="indicator-read ' . $read_action . '" data-id="' . absint( $entry->entry_id ) . '" title="' . esc_attr( $read_title ) . '"><span class="dashicons dashicons-marker"></span></a>';
				break;
		}

		return $value;
	}

	/**
	 * Ajax handler to toggle entry read state.
	 *
	 * @since 1.6.0
	 */
	public function ajax_entry_read() {
		check_ajax_referer( 'everest-forms-entry', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( apply_filters( 'everest_forms_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['entry_id'] ) || ! empty( $_POST['task'] ) ) {
			$entry_id   = isset( $_POST['entry_id'] ) ? absint( $_POST['entry_id'] ) : 0;
			$is_success = EVF_Admin_Entries::update_status( $entry_id, sanitize_key( $_POST['task'] ) );

			if ( $is_success ) {
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	/**
	 * Ajax handler to toggle entry stars.
	 *
	 * @since 1.6.0
	 */
	public function ajax_entry_star() {
		check_ajax_referer( 'everest-forms-entry', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( apply_filters( 'everest_forms_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['entry_id'] ) || ! empty( $_POST['task'] ) ) {
			$entry_id   = isset( $_POST['entry_id'] ) ? absint( $_POST['entry_id'] ) : 0;
			$is_success = EVF_Admin_Entries::update_status( $entry_id, sanitize_key( $_POST['task'] ) );

			if ( $is_success ) {
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	/**
	 * Add starred icon if needed.
	 *
	 * @param object $entry Entry data.
	 */
	public function add_starred_icon( $entry ) {
		echo '1' === $entry->starred ? '<span class="dashicons dashicons-star-filled"></span>' : '';
	}

	/**
	 * Entry details action metabox.
	 *
	 * @param object $entry      Submitted entry values.
	 * @param object $entry_meta Entry meta data.
	 * @param array  $form_data  Form data and settings.
	 */
	public function entry_details_actions( $entry, $entry_meta, $form_data ) {
		$is_viewed    = false;
		$action_links = array();

		// Marked entry as read.
		if ( '1' !== $entry->viewed && empty( $_GET['action'] ) && empty( $_GET['unread'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_viewed = EVF_Admin_Entries::update_status( absint( $entry->entry_id ), 'read' );
		}

		$base_url = add_query_arg(
			array(
				'page'     => 'evf-entries',
				'form_id'  => absint( $form_data['id'] ),
				'entry_id' => absint( $entry->entry_id ),
			),
			admin_url( 'admin.php' )
		);

		$action_links['star'] = array(
			'url'   => wp_nonce_url( add_query_arg( array( 'action' => '1' === $entry->starred ? 'unstar' : 'star' ), $base_url ), 'starred-entry' ),
			'icon'  => '1' === $entry->starred ? 'dashicons-star-empty' : 'dashicons-star-filled',
			'label' => '1' === $entry->starred ? esc_html__( 'Unstar', 'everest-forms-pro' ) : esc_html__( 'Star', 'everest-forms-pro' ),
		);

		if ( '1' === $entry->viewed || $is_viewed ) {
			$action_links['read'] = array(
				'url'   => wp_nonce_url( add_query_arg( array( 'action' => 'unread' ), $base_url ), 'unread-entry' ),
				'icon'  => 'dashicons-hidden',
				'label' => esc_html__( 'Mark Unread', 'everest-forms-pro' ),
			);
		}

		$action_links['export'] = array(
			'url'   => wp_nonce_url( add_query_arg( array( 'action' => 'export_csv' ), $base_url ), 'export-entry' ),
			'icon'  => 'dashicons-download',
			'label' => esc_html__( 'Export Entry (CSV)', 'everest-forms-pro' ),
		);

		if ( ! empty( $entry->fields ) ) {
			$action_links['notifications'] = array(
				'url'   => wp_nonce_url( add_query_arg( array( 'action' => 'notification' ), $base_url ), 'resend-entry' ),
				'icon'  => 'dashicons-email',
				'label' => esc_html__( 'Resend Notifications', 'everest-forms-pro' ),
			);
		}

		$action_links = apply_filters( 'everest_forms_entry_details_sidebar_actions_link', $action_links, $entry, $form_data );
		?>
		<!-- Entry Actions metabox -->
		<div id="everest-forms-entry-actions" class="postbox">
			<h2 class="hndle">
				<span><?php esc_html_e( 'Entry Actions', 'everest-forms-pro' ); ?></span>
			</h2>
			<div class="inside">
				<div class="everest-forms-entry-actions-meta">
				<?php
				foreach ( $action_links as $slug => $action_link ) {
					$target = ! empty( $action_link['target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '';
					printf( '<p class="everest-forms-entry-%s">', esc_attr( $slug ) );
						printf( '<a href="%s" %s>', esc_url( $action_link['url'] ), $target ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							printf( '<span class="dashicons %s"></span>', esc_attr( $action_link['icon'] ) );
							echo esc_html( $action_link['label'] );
						echo '</a>';
					echo '</p>';
				}

				do_action( 'everest_forms_entry_details_sidebar_actions', $entry, $form_data );
				?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Entries admin actions.
	 */
	public function process_actions() {
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : '';

		if ( isset( $_GET['page'], $_GET['action'], $_GET['entry_id'] ) && 'evf-entries' === $_GET['page'] ) {
			$args     = array();
			$entry_id = absint( $_GET['entry_id'] );

			switch ( $_GET['action'] ) {
				case 'star':
				case 'unstar':
					check_admin_referer( 'starred-entry' );

					$starred = 'star' === $_GET['action'] ? 'starred' : 'unstarred';
					if ( EVF_Admin_Entries::update_status( $entry_id, sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
						$args[ $starred ] = 1;
					}
					break;
				case 'unread':
					check_admin_referer( 'unread-entry' );

					if ( EVF_Admin_Entries::update_status( $entry_id, sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
						$args['unread'] = 1;
					}
					break;
				case 'export_csv':
					check_admin_referer( 'export-entry' );

					$file_name = strtolower( get_the_title( $form_id . '-' . $entry_id ) );

					if ( $file_name ) {
						include_once EVF_ABSPATH . 'includes/export/class-evf-entry-csv-exporter.php';

						$exporter = new EVF_Entry_CSV_Exporter( $form_id, $entry_id );
						$exporter->set_filename( evf_get_csv_file_name( $file_name ) );
					}

					$exporter->export();
					break;
				case 'notification':
					check_admin_referer( 'resend-entry' );

					$entry = evf_get_entry( $entry_id );

					if ( ! empty( $entry->fields ) ) {
						$fields    = evf_decode( $entry->fields );
						$form_data = evf()->form->get( $form_id, array( 'content_only' => true ) );

						// Resend email notification.
						evf()->task->entry_email( $fields, array(), $form_data, $entry_id );
						$args['resend'] = 1;
					}
					break;
			}

			wp_safe_redirect(
				esc_url_raw(
					add_query_arg(
						array_merge(
							array(
								'form_id'    => $form_id,
								'view-entry' => $entry_id,
							),
							$args
						),
						admin_url( 'admin.php?page=evf-entries' )
					)
				)
			);
			exit();
		}
	}

	/**
	 * Entry action notices.
	 */
	public function entry_notices() {
		$message = '';

		if ( isset( $_GET['starred'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$message = esc_html__( 'This entry has been starred.', 'everest-forms-pro' );
		} elseif ( isset( $_GET['unstarred'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$message = esc_html__( 'This entry has been unstarred.', 'everest-forms-pro' );
		} elseif ( isset( $_GET['unread'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$message = esc_html__( 'This entry has been marked unread.', 'everest-forms-pro' );
		} elseif ( isset( $_GET['resend'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$message = esc_html__( 'Notifications were resent!', 'everest-forms-pro' );
		}

		if ( $message ) {
			echo '<div id="message" class="updated notice is-dismissible">';
			echo '<p>' . wp_kses_post( $message ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Payment Details within Entry.
	 *
	 * @param object $entry     Entry Data.
	 * @param array  $form_data Form Data Object.
	 */
	public function payment_details_inside_entry( $entry, $form_data ) {
		if ( empty( $form_data['type'] ) || 'payment' !== $form_data['type'] ) {
			return;
		}

		$entry_meta  = json_decode( $entry->meta['meta'], true );
		$status      = ! empty( $entry->status ) ? ucwords( sanitize_text_field( $entry->status ) ) : esc_html__( 'Unknown', 'everest-forms-pro' );
		$currency    = ! empty( $entry_meta['payment_currency'] ) ? $entry_meta['payment_currency'] : get_option( 'everest_forms_currency', 'USD' );
		$total       = isset( $entry_meta['payment_total'] ) ? evf_format_amount( evf_sanitize_amount( $entry_meta['payment_total'], $currency ), true, $currency ) : '-';
		$note        = ! empty( $entry_meta['payment_note'] ) ? esc_html( $entry_meta['payment_note'] ) : '';
		$gateway     = esc_html( apply_filters( 'evf_entry_details_payment_gateway', '-', $entry_meta, $entry, $form_data ) );
		$transaction = esc_html( apply_filters( 'evf_entry_details_payment_transaction', '-', $entry_meta, $entry, $form_data ) );
		$mode        = ! empty( $entry_meta['payment_mode'] ) && 'test' === $entry_meta['payment_mode'] ? 'test' : 'production';

		switch ( $entry_meta['payment_type'] ) {
			case 'paypal_standard':
				$gateway = esc_html__( 'PayPal Standard', 'everest-forms-pro' );
				if ( ! empty( $entry_meta['payment_transaction'] ) ) {
					$type        = 'production' === $mode ? '' : 'sandbox.';
					$transaction = sprintf( '<a href="https://www.%spaypal.com/webscr?cmd=_history-details-from-hub&id=%s" target="_blank" rel="noopener noreferrer">%s</a>', $type, $entry_meta['payment_transaction'], $entry_meta['payment_transaction'] );
				}
				break;
			case 'stripe':
					$transaction = $entry_meta['payment_transaction'];
					$gateway     = 'Stripe';
				break;
		}

		?>
		<!-- Entry Payment details metabox -->
		<div id="everest-forms-entry-payment" class="postbox">
			<h2 class="hndle">
				<span><?php esc_html_e( 'Payment Details', 'everest-forms-pro' ); ?></span>
			</h2>
			<div class="inside">
				<div class="everest-forms-entry-payment-meta">

					<p class="everest-forms-entry-payment-status">
						<?php
						printf(
							/* translators: %s - entry payment status. */
							esc_html__( 'Status: %s', 'everest-forms-pro' ),
							'<strong>' . esc_html( $status ) . '</strong>'
						);
						?>
					</p>

					<p class="everest-forms-entry-payment-total">
						<?php
						printf(
							/* translators: %s - entry payment total. */
							esc_html__( 'Total: %s', 'everest-forms-pro' ),
							'<strong>' . esc_html( $total ) . '</strong>'
						);
						?>
					</p>

					<p class="everest-forms-entry-payment-gateway">
						<?php
						printf(
							/* translators: %s - entry payment gateway. */
							esc_html__( 'Gateway: %s', 'everest-forms-pro' ),
							'<strong>' . esc_html( $gateway ) . '</strong>'
						);
						if ( 'test' === $mode ) {
							printf( ' (%s)', esc_html( _x( 'Test', 'Gateway mode', 'everest-forms-pro' ) ) );
						}
						?>
					</p>

					<p class="everest-forms-entry-payment-transaction">
						<?php
						printf(
							/* translators: %s - entry payment transaction. */
							esc_html__( 'Transaction ID: %s', 'everest-forms-pro' ),
							'<strong>' . esc_html( $transaction ) . '</strong>'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Hide Payment entry field.
	 *
	 * @param  array $entry_fields Entry fields.
	 * @return array               List for fields.
	 */
	public function entry_hidden_fields( $entry_fields ) {
		return array_merge( $entry_fields, array( 'status', 'type', 'meta' ) );
	}

	/**
	 * Single Payment validation.
	 *
	 * @param string $field_id     Field ID.
	 * @param string $field_submit Field's submitted value.
	 * @param array  $form_data    Form data object.
	 * @param array  $field_type   Type of the field.
	 */
	public function payment_single_validation( $field_id, $field_submit, $form_data, $field_type ) {
		if ( isset( $form_data['form_fields'][ $field_id ]['required'] ) && empty( $field_submit ) && '0' !== $field_submit && 'user' === $form_data['form_fields'][ $field_id ]['item_type'] ) {
			$validation_text = get_option( 'evf_' . $field_type . '_validation', __( 'Please enter the desire amount.', 'everest-forms-pro' ) );
		}

		if ( isset( $validation_text ) ) {
			EVF()->task->errors[ $form_data['id'] ][ $field_id ] = apply_filters( 'everest_forms_type_validation', $validation_text );
			update_option( 'evf_validation_error', 'yes' );
		}
	}

	/**
	 * Multiple Payment validation.
	 *
	 * @param string $field_id     Field ID.
	 * @param string $field_submit Field submit flag.
	 * @param array  $form_data    Form data object.
	 * @param array  $field_type   Type of the field.
	 */
	public function payment_multiple_validation( $field_id, $field_submit, $form_data, $field_type ) {
		if ( isset( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) && '0' !== $field_submit ) {
			$validation_text = get_option( 'evf_' . $field_type . '_validation', __( 'Please choose one Item.', 'everest-forms-pro' ) );
		}
		if ( isset( $validation_text ) ) {
			EVF()->task->errors[ $form_data['id'] ][ $field_id ] = apply_filters( 'everest_forms_type_validation', $validation_text );
			update_option( 'evf_validation_error', 'yes' );
		}
	}

	/**
	 * Form script data.
	 *
	 * @param  array  $params Array of l10n data parameters.
	 * @param  string $handle Script handle the data will be attached to.
	 * @return array
	 */
	public function form_script_data( $params, $handle ) {
		if ( 'everest-forms' === $handle ) {
			$params = array_merge(
				$params,
				array(
					'i18n_no_countries'           => _x( 'No countries found', 'enhanced select', 'everest-forms' ),
					'i18n_messages_phone'         => get_option( 'everest_forms_phone_validation', __( 'Please enter a valid phone number.', 'everest-forms-pro' ) ),
					'i18n_messages_fileextension' => get_option( 'everest_forms_fileextension_validation', __( 'File type is not allowed.', 'everest-forms-pro' ) ),
					'i18n_messages_filesize'      => get_option( 'everest_forms_filesize_validation', __( 'File exceeds max size allowed.', 'everest-forms-pro' ) ),
				)
			);
		}

		return $params;
	}

	/**
	 * Display row meta in the Plugins list table.
	 *
	 * @param  array  $plugin_meta Plugin Row Meta.
	 * @param  string $plugin_file Plugin Base file.
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( plugin_basename( EFP_PLUGIN_FILE ) === $plugin_file ) {
			$new_plugin_meta = array(
				'docs' => '<a href="' . esc_url( 'https://docs.wpeverest.com/docs/everest-forms/everest-forms-pro/' ) . '" aria-label="' . esc_attr__( 'View Everest Forms Pro documentation', 'everest-forms-pro' ) . '">' . esc_html__( 'Docs', 'everest-forms-pro' ) . '</a>',
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}

	/**
	 * Everest Forms fallback notice.
	 */
	public function everest_forms_missing_notice() {
		/* translators: %s: everest-forms version */
		echo '<div class="error notice is-dismissible"><p>' . sprintf( esc_html__( 'Everest Forms Pro requires at least %s or later to work!', 'everest-forms-pro' ), '<a href="https://wpeverest.com/wordpress-plugins/everest-forms/" target="_blank">' . esc_html__( 'Everest Forms 1.6.0', 'everest-forms-pro' ) . '</a>' ) . '</p></div>';
	}
}
