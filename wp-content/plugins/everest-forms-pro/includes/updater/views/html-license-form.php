<?php
/**
 * Admin View: Plugins - License form
 *
 * @package EverestForms_Pro
 */

defined( 'ABSPATH' ) || exit;

$license_key = sanitize_title( $this->plugin_slug . '_license_key' );
?>
<tr class="plugin-update-tr active update" id="<?php echo esc_attr( sanitize_title( $this->plugin_slug . '-license-row' ) ); ?>">
	<td colspan="3" class="plugin-update colspanchange">
		<?php $this->error_notices(); ?>
		<input type="checkbox" name="checked[]" value="1" checked="checked" style="display: none;">
		<div class="update-message inline everest-forms-updater-license-key">
			<label for="<?php echo esc_attr( $license_key ); ?>"><?php esc_html_e( 'License:', 'everest-forms-pro' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $license_key ); ?>" name="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php esc_attr_e( 'XXXX-XXXX-XXXX-XXXX', 'everest-forms-pro' ); ?>" />
			<span class="description"><?php esc_html_e( 'Enter your license key and hit return. A valid key is required for updates.', 'everest-forms-pro' ); ?> <?php printf( 'Lost your key? <a href="%s">Retrieve it here</a>.', esc_url( 'https://wpeverest.com/lost-licence-key/' ) ); ?></span>
		</div>
	</td>
	<script>
		jQuery( function() {
			jQuery( 'tr#<?php echo esc_attr( $this->plugin_slug ); ?>-license-row' ).prev().attr( 'id', '<?php echo sanitize_title( $this->plugin_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>' ).addClass( 'update everest-forms-updater-licensed' );
		});
	</script>
</tr>
