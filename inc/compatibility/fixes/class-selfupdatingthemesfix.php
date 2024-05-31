<?php
/**
 * Self Updating Themes Fix
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Self Updating Themes Fix
 */
class SelfUpdatingThemesFix {


	public static function apply() {
		/** Disable theme FTP form */
		DefineConstantFix::apply( 'FS_METHOD', 'direct' );
		DefineConstantFix::apply( 'FS_CHMOD_DIR', ( 0755 & ~umask() ) );
		DefineConstantFix::apply( 'FS_CHMOD_FILE', ( 0755 & ~umask() ) );
		DefineConstantFix::apply( 'FTP_BASE', __DIR__ . '/../../' );
		DefineConstantFix::apply( 'FTP_CONTENT_DIR', __DIR__ . '/../../wp-content/' );
		DefineConstantFix::apply( 'FTP_PLUGIN_DIR', __DIR__ . '/../../wp-content/plugins/' );
	}

	public static function remove() {}
}
