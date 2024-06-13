<?php
/**
 * Autoptimize Compatibility
 *
 * @link https://docs.pantheon.io/plugins-known-issues#autoptimize
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\AutoptimizeFix;

/**
 * Autoptimize Compatibility
 */
class Autoptimize extends Base {
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Autoptimize';
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
		AutoptimizeFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {
		AutoptimizeFix::remove();
	}
}
