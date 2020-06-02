<?php
/**
 * Abstract EVF_Email_Marketing class.
 *
 * @package EverestForms/Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Email_Marketing class.
 */
abstract class EVF_Email_Marketing {

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
	 * @var mixed
	 */
	public $icon = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'everest_forms_available_integrations', array( $this, 'register_integration' ) );
		add_action( 'everest_forms_providers_panel_content', array( $this, 'output_panel_content' ) );
		add_action( 'everest_forms_integration_connections_' . $this->id, array( $this, 'output_connections_list' ) );
		add_action( 'everest_forms_process_complete', array( $this, 'process_feed' ), 5, 4 );

		// AJAX Events.
		$this->add_ajax_events();
	}

	/**
	 * Get form data
	 *
	 * @return array form data.
	 */
	private function form_data() {
		$form_data = array();

		if ( ! empty( $_GET['form_id'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$form_data = EVF()->form->get( absint( $_GET['form_id'] ), array( 'content_only' => true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $form_data;
	}

	/**
	 * Get integration ID
	 *
	 * @return array Integration stored data.
	 */
	private function get_integration() {
		$integrations = get_option( 'everest_forms_integrations', array() );

		return in_array( $this->id, array_keys( $integrations ), true ) ? $integrations[ $this->id ] : array();
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
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public function add_ajax_events() {
		$ajax_events = array(
			'new_connection_add',
			'add_account_form',
			'account_select',
			'account_list_select',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_everest_forms_' . $ajax_event . '_' . $this->id, array( $this, $ajax_event ) );
		}
	}

	/**
	 * AJAX Integration disconnect.
	 */
	public function new_connection_add() {
		check_ajax_referer( 'process-ajax-nonce', 'security' );

		if ( ! current_user_can( 'manage_everest_forms' ) && ! isset( $_POST['name'], $_POST['id'] ) ) {
			wp_die( -1 );
		}

		$connection = $this->output_integration_connection( '', array( 'connection_name' => sanitize_text_field( $_POST['name'] ) ), sanitize_text_field( $_POST['id'] ) ); // @codingStandardsIgnoreLine

		wp_send_json_success(
			array(
				'html'          => $connection['html'],
				'connection_id' => $connection['connection_id'],
			)
		);
	}

	/**
	 * AJAX Add account form.
	 */
	public function add_account_form() {
		check_ajax_referer( 'process-ajax-nonce', 'security' );

		if ( ! current_user_can( 'manage_everest_forms' ) ) {
			wp_die( -1 );
		}

		$auth = $this->authorize_api( wp_parse_args( $_POST['data'], array() ), isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '' ); // @codingStandardsIgnoreLine

		if ( is_wp_error( $auth ) ) {

			wp_send_json_error(
				array(
					'error' => $auth->get_error_message(),
				)
			);

		} else {

			$accounts = $this->output_connected_accounts(
				$_POST['connection_id'], // @codingStandardsIgnoreLine
				array(
					'account_id' => $auth,
				)
			);

			wp_send_json_success(
				array(
					'html' => $accounts,
				)
			);
		}
	}

	/**
	 * Account Select function - Outputs an array of account lists.
	 */
	public function account_select() {
		check_ajax_referer( 'process-ajax-nonce', 'security' );

		if ( ! current_user_can( 'manage_everest_forms' ) ) {
			wp_die( -1 );
		}

			$lists = $this->output_account_lists( sanitize_text_field( $_POST['connection_id'] ), array( 'account_id' => sanitize_text_field( $_POST['account_id'] ) ) ); // @codingStandardsIgnoreLine

		if ( is_wp_error( $lists ) ) {

			wp_send_json_error(
				array(
					'error' => $lists->get_error_message(),
				)
			);

		} else {

			wp_send_json_success(
				array(
					'html' => $lists,
				)
			);
		}
	}

	/**
	 * Account list Selection function
	 */
	public function account_list_select() {
		check_ajax_referer( 'process-ajax-nonce', 'security' );

		if ( ! current_user_can( 'manage_everest_forms' ) ) {
			wp_die( -1 );
		}

		// @codingStandardsIgnoreStart
		$fields = $this->output_account_fields(
			sanitize_text_field( $_POST['connection_id'] ),
			array(
				'account_id' => sanitize_text_field( $_POST['account_id'] ),
				'list_id'    => sanitize_text_field( $_POST['list_id'] ),
			),
			$_POST['form_id']
		);

		if ( is_wp_error( $fields ) ) {

			wp_send_json_error(
				array(
					'error' => $fields->get_error_message(),
				)
			);

		} else {

			$groups = $this->output_groups(
				sanitize_text_field( $_POST['connection_id'] ),
				array(
					'account_id' => sanitize_text_field( $_POST['account_id'] ),
					'list_id'    => sanitize_text_field( $_POST['list_id'] ),
				)
			);

			$options = $this->output_options(
				sanitize_text_field( $_POST['connection_id'] ),
				array(
					'account_id' => sanitize_text_field( $_POST['account_id'] ),
					'list_id'    => sanitize_text_field( $_POST['list_id'] ),
				)
			);

			$conditional_logic = $this->conditional_logic(
				sanitize_text_field( $_POST['connection_id'] ),
				array(
					'account_id' => sanitize_text_field( $_POST['account_id'] ),
					'list_id'    => sanitize_text_field( $_POST['list_id'] ),
				)
			);
			// @codingStandardsIgnoreEnd

			wp_send_json_success(
				array(
					'html' => $groups . $fields . $options . $conditional_logic,
				)
			);
		}
	}

	/**
	 * Outputs the connection lists on sidebar.
	 */
	public function output_connections_list() {
		$form_data   = $this->form_data();
		$integration = $this->get_integration();
		if ( empty( $form_data['integrations'][ $this->id ] ) || empty( $integration ) ) {
			$class = 'empty-list';
		} else {
			$class = '';
		}
		?>
			<div class="everest-forms-active-connections">
	<button class="everest-forms-btn everest-forms-btn-primary everest-forms-connections-add" data-form_id="<?php echo absint( $_GET['form_id'] ); /* @codingStandardsIgnoreLine */ ?>" data-source="<?php echo esc_attr( $this->id ); ?>" data-type="<?php echo esc_attr( 'connection' ); ?>">
					<?php printf( esc_html__( 'Add New Connection', 'everest-forms-pro' ) ); ?>
				</button>
					<ul class="everest-forms-active-connections-list <?php echo esc_attr( $class ); ?>">
					<?php if ( ! empty( $form_data['integrations'][ $this->id ] ) && ! empty( $integration ) ) { ?>
						<h4><?php echo esc_html__( $this->name . ' connections', 'everest-forms-pro' ); /* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText */ ?> </h4>
						<?php
					}

					if ( ! empty( $form_data['integrations'][ $this->id ] ) && ! empty( $integration ) ) {
						foreach ( $form_data['integrations'][ $this->id ] as $connection_id => $connection_data ) {
							?>
							<li data-connection-id="<?php echo $connection_id; /* @codingStandardsIgnoreLine */ ?>">
							<a class="user-nickname" href="#"><?php echo esc_html( $connection_data['connection_name'] ); ?></a>
							<a href="#"><span class="toggle-remove">Remove</a>
							</li>
							<?php
						}
					}
					?>
					</ul>
			</div>
			<?php
	}

	/**
	 * Output builder panel content.
	 */
	public function output_panel_content() {
		$form_data   = $this->form_data();
		$integration = $this->get_integration();

		?>
		<div class="evf-panel-content-section evf-panel-content-section-<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>-provider">
			<div class="evf-content-section-title"><?php echo esc_html( $this->name ); ?></div>
			<div class="evf-provider-connections-wrap evf-clear">
				<div class="evf-provider-connections">
					<?php
					if ( ! empty( $form_data['integrations'][ $this->id ] ) && ! empty( $integration ) ) {
						foreach ( $form_data['integrations'][ $this->id ] as $connection_id => $connection ) {
							foreach ( $integration as $account_id => $connections ) {
								if ( ! empty( $connection['account_id'] ) && $account_id === $connection['account_id'] ) {
									$output = $this->output_integration_connection( $connection_id, $connection, $form_data );
									echo $output['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							}
						}
					} else {
						echo '<div class="everest-form-add-connection-notice">' . esc_html__( 'Please Add a connection.', 'everest-forms-pro' ) . '</div>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output integration connection.
	 *
	 * @param  string $connection_id Connection ID.
	 * @param  array  $connection    Connection data.
	 * @param  array  $form_data     Form data.
	 */
	public function output_integration_connection( $connection_id, $connection = array(), $form_data = array() ) {
		$this->id      = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : $this->id; // @codingStandardsIgnoreLine
		$connection_id = empty( $connection_id ) ? 'connection_' . uniqid() : $connection_id;
		if ( empty( $connection ) || empty( $form_data ) ) {
			return;
		}
		$account_lists  = $this->output_account_lists( $connection_id, $connection );
		$group_lists    = $this->output_groups( $connection_id, $connection );
		$account_fields = $this->output_account_fields( $connection_id, $connection, $form_data );

		$output          = sprintf( '<div class="evf-provider-connection" data-provider="%s" data-connection_id="%s">', $this->id, $connection_id );
			$output     .= '<div class="evf-provider-connection-header">';
				$output .= sprintf( '<input type="hidden" name="integrations[%s][%s][connection_name]" value="%s">', $this->id, $connection_id, esc_attr( $connection['connection_name'] ) );
			$output     .= '</div>';
			$output     .= $this->authentication_form();
			$output     .= $this->output_connected_accounts( $connection_id, $connection );

		if ( ! is_wp_error( $account_lists ) ) {
			$output .= $account_lists;
		}

		if ( ! is_wp_error( $group_lists ) ) {
			$output .= $group_lists;
		}

		if ( ! is_wp_error( $account_fields ) ) {
			$output .= $account_fields;
		}

			$output .= $this->output_options( $connection_id, $connection );
			$output .= $this->conditional_logic( $connection_id, $connection, $form_data );
		$output     .= '</div>';

		return array(
			'html'          => $output,
			'connection_id' => $connection_id,
		);
	}

	/**
	 * Integration authentication form.
	 */
	public function authentication_form() {
		$this->id     = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : $this->id; // @codingStandardsIgnoreLine
		$this->name   = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : $this->name; // @codingStandardsIgnoreLine
		$integration  = $this->get_integration();
		$hidden_class = empty( $integration ) ? '' : ' hidden';

		$output      = '<div class="everest-forms-source-account-add evf-connection-block' . esc_attr( $hidden_class ) . '">';
			$output .= '<h4 class="new-account-title">' . __( 'Add New Account', 'everest-forms-pro' ) . '</h4>';
			$output .= '<div class="evf-connection-form">';

				/* translators: %s for Specific API Service Provider Name*/
				$output .= '<input type="text" data-name="apikey" placeholder="' . sprintf( esc_attr__( '%s API Key', 'everest-forms-pro' ), ucfirst( $this->name ) ) . '" class="everest-forms-required">';

				/* translators: %s for Specific API Service Provider Nickname*/
				$output .= '<input type="text" data-name="label" placeholder="' . sprintf( esc_attr__( '%s Account Nickname', 'everest-forms-pro' ), ucfirst( $this->name ) ) . '" class="everest-forms-required">';
			$output     .= '</div>';
			$output     .= '<button class="everest-forms-btn everest-forms-btn-primary" data-source="' . esc_attr( $this->id ) . '">' . __( 'Connect To ' . ucfirst( $this->name ), 'everest-forms-pro' ) . '</button>'; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$output         .= '</div>';

		return $output;
	}

	/**
	 * Output connected accounts.
	 *
	 * @param string $connection_id Connection identifier for connected accounts.
	 * @param array  $connection    Connection data object.
	 *
	 * @return string
	 */
	public function output_connected_accounts( $connection_id = '', $connection = array() ) {
		$integration = $this->get_integration();

		if ( empty( $integration ) && ( empty( $connection_id ) || empty( $connection ) ) ) {
			return '';
		}

		$output      = '<div class="evf-provider-accounts evf-connection-block">';
			$output .= sprintf( '<h4>%s</h4>', esc_html__( 'Select Account', 'everest-forms-pro' ) );
			$output .= sprintf( '<select name="integrations[%s][%s][account_id]">', $this->id, $connection_id );
		foreach ( $integration as $key => $integration_details ) {
			$selected = ! empty( $connection['account_id'] ) ? $connection['account_id'] : '';
			$output  .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $selected, $key, false ), esc_html( $integration_details['label'] ) );
		}
			$output .= sprintf( '<option value="">%s</a>', esc_html__( 'Add New Account', 'everest-forms-pro' ) );
			$output .= '</select>';
		$output     .= '</div>';

		return $output;
	}

	/**
	 * Integration account lists HTML.
	 *
	 * @param string $connection_id Connection identifier for connected accounts.
	 * @param array  $connection    Connection data object.
	 *
	 * @return WP_Error|string
	 */
	public function output_account_lists( $connection_id = '', $connection = array() ) {
		if ( empty( $connection_id ) || empty( $connection['account_id'] ) ) {
			return '';
		}

		$lists    = $this->api_lists( $connection_id, $connection['account_id'] );
		$selected = ! empty( $connection['list_id'] ) ? $connection['list_id'] : '';

		if ( is_wp_error( $lists ) ) {
			return $lists;
		}

		$output = '<div class="evf-provider-lists evf-connection-block">';
		if ( 'convertkit' !== $this->id ) {
			$output .= sprintf( '<h4>%s</h4>', esc_html__( 'Select List', 'everest-forms-pro' ) );
		} else {
			$output .= sprintf( '<h4>%s</h4>', esc_html__( 'Select Form', 'everest-forms-pro' ) );
		}
			$output .= sprintf( '<select name="integrations[%s][%s][list_id]">', $this->id, $connection_id );

		if ( ! empty( $lists ) ) {
			foreach ( $lists as $list ) {
				$output .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $list['id'] ),
					selected( $selected, $list['id'], false ),
					esc_attr( $list['name'] )
				);
			}
		}

			$output .= '</select>';
		$output     .= '</div>';

		return $output;
	}

	/**
	 * Integration account lists groups HTML.
	 *
	 * @param string $connection_id Connection identifier for connected accounts.
	 * @param array  $connection    Connection data object.
	 *
	 * @return WP_Error|string
	 */
	public function output_groups( $connection_id = '', $connection = array() ) {

		if ( empty( $connection_id ) || empty( $connection['account_id'] ) || empty( $connection['list_id'] ) ) {
			return '';
		}

		$groupsets = $this->api_groups( $connection_id, $connection['account_id'], $connection['list_id'] );

		if ( is_wp_error( $groupsets ) ) {
			return '';
		}

		$output = '<div class="evf-provider-groups evf-connection-block">';

		$output .= sprintf( '<label>%s<i class="dashicons dashicons-editor-help everest-forms-help-tooltip tooltipstered" title="%s"></i></label>', esc_html__( 'Select Groups', 'everest-forms-pro' ), esc_html__( 'There are multiple segments in your list. You may select specific list segments as per your needs.', 'everest-forms-pro' ) );

		$output .= '<div class="evf-provider-groups-list everest-forms-checklist">';

		foreach ( $groupsets as $groupset ) {

			if ( 'checkboxes' === $groupset['type'] ) {
				$groupset['type'] = 'checkbox';
			}
			$output .= '<div class="everest-forms-border-container">';
			$output .= sprintf( '<h4 class="everest-forms-border-container-title">%s</h4>', esc_html( $groupset['name'] ) );

			if ( 'dropdown' === $groupset['type'] ) {
				$output .= '<select name="integrations[' . $this->id . '][' . $connection_id . '][groups][' . $groupset['id'] . ']">';
				foreach ( $groupset['groups'] as $group ) {
					$selected = ! empty( $connection['groups'][ $groupset['id'] ] ) ? selected( $connection['groups'][ $groupset['id'] ], $group['id'], false ) : '';
					$output  .= sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $group['id'] ),
						$selected,
						esc_attr( $group['name'] )
					);
				}
				$output .= '</select>';
			} elseif ( 'radio' === $groupset['type'] || 'checkbox' === $groupset['type'] ) {
				$output .= '<ul>';
				foreach ( $groupset['groups'] as $group ) {
					if ( 'radio' === $groupset['type'] ) {
						$name     = sprintf(
							'integrations[%s][%s][groups][%s]',
							$this->id,
							$connection_id,
							$groupset['id']
						);
						$selected = ! empty( $connection['groups'] ) && ! empty( $connection['groups'][ $groupset['id'] ] ) && $connection['groups'][ $groupset['id'] ] === $group['id'] ? true : false;
					} else {
						$name     = sprintf(
							'integrations[%s][%s][groups][%s][%s]',
							$this->id,
							$connection_id,
							$groupset['id'],
							$group['id']
						);
						$selected = ! empty( $connection['groups'] ) && ! empty( $connection['groups'][ $groupset['id'] ] ) ? in_array( $group['id'], $connection['groups'][ $groupset['id'] ], true ) : false;
					}
					$output .= sprintf(
						'<li><input id="group_%s" type="%s" value="%s" name="%s" %s><label for="group_%s">%s</label></li>',
						esc_attr( $group['id'] ),
						esc_attr( $groupset['type'] ),
						esc_attr( $group['id'] ),
						$name,
						checked( $selected, true, false ),
						esc_attr( $group['id'] ),
						esc_attr( $group['name'] )
					);
				}
				$output .= '</ul>';
			}
			$output .= '</div>';
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Integration account list fields HTML.
	 *
	 * @param string $connection_id Connection identifier for connected accounts.
	 * @param array  $connection    Connection data object.
	 * @param mixed  $form_data     Form data object.
	 *
	 * @return WP_Error|string
	 */
	public function output_account_fields( $connection_id = '', $connection = array(), $form_data = '' ) {
		if ( empty( $connection_id ) || empty( $connection['account_id'] ) || empty( $connection['list_id'] ) || empty( $form_data ) ) {
			return '';
		}

		$whitelist_fields = array(
			'first-name',
			'last-name',
			'text',
			'textarea',
			'select',
			'radio',
			'checkbox',
			'email',
			'address',
			'country',
			'url',
			'name',
			'hidden',
			'date',
			'date-time',
			'phone',
			'number',
			'rating',
			'scale-rating',
			'payment-single',
			'payment-multiple',
			'payment-checkbox',
			'payment-quantity',
			'payment-total',
		);

		$form_fields    = evf_get_form_fields( $form_data, apply_filters( 'everest_forms_email_marketing_whitelist_fields', $whitelist_fields ) );
		$account_fields = $this->fetch_api_fields( $connection_id, $connection['account_id'], $connection['list_id'] );
		$output         = '';

		if ( is_wp_error( $account_fields ) ) {
			return $account_fields;
		}
		if ( is_array( $account_fields ) ) {
			$output      = '<div class="evf-provider-fields evf-connection-block">';
			$output     .= sprintf( '<h4>%s</h4>', esc_html__( 'List Fields', 'everest-forms-pro' ) );
			$output     .= '<table class="wp-list-table widefat striped list-fields">';
				$output .= sprintf( '<thead><tr><th scope="col" class="column-lists">%s</th><th scope="col" class="column-form-fields">%s</th></thead>', esc_html__( 'List Fields', 'everest-forms-pro' ), esc_html__( 'Available Form Fields', 'everest-forms-pro' ) );
				$output .= '<tbody id="the-list">';
			foreach ( $account_fields as $account_field ) {
				$output     .= '<tr>';
				$output     .= '<td class="column-lists">';
					$output .= esc_html( $account_field['name'] );
				if ( ! empty( $account_field['req'] ) && '1' === $account_field['req'] ) {
					$output .= '<span class="required">*</span>';
				}
					$output .= '</td><td class="column-form-fields">';
					$output .= sprintf( '<select name="integrations[%s][%s][fields][%s]">', $this->id, $connection_id, esc_attr( $account_field['tag'] ) );

					$options = $this->get_form_field_select( $form_fields, $account_field['field_type'] );
					$output .= '<option value=""></option>';
				foreach ( $options as $option ) {
							$value    = sprintf( '%s.%s.%s', $option['id'], $option['key'], $option['provider_type'] );
							$selected = ! empty( $connection['fields'][ $account_field['tag'] ] ) ? selected( $connection['fields'][ $account_field['tag'] ], $value, false ) : '';
							$output  .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $value ), $selected, esc_html( $option['label'] ) );
				}
									$output .= '</select>';
									$output .= '</td>';
									$output .= '</tr>';
			}
				$output .= '</tbody>';
			$output     .= '</table>';
			$output     .= '</div>';
		}
		return $output;
	}

	/**
	 * Process data and submit entry to Integration.
	 *
	 * @param array $fields    Fields for the Form.
	 * @param array $entry     Form Entry.
	 * @param array $form_data Form Data object.
	 * @param int   $entry_id  Entry Identifier.
	 */
	public function process_feed( $fields, $entry, $form_data, $entry_id ) {
	}

	/**
	 * Authenticate with the Integration API.
	 *
	 * @param array $data     Data to be parsed.
	 * @param int   $form_id  Form Identifier.
	 *
	 * @return mixed id or error object
	 */
	public function authorize_api( $data = array(), $form_id = '' ) {
	}

	/**
	 * Get Integration account lists.
	 *
	 * @param string $connection_id Connection Identifier.
	 * @param string $account_id    Account identifier.
	 *
	 * @return mixed array or error object
	 */
	public function api_lists( $connection_id = '', $account_id = '' ) {}

	/**
	 * Get Integration group lists.
	 *
	 * @param string $connection_id Connection Identifier.
	 * @param string $account_id    Account identifier.
	 * @param string $list_id       List id for fetching.
	 *
	 * @return mixed array or error object
	 */
	public function api_groups( $connection_id = '', $account_id = '', $list_id = '' ) {
		return array();
	}

	/**
	 * Fetch Integration account list fields.
	 *
	 * @param string $connection_id Connection Identifier.
	 * @param string $account_id    Account identifier.
	 * @param string $list_id       List id for fetching.
	 *
	 * @return mixed array or error object
	 */
	public function fetch_api_fields( $connection_id = '', $account_id = '', $list_id = '' ) {}

	/**
	 * Integration account list options HTML.
	 *
	 * @param string $connection_id Connection Identifier.
	 * @param array  $connection    Connection data object.
	 *
	 * @return void
	 */
	public function output_options( $connection_id = '', $connection = array() ) {}

	/**
	 * Getting fields ready for select list options.
	 *
	 * @param array  $form_fields     Form's field array.
	 * @param string $form_field_type Field Type for the specfic form.
	 *
	 * @return array
	 */
	public function get_form_field_select( $form_fields = array(), $form_field_type = '' ) {
		if ( empty( $form_fields ) || empty( $form_field_type ) ) {
			return array();
		}

		$formatted = array();
		foreach ( $form_fields as $id => $form_field ) {
			if ( 'email' === $form_field_type && ! in_array( $form_field['type'], array( 'email' ), true ) ) {
				unset( $form_fields[ $id ] );
			}
		}
		foreach ( $form_fields as $id => $form_field ) {
			$formatted[] = array(
				'id'            => $form_field['id'],
				'key'           => 'value',
				'type'          => $form_field['type'],
				'subtype'       => '',
				'provider_type' => $form_field_type,
				'label'         => $form_field['label'],
			);
		}
		return $formatted;
	}

	/**
	 * Conditional Logic handler function.
	 *
	 * @param string $connection_id Connection Identifier.
	 * @param array  $connection    Connection data object.
	 * @param string $form_data     Form data object.
	 *
	 * @return array $output        Output to be rendered.
	 */
	public function conditional_logic( $connection_id = '', $connection = array(), $form_data = '' ) {
		$selected_logic           = ! empty( $connection['conditional_logic']['logic'] ) ? $connection['conditional_logic']['logic'] : '';
		$selected_field_select    = ! empty( $connection['conditional_logic']['field_select'] ) ? $connection['conditional_logic']['field_select'] : '';
		$selected_condition       = ! empty( $connection['conditional_logic']['condition'] ) ? $connection['conditional_logic']['condition'] : '';
		$selected_input_choice    = ! empty( $connection['conditional_logic']['input_choice'] ) ? $connection['conditional_logic']['input_choice'] : '';
		$selected_multiple_choice = ! empty( $connection['conditional_logic']['multiple_choice'] ) ? $connection['conditional_logic']['multiple_choice'] : '';
		$selected_country_choice  = ! empty( $connection['conditional_logic']['country_choice'] ) ? $connection['conditional_logic']['country_choice'] : '';

		$output              = '<div class="evf-provider-conditional evf-connection-block">';
		$output             .= sprintf(
			'<p><input id="%s_contional_logic" class="evf-enable-conditional-logic" type="checkbox" value="1" name="integrations[%s][%s][conditional_logic][status]" %s><label for="%s_conditional_logic">%s</label></p>',
			esc_attr( $connection_id ),
			esc_attr( $this->id ),
			esc_attr( $connection_id ),
			checked( ! empty( $connection['conditional_logic']['status'] ), true, false ),
			esc_attr( $connection_id ),
			__( 'Use conditional logic', 'everest-forms-pro' )
		);
		$output             .= '<div class="evf-conditional-container" data-con_id="' . $connection_id . '" data-source="' . $this->id . '">';
			$output         .= '<h4>' . __( 'Conditional Rules', 'everest-forms-pro' ) . '</h4>';
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
		$output             .= '</div>';

		return $output;
	}

	/**
	 * Error wrapper for WP_Error.
	 *
	 * @param string $message message to be printed.
	 * @param string $parent  parent passed for error rendering.
	 *
	 * @return WP_Error
	 */
	public function error( $message = '', $parent = '0' ) {
		return new WP_Error( $this->id . '-error', $message );
	}
}
