<?php 
/**
 * Pantheon Updates Tests
 * 
 * @package pantheon
 */

/**
 * Pantheon Updates Test Case
 */
class Test_Pantheon_Updates extends WP_UnitTestCase {
	/**
	 * The current WordPress version.
	 * 
	 * @var string
	 */
	private static $wp_version;

	/**
	 * The main constructor.
	 */
	public function __construct() {
		parent::__construct();
		self::$wp_version = _pantheon_get_current_wordpress_version();
	}

	/**
	 * Get the latest WordPress version from the wp-latest.json file.
	 * 
	 * @return string|bool The latest WordPress version or false if the file doesn't exist.
	 */
	private static function get_latest_wp_version_from_file() {
		global $_tests_dir;
		
		$file = dirname( $_tests_dir ) . '/wp-latest.json';
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$version_raw = json_decode( file_get_contents( $file ) );
		$version = $version_raw->offers[0]->current;

		return $version;
	}

	/**
	 * Test the _pantheon_hide_update_nag function.
	 */
	public function test_pantheon_hide_update_nag() {
		// Add the action before testing.
		add_action( 'admin_notices', 'update_nag', 3 );
		add_action( 'network_admin_notices', 'update_nag', 3 );

		// Run the function.
		_pantheon_hide_update_nag();

		// Check that the action has been removed.
		$this->assertFalse( has_action( 'admin_notices', 'update_nag' ) );
		$this->assertFalse( has_action( 'network_admin_notices', 'update_nag' ) );
	}

	/**
	 * Test the _pantheon_get_current_wordpress_version function.
	 */
	public function test_pantheon_get_current_wordpress_version() {
		// Run the function.
		$result = _pantheon_get_current_wordpress_version();
		$current_version = self::get_latest_wp_version_from_file();
		
		// If the current version is greater than the result, then we downloaded a nightly version for testing.
		if ( $this->is_prerelease() ) {
			$this->markTestSkipped( 'The current version is greater than the result. We downloaded a nightly version for testing.' );
		}
		// Check that the returned version is correct. This value needs to be changed when the WordPress version is updated.
		$this->assertEquals( $current_version, $result );
	}

	/**
	 * Test the _pantheon_get_latest_wordpress_version function.
	 */
	public function test_pantheon_get_latest_wordpress_version() {
		// Mock the get_core_updates function by setting a transient.
		set_site_transient( 
			'update_core', 
			(object) [
				'last_checked' => time(),
				'updates' => [
					(object) [ 
						'current' => '6.3.1', 
						'response' => 'upgrade', 
						'locale' => 'en_us',
					],
				],
				'version_checked' => '5.8',
			],
		);
	
		// Run the function.
		$result = _pantheon_get_latest_wordpress_version();
	
		// Check that the returned version is correct.
		$this->assertEquals( '6.3.1', $result );
	}

	/**
	 * Test the _pantheon_upstream_update_notice function for latest core.
	 */
	public function test_pantheon_upstream_update_notice_core_latest() {
		if ( $this->is_prerelease() ) {
			$this->markTestSkipped( 'The current version is greater than the result. We will get a different message than the one this test expects.' );
		}

		set_current_screen( 'update-core' );
	
		// Simulate that the core is the latest version.
		set_site_transient(
			'update_core',
			(object) [
				'updates' => [
					(object) [
						'current' => '6.3.1',
						'response' => 'upgrade', 
						'locale' => 'en_us',
					],
				],
				'version_checked' => '6.3.1',
			],
		);
	
		ob_start();
		_pantheon_upstream_update_notice();
		$output = ob_get_clean();
	
		$this->assertStringContainsString( 'Check for updates on', $output );
	}
	
	/**
	 * Test the _pantheon_upstream_update_notice function for older core.
	 */
	public function test_pantheon_upstream_update_notice_core_not_latest() {
		if ( $this->is_prerelease() ) {
			$this->markTestSkipped( 'The current version is greater than the result. We will get a different message than the one this test expects.' );
		}

		set_current_screen( 'update-core' );
	
		// Simulate that the core is not the latest version.
		set_site_transient(
			'update_core',
			(object) [
				'updates' => [
					(object) [
						'current' => '6.3.1',
						'response' => 'upgrade', 
						'locale' => 'en_us',
					],
				],
				'version_checked' => '6.2',
			],
		);
	
		ob_start();
		_pantheon_upstream_update_notice();
		$output = ob_get_clean();
	
		$this->assertStringContainsString( 'Check for updates on <a href="https://dashboard.pantheon.io/sites/test-site">your Pantheon dashboard</a>', $output );
	}
	
	/**
	 * Get the next beta version based on the current version.
	 */
	private static function get_next_beta_version() {
		$version_parts = explode( '.', self::$wp_version );
		$version_parts[1] = (int) $version_parts[1] + 1;
		return implode( '.', $version_parts ) . '-beta';
	}

	/**
	 * Check if we're using a beta version.
	 */
	private static function is_prerelease() {
		$current_version = _pantheon_get_current_wordpress_version();
		$installed_version = self::get_latest_wp_version_from_file();

		if ( version_compare( $current_version, $installed_version, '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Test the _pantheon_upstream_update_notice function for beta/pre-release version.
	 */
	public function test_pantheon_upstream_update_notice_core_prerelease() {
		if ( $this->is_prerelease() ) {
			$this->markTestSkipped( 'The current version is greater than the result. We will get a different message than the one this test expects.' );
		}

		$beta_version = self::get_next_beta_version();
		set_current_screen( 'update-core' );
	
		// Simulate that the core is a prerelease.
		set_site_transient(
			'update_core',
			(object) [
				'updates' => [
					(object) [
						'current' => $beta_version,
						'response' => 'upgrade', 
						'locale' => 'en_us',
					],
				],
				'version_checked' => $beta_version,
			],
		);
	
		ob_start();
		_pantheon_upstream_update_notice();
		$output = ob_get_clean();
	
		$this->assertStringContainsString( 'A new WordPress update is available!', $output );
	}
	
	/**
	 * Test the that updates are disabled.
	 */
	public function test_pantheon_disable_wp_updates() {
		$result = _pantheon_disable_wp_updates();

		$this->assertIsObject( $result );
		$this->assertEmpty( $result->updates );
		$this->assertIsInt( $result->last_checked );
	}
}
