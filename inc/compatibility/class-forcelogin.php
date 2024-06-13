<?php
/**
 * Force Login compatibility fix.
 *
 * @link https://docs.pantheon.io/plugins-known-issues#force-login
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Force Login compatibility fix.
 */
class ForceLogin extends Base {
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Force Login';
	/**
	 * Run fix on each request.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	/**
	 * @return void
	 */
	public function apply_fix() {
		SetServerPortFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
