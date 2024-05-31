<?php
/**
 * Auth0 Compatibility
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\Auth0Fix;

/**
 * Auth0 Compatibility
 */
class Auth0 extends Base {


	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	public static $plugin_slug = 'auth0/WP_Auth0.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Auth0';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		Auth0Fix::apply();
	}

	public function remove_fix() {
		Auth0Fix::remove();
	}
}
