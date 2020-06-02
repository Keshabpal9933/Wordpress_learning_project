<?php
/**
 * EverestForms Conditional Logics
 *
 * @package EverestForms\Admin
 * @version 1.1.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Conditional_Logics Class.
 */
class EVF_Conditional_Logics {

	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'everest_forms_field_options_after_advanced-options', array( $this, 'conditional_logic_field' ), 11, 2 );
		add_action( 'everest_forms_field_properties', array( $this, 'conditional_logic_field_properties' ), 10, 3 );
		add_action( 'everest_forms_entry_email_process', array( $this, 'conditional_logic_email_process' ), 10, 5 );
		add_action( 'everest_forms_entry_payment_process', array( $this, 'conditional_logic_payment_process' ), 10, 5 );
		add_action( 'everest_forms_inline_email_settings', array( $this, 'conditional_logic_email_setting' ), 50, 2 );
		add_action( 'everest_forms_inline_payment_settings', array( $this, 'conditional_logic_payment_setting' ), 50, 3 );
		add_action( 'everest_forms_inline_submit_settings', array( $this, 'conditional_logic_submit_setting' ), 50, 3 );
		add_filter( 'everest_forms_visible_fields', array( $this, 'conditional_logic_visible_field' ), 50, 4 );
		add_action( 'everest_forms_process_complete', array( $this, 'set_transient' ), 5, 4 );
		add_action( 'evf_paypal_standard_process_complete', array( $this, 'send_email_after_payment_complete' ), 50, 4 );
		add_action( 'everest_forms_stripe_process_complete', array( $this, 'send_email_after_payment_complete' ), 50, 4 );
	}

	/**
	 * Save posted entry data in transient.
	 *
	 * @param array $form_fields Form fields.
	 * @param array $entry Entry data.
	 * @param array $form_data Form data.
	 * @param int   $entry_id Entry ID.
	 */
	public function set_transient( $form_fields, $entry, $form_data, $entry_id ) {
		$notifications       = isset( $form_data['settings']['email'] ) ? $form_data['settings']['email'] : array();
		$data                = array();
		$data['form_fields'] = $form_fields;
		$data['entry']       = $entry;
		$transient_name      = 'evf_entry_data_' . (int) $entry_id;

		foreach ( $notifications as $notification ) :
			if ( ! isset( $notification['conditional_logic_status'] ) || '1' !== $notification['conditional_logic_status'] ) {
				continue;
			}

			$conditionals = isset( $notification['conditionals'] ) ? $notification['conditionals'] : array();

			foreach ( $conditionals as $group ) {
				foreach ( $group as $rule ) {
					$rule_field = isset( $rule['field'] ) ? $rule['field'] : '';
					if ( ! empty( $rule_field ) && 'payment' === $rule_field ) {
						if ( false === get_transient( $transient_name ) ) {
							set_transient( $transient_name, $data, DAY_IN_SECONDS );
						}
					}
				}
			}
		endforeach;
	}

	/**
	 * Send Email only after payment is completed.
	 *
	 * @param array $payment Entry details.
	 * @param array $form_data Form data.
	 * @param int   $payment_id Payment ID.
	 * @param array $data Payment details.
	 */
	public function send_email_after_payment_complete( $payment, $form_data, $payment_id, $data ) {
		$payment_status = isset( $data['payment_status'] ) ? $data['payment_status'] : '';
		$notifications  = isset( $form_data['settings']['email'] ) ? $form_data['settings']['email'] : array();
		$is_not_field   = array( 'location', 'status', 'type', 'meta' );
		$transient_data = get_transient( 'evf_entry_data_' . absint( $payment_id ) );
		$fields         = isset( $data['fields'] ) ? $data['fields'] : $transient_data['form_fields'];
		$entry          = isset( $data['entry'] ) ? $data['entry'] : $transient_data['entry'];

		if ( 'complete' === strtolower( $payment_status ) ) {
			$payment_status = 'completed';
		}

		foreach ( $fields as $key => $value ) {
			if ( ! in_array( $key, $is_not_field ) ) {
				$entry['form_fields'][ $key ] = $value;
			}
		}

		foreach ( $notifications as $connection_id => $notification ) :
			if ( ! isset( $notification['conditional_logic_status'] ) || '1' !== $notification['conditional_logic_status'] ) {
				continue;
			}

			$type          = isset( $notification['conditional_option'] ) ? $notification['conditional_option'] : '';
			$conditionals  = isset( $notification['conditionals'] ) ? $notification['conditionals'] : array();
			$process_email = true;

			foreach ( $conditionals as $group_id => $group ) {

				foreach ( $group as $rule_id => $rule ) {

					$rule_field    = isset( $rule['field'] ) ? $rule['field'] : '';
					$rule_operator = isset( $rule['operator'] ) ? $rule['operator'] : '';
					$rule_value    = isset( $rule['value'] ) ? $rule['value'] : '';

					if ( ! empty( $rule_field ) && 'payment' !== $rule_field ) {
						continue;
					}

					switch ( $rule_operator ) {
						case 'is':
							$process_email = ( $rule_value == strtolower( $payment_status ) );
							break;
						case 'is_not':
							$process_email = ( $rule_value != strtolower( $payment_status ) );
							break;
					}
				}
			}

			if ( 'not_send' === $type ) {
				$process_email = ! $process_email;
			}

			if ( ! $process_email ) {
				continue;
			}

			$email        = array();
			$evf_to_email = isset( $notification['evf_to_email'] ) ? $notification['evf_to_email'] : '';

			// Setup email properties.
			/* translators: %s - form name. */
			$email['subject']        = ! empty( $notification['evf_email_subject'] ) ? $notification['evf_email_subject'] : sprintf( esc_html__( 'New %s Entry', 'everest-forms-pro' ), $form_data['settings']['form_title'] );
			$email['address']        = explode( ',', apply_filters( 'everest_forms_process_smart_tags', $evf_to_email, $form_data, $fields, $payment->entry_id ) );
			$email['address']        = array_map( 'sanitize_email', $email['address'] );
			$email['sender_name']    = ! empty( $notification['evf_from_name'] ) ? $notification['evf_from_name'] : get_bloginfo( 'name' );
			$email['sender_address'] = ! empty( $notification['evf_from_email'] ) ? $notification['evf_from_email'] : get_option( 'admin_email' );
			$email['reply_to']       = ! empty( $notification['evf_reply_to'] ) ? $notification['evf_reply_to'] : $email['sender_address'];
			$email['message']        = ! empty( $notification['evf_email_message'] ) ? $notification['evf_email_message'] : '{all_fields}';
			$email                   = apply_filters( 'everest_forms_entry_email_atts', $email, $fields, $entry, $form_data );

			$attachment = '';

			// Create new email.
			$emails = new EVF_Emails();
			$emails->__set( 'form_data', $form_data );
			$emails->__set( 'fields', $fields );
			$emails->__set( 'entry_id', $payment->entry_id );
			$emails->__set( 'from_name', $email['sender_name'] );
			$emails->__set( 'from_address', $email['sender_address'] );
			$emails->__set( 'reply_to', $email['reply_to'] );
			$emails->__set( 'attachments', apply_filters( 'everest_forms_email_file_attachments', $attachment, $entry, $form_data, 'entry-email', $connection_id, $payment->entry_id ) );

			// Maybe include Cc and Bcc email addresses.
			if ( 'yes' === get_option( 'everest_forms_enable_email_copies' ) ) {

				if ( ! empty( $notification['evf_carboncopy'] ) ) {
					$emails->__set( 'cc', $notification['evf_carboncopy'] );
				}

				if ( ! empty( $notification['evf_blindcarboncopy'] ) ) {
					$emails->__set( 'bcc', $notification['evf_blindcarboncopy'] );
				}
			}

			$emails = apply_filters( 'everest_forms_entry_email_before_send', $emails );

			// Send entry email.
			foreach ( $email['address'] as $address ) {
				$emails->send( trim( $address ), $email['subject'], $email['message'] );
			}

		endforeach;
	}

	public function conditional_logic_visible_field( $visibility, $field, $entry, $form_data ) {
		$fields                   = isset( $form_data['form_fields'] ) ? $form_data['form_fields'] : array();
		$field_conditional_status = isset( $form_data['form_fields'][ $field['id'] ]['conditional_logic_status'] ) ? $form_data['form_fields'][ $field['id'] ]['conditional_logic_status'] : '0';
		$field_conditional_option = isset( $form_data['form_fields'][ $field['id'] ]['conditional_option'] ) ? $form_data['form_fields'][ $field['id'] ]['conditional_option'] : 'show';
		$field_conditions         = isset( $form_data['form_fields'][ $field['id'] ]['conditionals'] ) ? $form_data['form_fields'][ $field['id'] ]['conditionals'] : array();

		if ( '1' === $field_conditional_status ) {
			foreach ( $field_conditions as $group_id => $group ) {
				$pass_group = true;

				foreach ( $group as $rule_id => $rule ) {

					$rule_field    = $rule['field'];
					$rule_operator = $rule['operator'];
					$rule_value    = isset( $rule['value'] ) ? $rule['value'] : '';
					$pass_rule     = true;

					if ( empty( $rule_field ) || ! isset( $fields[ $rule_field ]['type'] ) ) {
						continue;
					}

					if ( in_array( $fields[ $rule_field ]['type'], array( 'text', 'first-name', 'last-name', 'textarea', 'email', 'url', 'number', 'hidden' ), true ) ) {
							$left  = trim( strtolower( $entry['form_fields'][ $rule_field ] ) );
							$right = trim( strtolower( $rule_value ) );

						switch ( $rule_operator ) {
							case 'is':
								$pass_rule = ( $left == $right );
								break;
							case 'is_not':
								$pass_rule = ( $left != $right );
								break;
						}
					} else {
						if ( in_array( $fields[ $rule_field ]['type'], array( 'checkbox' ), true ) ) {
							$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
							if ( is_array( $values ) ) {
								$values = implode( ',', $values );
							}
						} else {
							$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
						}

						if ( ! isset( $fields[ $rule_field ]['value_raw'] ) ) {
							$provided_id = array();
							foreach ( $form_data['form_fields'][ $rule_field ]['choices'] as $key => $choice ) {
								$choice = array_map( 'sanitize_text_field', $choice );
								foreach ( $values as $value ) {
									$value = evf_decode_string( $value );
									if ( in_array( $value, $choice, true ) ) {
										$provided_id[] = $value;
									}
								}
							}
						}
						if ( ! isset( $fields[ $rule_field ]['value_raw'] ) && ! empty( $provided_id ) ) {
							$left = (array) $provided_id;
						} else {
							$left = explode( ',', $values );
						}
						$right = trim( $rule_value );

						switch ( $rule_operator ) {
							case 'is':
								$pass_rule = in_array( $right, $left );
								break;
							case 'is_not':
								$pass_rule = ! in_array( $right, $left );
								break;
						}
					}

					if ( ! $pass_rule ) {
						$pass_group = false;
						break;
					}
				}

				if ( ! $pass_group ) {
					$visibility = false;
				}
			}

			if ( 'hide' === $field_conditional_option ) {
				$visibility = ! $visibility;
			}
		}

		return $visibility;
	}

	/**
	 * Adding conditional logic rules attr.
	 *
	 * @param array $field_properties Field properties array data.
	 * @param array $field Field data.
	 * @param array $form_data Form data.
	 */
	public function conditional_logic_field_properties( $field_properties, $field, $form_data ) {
		$required = isset( $field['required'] ) ? $field['required'] : 0;
		$field_properties['inputs']['primary']['attr']['conditional_id'] = $field['id'];

		if ( isset( $form_data['settings']['submit']['connection_1']['conditional_logic_status'] ) && '1' === $form_data['settings']['submit']['connection_1']['conditional_logic_status'] ) {
			$conditional_field_trigger_submit = $this->field_is_trigger_for_submit( $field, $form_data );
			if ( $conditional_field_trigger_submit ) {
				$field_properties['container']['class'][] = 'everest-forms-trigger-conditional';
			}
		}

		$conditional_field_trigger_field = $this->field_is_trigger( $field, $form_data );

		if ( $conditional_field_trigger_field ) {
			$field_properties['container']['class'][] = 'everest-forms-trigger-conditional';
		}

		if ( ! isset( $field['conditional_logic_status'] ) ) {
			return $field_properties;
		}

		$conditional_rules = array(
			'conditional_option' => $field['conditional_option'],
			'conditionals'       => $field['conditionals'],
			'required'           => $required,
		);

		$field_properties['inputs']['primary']['attr']['conditional_rules'] = json_encode( $conditional_rules );
		$field_properties['container']['class'][]                           = 'everest-forms-conditional-field';

		return $field_properties;
	}

	/**
	 * Check if the field is triggerd on Submit button conditional logic.
	 *
	 * @param [array] $field Fields.
	 * @param [array] $form_data Form data.
	 */
	public function field_is_trigger_for_submit( $field, $form_data ) {

		$field_id = $field['id'];

		foreach ( $form_data['settings']['submit'] as $field ) {

			if ( empty( $field['conditional_logic_status'] ) || empty( $field['conditionals'] ) || '1' != $field['conditional_logic_status'] ) {
				continue;
			}

			foreach ( $field['conditionals'] as $group ) {

				foreach ( $group as $rule ) {

					if ( ! isset( $rule['field'] ) || '' === trim( $rule['field'] ) || empty( $rule['operator'] ) ) {
						continue;
					}

					if (
						( $rule['field'] == $field_id ) || ( isset( $rule['value'] ) && '' !== trim( $rule['value'] ) && $rule['field'] == $field_id ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if the field is triggerd on conditional logic.
	 *
	 * @param [array] $field Fields.
	 * @param [array] $form_data Form data.
	 */
	public function field_is_trigger( $field, $form_data ) {
		$field_id = $field['id'];

		foreach ( $form_data['form_fields'] as $field ) {

			if ( empty( $field['conditional_logic_status'] ) || empty( $field['conditionals'] ) || '1' != $field['conditional_logic_status'] ) {
				continue;
			}

			foreach ( $field['conditionals'] as $group ) {

				foreach ( $group as $rule ) {

					if ( ! isset( $rule['field'] ) || '' === trim( $rule['field'] ) || empty( $rule['operator'] ) ) {
						continue;
					}

					if ( ( $rule['field'] == $field_id ) || ( isset( $rule['value'] ) && '' !== trim( $rule['value'] ) && $rule['field'] == $field_id ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Form setting for admin and user
	 *
	 * @param  setting
	 * @return void
	 */
	public function conditional_logic_email_setting( $setting, $connection_id ) {
		$form_id   = isset( $_GET['form_id'] ) ? $_GET['form_id'] : 0;
		$form_obj  = EVF()->form->get( $form_id );
		$form_data = ( ! empty( $form_obj->post_content ) ) ? evf_decode( $form_obj->post_content ) : '';
		$this->conditional_block(
			array(
				'form'          => $form_data,
				'type'          => 'settings',
				'panel'         => 'email',
				'connection_id' => $connection_id,
			)
		);
	}

	/**
	 * Form setting for admin and user
	 *
	 * @param  setting
	 * @return void
	 */
	public function conditional_logic_payment_setting( $setting, $gateway, $connection_id ) {
		$form_id   = isset( $_GET['form_id'] ) ? $_GET['form_id'] : 0;
		$form_obj  = EVF()->form->get( $form_id );
		$form_data = ! empty( $form_obj->post_content ) ? evf_decode( $form_obj->post_content ) : '';

		$this->conditional_block(
			array(
				'form'          => $form_data,
				'type'          => 'payments',
				'panel'         => $gateway,
				'connection_id' => $connection_id,
			)
		);
	}

	/**
	 * Form setting for admin and user
	 *
	 * @param  setting
	 * @return void
	 */
	public function conditional_logic_submit_setting( $setting, $panel, $connection_id ) {
		$form_id   = isset( $_GET['form_id'] ) ? $_GET['form_id'] : 0;
		$form_obj  = EVF()->form->get( $form_id );
		$form_data = ! empty( $form_obj->post_content ) ? evf_decode( $form_obj->post_content ) : '';

		$this->conditional_block(
			array(
				'form'          => $form_data,
				'type'          => 'settings',
				'panel'         => $panel,
				'connection_id' => $connection_id,
			)
		);
	}

	public function conditional_logic_email_process( $process, $fields, $form_data, $context, $connection_id ) {
		$email_setting = isset( $form_data['settings']['email'][ $connection_id ] ) ? $form_data['settings']['email'][ $connection_id ] : array();

		if ( ! isset( $email_setting['conditional_logic_status'] ) || '1' !== $email_setting['conditional_logic_status'] ) {
			return $process;
		}

		$type         = isset( $email_setting['conditional_option'] ) ? $email_setting['conditional_option'] : '';
		$conditionals = isset( $email_setting['conditionals'] ) ? $email_setting['conditionals'] : array();
		$pass         = false;

		foreach ( $conditionals as $group_id => $group ) {
			$pass_group = true;

			foreach ( $group as $rule_id => $rule ) {

				$rule_field    = isset( $rule['field'] ) ? $rule['field'] : '';
				$rule_operator = isset( $rule['operator'] ) ? $rule['operator'] : '';
				$rule_value    = isset( $rule['value'] ) ? $rule['value'] : '';

				if ( ! empty( $rule_field ) && 'payment' === $rule_field ) {
					return false;
				}

				if ( empty( $rule_field ) || ! isset( $fields[ $rule_field ]['type'] ) ) {
					continue;
				}

				if ( isset( $fields[ $rule_field ]['type'] ) && in_array( $fields[ $rule_field ]['type'], array( 'text', 'first-name', 'last-name', 'textarea', 'email', 'url', 'number', 'hidden', 'country' ), true ) ) {
						$right = trim( strtolower( $rule_value ) );

					if ( 'country' === $fields[ $rule_field ]['type'] ) {
							$left = trim( strtolower( $fields[ $rule_field ]['value']['country_code'] ) );
					} else {
							$left = trim( strtolower( $fields[ $rule_field ]['value'] ) );
					}

					switch ( $rule_operator ) {
						case 'is':
							$pass_rule = ( $left == $right );
							break;
						case 'is_not':
							$pass_rule = ( $left != $right );
							break;
						case 'empty':
							$pass_rule = ( '' == $left );
							break;
						case 'not_empty':
							$pass_rule = ( '' != $left );
							break;
						case 'greater_than':
							$left      = preg_replace( '/[^0-9.]/', '', $left );
							$pass_rule = ( '' !== $left ) && ( floatval( $left ) > floatval( $right ) );
							break;
						case 'less_than':
							$left      = preg_replace( '/[^0-9.]/', '', $left );
							$pass_rule = ( '' !== $left ) && ( floatval( $left ) < floatval( $right ) );
							break;
					}
				} else {

					if ( in_array( $fields[ $rule_field ]['type'], array( 'checkbox' ), true ) ) {
						$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
						if ( is_array( $values ) ) {
							$values = implode( ',', $values );
						}
					} else {
						$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
					}

					if ( ! isset( $fields[ $rule_field ]['value_raw'] ) ) {
						$provided_id = array();
						foreach ( $form_data['form_fields'][ $rule_field ]['choices'] as $key => $choice ) {
							$choice = array_map( 'sanitize_text_field', $choice );
							foreach ( $values as $value ) {
								$value = evf_decode_string( $value );
								if ( in_array( $value, $choice, true ) ) {
									$provided_id[] = $value;
								}
							}
						}
					}
					if ( ! isset( $fields[ $rule_field ]['value_raw'] ) && ! empty( $provided_id ) ) {
						$left = (array) $provided_id;
					} else {
						$left = explode( ',', $values );
					}
					$right = trim( $rule_value );

					switch ( $rule_operator ) {
						case 'is':
							$pass_rule = in_array( $right, $left );
							break;
						case 'is_not':
							$pass_rule = ! in_array( $right, $left );
							break;
						case 'empty':
							$pass_rule = ( false === $left[0] );
							break;
						case 'not_empty':
							$pass_rule = ( false !== $left[0] );
							break;
					}
				}

				if ( ! $pass_rule ) {
					$pass_group = false;
					break;
				}
			}

			if ( $pass_group ) {
				$pass = true;
			}
		}

		if ( 'not_send' === $type ) {
			$pass = ! $pass;
		}

		return $pass;
	}

	/**
	 * Conditional logic process for payment.
	 *
	 * @param array  $process
	 * @param array  $fields
	 * @param array  $form_data
	 * @param array  $gateway
	 * @param string $connection_id
	 */
	public function conditional_logic_payment_process( $process, $fields, $form_data, $gateway, $connection_id ) {
		$payment_setting = isset( $form_data['payments'][ $gateway ][ $connection_id ] ) ? $form_data['payments'][ $gateway ][ $connection_id ] : array();

		if ( ! isset( $payment_setting['conditional_logic_status'] ) || '1' !== $payment_setting['conditional_logic_status'] ) {
			return $process;
		}

		$type         = isset( $payment_setting['conditional_option'] ) ? $payment_setting['conditional_option'] : '';
		$conditionals = isset( $payment_setting['conditionals'] ) ? $payment_setting['conditionals'] : array();
		$pass         = false;

		foreach ( $conditionals as $group_id => $group ) {
			$pass_group = true;

			foreach ( $group as $rule_id => $rule ) {

				$rule_field    = $rule['field'];
				$rule_operator = $rule['operator'];
				$rule_value    = isset( $rule['value'] ) ? $rule['value'] : '';

				if ( empty( $rule_field ) || ! isset( $fields[ $rule_field ]['type'] ) ) {
					continue;
				}

				if ( in_array( $fields[ $rule_field ]['type'], array( 'text', 'first-name', 'last-name', 'textarea', 'email', 'url', 'number', 'hidden' ), true ) ) {

					$left  = trim( strtolower( $fields[ $rule_field ]['value'] ) );
					$right = trim( strtolower( $rule_value ) );

					switch ( $rule_operator ) {
						case 'is':
							$pass_rule = ( $left == $right );
							break;
						case 'is_not':
							$pass_rule = ( $left != $right );
							break;
						case 'empty':
							$pass_rule = ( '' == $left );
							break;
						case 'not_empty':
							$pass_rule = ( '' != $left );
							break;
						case 'greater_than':
							$left      = preg_replace( '/[^0-9.]/', '', $left );
							$pass_rule = ( '' !== $left ) && ( floatval( $left ) > floatval( $right ) );
							break;
						case 'less_than':
							$left      = preg_replace( '/[^0-9.]/', '', $left );
							$pass_rule = ( '' !== $left ) && ( floatval( $left ) < floatval( $right ) );
							break;
					}
				} else {
					if ( in_array( $fields[ $rule_field ]['type'], array( 'checkbox' ), true ) ) {
						$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
						if ( is_array( $values ) ) {
							$values = implode( ',', $values );
						}
					} else {
						$values = isset( $fields[ $rule_field ]['value_raw'] ) ? $fields[ $rule_field ]['value_raw'] : $fields[ $rule_field ]['value'];
					}

					if ( ! isset( $fields[ $rule_field ]['value_raw'] ) ) {
						$provided_id = array();
						foreach ( $form_data['form_fields'][ $rule_field ]['choices'] as $key => $choice ) {
							$choice = array_map( 'sanitize_text_field', $choice );
							foreach ( $values as $value ) {
								$value = evf_decode_string( $value );
								if ( in_array( $value, $choice, true ) ) {
									$provided_id[] = $value;
								}
							}
						}
					}
					if ( ! isset( $fields[ $rule_field ]['value_raw'] ) && ! empty( $provided_id ) ) {
						$left = (array) $provided_id;
					} else {
						$left = explode( ',', $values );
					}
					$right = trim( $rule_value );

					switch ( $rule_operator ) {
						case 'is':
							$pass_rule = in_array( $right, $left );
							break;
						case 'is_not':
							$pass_rule = ! in_array( $right, $left );
							break;
						case 'empty':
							$pass_rule = ( false === $left[0] );
							break;
						case 'not_empty':
							$pass_rule = ( false !== $left[0] );
							break;
					}
				}

				if ( ! $pass_rule ) {
					$pass_group = false;
					break;
				}
			}

			if ( $pass_group ) {
				$pass = true;
			}
		}

		if ( 'not_send' === $type ) {
			$pass = ! $pass;
		}

		return $pass;
	}

	public static function conditional_block( $args = array() ) {
		if ( ! empty( $args['form'] ) ) {
			$form_fields = evf_get_form_fields( $args['form'], array( 'text', 'textarea', 'select', 'radio', 'email', 'url', 'checkbox', 'number', 'payment-multiple', 'payment-single', 'hidden' ) );
		} else {
			$form_fields = array();
		}

		$type  = ! empty( $args['type'] ) ? $args['type'] : 'field';
		$panel = ! empty( $args['panel'] ) ? $args['panel'] : false;
		$field = ! empty( $args['field'] ) ? $args['field'] : array();

		// Check if form fields has no support for conditional logic.
		$disable_conditional_fields = apply_filters( 'everest_forms_disable_conditional_fields', array( 'hidden' ) );
		$field_to_be_restricted     = apply_filters(
			'everest_forms_restricted_conditional_fields',
			array(
				'html',
				'title',
				'address',
				'image-upload',
				'file-upload',
				'date-time',
				'hidden',
				'scale-rating',
				'likert',
			)
		);

		if ( isset( $field['type'] ) && in_array( $field['type'], $disable_conditional_fields, true ) ) {
			return;
		}

		$form_id            = isset( $_GET['form_id'] ) ? $_GET['form_id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$form_obj           = EVF()->form->get( $form_id );
		$form_data          = ! empty( $form_obj->post_content ) ? evf_decode( $form_obj->post_content ) : '';
		$conditional_fields = array();

		if ( ! empty( $form_data['form_fields'] ) ) {
			foreach ( $form_data['form_fields'] as $all_field ) {
				if ( ! in_array( $all_field['type'], $field_to_be_restricted, true ) ) {
					if ( isset( $field['id'] ) && $field['id'] === $all_field['id'] ) {
						continue;
					}

					$conditional_fields[] = $all_field;
				}
			}
		}

		if ( 'field' === $type ) {
			$conditional_option = isset( $field['conditional_option'] ) ? $field['conditional_option'] : '';
			$conditionals       = ! empty( $field['conditionals'] ) ? $field['conditionals'] : array();
			$data               = array(
				'conditional_option' => $conditional_option,
				'conditionals'       => $conditionals,
			);
			$f_id               = str_replace( '-', '_', $field['id'] );

			wp_localize_script( 'everest-forms-conditionals-scripts', 'evf_field_integration_data_' . $f_id, ! empty( $data ) ? wp_json_encode( $data ) : array() );
			$instance = $args['instance'];
			$value    = isset( $field['conditional_logic_status'] ) ? $field['conditional_logic_status'] : '0';
			$tooltip  = __( 'Check this option to enable condition logic.', 'everest-forms-pro' );

			// Build output.
			$output = $instance->field_element(
				'checkbox',
				$field,
				array(
					'slug'    => 'conditional_logic_status',
					'value'   => $value,
					'desc'    => __( 'Enable Conditional Logic', 'everest-forms-pro' ),
					'tooltip' => $tooltip,
					'data'    => array( 'panel-source' => $type ),
				),
				false
			);
			$output = $instance->field_element(
				'row',
				$field,
				array(
					'slug'    => 'conditional_logic_status',
					'class'   => 'evf_conditional_logic_container',
					'content' => $output,
				),
				false
			);

			$output .= '<div class="evf-field-conditional-container">';
			$output .= '<h4>' . __( 'Conditional Rules', 'everest-forms-pro' ) . '</h4>';
			$output .= '<div class="evf-field-logic">';
			$output .= sprintf( '<select class="evf-field-show-hide" name="form_fields[%s][conditional_option]">', $field['id'] );
			$output .= '<option value="show"  ' . selected( $conditional_option, 'show', false ) . '>Show</option>';
			$output .= '<option value="hide" ' . selected( $conditional_option, 'hide', false ) . '>Hide</option>';
			$output .= '</select>';
			$output .= '<p> only if following matches.</p>';
			$output .= '</div>';

			if ( $conditionals ) {
				foreach ( $conditionals as $group_id => $conditions ) {
					$output .= '<ul class="evf-field-conditional-wrapper" data-group=' . $group_id . ' data-field-id=' . $field['id'] . '>';

					foreach ( $conditions as $key => $condition ) {
						$output .= '<li class="evf-conditional-group" data-key="' . $key . '">';
						$output .= '<div class="evf-form-group">';
						$output .= sprintf( '<select class="evf-field-conditional-field-select" data-panel-source = ' . $type . ' name="form_fields[%s][conditionals][' . $group_id . '][' . $key . '][field]"><option>---Select Field---</option>', $field['id'] );

						if ( ! empty( $conditional_fields ) ) {
							foreach ( $conditional_fields as $form_fields ) {
								if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
									$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
								}
							}
						}

						$output .= '</select>';
						$output .= sprintf( '<select class="evf-field-conditional-condition" name="form_fields[%s][conditionals][' . $group_id . '][' . $key . '][operator]">', $field['id'] );
						$output .= '<option value = "is"  ' . selected( $condition['operator'], 'is', false ) . '> is </option>';
						$output .= '<option value = "is_not" ' . selected( $condition['operator'], 'is_not', false ) . '> is not </option>';
						$output .= '<option value = "empty" ' . selected( $condition['operator'], 'empty', false ) . '> empty </option>';
						$output .= '<option value = "not_empty" ' . selected( $condition['operator'], 'not_empty', false ) . '> not empty </option>';
						$output .= '<option value = "greater_than" ' . selected( $condition['operator'], 'greater_than', false ) . '> greater than </option>';
						$output .= '<option value = "less_than" ' . selected( $condition['operator'], 'less_than', false ) . '> less than </option>';
						$output .= '</select>';
						$output .= '</div class="evf-form-group">';
						$output .= '<a class="conditonal-rule-add" href="#">AND</a>';
						$output .= '<a class="conditonal-rule-remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
						$output .= '</li>';
					}

					$output .= '<span class="conditional_or">OR</span>';
					$output .= '</ul>';
				}
			} else {
					$output .= '<ul class="evf-field-conditional-wrapper" data-group="1" data-field-id =' . $field['id'] . '>';
					$output .= '<li class="evf-conditional-group" data-key="1">';
					$output .= '<div class="evf-form-group">';
					$output .= sprintf( '<select class="evf-field-conditional-field-select" data-panel-source = ' . $type . ' name="form_fields[%s][conditionals][1][1][field]"><option>---Select Field---</option>', $field['id'] );
				if ( ! empty( $conditional_fields ) ) {
					foreach ( $conditional_fields as $form_fields ) {
						if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
							$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
						}
					}
				}
					$output .= '</select>';
					$output .= sprintf( '<select class="evf-field-conditional-condition" name="form_fields[%s][conditionals][1][1][operator]">', $field['id'] );
					$output .= '<option value = "is"> is </option>';
					$output .= '<option value = "is_not"> is not </option>';
					$output .= '<option value = "empty"> empty </option>';
					$output .= '<option value = "not_empty"> not empty </option>';
					$output .= '<option value = "greater_than"> greater than </option>';
					$output .= '<option value = "less_then"> less than </option>';
					$output .= '</select>';
					$output .= '<input class="evf-field-conditional-input" name="form_fields[' . $field['id'] . '][conditionals][1][1][value]" type="text" value="" />';
					$output .= '</div>';
					$output .= '<a class="conditonal-rule-add" href="#">AND</a>';
					$output .= '<a class="conditonal-rule-remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
					$output .= '</li>';
					$output .= '<span class="conditional_or">OR</span>';
					$output .= '</ul>';
			}

			$output .= '<a class="button button-small conditonal-group-add" data-panel-source=' . $type . ' href="#">Add Conditional Group</a>';
			$output .= '</div>';

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ! empty( $form_data ) && ( 'settings' === $type || 'payments' === $type ) ) {
			$con_id   = ! empty( $args['connection_id'] ) ? $args['connection_id'] : false;
			$settings = $form_data;

			if ( ! isset( $settings[ $type ][ $panel ]['connection_1'] ) ) {
				$settings[ $type ][ $panel ]['connection_1'] = array( 'connection_name' => __( 'Admin Notification', 'everest-forms-pro' ) );
				$email_settings                              = array( 'conditional_logic_status', 'conditional_option', 'conditionals' );
				foreach ( $email_settings as $email_setting ) {
					$settings[ $type ][ $panel ]['connection_1'][ $email_setting ] = isset( $settings[ $type ][ $panel ][ $email_setting ] ) ? $settings[ $type ][ $panel ][ $email_setting ] : '';
				}
			}

			foreach ( $settings[ $type ][ $panel ] as $connection_id => $data ) {
				if ( $connection_id === $con_id ) {
					$conditionals       = ! empty( $settings[ $type ][ $panel ][ $connection_id ]['conditionals'] ) ? $settings[ $type ][ $panel ][ $connection_id ]['conditionals'] : array();
					$conditional_option = isset( $settings[ $type ][ $panel ][ $connection_id ]['conditional_option'] ) ? $settings[ $type ][ $panel ][ $connection_id ]['conditional_option'] : 'send';
					$data               = array(
						'conditional_option' => $conditional_option,
						'conditionals'       => $conditionals,
					);
					wp_localize_script( 'everest-forms-conditionals-scripts', 'evf_' . $type . '_' . $panel . '_conditional_data_' . $connection_id, ! empty( $data ) ? wp_json_encode( $data ) : array() );
					$tooltip = __( 'Check this option to enable condition logic.', 'everest-forms-pro' );
					everest_forms_panel_field(
						'checkbox',
						$panel,
						'conditional_logic_status',
						$form_data,
						sprintf( __( 'Enable Conditional Logic', 'everest-forms-pro' ) ),
						array(
							'default'    => isset( $settings[ $type ][ $panel ][ $connection_id ]['conditional_logic_status'] ) ? $settings[ $type ][ $panel ][ $connection_id ]['conditional_logic_status'] : 0,
							'tooltip'    => $tooltip,
							'class'      => 'evf_conditional_logic_container',
							'data'       => array( 'panel-source' => $panel ),
							'parent'     => $type,
							'subsection' => $connection_id,
						)
					);

					$output  = '<div class="everest-forms-panel-field evf-field-conditional-container everest-forms-border-container" data-connection_id="' . $connection_id . '">';
					$output .= '<h4 class="everest-forms-border-container-title">' . __( 'Conditional Rules', 'everest-forms-pro' ) . '</h4>';
					$output .= '<div class="evf-field-logic">';
					if ( 'submit' === $panel ) {
						$output .= '<select class="evf-field-show-hide" name="' . $type . '[' . $panel . '][' . $connection_id . '][conditional_option]">';
						$output .= '<option value="show"  ' . selected( $conditional_option, 'show', false ) . '>' . __( 'Show', 'everest-forms-pro' ) . '</option>';
						$output .= '<option value="hide" ' . selected( $conditional_option, 'hide', false ) . '>' . __( 'Hide', 'everest-forms-pro' ) . '</option>';
						$output .= '<option value="disable" ' . selected( $conditional_option, 'disable', false ) . '>' . __( 'Disable', 'everest-forms-pro' ) . '</option>';
						$output .= '</select>';
						$output .= '<p> only if following matches.</p>';
					} else {
						$output .= '<select class="evf-field-show-hide" name="' . $type . '[' . $panel . '][' . $connection_id . '][conditional_option]">';
						$output .= '<option value="send"  ' . selected( $conditional_option, 'send', false ) . '>Send</option>';
						$output .= '<option value="not_send" ' . selected( $conditional_option, 'not_send', false ) . '>Don\'t send</option>';
						$output .= '</select>';
						$output .= '<p> only if following matches.</p>';
					}
					$output .= '</div>';
					if ( $conditionals ) {
						foreach ( $conditionals as $group_id => $conditions ) {
							$output .= '<ul class="evf-field-conditional-wrapper" data-group=' . $group_id . '>';
							foreach ( $conditions as $key => $condition ) {
								$operator = isset( $condition['operator'] ) ? $condition['operator'] : 'is';
								$output  .= '<li class="evf-conditional-group" data-key="' . $key . '">';
								$output  .= '<div class="evf-form-group">';
								$output  .= '<select class="evf-field-conditional-field-select" data-source=' . $panel . ' data-panel-source = ' . $type . ' name="' . $type . '[' . $panel . '][' . $connection_id . '][conditionals][' . $group_id . '][' . $key . '][field]">';
								if ( ! empty( $conditional_fields ) ) {
									if ( 'email' === $panel ) {
											$output .= '<optgroup label="Fields"><option>---Select Field---</option>';
										foreach ( $conditional_fields as $form_fields ) {
											if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
												$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
											}
										}
											$output .= '</optgroup>';
											$output .= '<optgroup label="Payments"><option data-field_type="payment" value="payment" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', 'payment', false ) . '>' . __( 'Payment', 'everest-forms-pro' ) . '</option></optgroup>';
									} else {
											$output .= '<option>---Select Field---</option>';
										foreach ( $conditional_fields as $form_fields ) {
											if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
												$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
											}
										}
									}
								}
								$output .= '</select>';
								$output .= '<select class="evf-field-conditional-condition" name="' . $type . '[' . $panel . '][' . $connection_id . '][conditionals][' . $group_id . '][' . $key . '][operator]">';
								$output .= '<option value = "is"  ' . selected( $operator, 'is', false ) . '> is </option>';
								$output .= '<option value = "is_not" ' . selected( $operator, 'is_not', false ) . '> is not </option>';
								$output .= '<option value = "empty" ' . selected( $operator, 'empty', false ) . '> empty </option>';
								$output .= '<option value = "not_empty" ' . selected( $operator, 'not_empty', false ) . '> not empty </option>';
								$output .= '<option value = "greater_than" ' . selected( $operator, 'greater_than', false ) . '> greater than </option>';
								$output .= '<option value = "less_than" ' . selected( $operator, 'less_than', false ) . '> less than </option>';
								$output .= '</select>';
								$output .= '</div class="evf-form-group">';
								$output .= '<a class="conditonal-rule-add" href="#">AND</a>';
								$output .= '<a class="conditonal-rule-remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
								$output .= '</li>';
							}
							$output .= '<span class="conditional_or">OR</span>';
							$output .= '</ul>';
						}
					} else {
							$output .= '<ul class="evf-field-conditional-wrapper" data-group="1">';
							$output .= '<li class="evf-conditional-group" data-key="1">';
							$output .= '<div class="evf-form-group">';
							$output .= '<select class="evf-field-conditional-field-select" data-source=' . $panel . ' data-panel-source = ' . $type . ' name="' . $type . '[' . $panel . '][connection_1][conditionals][1][1][field]"><option>---Select Field---</option>';
						if ( ! empty( $conditional_fields ) ) {
							if ( 'email' === $panel ) {
								$output .= '<optgroup label="Fields"><option>---Select Field---</option>';
								foreach ( $conditional_fields as $form_fields ) {
									if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
										$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
									}
								}
								$output .= '</optgroup>';
								$output .= '<optgroup label="Payments"><option data-field_type="payment" value="payment" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', 'payment', false ) . '>' . __( 'Payment', 'everest-forms-pro' ) . '</option></optgroup>';
							} else {
								$output .= '<option>---Select Field---</option>';
								foreach ( $conditional_fields as $form_fields ) {
									if ( isset( $form_fields['meta-key'], $form_fields['label'], $form_fields['type'] ) ) {
										$output .= '<option class="evf-conditional-fields" data-field_type="' . $form_fields['type'] . '" data-field_id="' . $form_fields['id'] . '" value="' . $form_fields['id'] . '" ' . selected( isset( $condition['field'] ) ? $condition['field'] : '', $form_fields['id'], false ) . '>' . $form_fields['label'] . '</option>';
									}
								}
							}
						}
							$output .= '</select>';
							$output .= '<select class="evf-field-conditional-condition" name="' . $type . '[' . $panel . '][connection_1][conditionals][1][1][operator]">';
							$output .= '<option value = "is"> is </option>';
							$output .= '<option value = "is_not"> is not </option>';
							$output .= '<option value = "empty"> empty </option>';
							$output .= '<option value = "not_empty"> not empty </option>';
							$output .= '<option value = "greater_than"> greater than </option>';
							$output .= '<option value = "less_then"> less than </option>';
							$output .= '</select>';
							$output .= '<input class="evf-field-conditional-input" name="' . $type . '[' . $panel . '][connection_1][conditionals][1][1][value]" type="text" value="" />';
							$output .= '</div>';
							$output .= '<a class="conditonal-rule-add" href="#">AND</a>';
							$output .= '<a class="conditonal-rule-remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
							$output .= '</li>';
							$output .= '<span class="conditional_or">OR</span>';
							$output .= '</ul>';
					}
					$output .= '<a class="button button-small conditonal-group-add" data-panel-source=' . $type . ' href="#">Add Conditional Group</a>';
					$output .= '</div>';

					echo $output;
				}
			}
		}
	}

	/**
	 * Output conditional logic field option settings.
	 *
	 * @param array  $field Field array data.
	 * @param object $instance Form instance.
	 */
	public function conditional_logic_field( $field, $instance ) {
		?>
		<div class="everest-forms-conditional-fields everest-forms-field-option-group everest-forms-field-option-group-conditionals everest-forms-hide closed" id="everest-forms-field-option-conditionals-<?php echo esc_attr( $field['id'] ); ?>">
			<a href="#" class="everest-forms-field-option-group-toggle">
				<?php esc_html_e( 'Conditional Logic', 'everest-forms-pro' ); ?> <i class="handlediv"></i>
			</a>
			<div class="everest-forms-field-option-group-inner">
				<?php
				self::conditional_block(
					array(
						'form'     => $instance->form_id,
						'field'    => $field,
						'instance' => $instance,
					)
				);
				?>
			</div>
		</div>
		<?php
	}
}

new EVF_Conditional_Logics();
