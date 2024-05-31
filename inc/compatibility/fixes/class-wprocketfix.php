<?php
/**
 * WP Rocket compatibility fix.
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * WP Rocket compatibility fix class.
 */
class WPRocketFix {


	public static function apply() {
		DefineConstantFix::apply( 'WP_CACHE', true );
		DefineConstantFix::apply( 'WP_ROCKET_CONFIG_PATH', sprintf( '%s/wp-content/uploads/wp-rocket/config/', $_SERVER['DOCUMENT_ROOT'] ) );
		DefineConstantFix::apply( 'WP_ROCKET_CACHE_ROOT_PATH', sprintf( '%s/wp-content/uploads/wp-rocket/cache/', $_SERVER['DOCUMENT_ROOT'] ) );
		$home_url = defined( 'WP_SITEURL' ) ? WP_SITEURL : get_option( 'siteurl' );
		DefineConstantFix::apply( 'WP_ROCKET_CACHE_ROOT_URL', sprintf( '%s/wp-content/uploads/wp-rocket/cache/', $home_url ) );
	}

	public static function remove() {
		DefineConstantFix::remove( 'WP_CACHE' );
		DefineConstantFix::remove( 'WP_ROCKET_CONFIG_PATH' );
		DefineConstantFix::remove( 'WP_ROCKET_CACHE_ROOT_PATH' );
		DefineConstantFix::remove( 'WP_ROCKET_CACHE_ROOT_URL' );
	}
}
