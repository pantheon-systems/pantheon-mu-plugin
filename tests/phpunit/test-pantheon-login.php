<?php
class Test_Pantheon_Login extends WP_UnitTestCase {

	private $original_site_url;
	private $original_http_host;
	private $pantheon_site_url = 'https://something.pantheonsite.io';

	public function setUp() : void {
		parent::setUp();
		$this->original_site_url = get_option( 'siteurl' );
		$this->original_http_host = $_SERVER['HTTP_HOST'] ?? null;		
	}
	
	public function tearDown() : void {
		// Reset site URL to its original value
		update_option( 'siteurl', $this->original_site_url );
		$_SERVER['HTTP_HOST'] = $this->original_http_host;
		parent::tearDown();
	}

	public function test_pantheon_dashboard_url() {
		// Simulate Pantheon environment.
		update_option( 'siteurl', $this->pantheon_site_url );
		$_ENV['PANTHEON_SITE'] = 'test-site';
		$_ENV['PANTHEON_ENVIRONMENT'] = 'test-env';
		$_SERVER['HTTP_HOST'] = 'something.pantheonsite.io';
		
		if ( ! function_exists( 'Return_To_Pantheon_Button_HTML' ) ) {
			// Include pantheon-login-form-mods.php.
			require_once dirname( __FILE__, 3 ) . '/inc/pantheon-login-form-mods.php';
		}

		$this->assertTrue( function_exists( 'Return_To_Pantheon_Button_HTML' ) );
		// Capture the output of the function
		ob_start();
		Return_To_Pantheon_Button_HTML();
		$output = ob_get_clean();

		// Check that the URL is as expected
		$expected_url = 'https://dashboard.pantheon.io/sites/test-site#test-env';
		$this->assertStringContainsString( $expected_url, $output );
	}
}
