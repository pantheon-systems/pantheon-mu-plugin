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
	 * Test that EP_DIRECT_HOST is defined.
	 */
	public function test_ep_direct_host_defined() {
		$this->assertTrue( defined( 'EP_DIRECT_HOST' ) );
		$this->assertSame( 'https://test.hosted-elasticpress.io', EP_DIRECT_HOST );
	}

	/**
	 * Test that the autosuggest options filter is registered.
	 */
	public function test_autosuggest_filter_registered() {
		$this->assertNotFalse(
			has_filter( 'ep_autosuggest_options', 'Pantheon\\ElasticPress\\filter_autosuggest_options' )
		);
	}

	/**
	 * Test that the instant results filter is registered.
	 */
	public function test_instant_results_filter_registered() {
		$this->assertNotFalse(
			has_filter( 'ep_instant_results_search_endpoint', 'Pantheon\\ElasticPress\\filter_instant_results_endpoint' )
		);
	}
}
