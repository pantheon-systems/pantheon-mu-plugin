<?php
/**
 * Tests for ElasticPress client-side search configuration.
 *
 * @package pantheon
 */

/**
 * Test class for ElasticPress client-side endpoint overrides.
 */
class Test_ElasticPress_Client_Side extends WP_UnitTestCase {
	/**
	 * Test that EP_DIRECT_HOST is defined from environment variable.
	 */
	public function test_ep_direct_host_defined() {
		$this->assertTrue( defined( 'EP_DIRECT_HOST' ) );
		$this->assertSame( 'https://test.hosted-elasticpress.io', EP_DIRECT_HOST );
	}

	/**
	 * Test that the instant results filter is registered.
	 */
	public function test_instant_results_filter_registered() {
		$this->assertNotFalse(
			has_filter( 'ep_instant_results_search_endpoint', 'Pantheon\\ElasticPress\\filter_instant_results_endpoint' )
		);
	}

	/**
	 * Test that the autosuggest override action is registered.
	 */
	public function test_autosuggest_action_registered() {
		$this->assertNotFalse(
			has_action( 'plugins_loaded', 'Pantheon\\ElasticPress\\override_autosuggest_endpoint' )
		);
	}

	/**
	 * Data provider for instant results endpoint tests.
	 *
	 * @return array[] Test cases with index name and expected URL.
	 */
	public function instant_results_endpoint_provider() {
		return [
			'simple index'      => [
				'mysite-post-1',
				'https://test.hosted-elasticpress.io/api/v1/search/posts/mysite-post-1',
			],
			'index with dashes' => [
				'abc123-def456-post-1',
				'https://test.hosted-elasticpress.io/api/v1/search/posts/abc123-def456-post-1',
			],
		];
	}

	/**
	 * Test instant results endpoint filter output.
	 *
	 * @dataProvider instant_results_endpoint_provider
	 *
	 * @param string $index    The index name.
	 * @param string $expected The expected endpoint URL.
	 */
	public function test_instant_results_endpoint( $index, $expected ) {
		$result = \Pantheon\ElasticPress\filter_instant_results_endpoint( '', $index );
		$this->assertSame( $expected, $result );
	}

	/**
	 * Test that the instant results endpoint is a valid HTTPS URL.
	 */
	public function test_instant_results_endpoint_is_https() {
		$result = \Pantheon\ElasticPress\filter_instant_results_endpoint( '', 'test-post-1' );
		$this->assertStringStartsWith( 'https://', $result );
	}

	/**
	 * Test that instant results endpoint works via apply_filters.
	 */
	public function test_instant_results_endpoint_via_filter() {
		$result = apply_filters( 'ep_instant_results_search_endpoint', 'api/v1/search/posts/test-index', 'test-index' );
		$this->assertSame(
			'https://test.hosted-elasticpress.io/api/v1/search/posts/test-index',
			$result
		);
	}
}
