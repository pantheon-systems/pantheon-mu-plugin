<?php
/**
 * Pantheon Site Health page Tests
 * 
 * @package pantheon
 */

/**
 * Pantheon Site Health page Test Case
 */
class Test_Site_Health extends WP_UnitTestCase {
	/**
	 * The original active plugins.
	 * 
	 * Used to restore the original active plugins after the test.
	 *
	 * @var array
	 */
	private $original_active_plugins;

	public function setUp(): void {
		parent::setUp();
		$this->original_active_plugins = get_option( 'active_plugins' );
		add_filter( 'site_status_tests', '\\Pantheon\\Site_Health\\site_health_mods' );
		add_filter( 'site_status_tests', '\\Pantheon\\Site_Health\\object_cache_tests' );
	}

	public function tearDown(): void {
		parent::tearDown();
		update_option( 'active_plugins', $this->original_active_plugins );
		$this->cleanup_dummy_plugin();
	}

	private function set_active_plugin( $plugin ) {
		update_option( 'active_plugins', $plugin );
		wp_cache_delete( 'plugins', 'plugins' );
	}

	public function test_site_health_mods() {
		// Mock array to represent the structure passed to the filter.
		$mock_tests = [
			'direct' => [
				'update_temp_backup_writable' => [],
				'available_updates_disk_space' => [],
			],
			'async' => [
				'background_updates' => [],
			],
		];

		$result = apply_filters( 'site_status_tests', $mock_tests );

		$this->assertArrayNotHasKey( 'update_temp_backup_writable', $result['direct'] );
		$this->assertArrayNotHasKey( 'available_updates_disk_space', $result['direct'] );
		$this->assertArrayNotHasKey( 'background_updates', $result['async'] );
	}

	public function test_object_cache_no_redis() {
		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'critical', $result['status'] );
		$this->assertStringContainsString( 'Redis object cache is not active', $result['description'] );
	}

	public function test_object_cache_with_redis_no_plugin() {
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'critical', $result['status'] );
		$this->assertStringContainsString( 'Redis object cache is active for your site but you have no object cache plugin installed.', $result['description'] );
	}

	public function test_object_cache_with_wpredis_active() {
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.
		$this->set_active_plugin( 'wp-redis/wp-redis.php' );

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'recommended', $result['status'] );
		$this->assertStringContainsString( 'WP Redis is active for your site. We recommend using Object Cache Pro.', $result['description'] );
	}

	public function test_object_cache_with_ocp_active() {
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.
		$this->set_active_plugin( 'object-cache-pro/object-cache-pro.php' );

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'good', $result['status'] );
		$this->assertStringContainsString( 'Object Cache Pro is active for your site.', $result['description'] );
	}

	public function test_tin_canny_reporting_unpatched() {
		// Create a dummy plugin file with the rename function.
		$plugin_dir = WP_PLUGIN_DIR . '/tin-canny-reporting/tincanny-zip-uploader';
		if ( ! is_dir( $plugin_dir ) ) {
			mkdir( $plugin_dir, 0777, true );
		}
		
		if ( ! file_exists( $plugin_dir . '/tincanny-zip-uploader.php' ) ) {
			touch( $plugin_dir . '/tincanny-zip-uploader.php' );
			file_put_contents( $plugin_dir . '/tincanny-zip-uploader.php', '<?php rename("foo", "bar");' );
		}

		$this->set_active_plugin( [ 'tin-canny-reporting/tin-canny-reporting.php' ] );

		$manual_fixes = Pantheon\Site_Health\get_compatibility_manual_fixes();
		$this->assertArrayHasKey( 'tin-canny-reporting', $manual_fixes );
		$this->assertEquals( 'Manual Fix Required', $manual_fixes['tin-canny-reporting']['plugin_status'] );
	}

	public function test_tin_canny_reporting_patched() {
		// Create a dummy plugin file without the rename function.
		$plugin_dir = WP_PLUGIN_DIR . '/tin-canny-reporting/tincanny-zip-uploader';
		if ( ! is_dir( $plugin_dir ) ) {
			mkdir( $plugin_dir, 0777, true );
		}

		if ( ! file_exists( $plugin_dir . '/tincanny-zip-uploader.php' ) ) {
			touch( $plugin_dir . '/tincanny-zip-uploader.php' );
			file_put_contents( $plugin_dir . '/tincanny-zip-uploader.php', '<?php copy("foo", "bar");' );
		}

		$this->set_active_plugin( [ 'tin-canny-reporting/tin-canny-reporting.php' ] );

		$review_fixes = Pantheon\Site_Health\get_compatibility_review_fixes();
		$this->assertArrayHasKey( 'tin-canny-reporting', $review_fixes );
		$this->assertEquals( 'Partial Compatibility', $review_fixes['tin-canny-reporting']['plugin_status'] );
	}

	private function cleanup_dummy_plugin() {
		$plugin_dir = WP_PLUGIN_DIR . '/tin-canny-reporting';
		if ( is_dir( $plugin_dir ) ) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ( $files as $fileinfo ) {
				$todo = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
				$todo( $fileinfo->getRealPath() );
			}

			rmdir( $plugin_dir );
		}
	}
}
