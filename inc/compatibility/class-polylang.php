<?php
/**
 * Compatibility fix for Polylang plugin.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DefineConstantFix;

/**
 * Class Polylang
 */
class Polylang extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'polylang/polylang.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'PolyLang';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		DefineConstantFix::apply( 'PLL_CACHE_HOME_URL', false );
		DefineConstantFix::apply( 'PLL_COOKIE', false );
	}

	public function remove_fix() {
		DefineConstantFix::remove( 'PLL_CACHE_HOME_URL' );
		DefineConstantFix::remove( 'PLL_COOKIE' );
	}
}
