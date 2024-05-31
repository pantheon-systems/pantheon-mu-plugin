<?php
/**
 * Compatibility fix for Fast Velocity Minify plugin.
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DefineConstantFix;

/**
 * Class FastVelocityMinify
 */
class FastVelocityMinify extends Base {


	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'fast-velocity-minify/fvm.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Fast Velocity Minify';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		$home_url = defined( 'WP_SITEURL' ) ? WP_SITEURL : get_option( 'siteurl' );
		DefineConstantFix::apply( 'FVM_CACHE_DIR', '/code/wp-content/uploads' );
		DefineConstantFix::apply( 'FVM_CACHE_URL', sprintf( '%s/code/wp-content/uploads', $home_url ) );
	}

	public function remove_fix() {
		DefineConstantFix::remove( 'FVM_CACHE_DIR' );
		DefineConstantFix::remove( 'FVM_CACHE_URL' );
	}
}
