<?php
/**
 * Slider Revolution Compatibility
 *
 * @link https://docs.pantheon.io/plugins-known-issues#slider-revolution
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SliderRevolutionFix;

/**
 * Slider Revolution Compatibility
 */
class SliderRevolution extends Base {


	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Slider Revolution';
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
		SliderRevolutionFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {}
}
