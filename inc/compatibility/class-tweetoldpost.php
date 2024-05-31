<?php
/**
 * Compatibility class for Tweet Old Post plugin.
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use Pantheon\Compatibility\Fixes\SetServerPortFix;

/**
 * Class TweetOldPost
 */
class TweetOldPost extends Base {


	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public static $plugin_slug = 'tweet-old-post/tweet-old-post.php';
	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	public static $plugin_name = 'Revive Old Post';
	/**
	 * Run fix everytime either frontend or dashboard.
	 *
	 * @var bool
	 */
	protected $run_fix_everytime = true;

	public function apply_fix() {
		SetServerPortFix::apply();
	}

	public function remove_fix() {
		SetServerPortFix::remove();
	}
}
