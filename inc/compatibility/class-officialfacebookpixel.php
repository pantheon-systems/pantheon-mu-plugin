<?php
/**
 * Compatibility fix for Official Facebook Pixel plugin.
 *
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\DeleteFileFix;

/**
 * Class OfficialFacebookPixel
 */
class OfficialFacebookPixel extends Base {


	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'official-facebook-pixel/facebook-for-wordpress.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Facebook for WordPress';
	/**
	 * Run fix on plugin activation flag.
	 *
	 * @var bool
	 */
	protected $run_on_plugin_activation = true;

	public function apply_fix() {
		DeleteFileFix::apply( ABSPATH . 'wp-content/plugins/official-facebook-pixel/vendor/techcrunch/wp-async-task/.gitignore' );
	}

	public function remove_fix() {}
}
