<?php
/**
 * Pantheon MU Plugin Font Library Tests
 * 
 * @package pantheon
 */

Use Pantheon\Fonts;

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

	/**
	 * Test the pantheon_font_dir function.
	 */	
	public function test_pantheon_font_dir() {
		$this->assertTrue( function_exists( 'Pantheon\Fonts\pantheon_font_dir' ) );
		
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			// Skip the test if wp_get_font_dir does not exist.
			$this->markTestSkipped( 'wp_get_font_dir does not exist. WP 6.5+ or Gutenberg 17.6+ must be available to test the font library modifications.' );
		}
		$default_fonts = wp_get_font_dir();
		$font_dir = Fonts\pantheon_font_dir( $default_fonts );

		$this->assertNotEquals( $default_fonts, $font_dir );
		$this->assertEquals( array_keys( $default_fonts ), array_keys( $font_dir ) );
		$this->assertArrayHasKey( 'path', $font_dir );
		$this->assertArrayHasKey( 'url', $font_dir );
		$this->assertArrayHasKey( 'basedir', $font_dir );
		$this->assertArrayHasKey( 'baseurl', $font_dir );
		$this->assertStringContainsString( 'uploads/fonts', $font_dir['path'] );
		$this->assertStringContainsString( 'uploads/fonts', $font_dir['url'] );
		$this->assertStringContainsString( 'uploads/fonts', $font_dir['basedir'] );
		$this->assertStringContainsString( 'uploads/fonts', $font_dir['baseurl'] );
	}

	/**
	 * Get the font library from Gutenberg if it's not available.
	 */
	private function maybe_get_font_library() {
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			require_once WP_PLUGIN_DIR . '/gutenberg/lib/compat/wordpress-6.5/fonts/fonts.php';
		}
	}
}
