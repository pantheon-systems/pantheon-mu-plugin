<?php
/**
 * Compatibility class for Tweet Old Post plugin.
 *
 * @link https://docs.pantheon.io/plugins-known-issues#revive-old-post
 * @package Pantheon\Compatibility
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Class TweetOldPost
 */
class TweetOldPost extends Base {
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Revive Old Post';
	/**
	 * Run fix on each request.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	/**
	 * @return void
	 */
	public function apply_fix() {
		SetServerPortFix::apply();
	}

	/**
	 * @return void
	 */
	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
