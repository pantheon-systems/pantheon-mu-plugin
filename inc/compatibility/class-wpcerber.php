<?php
/**
 * Compatibility class for WP Cerber plugin.
 *
 * @link https://docs.pantheon.io/plugins-known-issues#wp-rocket
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\WPCerberFix;

/**
 * Class WPRocket
 *
 * @package Pantheon\Compatibility
 */
class WPCerber extends Base {
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
		WPCerberFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {
		WPCerberFix::remove();
	}
}
