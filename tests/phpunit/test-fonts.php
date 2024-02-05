<?php
/**
 * Pantheon MU Plugin Font Library Tests
 * 
 * @package pantheon
 */

/**
 * Main Mu Plugin Test Case
 */
class Test_Fonts extends WP_UnitTestCase
{
	/**
	 * Test the font library modifications have been loaded.
	 */
	public function test_font_library_modifications() {
		$this->assertTrue( function_exists( 'Pantheon\Fonts\bootstrap' ) );
		$this->assertEquals( has_action( 'init', 'Pantheon\Fonts\bootstrap' ), 10 );
		$this->assertTrue( defined( 'PANTHEON_MODIFY_FONTS_DIR' ) );
		$this->assertTrue( PANTHEON_MODIFY_FONTS_DIR );
	}
}
