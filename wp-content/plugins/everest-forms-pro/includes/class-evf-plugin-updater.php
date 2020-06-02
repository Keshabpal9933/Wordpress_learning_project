<?php
/**
 * Plugin Updater
 *
 * @package EverestForms_Pro/Admin
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Plugin_Updater Class.
 */
class EVF_Plugin_Updater {

	/**
	 * Plugin File.
	 *
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * Plugins data.
	 *
	 * @var array of strings
	 */
	private $plugin_data = array();

	/**
	 * Validation errors.
	 *
	 * @var array of strings
	 */
	private $errors = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin_file = EFP_PLUGIN_FILE;
		$this->plugin_slug = str_replace( '.php', '', basename( $this->plugin_file ) );

		// Get the stored license key.
		$this->api_key = get_option( $this->plugin_slug . '_license_key' );

		register_activation_hook( $this->plugin_file, array( $this, 'plugin_activation' ), 10 );
		register_deactivation_hook( $this->plugin_file, array( $this, 'plugin_deactivation' ), 10 );

		add_filter( 'block_local_requests', '__return_false' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Include required files.
		include_once dirname( __FILE__ ) . '/updater/class-evf-updater-api.php';
		include_once dirname( __FILE__ ) . '/updater/class-evf-updater-key-api.php';
	}

	/**
	 * Handles plugin updater.
	 *
	 * @param string  $plugin_file    Path to the plugin file.
	 * @param integer $item_id        Item ID to send with API calls.
	 * @param integer $plugin_version Plugin Version to send with API calls.
	 */
	public static function updates( $plugin_file, $item_id, $plugin_version ) {
		$license_key = trim( get_option( 'everest-forms-pro_license_key' ) );
		$plugin_data = array(
			'item_id' => $item_id,
			'version' => $plugin_version,
			'license' => $license_key,
			'author'  => 'WPEverest',
			'url'     => home_url(),
		);

		return new EVF_Updater_API( 'https://wpeverest.com/edd-sl-api/', $plugin_file, $plugin_data );
	}

	/**
	 * Run on admin init.
	 */
	public function admin_init() {
		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );

		$this->plugin_data = get_plugin_data( $this->plugin_file );

		// Check for plugin update capability.
		if ( current_user_can( 'update_plugins' ) ) {
			$this->plugin_requests();
			$this->plugin_license_view();
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		// Register admin styles.
		wp_register_style( 'everest-forms-license', plugins_url( '/assets/css/license.css', EFP_PLUGIN_FILE ), array(), '1.0.0' );

		// Add RTL support for admin styles.
		wp_style_add_data( 'everest-forms-license', 'rtl', 'replace' );

		// Sitewide menu CSS.
		if ( isset( $screen->id ) && in_array( $screen->id, array( 'plugins' ), true ) ) {
			wp_enqueue_style( 'everest-forms-license' );
		}
	}

