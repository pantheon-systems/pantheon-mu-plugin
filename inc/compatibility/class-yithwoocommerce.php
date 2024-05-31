<?php
/**
 * YITH WooCommerce Compatibility
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\YITHChangePdfLocationFix;

/**
 * Class YITHWoocommerce
 */
class YITHWoocommerce extends Base {


	/**
	 * List of plugin slugs
	 *
	 * @var array
	 */
	public static $plugin_slugs = [
		'yith-woocommerce-request-a-quote/yith-woocommerce-request-a-quote.php',
		'yith-woocommerce-gift-cards/init.php',
		'yith-woocommerce-pdf-invoice/init.php',
	];
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'YITH WooCommerce Extensions with MPDF Library';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		YITHChangePdfLocationFix::apply();
	}

	public function remove_fix() {
		YITHChangePdfLocationFix::remove();
	}
}
