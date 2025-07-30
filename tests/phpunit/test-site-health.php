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
		$this->cleanup_dummy_plugin( 'tin-canny-learndash-reporting' );
	}

	private function set_active_plugin( $plugin ) {
		update_option( 'active_plugins', $plugin );
		wp_cache_delete( 'plugins', 'plugins' );
	}

	/**
	 * Add a dummy plugin for testing purposes.
	 *
	 * @param string $plugin_filename The filename of the plugin in plugin-directory/plugin-filename.php format.
	 * @param string $plugin_content The content of the plugin file.
	 * @param bool $activate_plugin Whether to activate the plugin after adding it.
	 */
	private function add_dummy_plugin_file( string $plugin_filename, string $plugin_content, bool $activate_plugin = true ) {
		$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_filename );
		if ( ! is_dir( $plugin_dir ) ) {
			mkdir( $plugin_dir, 0755, true );
		}

		file_put_contents( WP_PLUGIN_DIR . '/' . $plugin_filename, $plugin_content );

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_filename ) ) {
			throw new Exception( 'Failed to create dummy plugin file: ' . WP_PLUGIN_DIR . '/' . $plugin_filename );
		}

		/**
		 * End here if we don't want to activate the plugin or if the file
		 * is not a plugin to activate.
		 */
		if ( ! $activate_plugin ) {
			return;
		}

		// Activate the plugin.
		$this->set_active_plugin( [ $plugin_filename ] );
		// We need to clear the plugins cache so the new plugin is recognized.
		wp_cache_delete( 'plugins', 'plugins' );
	}

	private function cleanup_dummy_plugin( string $plugin_slug ) {
		$plugin_dir = WP_PLUGIN_DIR . "/$plugin_slug";
		if ( ! is_dir( $plugin_dir ) ) {
			return;
		}
		$this->rmdir_recursive( $plugin_dir );
	}

	private function rmdir_recursive( $dir ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}
			if ( is_dir( "$dir/$file" ) ) {
				$this->rmdir_recursive( "$dir/$file" );
			} else {
				unlink( "$dir/$file" );
			}
		}
		rmdir( $dir );
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

	public function test_get_tincanny_reporting_version() {
		// Test with no Tin Canny Reporting plugin installed.
		$this->assertEquals( '', Pantheon\Site_Health\get_tincanny_reporting_version() );

		$tin_canny_dummy_content = '<?php
		/*		 
		 * Plugin Name: Tin Canny Reporting
		 * Version: 5.1.0
		 * Description: A dummy plugin for testing purposes.
		 */';
		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php', $tin_canny_dummy_content );

		$this->assertEquals( '5.1.0', Pantheon\Site_Health\get_tincanny_reporting_version() );
	}

	public function test_check_tincanny_reporting_status() {
		// Test with no Tin Canny Reporting plugin installed.
		$this->assertFalse( Pantheon\Site_Health\check_tincanny_reporting_status() );

		// Test with a newer version of Tin Canny Reporting.
		$tin_canny_dummy_content = '<?php
		/*		 
		 * Plugin Name: Tin Canny Reporting
		 * Version: 5.2.0
		 * Description: A dummy plugin for testing purposes.
		 */';
		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php', $tin_canny_dummy_content );
		$this->assertFalse( Pantheon\Site_Health\check_tincanny_reporting_status() );

		$this->cleanup_dummy_plugin( 'tin-canny-learndash-reporting' );

		// Test with an unpatched version of Tin Canny Reporting.
		$tin_canny_dummy_content = '<?php
		/*		 
		 * Plugin Name: Tin Canny Reporting
		 * Version: 5.1.0
		 * Description: A dummy plugin for testing purposes.
		 */';

		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php', $tin_canny_dummy_content );
		$tin_canny_zip_uploader_content = '<?php
		function finalize_module_upload() {
			if ( ! rename( "{$target}/{$directory}", "{$target}/{$database_id}" ) ) {
				return $this->error_response( esc_html_x( "Could not rename directory.", "Tin Canny Zip Uploader", "uncanny-learndash-reporting" ) );
			}	
		}';
		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/src/tincanny-zip-uploader/tincanny-zip-uploader.php', $tin_canny_zip_uploader_content, false );
		$this->assertEquals( 'unpatched', Pantheon\Site_Health\check_tincanny_reporting_status() );

		$this->cleanup_dummy_plugin( 'tin-canny-learndash-reporting' );

		// Test with a patched version of Tin Canny Reporting.
		$tin_canny_dummy_content = '<?php
		/*		 
		 * Plugin Name: Tin Canny Reporting
		 * Version: 5.1.0
		 * Description: A dummy plugin for testing purposes.
		 */';

		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php', $tin_canny_dummy_content );
		$tin_canny_zip_uploader_content = '<?php
		function finalize_module_upload() {
			copy("{$target}/{$directory}", "{$target}/{$database_id}");
			unlink("{$target}/{$directory}");	
		}';
		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/src/tincanny-zip-uploader/tincanny-zip-uploader.php', $tin_canny_zip_uploader_content, false );
		$this->assertEquals( 'patched', Pantheon\Site_Health\check_tincanny_reporting_status() );
		
	}

	public function test_tin_canny_reporting_unpatched() {
		$tin_canny_dummy_content = '<?php
		/*		 
		 * Plugin Name: Tin Canny Reporting
		 * Version: 5.1.0
		 * Description: A dummy plugin for testing purposes.
		 */';
		$tin_canny_zip_uploader_content = '<?php
		function finalize_module_upload() {
			if ( ! rename( "{$target}/{$directory}", "{$target}/{$database_id}" ) ) {
				return $this->error_response( esc_html_x( "Could not rename directory.", "Tin Canny Zip Uploader", "uncanny-learndash-reporting" ) );
			}	
		}';

		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php', $tin_canny_dummy_content );

		$this->add_dummy_plugin_file( 'tin-canny-learndash-reporting/src/tincanny-zip-uploader/tincanny-zip-uploader.php', $tin_canny_zip_uploader_content, false );

		$manual_fixes = Pantheon\Site_Health\get_compatibility_manual_fixes();
		$this->assertArrayHasKey( 'tin-canny-learndash-reporting', $manual_fixes );
		$this->assertEquals( 'Update Required', $manual_fixes['tin-canny-learndash-reporting']['plugin_status'] );
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

	private function cleanup_dummy_plugin( string $plugin_slug ) {
		$plugin_dir = WP_PLUGIN_DIR . "/$plugin_slug";
		if ( ! is_dir( $plugin_dir ) ) {
			return;
		}
		$this->rmdir_recursive( $plugin_dir );
	}

	private function rmdir_recursive( $dir ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}
			if ( is_dir( "$dir/$file" ) ) {
				$this->rmdir_recursive( "$dir/$file" );
			} else {
				unlink( "$dir/$file" );
			}
		}
		rmdir( $dir );
	}
}
