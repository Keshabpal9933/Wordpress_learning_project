<?php
/**
 * Admin View: Notice - License Error
 *
 * @package EverestForms_Pro
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo did_action( 'all_admin_notices' ) ? 'update-message notice inline notice-alt notice-error' : 'error'; ?>">
	<p><?php echo wp_kses_post( $error ); ?></p>
</div>
