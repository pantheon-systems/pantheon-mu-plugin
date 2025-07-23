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

	public function test_object_cache_no_redis_unavailable() {
		$_ENV['HTTP_PCONTEXT_SERVICE_LEVEL'] = 'basic';
		unset( $_ENV['CACHE_HOST'] );

		$result = Pantheon\Site_Health\test_object_cache();
		$this->assertEquals( 'good', $result['status'] );
		$this->assertStringContainsString( 'Redis object cache is not available for Basic plans.', $result['description'] );
	}

	public function test_object_cache_no_redis() {
		$_ENV['HTTP_PCONTEXT_SERVICE_LEVEL'] = 'performance_small';
		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'critical', $result['status'] );
		$this->assertStringContainsString( 'Redis object cache is not active', $result['description'] );
	}

	public function test_object_cache_with_redis_no_plugin() {
		$_ENV['HTTP_PCONTEXT_SERVICE_LEVEL'] = 'performance_small';
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'critical', $result['status'] );
		$this->assertStringContainsString( 'Redis object cache is active for your site but you have no object cache plugin installed.', $result['description'] );
	}

	public function test_object_cache_with_wpredis_active() {
		$_ENV['HTTP_PCONTEXT_SERVICE_LEVEL'] = 'performance_small';
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.
		$this->set_active_plugin( 'wp-redis/wp-redis.php' );

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'recommended', $result['status'] );
		$this->assertStringContainsString( 'WP Redis is active for your site. We recommend using Object Cache Pro.', $result['description'] );
	}

	public function test_object_cache_with_ocp_active() {
		$_ENV['HTTP_PCONTEXT_SERVICE_LEVEL'] = 'performance_small';
		$_ENV['CACHE_HOST'] = 'cacheserver'; // Ensure CACHE_HOST is set.
		$this->set_active_plugin( 'object-cache-pro/object-cache-pro.php' );

		$result = Pantheon\Site_Health\test_object_cache();

		$this->assertEquals( 'good', $result['status'] );
		$this->assertStringContainsString( 'Object Cache Pro is active for your site.', $result['description'] );
	}
}
