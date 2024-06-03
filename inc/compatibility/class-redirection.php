<?php
/**
 * Redirection compatibility class
 *
 * @link https://docs.pantheon.io/plugins-known-issues#redirection
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Redirection compatibility class
 */
class Redirection extends Base {


	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Redirection';
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
		SetServerPortFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
