<?php
/**
 * Tests for ElasticPress CLI HTTPS fix.
 *
 * @package pantheon
 */

/**
 * Test class for ElasticPress CLI HTTPS forcing functionality.
 */
class Test_ElasticPress_CLI extends WP_UnitTestCase {
	/**
	 * Test that _pantheon_ep_force_https_url replaces http with https.
	 */
	public function test_force_https_url_replaces_http() {
		$this->assertEquals(
			'https://example.com',
			_pantheon_ep_force_https_url( 'http://example.com' )
		);
	}

	/**
	 * Test that _pantheon_ep_force_https_url does not modify https URLs.
	 */
	public function test_force_https_url_preserves_https() {
		$this->assertEquals(
			'https://example.com',
			_pantheon_ep_force_https_url( 'https://example.com' )
		);
	}

	/**
	 * Test that _pantheon_ep_force_https_url does not modify non-URL strings.
	 */
	public function test_force_https_url_ignores_non_url_strings() {
		$this->assertEquals(
			'not a url',
			_pantheon_ep_force_https_url( 'not a url' )
		);
	}

	/**
	 * Test that _pantheon_ep_force_https_url handles non-string values.
	 */
	public function test_force_https_url_handles_non_string() {
		$this->assertNull( _pantheon_ep_force_https_url( null ) );
		$this->assertFalse( _pantheon_ep_force_https_url( false ) );
	}

	/**
	 * Test that the opt-out filter disables HTTPS forcing.
	 */
	public function test_force_https_opt_out_filter() {
		add_filter( 'pantheon_elasticpress_force_https_in_cli', '__return_false' );
		$this->assertFalse( apply_filters( 'pantheon_elasticpress_force_https_in_cli', true ) );
		remove_filter( 'pantheon_elasticpress_force_https_in_cli', '__return_false' );
	}

	/**
	 * Test that the option_home filter works when applied.
	 */
	public function test_option_home_filter_forces_https() {
		add_filter( 'option_home', '_pantheon_ep_force_https_url' );
		update_option( 'home', 'http://example.com' );
		$this->assertEquals( 'https://example.com', get_option( 'home' ) );
		remove_filter( 'option_home', '_pantheon_ep_force_https_url' );
	}

	/**
	 * Test that the option_siteurl filter works when applied.
	 */
	public function test_option_siteurl_filter_forces_https() {
		add_filter( 'option_siteurl', '_pantheon_ep_force_https_url' );
		update_option( 'siteurl', 'http://example.com' );
		$this->assertEquals( 'https://example.com', get_option( 'siteurl' ) );
		remove_filter( 'option_siteurl', '_pantheon_ep_force_https_url' );
	}
}
