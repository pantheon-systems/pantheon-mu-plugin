<?php
/**
 * Stub compatibility class for testing Base::persist_data().
 *
 * @package pantheon
 */

use Pantheon\Compatibility\Base;

/**
 * Stub compatibility plugin for testing.
 */
class Stub_Compatibility_Plugin extends Base {
	/**
	 * Run fix on each request.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	/**
	 * Apply fix (no-op for testing).
	 *
	 * @return void
	 */
	public function apply_fix() {}

	/**
	 * Remove fix (no-op for testing).
	 *
	 * @return void
	 */
	public function remove_fix() {}

	/**
	 * Expose persist_data for testing.
	 *
	 * @param array $plugin_methods Plugin methods.
	 * @return void
	 */
	public function call_persist_data( array $plugin_methods = [] ) {
		$this->persist_data( $plugin_methods );
	}
}
