<?php
/**
 * Autoptimize Compatibility
 *
 * @link https://docs.pantheon.io/wordpress-known-issues#autoptimize
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\AutoptimizeFix;

/**
 * Autoptimize Compatibility
 */
class Autoptimize extends Base {
	/**
	 * Run fix on each request.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	/**
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 * @return void
	 */
	public function apply_fix() {
		AutoptimizeFix::apply();
	}

	/**
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 * @return void
	 */
	public function remove_fix() {
		AutoptimizeFix::remove();
	}
}
