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
	
		// Check that the returned version is correct.
		$this->assertEquals( '6.3.1', $result );
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
	 * Test the _pantheon_upstream_update_notice function for beta/pre-release version.
	 */
	public function test_pantheon_upstream_update_notice_core_prerelease() {
		set_current_screen( 'update-core' );
	
		// Simulate that the core is a prerelease.
		set_site_transient(
			'update_core',
			(object) [
				'updates' => [
					(object) [
						'current' => '6.4-beta',
						'response' => 'upgrade', 
						'locale' => 'en_us',
					],
				],
				'version_checked' => '6.4-beta',
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
