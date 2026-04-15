<?php
/**
 * Compatibility fix for Independent Analytics plugin.
 *
 * @link https://github.com/pantheon-systems/pantheon-mu-plugin/issues/110
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DefineConstantFix;

/**
 * Class IndependentAnalytics
 */
class IndependentAnalytics extends Base {
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
		DefineConstantFix::apply( 'IAWP_TEMP_DIR', '/code/wp-content/uploads/iawp/' );
	}

	/**
	 * @return void
	 */
	public function remove_fix() {}
}
