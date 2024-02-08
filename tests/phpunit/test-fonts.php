<?php
/**
 * Pantheon MU Plugin Font Library Tests
 * 
 * @package pantheon
 */

use Pantheon\Fonts;

use function Pantheon\Fonts\pantheon_modify_fonts_dir;

/**
 * Main Mu Plugin Test Case
 */
class Test_Fonts extends WP_UnitTestCase {
	protected $original_pantheon_upload_dir;

	public function setUp(): void {
		parent::setUp();
		remove_all_filters( 'font_dir' );
		remove_all_filters( 'pantheon_modify_fonts_dir' );

		// Mock the global variable before each test
		global $_pantheon_upload_dir;
		$this->original_pantheon_upload_dir = $_pantheon_upload_dir; // Backup original global if needed

		// Manually set the global variable to a mocked value
		$_pantheon_upload_dir = [
			'basedir' => WP_CONTENT_DIR . '/uploads',
			'baseurl' => 'http://example.org/wp-content/uploads',
		];		
	}

	public function tearDown(): void {
		// Restore original global state after each test if necessary
		global $_pantheon_upload_dir;
		$_pantheon_upload_dir = $this->original_pantheon_upload_dir;

		parent::tearDown();		
	}

	/**
	 * Test the font library modifications have been loaded.
	 */
	public function test_font_library_modifications() {
		$this->assertTrue( function_exists( 'Pantheon\Fonts\bootstrap' ) );
		$this->assertEquals( has_action( 'init', 'Pantheon\Fonts\bootstrap' ), 10 );
	}

	/**
	 * Test the pantheon_font_dir function.
	 */ 
	public function test_pantheon_font_dir() {
		$this->assertTrue( function_exists( 'Pantheon\Fonts\pantheon_font_dir' ) );
		
		// Check current WP version to see if we can run the test.
		$version = _pantheon_get_current_wordpress_version();
		if ( version_compare( $version, '6.4.x', '<=' ) ) {
			// Skip the test if the current WP version is less than 6.5.
			$this->markTestSkipped( 'WP 6.5+ or Gutenberg 17.6+ must be available to test the font library modifications.' );
		}

		$this->maybe_get_font_library();
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			// If the function still doesn't exist after trying to get the font library from gutenberg, mark the test skipped.
			$this->markTestSkipped( 'The wp_get_font_dir function is not available. We\'re probably not using WP 6.5+' );
		}

		// Remove the filters we apply to `font_dir` so we're getting the default data.
		remove_all_filters( 'font_dir' );
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
	 * Test that our filtered font directory is filtered properly.
	 */
	public function test_pantheon_font_dir_filter() {
		// Check current WP version to see if we can run the test.
		$version = _pantheon_get_current_wordpress_version();
		if ( version_compare( $version, '6.4.x', '<=' ) ) {
			// Skip the test if the current WP version is less than 6.5.
			$this->markTestSkipped( 'WP 6.5+ or Gutenberg 17.6+ must be available to test the font library modifications.' );
		}
		
		$this->maybe_get_font_library();
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			// If the function still doesn't exist after trying to get the font library from gutenberg, mark the test skipped.
			$this->markTestSkipped( 'The wp_get_font_dir function is not available. We\'re probably not using WP 6.5+' );
		}

		add_filter( 'font_dir', '\\Pantheon\\Fonts\\pantheon_font_dir' );
		$font_dir = wp_get_font_dir();
		
		$expected = [
			'path' => WP_CONTENT_DIR . '/uploads/fonts',
			'url' => WP_CONTENT_URL . '/uploads/fonts',
			'subdir' => '',
			'basedir' => WP_CONTENT_DIR . '/uploads/fonts',
			'baseurl' => WP_CONTENT_URL . '/uploads/fonts',
			'error' => false,
		];

		$this->assertEquals( $expected, $font_dir );
	}

	/**
	 * Test that using the font_dir filter at priority 10 overrides our modifications.
	 */
	public function test_pantheon_fonts_dir_filter() {
		// Check current WP version to see if we can run the test.
		$version = _pantheon_get_current_wordpress_version();
		if ( version_compare( $version, '6.4.x', '<=' ) ) {
			// Skip the test if the current WP version is less than 6.5.
			$this->markTestSkipped( 'WP 6.5+ or Gutenberg 17.6+ must be available to test the font library modifications.' );
		}

		$this->maybe_get_font_library();
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			// If the function still doesn't exist after trying to get the font library from gutenberg, mark the test skipped.
			$this->markTestSkipped( 'The wp_get_font_dir function is not available. We\'re probably not using WP 6.5+' );
		}

		$custom_directory = [
			'path' => WP_CONTENT_DIR . '/custom-fonts',
			'url' => WP_CONTENT_URL . '/custom-fonts',
			'basedir' => WP_CONTENT_DIR . '/custom-fonts',
			'baseurl' => WP_CONTENT_URL . '/custom-fonts',
		];

		add_filter( 'font_dir', function ( $defaults ) use ( $custom_directory ) {
			$defaults['path'] = $custom_directory['path'];
			$defaults['url'] = $custom_directory['url'];
			$defaults['basedir'] = $custom_directory['basedir'];
			$defaults['baseurl'] = $custom_directory['baseurl'];
			return $defaults;
		} );

		Fonts\bootstrap();
		$font_dir = wp_get_font_dir();

		$expected = [
			'path' => WP_CONTENT_DIR . '/custom-fonts',
			'url' => WP_CONTENT_URL . '/custom-fonts',
			'subdir' => '',
			'basedir' => WP_CONTENT_DIR . '/custom-fonts',
			'baseurl' => WP_CONTENT_URL . '/custom-fonts',
			'error' => false,
		];

		$this->assertEquals( $expected, $font_dir );
	}

	/**
	 * Test that the font directory modifications can be disabled.
	 */
	public function test_disable_pantheon_font_dir_mods() {
		// Check current WP version to see if we can run the test.
		$version = _pantheon_get_current_wordpress_version();
		if ( version_compare( $version, '6.4.x', '<=' ) ) {
			// Skip the test if the current WP version is less than 6.5.
			$this->markTestSkipped( 'WP 6.5+ or Gutenberg 17.6+ must be available to test the font library modifications.' );
		}

		$this->maybe_get_font_library();
		if ( ! function_exists( 'wp_get_font_dir' ) ) {
			// If the function still doesn't exist after trying to get the font library from gutenberg, mark the test skipped.
			$this->markTestSkipped( 'The wp_get_font_dir function is not available. We\'re probably not using WP 6.5+' );
		}

		// Disable the font directory modifications.
		add_filter( 'pantheon_modify_fonts_dir', '__return_false' );
		$modify_fonts_dir = Fonts\pantheon_modify_fonts_dir();
		$this->assertFalse( $modify_fonts_dir );

		$font_dir = wp_get_font_dir();

		$expected = [
			'path' => WP_CONTENT_DIR . '/fonts',
			'url' => WP_CONTENT_URL . '/fonts',
			'subdir' => '',
			'basedir' => WP_CONTENT_DIR . '/fonts',
			'baseurl' => WP_CONTENT_URL . '/fonts',
			'error' => false,
		];

		$this->assertEquals( $expected, $font_dir );
	}

	/**
	 * Get the font library from Gutenberg if it's not available.
	 */
	private function maybe_get_font_library() {
		$fonts_php = WP_PLUGIN_DIR . '/gutenberg/lib/compat/wordpress-6.5/fonts/fonts.php';
		if ( ! function_exists( 'wp_get_font_dir' ) && file_exists( $fonts_php ) ) {
			require_once $fonts_php;
		}
	}
}
