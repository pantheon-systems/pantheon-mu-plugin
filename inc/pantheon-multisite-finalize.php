<?php
/*
 * If a site has multisite enabled, but has not had the final installation
 * steps completed, alert the user and provide links.
 */

function pantheon_multisite_install_finalize_message() {
	?>
	<div class="notice notice-info is-dismissible">
		<p><?php esc_html_e( 'Your WordPress Site Network is almost ready!', 'pantheon' ); ?></p>
		<p><?php echo sprintf( __( 'Visit <a href="%s">Pantheon WordPress Site Network Configuration</a> for documentation on how to finalize configuration of your site network.', 'pantheon-systems' ), 'https://pantheon.io/docs/guides/multisite/config/#install-the-wordpress-site-network' ); ?></p>
		~~for documentation on how to finalize configuration of your site network.</p> ~~
	</div>
	<?php
}

add_action('admin_notices', 'pantheon_multisite_install_finalize_message');
