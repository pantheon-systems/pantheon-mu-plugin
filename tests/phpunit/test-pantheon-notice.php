<?php
/**
 * Plugin install notice tests.
 * 
 * @package pantheon
 */

/**
 * Plugin install notice test case.
 */
class Test_Pantheon_Notice extends WP_UnitTestCase {

	/**
	 * Test that the Pantheon plugin install notice is displayed on the plugins page.
	 */
	public function test_pantheon_plugin_install_notice_plugins_page() {
		set_current_screen( 'plugins' );

		ob_start();
		_pantheon_plugin_install_notice();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'If you wish to update or add plugins using the WordPress UI', $output );
	}

	/**
	 * Test that the Pantheon plugin install notice is not displayed on other pages.
	 */
	public function test_pantheon_plugin_install_notice_other_page() {
		set_current_screen( 'dashboard' );

		ob_start();
		_pantheon_plugin_install_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}
}
