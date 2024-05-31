<?php
/**
 * DefineConstantFix
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Class DefineConstantFix
 *
 * @package Pantheon\Compatibility\Fixes
 */
class DefineConstantFix {


	public static function apply( $key, $value ): void {
		if ( ! defined( $key ) ) {
			define( $key, $value );
		}
	}

	public static function remove( $key ): void {
		if ( defined( $key ) ) {
			define( $key, null );
		}
	}
}
