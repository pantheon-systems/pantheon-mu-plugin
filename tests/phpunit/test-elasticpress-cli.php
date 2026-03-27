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
	 * Data provider for force_https_url tests.
	 *
	 * @return array[] Test cases with input and expected output.
	 */
	public function force_https_url_provider() {
		return [
			'http to https'       => [ 'http://example.com', 'https://example.com' ],
			'https unchanged'     => [ 'https://example.com', 'https://example.com' ],
			'http with path'      => [ 'http://example.com/path', 'https://example.com/path' ],
			'https with path'     => [ 'https://example.com/path', 'https://example.com/path' ],
			'non-url string'      => [ 'not a url', 'not a url' ],
			'empty string'        => [ '', '' ],
			'http in middle'      => [ 'prefix http://example.com', 'prefix http://example.com' ],
			'null value'          => [ null, null ],
			'false value'         => [ false, false ],
			'integer value'       => [ 123, 123 ],
		];
	}

	/**
	 * Test _pantheon_ep_force_https_url with various inputs.
	 *
	 * @dataProvider force_https_url_provider
	 *
	 * @param mixed $input    The input value.
	 * @param mixed $expected The expected output.
	 */
	public function test_force_https_url( $input, $expected ) {
		$this->assertSame(
			$expected,
			\Pantheon\CLI\_pantheon_ep_force_https_url( $input )
		);
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
	 * Data provider for option filter tests.
	 *
	 * @return array[] Test cases with option name.
	 */
	public function option_filter_provider() {
		return [
			'home option'    => [ 'home' ],
			'siteurl option' => [ 'siteurl' ],
		];
	}

	/**
	 * Test that option filters force HTTPS.
	 *
	 * @dataProvider option_filter_provider
	 *
	 * @param string $option The option name to test.
	 */
	public function test_option_filter_forces_https( $option ) {
		$filter_name = 'option_' . $option;
		add_filter( $filter_name, '\\Pantheon\\CLI\\_pantheon_ep_force_https_url' );
		update_option( $option, 'http://example.com' );
		$this->assertEquals( 'https://example.com', get_option( $option ) );
		remove_filter( $filter_name, '\\Pantheon\\CLI\\_pantheon_ep_force_https_url' );
	}
}
