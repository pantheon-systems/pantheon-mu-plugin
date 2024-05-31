<?php
/**
 * Compatibility class for Contact Form 7 plugin.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DefineConstantFix;
use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Class ContactFormSeven
 */
class ContactFormSeven extends Base {


	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'contact-form-7/wp-contact-form-7.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Contact Form 7';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		SetServerPortFix::apply();
		// Set the temporary uploads directory for Contact Form 7.
		DefineConstantFix::apply( 'WPCF7_UPLOADS_TMP_DIR', ( WP_CONTENT_DIR . '/uploads/wpcf7_uploads' ) );
	}

	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
