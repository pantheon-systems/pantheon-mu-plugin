<?php
/**
 * Slider Revolution Compatibility
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SliderRevolutionFix;

/**
 * Slider Revolution Compatibility
 */
class SliderRevolution extends Base {


	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	public static $plugin_slug = 'slider-revolution/slider-revolution.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Slider Revolution';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		SliderRevolutionFix::apply();
	}

	public function remove_fix() {}
}
