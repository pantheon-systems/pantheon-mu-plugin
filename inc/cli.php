<?php
/**
 * WP-CLI commands for the Pantheon mu-plugin.
 *
 * @package pantheon
 */

namespace Pantheon\CLI;

use Pantheon_Cache;
use WP_CLI;
WP_CLI::add_command( 'pantheon set-maintenance-mode', [ Pantheon_Cache::instance(), 'set_maintenance_mode_command' ] );
