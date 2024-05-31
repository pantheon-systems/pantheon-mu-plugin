<?php
/**
 * Force Login compatibility fix.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Force Login compatibility fix.
 */
class ForceLogin extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'wp-force-login/wp-force-login.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Force Login';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		SetServerPortFix::apply();
	}

	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
