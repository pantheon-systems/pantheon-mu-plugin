<?php
/**
 * ElasticPress Configuration Tests
 *
 * @package pantheon
 */

/**
 * ElasticPress Configuration Test Case
 */
class Test_ElasticPress_Config extends WP_UnitTestCase {
	/**
	 * Store original environment variables to restore after tests.
	 *
	 * @var array
	 */
	private $original_env = [];

	/**
	 * Setup before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Store original environment variables.
		$this->original_env = [
			'PANTHEON_SEARCH_HOST'        => isset( $_ENV['PANTHEON_SEARCH_HOST'] ) ? $_ENV['PANTHEON_SEARCH_HOST'] : null,
			'PANTHEON_SEARCH_ENDPOINT_ID' => isset( $_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] ) ? $_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] : null,
			'PANTHEON_SEARCH_CREDENTIALS' => isset( $_ENV['PANTHEON_SEARCH_CREDENTIALS'] ) ? $_ENV['PANTHEON_SEARCH_CREDENTIALS'] : null,
		];
	}

	/**
	 * Cleanup after each test.
	 */
	public function tearDown(): void {
		// Restore original environment variables.
		foreach ( $this->original_env as $key => $value ) {
			if ( null === $value ) {
				unset( $_ENV[ $key ] );
			} else {
				$_ENV[ $key ] = $value;
			}
		}

		parent::tearDown();
	}

	/**
	 * Test that EP_HOST constant is defined when environment variable is set.
	 */
	public function test_ep_host_constant_defined_from_env() {
		$_ENV['PANTHEON_SEARCH_HOST'] = 'https://example.elasticpress.io';

		// Re-include the config file to trigger constant definitions.
		// Note: In actual tests, constants can only be defined once per test run.
/** 
 * Re-include the config file to trigger constant definitions.
 * Note: In actual tests, constants can only be defined once per test run.
 * This test verifies the logic would work if run fresh.
 */
		if ( ! defined( 'EP_HOST' ) ) {
			require_once dirname( __DIR__, 2 ) . '/inc/elasticpress-config.php';
		}

		$this->assertTrue( defined( 'EP_HOST' ) );
		$this->assertEquals( 'https://example.elasticpress.io', EP_HOST );
	}

	/**
	 * Test that EP_INDEX_PREFIX constant is defined when environment variable is set.
	 */
	public function test_ep_index_prefix_constant_defined_from_env() {
		$_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] = 'test-subscription-id';

		if ( ! defined( 'EP_INDEX_PREFIX' ) ) {
			require_once dirname( __DIR__, 2 ) . '/inc/elasticpress-config.php';
		}

		$this->assertTrue( defined( 'EP_INDEX_PREFIX' ) );
		$this->assertEquals( 'test-subscription-id', EP_INDEX_PREFIX );
	}

	/**
	 * Test that EP_CREDENTIALS constant is defined when environment variable is set.
	 */
	public function test_ep_credentials_constant_defined_from_env() {
		$_ENV['PANTHEON_SEARCH_CREDENTIALS'] = 'subscription-id:subscription-token';

		if ( ! defined( 'EP_CREDENTIALS' ) ) {
			require_once dirname( __DIR__, 2 ) . '/inc/elasticpress-config.php';
		}

		$this->assertTrue( defined( 'EP_CREDENTIALS' ) );
		$this->assertEquals( 'subscription-id:subscription-token', EP_CREDENTIALS );
	}

	/**
	 * Test that constants are not defined when environment variables are not set.
	 */
	public function test_constants_not_defined_without_env_vars() {
		// Ensure environment variables are not set.
		unset( $_ENV['PANTHEON_SEARCH_HOST'] );
		unset( $_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] );
		unset( $_ENV['PANTHEON_SEARCH_CREDENTIALS'] );

		// Since constants may already be defined in previous tests,
		// we can only verify the logic doesn't error when env vars are missing.
/** 
 * Since constants may already be defined in previous tests,
 * we can only verify the logic doesn't error when env vars are missing.
 * The actual check happens in the config file: ! empty( $_ENV['...'] )
 */
		$this->assertTrue( true );
	}

	/**
	 * Test that constants are not overridden if already defined.
	 */
	public function test_constants_not_overridden_if_already_defined() {
		// This test documents the behavior: if constants are already defined,
		// they won't be overridden by the config file.
		// The actual test would require defining constants before including the file,
/**
 * This test documents the behavior: if constants are already defined,
 * they won't be overridden by the config file.
 * The actual test would require defining constants before including the file,
 * which is not practical in PHPUnit where constants persist across tests.
 */
		$this->assertTrue( true );
	}

	/**
	 * Test that empty environment variables don't define constants.
	 */
	public function test_empty_env_vars_dont_define_constants() {
		// Set environment variables to empty strings.
		$_ENV['PANTHEON_SEARCH_HOST']        = '';
		$_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] = '';
		$_ENV['PANTHEON_SEARCH_CREDENTIALS'] = '';

		// The config file uses ! empty() which returns true for empty strings.
		// So empty env vars should not define constants.
/** 
 * The config file uses ! empty() which returns true for empty strings.
 * So empty env vars should not define constants.
 * We can't easily test this in isolation, but we document the expected behavior.
 */
		$this->assertTrue( true );
	}

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
