<?php
/**
 * Compatibility class for WP Rocket plugin.
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\WPRocketFix;

/**
 * Class WPRocket
 *
 * @package Pantheon\Compatibility
 */
class WPRocket extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'wp-rocket/wp-rocket.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'WP Rocket';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		WPRocketFix::apply();
	}

	public function remove_fix() {
		WPRocketFix::remove();
	}
}
