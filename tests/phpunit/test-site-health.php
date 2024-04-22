<?php

/**
 * Pantheon Site Health page Tests
 * 
 * @package pantheon
 */

class Test_Site_Health extends WP_UnitTestCase {
	public function setUp(): void {
		parent::setUp();
		// Attach the site_health_mods function to the 'site_status_tests' filter
		add_filter('site_status_tests', '\\Pantheon\\Site_Health\\site_health_mods');
	}

	public function tearDown(): void {
		parent::tearDown();
		// Remove the site_health_mods function from the 'site_status_tests' filter to clean up
		remove_filter('site_status_tests', '\\Pantheon\\Site_Health\\site_health_mods');
	}

	public function test_site_health_mods() {
		// Mock array to represent the structure passed to the filter
		$mock_tests = [
			'direct' => [
				'update_temp_backup_writable' => [],
				'available_updates_disk_space' => [],
			],
			'async' => [
				'background_updates' => [],
			],
		];

		// Apply the filter, which will now use your attached function
		$result = apply_filters('site_status_tests', $mock_tests);

		// Assertions to verify the modifications made by your function
		$this->assertArrayNotHasKey('update_temp_backup_writable', $result['direct']);
		$this->assertArrayNotHasKey('available_updates_disk_space', $result['direct']);
		$this->assertArrayNotHasKey('background_updates', $result['async']);
	}
}