	/**
	 * Process plugin requests.
	 */
	private function plugin_requests() {
		// @codingStandardsIgnoreStart
		if ( ! empty( $_POST[ $this->plugin_slug . '_license_key' ] ) ) {
			$this->activate_license_request();
		} elseif ( ! empty( $_GET[ $this->plugin_slug . '_deactivate_license' ] ) ) {
			$this->deactivate_license_request();
		} elseif ( ! empty( $_GET[ 'dismiss-' . sanitize_title( $this->plugin_slug ) ] ) ) {
			update_option( $this->plugin_slug . '_hide_key_notice', 1 );
		} elseif ( ! empty( $_GET['activated_license'] ) && $_GET['activated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'activated_key_notice' ) );
		} elseif ( ! empty( $_GET['deactivated_license'] ) && $_GET['deactivated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'deactivated_key_notice' ) );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Activate a license request.
	 */
	private function activate_license_request() {
		$license_key = sanitize_text_field( wp_unslash( $_POST[ $this->plugin_slug . '_license_key' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( $this->activate_license( $license_key ) ) {
			wp_safe_redirect( remove_query_arg( array( 'deactivated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'activated_license', $this->plugin_slug ) ) );
			exit;
		} else {
			wp_safe_redirect( remove_query_arg( array( 'activated_license', 'deactivated_license', $this->plugin_slug . '_deactivate_license' ) ) );
			exit;
		}
	}

	/**
	 * Deactivate a license request
	 */
	private function deactivate_license_request() {
		$this->deactivate_license();
		delete_transient( 'evf_pro_license_plan' );
		wp_safe_redirect( remove_query_arg( array( 'activated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'deactivated_license', $this->plugin_slug ) ) );
		exit;
	}

	/**
	 * Display plugin license view.
	 */
	private function plugin_license_view() {
		if ( ! $this->api_key ) {
			add_action( 'after_plugin_row', array( $this, 'plugin_license_form' ) );
			$this->add_notice( array( $this, 'key_notice' ) );
		} else {
			add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), array( $this, 'plugin_action_links' ) );
		}

		add_action( 'admin_notices', array( $this, 'error_notices' ) );
	}

	/**
	 * Add notices.
	 *
	 * @param string $callback Callback for notices action.
	 */
	private function add_notice( $callback ) {
		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Add an error message
	 *
	 * @param string $message Your error message.
	 * @param string $type    Type of error message.
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Load errors from option
	 */
	public function load_errors() {
		$this->errors = get_option( $this->plugin_slug . '_errors', array() );
	}

	/**
	 * Store errors in option
	 */
	public function store_errors() {
		if ( count( $this->errors ) > 0 ) {
			update_option( $this->plugin_slug . '_errors', $this->errors );
		} else {
			delete_option( $this->plugin_slug . '_errors' );
		}
	}

	/**
	 * Output errors
	 */
	public function error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				include dirname( __FILE__ ) . '/updater/views/html-notice-error.php';
				if ( 'invalid_key' !== $key && did_action( 'all_admin_notices' ) ) {
					unset( $this->errors[ $key ] );
				}
			}
		}
	}

	/**
	 * Run on plugin-activation.
	 */
	public function plugin_activation() {
		delete_option( $this->plugin_slug . '_hide_key_notice' );
	}

	/**
	 * Run on plugin-deactivation.
	 */
	public function plugin_deactivation() {
		$this->deactivate_license();
	}

	/**
	 * Show the input form for the license key.
	 *
	 * @param string $plugin_file Plugin filename.
	 */
	public function plugin_license_form( $plugin_file ) {
		if ( strtolower( basename( dirname( $plugin_file ) ) ) === strtolower( $this->plugin_slug ) ) {
			include_once dirname( __FILE__ ) . '/updater/views/html-license-form.php';
		}
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'deactivate_license' => '<a href="' . remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( $this->plugin_slug . '_deactivate_license', 1 ) ) . '" class="deactivate-license" style="color: #a00;" title="' . esc_attr( __( 'Deactivate License Key', 'everest-forms-pro' ) ) . '">' . __( 'Deactivate License', 'everest-forms-pro' ) . '</a>',
		);

		return array_merge( $actions, $new_actions );
	}

	/**
	 * Try to activate a license.
	 *
	 * @param string $license_key License key.
	 *
	 * @throws \Exception May throw exception if license key is invalid.
	 */
	public function activate_license( $license_key ) {
		try {
			if ( empty( $license_key ) ) {
				throw new Exception( 'Please enter your license key' );
			}

			$activate_results = json_decode(
				EVF_Updater_Key_API::activate(
					array(
						'license' => $license_key,
					)
				)
			);

			// Update activate results.
			update_option( $this->plugin_slug . '_license_active', $activate_results );

			if ( ! empty( $activate_results ) && is_object( $activate_results ) ) {
				if ( isset( $activate_results->error_code ) ) {
					throw new Exception( $activate_results->error );
				} elseif ( false === $activate_results->success ) {
					switch ( $activate_results->error ) {
						case 'expired':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: 1: expiry date 2: upgrade link */
									__( 'The provided license key expired on %1$s. Please <a href="%2$s" target="_blank">renew your license key</a>.', 'everest-forms-pro' ),
									date_i18n( get_option( 'date_format' ), strtotime( $activate_results->expires, time() ) ),
									'https://wpeverest.com/checkout/?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
								)
							);
							break;

						case 'revoked':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: %s: support link */
									__( 'The provided license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'everest-forms-pro' ),
									'https://wpeverest.com/contact?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
								)
							);
							break;

						case 'missing':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: %s: account link */
									__( 'The provided license is invalid. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'everest-forms-pro' ),
									'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
								)
							);
							break;

						case 'invalid':
						case 'site_inactive':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: %s: account link */
									__( 'The provided license is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'everest-forms-pro' ),
									'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
								)
							);
							break;

						case 'invalid_item_id':
						case 'item_name_mismatch':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: %s: plugin name */
									__( 'This appears to be an invalid license key for <strong>%1$s</strong>.', 'everest-forms-pro' ),
									$this->plugin_data['Name']
								)
							);
							break;

						case 'no_activations_left':
							$error_msg = wp_kses_post(
								sprintf(
									/* translators: %s: upgrade link */
									__( 'The provided license key has reached its activation limit. Please <a href="%1$s" target="_blank">View possible upgrades</a> now.', 'everest-forms-pro' ),
									'https://wpeverest.com/my-account/'
								)
							);
							break;

						case 'license_not_activable':
							$error_msg = esc_html__( 'The key you entered belongs to a bundle, please use the product specific license key.', 'everest-forms-pro' );
							break;

						default:
							$error_msg = wp_kses_post(
								sprintf(
								/* translators: %s: support link */
									__( 'The provided license key could not be found. Please <a href="%s" target="_blank">contact support</a> for more information.', 'everest-forms-pro' ),
									'https://wpeverest.com/contact/'
								)
							);
							break;
					}

					/* translators: %s: error message */
					throw new Exception( wp_kses_post( sprintf( __( '<strong>Activation error:</strong> %1$s', 'everest-forms-pro' ), $error_msg ) ) );

				} elseif ( 'valid' === $activate_results->license ) {
					$this->api_key = $license_key;
					$this->errors  = array();

					update_option( $this->plugin_slug . '_license_key', $this->api_key );
					delete_option( $this->plugin_slug . '_errors' );

					return true;
				}

				throw new Exception( esc_html__( 'License could not activate. Please contact support.', 'everest-forms-pro' ) );
			} else {
				throw new Exception( esc_html__( 'Connection failed to the License Key API server - possible server issue.', 'everest-forms-pro' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Deactivate a license.
	 */
	public function deactivate_license() {
		$reset = EVF_Updater_Key_API::deactivate(
			array(
				'license' => $this->api_key,
			)
		);

		delete_option( $this->plugin_slug . '_errors' );
		delete_option( $this->plugin_slug . '_license_key' );
		delete_option( $this->plugin_slug . '_license_active' );

		// Reset huh?
		$this->errors  = array();
		$this->api_key = '';
	}

	/**
	 * Show a notice prompting the user to update.
	 */
	public function key_notice() {
		if ( count( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			include dirname( __FILE__ ) . '/updater/views/html-notice-key-unvalidated.php';
		}
	}

	/**
	 * Activation success notice.
	 */
	public function activated_key_notice() {
		include dirname( __FILE__ ) . '/updater/views/html-notice-key-activated.php';
	}

	/**
	 * Dectivation success notice.
	 */
	public function deactivated_key_notice() {
		include dirname( __FILE__ ) . '/updater/views/html-notice-key-deactivated.php';
	}
}

new EVF_Plugin_Updater();
