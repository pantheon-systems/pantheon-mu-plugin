<?php
<?php
/**
 * Pantheon Site Health Modifications
 *
 * @package pantheon
 */

namespace Pantheon\Site_Health;

// If on Pantheon...
if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
	add_filter( 'site_status_tests', __NAMESPACE__ . '\\site_health_mods' );
}

/**
 * Modify the Site Health tests.
 *
 * @param array $tests The Site Health tests.
 * @return array
 */
function site_health_mods( $tests ) {
	// Remove checks that aren't relevant to Pantheon environments.
    unset( $tests['direct']['update_temp_backup_writable'] );
    unset( $tests['direct']['available_updates_disk_space'] );
    unset( $tests['async']['background_updates'] );
	return $tests;
}
