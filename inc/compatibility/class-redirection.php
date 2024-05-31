<?php
/**
 * Redirection compatibility class
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Redirection compatibility class
 */
class Redirection extends Base {


	/**
	 * The plugin slug
	 *
	 * @var string
	 */
	public static $plugin_slug = 'redirection/redirection.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Redirection';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		SetServerPortFix::apply();
	}

	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
