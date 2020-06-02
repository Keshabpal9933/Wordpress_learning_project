<?php
/**
 * Admin View: Notice - License Unvalidated
 *
 * @package EverestForms_Pro
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated">
	<p class="evf-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $this->plugin_slug ), '1' ) ); ?>"><?php esc_html_e( 'Hide notice', 'everest-forms-pro' ); ?></a></p>
	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: 1: license key URL 2: plugin name */
			__( '<a href="%1$s">Please enter your license key</a> in the plugin list below to get updates for <strong>%2$s</strong> Add-ons.', 'everest-forms-pro' ),
			esc_url( admin_url( 'plugins.php#' . sanitize_title( $this->plugin_slug ) ) ),
			esc_html( $this->plugin_data['Name'] )
		)
	);
	?>
	</p>
</div>
