<?php
/**
 * Class AddFilterFix
 *
 * @package Pantheon\Compatibility\Fixes
 */

namespace Pantheon\Compatibility\Fixes;

/**
 * Class AddFilterFix
 *
 * @package Pantheon\Compatibility\Fixes
 */
class AddFilterFix {


	public static function apply( $filter_name, callable $callback ) {
		add_filter( $filter_name, $callback );
	}

	public static function remove( $filter_name, callable $callback ) {
		remove_filter( $filter_name, $callback );
	}
}
