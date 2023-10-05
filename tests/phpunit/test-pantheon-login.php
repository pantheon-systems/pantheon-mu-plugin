<?php
/**
 * Pantheon Login Form Tests
 * 
 * @package pantheon
 */

/**
 * Pantheon Login Form Test Case
 */
class Test_Pantheon_Login extends WP_UnitTestCase {

	/**
	 * The original site URL.
	 *
	 * @var string
	 */
	private $original_site_url;

	/**
	 * The original HTTP host.
	 *
	 * @var string
	 */
	private $original_http_host;

	/**
	 * The Pantheon site URL.
	 *
	 * @var string
	 */
	private $pantheon_site_url = 'https://something.pantheonsite.io';

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->original_site_url = get_option( 'siteurl' );
		$this->original_http_host = $_SERVER['HTTP_HOST'] ?? null;
	}
	
	/**
	 * Tear down the test environment.
	 */
	public function tearDown(): void {
		// Reset site URL to its original value.
		update_option( 'siteurl', $this->original_site_url );
		$_SERVER['HTTP_HOST'] = $this->original_http_host;
		parent::tearDown();
	}

	/**
	 * Test that the Pantheon login form mods are loaded.
	 */
	public function test_pantheon_dashboard_url() {
		// Simulate Pantheon environment.
		update_option( 'siteurl', $this->pantheon_site_url );
		$_ENV['PANTHEON_SITE'] = 'test-site';
		$_ENV['PANTHEON_ENVIRONMENT'] = 'test-env';
		$_SERVER['HTTP_HOST'] = 'something.pantheonsite.io';
		
		if ( ! function_exists( 'Return_To_Pantheon_Button_HTML' ) ) {
			// Include pantheon-login-form-mods.php.
			require_once dirname( __DIR__, 2 ) . '/inc/pantheon-login-form-mods.php';
		}

		$this->assertTrue( function_exists( 'Return_To_Pantheon_Button_HTML' ) );
		// Capture the output of the function.
		ob_start();
		Return_To_Pantheon_Button_HTML();
		$output = ob_get_clean();

		// Check that the URL is as expected.
		$expected_url = 'https://dashboard.pantheon.io/sites/test-site#test-env';
		$this->assertStringContainsString( $expected_url, $output );
	}
}
