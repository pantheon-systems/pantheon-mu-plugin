<?php
/**
 * Better Search Replace compatibility fix.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\AddFilterFix;

/**
 * Better Search Replace compatibility fix.
 */
class BetterSearchReplace extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'better-search-replace/better-search-replace.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Better Search Replace';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		AddFilterFix::apply('bsr_capability', function () {
			return 'manage_options';
		});
	}

	public function remove_fix() {
		AddFilterFix::remove('bsr_capability', function () {
			return 'manage_options';
		});
	}
}
