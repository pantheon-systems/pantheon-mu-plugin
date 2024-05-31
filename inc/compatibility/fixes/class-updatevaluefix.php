<?php
/**
 * Update Value Fix
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Update Value Fix
 */
class UpdateValueFix {


	public static function apply( $option_name, $option_key, $option_value ) {
		$options = json_decode( get_option( $option_name ) );
		$options->$option_key = $option_value;
		update_option( $option_name, json_encode( $options ) );
	}

	public static function remove( $option_name, $option_key ) {
		$options = json_decode( get_option( $option_name ) );
		unset( $options->$option_key );
		update_option( $option_name, json_encode( $options ) );
	}
}
