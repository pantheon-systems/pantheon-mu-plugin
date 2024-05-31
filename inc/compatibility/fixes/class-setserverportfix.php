<?php
/**
 * Set Server Port Fix
 *
 * This fix sets the server port based on the HTTP_USER_AGENT_HTTPS header.
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Class SetServerPortFix
 *
 * @package Pantheon\Compatibility\Fixes
 */
class SetServerPortFix {



	public static function apply() {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
			return;
		}

		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];

		if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT_HTTPS'] ) && 'ON' === $_SERVER['HTTP_USER_AGENT_HTTPS'] ) {
				$_SERVER['SERVER_PORT'] = 443;
			} else {
				$_SERVER['SERVER_PORT'] = 80;
			}
		}
	}

	public static function remove() {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
			return;
		}

		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
		$_SERVER['SERVER_PORT'] = 80;
	}
}
