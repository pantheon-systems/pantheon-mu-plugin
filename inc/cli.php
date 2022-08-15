<?php
/**
 * WP-CLI commands for the Pantheon mu-plugin.
 *
 * @package pantheon
 */

namespace Pantheon\CLI;

use Pantheon_Cache;
use WP_CLI;

// Support the old pantheon-cache command but return a deprecation notice.
WP_CLI::add_command( 'pantheon-cache', '\\Pantheon\\CLI\\__deprecated_maintenance_mode_output' );
WP_CLI::add_command( 'pantheon set-maintenance-mode', '\\Pantheon\\CLI\\set_maintenance_mode_command' );

/**
 * Sets maintenance mode status.
 *
 * Enable maintenance mode to work on your site while serving cached pages
 * to visitors and bots, or everyone except administators.
 *
 * ## DEPRECATION NOTICE
 *
 * This command is deprecated. Use `pantheon set-maintenance-mode` instead.
 *
 * ## USAGE
 *
 * wp pantheon-cache set-maintenance-mode <status> (deprecated) or
 * wp pantheon set-maintenance-mode <status>
 *
 * ## OPTIONS
 *
 * <status>
 * : Maintenance mode status.
 * ---
 * options:
 *   - disabled
 *   - anonymous
 *   - everyone
 * ---
 *
 * @subcommand set-maintenance-mode
 *
 * @deprecated 1.0.0
 */
function __deprecated_maintenance_mode_output( $args ) {
	$replacement_command = ( ! empty( $args ) && in_array( 'set-maintenance-mode', $args, true ) ) ? 'set-maintenance-mode' : '<command>';

	WP_CLI::warning( sprintf( __( 'This command is deprecated. Use `wp pantheon %s` instead. Run `wp pantheon --help` for more infomation.', 'pantheon-systems' ), $replacement_command ) );

	set_maintenance_mode_command( $args );
}

/**
 * Sets maintenance mode status.
 *
 * Enable maintenance mode to work on your site while serving cached pages
 * to visitors and bots, or everyone except administators.
 *
 * ## OPTIONS
 *
 * <status>
 * : Maintenance mode status.
 * ---
 * options:
 *   - disabled
 *   - anonymous
 *   - everyone
 * ---
 *
 * @subcommand set-maintenance-mode
 */
function set_maintenance_mode_command( $args ) {

	list( $status ) = $args;

	$out = Pantheon_Cache()->default_options;
	if ( ! empty( $status )
		&& in_array( $status, [ 'anonymous', 'everyone' ], true ) ) {
		$out['maintenance_mode'] = $status;
	} else {
		$out['maintenance_mode'] = 'disabled';
	}
	update_option( Pantheon_Cache::SLUG, $out );
	WP_CLI::success( sprintf( 'Maintenance mode set to: %s', $out['maintenance_mode'] ) );
}
