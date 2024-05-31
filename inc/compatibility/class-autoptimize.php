<?php
/**
 * Autoptimize Compatibility
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\AutoptimizeFix;

/**
 * Autoptimize Compatibility
 */
class Autoptimize extends Base {


	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	public static $plugin_slug = 'autoptimize/autoptimize.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Autoptimize';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		AutoptimizeFix::apply();
	}

	public function remove_fix() {
		AutoptimizeFix::remove();
	}
}
