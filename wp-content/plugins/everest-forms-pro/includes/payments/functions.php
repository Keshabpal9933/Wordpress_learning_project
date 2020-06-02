<?php
/**
 * Payment related functions.
 *
 * @package    Everest Forms Pro
 */

/**
 * Get supported currencies.
 *
 * @since 1.2.4
 *
 * @return array
 */
function evf_get_currencies() {
	$currencies = array(
		'USD' => array(
			'name'                => esc_html__( 'U.S. Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'GBP' => array(
			'name'                => esc_html__( 'Pound Sterling', 'everest-forms-pro' ),
			'symbol'              => '&pound;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'EUR' => array(
			'name'                => esc_html__( 'Euro', 'everest-forms-pro' ),
			'symbol'              => '&euro;',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'AUD' => array(
			'name'                => esc_html__( 'Australian Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'BRL' => array(
			'name'                => esc_html__( 'Brazilian Real', 'everest-forms-pro' ),
			'symbol'              => 'R$',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'CAD' => array(
			'name'                => esc_html__( 'Canadian Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'CZK' => array(
			'name'                => esc_html__( 'Czech Koruna', 'everest-forms-pro' ),
			'symbol'              => '&#75;&#269;',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'DKK' => array(
			'name'                => esc_html__( 'Danish Krone', 'everest-forms-pro' ),
			'symbol'              => 'kr.',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'HKD' => array(
			'name'                => esc_html__( 'Hong Kong Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'right',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'HUF' => array(
			'name'                => esc_html__( 'Hungarian Forint', 'everest-forms-pro' ),
			'symbol'              => 'Ft',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'ILS' => array(
			'name'                => esc_html__( 'Israeli New Sheqel', 'everest-forms-pro' ),
			'symbol'              => '&#8362;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'MYR' => array(
			'name'                => esc_html__( 'Malaysian Ringgit', 'everest-forms-pro' ),
			'symbol'              => '&#82;&#77;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'MXN' => array(
			'name'                => esc_html__( 'Mexican Peso', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'NOK' => array(
			'name'                => esc_html__( 'Norwegian Krone', 'everest-forms-pro' ),
			'symbol'              => 'Kr',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'NZD' => array(
			'name'                => esc_html__( 'New Zealand Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'PHP' => array(
			'name'                => esc_html__( 'Philippine Peso', 'everest-forms-pro' ),
			'symbol'              => 'Php',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'PLN' => array(
			'name'                => esc_html__( 'Polish Zloty', 'everest-forms-pro' ),
			'symbol'              => '&#122;&#322;',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'RUB' => array(
			'name'                => esc_html__( 'Russian Ruble', 'everest-forms-pro' ),
			'symbol'              => 'pyб',
			'symbol_pos'          => 'right',
			'thousands_separator' => ' ',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'SGD' => array(
			'name'                => esc_html__( 'Singapore Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'ZAR' => array(
			'name'                => esc_html__( 'South African Rand', 'everest-forms-pro' ),
			'symbol'              => 'R',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'SEK' => array(
			'name'                => esc_html__( 'Swedish Krona', 'everest-forms-pro' ),
			'symbol'              => 'Kr',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		),
		'CHF' => array(
			'name'                => esc_html__( 'Swiss Franc', 'everest-forms-pro' ),
			'symbol'              => 'CHF',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'TWD' => array(
			'name'                => esc_html__( 'Taiwan New Dollar', 'everest-forms-pro' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'THB' => array(
			'name'                => esc_html__( 'Thai Baht', 'everest-forms-pro' ),
			'symbol'              => '&#3647;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
	);

	return apply_filters( 'everest_forms_currencies', $currencies );
}

/**
 * Sanitize Amount.
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/formatting.php#L24
 *
 * @param string $amount   Amount.
 * @param string $currency Currency usage.
 *
 * @return string $amount
 */
function evf_sanitize_amount( $amount, $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = get_option( 'everest_forms_currency', 'USD' );
	}

	$currencies    = evf_get_currencies();
	$thousands_sep = $currencies[ $currency ]['thousands_separator'];
	$decimal_sep   = $currencies[ $currency ]['decimal_separator'];
	$is_negative   = false;

	// Sanitize the amount.
	// @codingStandardsIgnoreStart
	if ( ',' === $decimal_sep && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( ( $thousands_sep === '.' || $thousands_sep === ' ' ) && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif ( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}
		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif ( $thousands_sep === ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}
	// @codingStandardsIgnoreEnd

	if ( 0 > $amount ) {
		$is_negative = true;
	}

	$amount   = preg_replace( '/[^0-9\.]/', '', $amount );
	$decimals = apply_filters( 'evf_sanitize_amount_decimals', 2, $amount );
	$amount   = number_format( (float) $amount, $decimals, '.', '' );

	if ( $is_negative ) {
		$amount *= - 1;
	}

	return $amount;
}

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.2.6
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/formatting.php#L83
 *
 * @param string  $amount   Amount.
 * @param boolean $symbol   Symbol padding.
 * @param string  $currency Currency.
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function evf_format_amount( $amount, $symbol = false, $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = get_option( 'everest_forms_currency', 'USD' );
	}

	$currency      = strtoupper( $currency );
	$currencies    = evf_get_currencies();
	$thousands_sep = $currencies[ $currency ]['thousands_separator'];
	$decimal_sep   = $currencies[ $currency ]['decimal_separator'];

	// Format the amount.
	if ( ',' === $decimal_sep && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) { // @codingStandardsIgnoreLine
		$whole  = substr( $amount, 0, $sep_found );
		$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator).
	if ( ',' === $thousands_sep && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) { // @codingStandardsIgnoreLine
		$amount = floatval( str_replace( ',', '', $amount ) );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}

	$decimals = apply_filters( 'evf_sanitize_amount_decimals', 2, $amount );
	$number   = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	if ( $symbol ) {
		$symbol_padding      = apply_filters( 'evf_currency_symbol_padding', ' ' );
		$currency_symbol     = $currencies[ $currency ]['symbol'];
		$currency_symbol_pos = $currencies[ $currency ]['symbol_pos'];

		if ( 'right' === $currency_symbol_pos ) {
				$number = $number . $symbol_padding . $currency_symbol;
		} else {
				$number = $currency_symbol . $symbol_padding . $number;
		}
	}
	return $number;
}

/**
 * Return recognized payment field types.
 *
 * @since 1.0.0
 * @return array
 */
function evf_payment_fields() {

	$fields = array( 'payment-single', 'payment-multiple', 'payment-checkbox', 'payment-quantity' );

	return apply_filters( 'evf_payment_fields', $fields );
}

/**
 * Check if form or entry contains payment
 *
 * @since 1.0.0
 *
 * @param string       $type Flag variable for form/entry.
 * @param array|string $data Data object.
 *
 * @return bool
 */
function evf_has_payment( $type = 'entry', $data = '' ) {
	$payment = false;

	if ( ! empty( $data['form_fields'] ) ) {
		$data = $data['form_fields'];
	}

	if ( empty( $data ) ) {
		return false;
	}

	foreach ( $data as $field ) {
		if ( isset( $field['type'] ) && in_array( $field['type'], $payment_fields, true ) ) {

			// For entries, only return true if the payment field has an amount.
			if (
				'form' === $type ||
				(
					'entry' === $type &&
					! empty( $field['item_price'] ) &&
					evf_sanitize_amount( '0' ) !== $field['item_price']
				)
			) {
				$payment = true;
				break;
			}
		}
	}
}

/**
 * Get payment total amount from entry.
 *
 * @param array $fields    Field data object.
 * @param array $entry     Entry of payment.
 * @param array $form_data Form data object.
 */
function evf_get_total_payment( $fields = array(), $entry = array(), $form_data = array() ) {
	$fields         = evf_get_payment_items( $fields, $entry, $form_data );
	$total          = 0;
	$quantity_price = array();
	$map_field      = array();

	if ( empty( $fields ) ) {
		return false;
	}

	foreach ( $fields as $field ) {
		$map_field[] = isset( $form_data['form_fields'][ $field['id'] ]['map_field'] ) ? $form_data['form_fields'][ $field['id'] ]['map_field'] : '';
	}

	foreach ( $fields as $field ) {

		if ( ! empty( $field['amount_raw'] ) && ! in_array( $field['id'], $map_field, true ) ) {
				$amount = evf_sanitize_amount( $field['amount_raw'] );
				$total  = $total + $amount;
		}

		if ( 'payment-quantity' === $field['type'] && ! empty( $map_field ) ) {
			if ( ! empty( $field['value'] ) ) {
				foreach ( $map_field as $id ) {
					if ( $form_data['form_fields'][ $field['id'] ]['map_field'] === $id && isset( $fields[ $id ] ) ) {
						$quantity_price[] = evf_sanitize_amount( $fields[ $id ]['amount_raw'] ) * $field['value'];
					}
				}
			}
		}
	}

	foreach ( $quantity_price as $price ) {
		$total = $total + $price;
	}

	return evf_sanitize_amount( $total );
}

/**
 * Get payment fields in an entry.
 *
 * @param array $fields    Field Object Data.
 * @param array $entry     Payment Entry.
 * @param array $form_data Form Object.
 *
 * @return array|bool False if no fields provided, otherwise array.
 */
function evf_get_payment_items( $fields = array(), $entry = array(), $form_data = array() ) {

	if ( empty( $fields ) ) {
		return false;
	}

	$payment_fields = evf_payment_fields();

	foreach ( $fields as $id => $field ) {
		$field['amount_raw'] = isset( $field['amount_raw'] ) ? $field['amount_raw'] : '';

		if ( ( ! in_array( $field['type'], $payment_fields, true ) || ( empty( $field['amount_raw'] ) && 'payment-quantity' !== $field['type'] ) || ( evf_sanitize_amount( '0' ) === $field['amount_raw'] && 'payment-quantity' !== $field['type'] ) ) || ( in_array( $field['type'], $payment_fields, true ) && ! apply_filters( 'everest_forms_visible_fields', true, $field, $entry, $form_data ) ) ) {
			// Remove all non-payment fields as well as payment fields with no amount.
			unset( $fields[ $id ] );
		}
	}

	return $fields;
}

/**
 * Insert payement data into meta.
 *
 * @param string $entry_id   Entry id for paymment.
 * @param array  $entry_data The entry data for payment.
 * @param bool   $update     Flag for checking if the query ran with no problems.
 */
function evf_payment_entries( $entry_id, $entry_data, $update = false ) {
	global $wpdb;

	foreach ( $entry_data as $key => $data ) {
		if ( $update ) {
				$table_name = $wpdb->prefix . 'evf_entrymeta';

				// @codingStandardsIgnoreStart
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $table_name
								SET meta_key = %s , meta_value = %s
								WHERE entry_id = %d and meta_key = %s ",
						$key,
						$data,
						$entry_id,
						$key
					)
				);
				// @codingStandardsIgnoreEnd
		} else {
				// @codingStandardsIgnoreStart
				$wpdb->insert(
					$wpdb->prefix . 'evf_entrymeta',
					array(
						'entry_id'   => $entry_id,
						'meta_key'   => $key,
						'meta_value' => $data,
					),
					array( '%d', '%s', '%s' )
				);
				// @codingStandardsIgnoreEnd
		}
	}
}
