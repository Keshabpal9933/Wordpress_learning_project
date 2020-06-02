<?php
/**
 * Abstract EVF_Payments class.
 *
 * @package EverestForms/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Payments class.
 */
abstract class EVF_Payments {

	/**
	 * Payments ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Payments name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Payments icon.
	 *
	 * @var mixed
	 */
	public $icon = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'everest_forms_available_payments', array( $this, 'register_payments' ) );
	}

	/**
	 *
	 * Register payments.
	 *
	 * @param array $payments Payment Data.
	 */
	public function register_payments( $payments ) {
		$enable_payment = false;

		if ( isset( $_GET['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$form_data = evf()->form->get( absint( $_GET['form_id'] ), array( 'content_only' => true ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( isset( $form_data['payments'], $form_data['payments'][ $this->id ][ 'enable_' . $this->id ] ) ) {
				$enable_payment = $form_data['payments'][ $this->id ][ 'enable_' . $this->id ];
			}
		}

		$payments[ $this->id ] = array(
			'id'     => $this->id,
			'icon'   => $this->icon,
			'name'   => $this->name,
			'enable' => $enable_payment,
		);

		return $payments;
	}
}
