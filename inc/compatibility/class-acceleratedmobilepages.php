<?php
/**
 * Accelerated Mobile Pages compatibility fix.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\AcceleratedMobilePagesFix;

/**
 * Accelerated Mobile Pages compatibility fix.
 */
class AcceleratedMobilePages extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'accelerated-mobile-pages/accelerated-moblie-pages.php';

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'AMP for WP – Accelerated Mobile Pages';

	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		AcceleratedMobilePagesFix::apply();
	}

	public function remove_fix() {
		AcceleratedMobilePagesFix::remove();
	}
}
