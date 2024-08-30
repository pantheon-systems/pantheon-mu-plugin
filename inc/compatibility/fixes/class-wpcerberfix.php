<?php
/**
 * WP Rocket compatibility fix.
 *
 * @link https://docs.pantheon.io/plugins-known-issues#wp-rocket
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * WP Rocket compatibility fix class.
 */
class WPCerberFix {
	/**
	 * @return void
	 */
	public static function apply() {
		$cerber_antispam = get_option( 'cerber-antispam' );
		if ( ! $cerber_antispam ) {
			return;
		}

		if ( isset( $cerber_antispam['botsany'] ) ) {
			UpdateValueFix::apply( 'cerber-antispam', 'botsany', '0' );
		}

		if ( isset( $cerber_antispam['botscomm'] ) ) {
			UpdateValueFix::apply( 'cerber-antispam', 'botscomm', '0' );
		}

		if ( isset( $cerber_antispam['botsreg'] ) ) {
			UpdateValueFix::apply( 'cerber-antispam', 'botsreg', '0' );
		}
	}
}
