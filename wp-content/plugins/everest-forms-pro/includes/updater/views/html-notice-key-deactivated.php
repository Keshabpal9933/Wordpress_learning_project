<?php
/**
 * Admin View: Notice - License Deactivated
 *
 * @package EverestForms_Pro
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated notice is-dismissible">
	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %s: plugin name */
			__( 'Your licence for <strong>%s</strong> has been deactivated.', 'everest-forms-pro' ),
			esc_html( $this->plugin_data['Name'] )
		)
	);
	?>
	</p>
</div>
