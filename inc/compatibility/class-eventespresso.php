<?php
/**
 * Compatibility fix for Event Espresso
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

/**
 * Class EventEspresso
 */
class EventEspresso extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'event-espresso-decaf/espresso.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Event Espresso';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		add_filter( 'FHEE_load_EE_Session', '__return_false' );
	}

	public function remove_fix() {
		remove_filter( 'FHEE_load_EE_Session', '__return_false' );
	}
}
