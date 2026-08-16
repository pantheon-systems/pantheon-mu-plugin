<?php
/**
 * Pantheon-specific settings overrides.
 *
 * @package pantheon
 */

namespace Pantheon\Settings;

/**
 * Bootstrap the settings.
 */
function bootstrap() {
	add_action( 'admin_init', __NAMESPACE__ . '\\set_default_fair_avatar_to_gravatar' );
}

/**
 * Set the default FAIR avatar to Gravatar if FAIR is active and not already set.
 */
function set_default_fair_avatar_to_gravatar() {
	if ( ! function_exists( '\\FAIR\\bootstrap' ) ) {
		// Bail early if we're not using FAIR.
		return;
	}

	// Check for FAIR settings.
	$fair_settings = get_option( 'fair_settings', [] );
	if ( ! empty( $fair_settings ) ) {
		return;
	}

	// Make sure that avatar_source is not set.
	if ( isset( $fair_settings['avatar_source'] ) ) {
		return;
	}

	// Set avatar_source to gravatar if unset.
	$fair_settings['avatar_source'] = 'gravatar';
	update_option( 'fair_settings', $fair_settings );
}

// Kick it off.
bootstrap();
