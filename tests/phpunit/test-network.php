<?php
/**
 * Pantheon MU Plugin Multisite Tests
 * 
 * @package pantheon
 */

use function Pantheon\NetworkSetup\pantheon_remove_network_setup;

/**
 * Network Test Case
 */
class Test_Network extends WP_UnitTestCase {
	public function setUp(): void {
		global $submenu;
		parent::setUp();
		
		if ( ! getenv( 'WP_MULTISITE' ) || getenv( 'WP_MULTISITE' ) !== '1' ) {
			$this->markTestSkipped( 'Multisite not enabled.' );
		}

		require_once dirname( __DIR__, 2 ) . '/inc/network/includes-network.php';
		require_once dirname( __DIR__, 2 ) . '/inc/pantheon-network-setup.php';

		$submenu = [
			'tools.php' => [
				50 => 'Network Setup',
			],
		];
	}

	public function test_network_domain_check() {
		$this->assertNotFalse( network_domain_check() );
		$this->assertEquals( 'example.org', network_domain_check() );
	}

	public function test_allow_subdomain_install() {
		// This should evaluate true by default.
		$this->assertTrue( allow_subdomain_install() );

		$old_home = get_option( 'home' );

		// Override the 'home' option to return 'http://localhost'.
		update_option( 'home', 'http://localhost' );
		$this->assertFalse( allow_subdomain_install() );

		// Clean up.
		update_option( 'home', $old_home );
	}

	public function test_allow_subdirectory_install() {
		// This should evaluate true by default.
		$this->assertTrue( allow_subdirectory_install() );

		// Now create a post older than one month with a status of 'publish'. This will prevent subdirectory installs.
		$post_id = $this->factory->post->create([
			'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 months' ) ),
			'post_status' => 'publish',
		]);

		// Test for the 'false' condition.
		$result = allow_subdirectory_install();
		$this->assertFalse( $result );

		// Clean up by deleting the post.
		wp_delete_post( $post_id, true );
	}

	public function test_get_clean_basedomain() {
		$this->assertEquals( 'example.org', get_clean_basedomain() );
	}

	public function test_pantheon_remove_network_setup() {
		$this->assertNull( Pantheon\NetworkSetup\pantheon_remove_network_setup() );
	}

	public function test_disable_subdirectory_custom_wp_content_warning() {
		// Test the default condition.
		$this->assertNotEmpty( pantheon_get_subdirectory_networks_message() );
	}

	public function test_disable_subdirectory_custom_wp_content_warning_filtered() {
		add_filter( 'pantheon.enable_subdirectory_networks_message', '__return_false' );
		$this->assertEmpty( pantheon_get_subdirectory_networks_message() );
	}

}
