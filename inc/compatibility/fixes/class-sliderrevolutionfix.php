<?php
/**
 * Slider Revolution Fix
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Slider Revolution Fix
 */
class SliderRevolutionFix {


	public static function apply() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
	}
}
