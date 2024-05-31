<?php
/**
 * Compatibility fix for WooZone.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DefineConstantFix;

/**
 * Class WooZone
 */
class WooZone extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'woozone/plugin.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'WooZone';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		DefineConstantFix::apply( 'WP_MEMORY_LIMIT', '256M' );
	}

	public function remove_fix() {}
}
