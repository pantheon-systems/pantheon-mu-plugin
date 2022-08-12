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
WP_CLI::add_command( 'pantheon set-maintenance-mode', [ Pantheon_Cache::instance(), 'set_maintenance_mode_command' ] );

/**
 * Returns a deprecated notice for the pantheon-cache command.
 *
 * @deprecated 1.0.0
 */
function __deprecated_maintenance_mode_output( $args ) {
	$replacement_command = ( ! empty( $args ) && in_array( 'set-maintenance-mode', $args, true ) ) ? 'set-maintenance-mode' : '<command>';

	WP_CLI::error( sprintf( __( 'This command is deprecated. Use `wp pantheon %s` instead. Run `wp pantheon --help` for more infomation.', 'pantheon-systems' ), $replacement_command ) );
}
