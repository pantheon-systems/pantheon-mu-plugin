<?php
/**
 * Pantheon MU Plugin Tests
 * 
 * @package pantheon
 */

/**
 * Main Mu Plugin Test Case
 */
class Test_Main extends WP_UnitTestCase {
	/**
	 * Test that the mu-plugin is loaded.
	 */
	public function test_mu_plugin_constants() {
		$this->assertTrue( defined( 'PANTHEON_MU_PLUGIN_VERSION' ) );
		$this->assertEquals( '1.2.1', PANTHEON_MU_PLUGIN_VERSION );
		$this->assertTrue( defined( 'FS_METHOD' ) );
		$this->assertEquals( 'direct', FS_METHOD );
		
		// Multisite-only tests.
		if ( is_multisite() ) {
			$this->assertTrue( defined( 'WP_ALLOW_MULTISITE' ) );
			$this->assertTrue( WP_ALLOW_MULTISITE );
			$this->assertTrue( defined( 'MULTISITE' ) );
			$this->assertTrue( MULTISITE );
		}
	}
}