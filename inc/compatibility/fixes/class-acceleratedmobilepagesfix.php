<?php
/**
 * Accelerated Mobile Pages Fix
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Accelerated Mobile Pages Fix
 *
 * @package Pantheon\Compatibility\Fixes
 */
class AcceleratedMobilePagesFix {



	public static function apply() {
		SelfUpdatingThemesFix::apply();
		global $redux_builder_amp;
		// Force disabling AMP mobile redirection.
		$redux_builder_amp['amp-mobile-redirection'] = 0;

		if ( ( self::is_mobile() ) && ( false === strrpos( $_SERVER['REQUEST_URI'], 'amp' ) ) ) {
			header( 'HTTP/1.0 301 Moved Permanently' );
			header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/amp' );

			// Name transaction "redirect" in New Relic for improved reporting (optional).
			if ( extension_loaded( 'newrelic' ) ) {
				newrelic_name_transaction( 'redirect' );
			}
			exit();
		}
	}

	/**
	 * Check if the request is from a mobile device
	 *
	 * @phpcs:disable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__HTTP_USER_AGENT__
	 *
	 * @return bool
	 */
	private static function is_mobile() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}
		if ( str_contains( $_SERVER['HTTP_USER_AGENT'], 'Mobile' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'Android' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'Silk/' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'Kindle' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' )
			|| str_contains( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) ) {
			return true;
		}

		return false;
	}

	public static function remove() {
		SelfUpdatingThemesFix::remove();
	}
}
