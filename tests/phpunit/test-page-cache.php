<?php
/**
 * Pantheon Page Cache Tests
 * 
 * @package pantheon
 */

/**
 * Pantheon Page Cache Test Case
 */
class Test_Page_Cache extends WP_UnitTestCase {
	/**
	 * The Pantheon Cache instance.
	 *
	 * @var Pantheon_Cache
	 */
	private $pantheon_cache;

	/**
	 * The default options.
	 *
	 * @var array
	 */
	private $default_options;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->pantheon_cache = Pantheon_Cache::instance();
		$this->default_options = [
			'default_ttl' => 600,
			'maintenance_mode' => 'disabled',
		];
		$this->pantheon_cache->paths = []; // Clear any leftover paths.
	}

	/**
	 * Test that the Pantheon page cache is enabled on Pantheon environments.
	 */
	public function test_sanitize_default_ttl() {
		// Test case where default_ttl is above 60 and not in live environment.
		$_ENV['PANTHEON_ENVIRONMENT'] = 'dev';
		$input = [
			'default_ttl' => 100, 
			'maintenance_mode' => 'disabled',
		];
		$output = $this->pantheon_cache->sanitize_options( $input );
		$this->assertEquals( 100, $output['default_ttl'] );

		// Test case where default_ttl is below 60 and in live environment.
		$_ENV['PANTHEON_ENVIRONMENT'] = 'live';
		$input = [ 
			'default_ttl' => 30, 
			'maintenance_mode' => 'disabled',
		];
		$output = $this->pantheon_cache->sanitize_options( $input );
		$this->assertEquals( 60, $output['default_ttl'] );
	}

	/**
	 * Test sanitize_options() with valid and invalid maintenance_mode values.
	 */
	public function test_sanitize_maintenance_mode() {
		// Test with valid maintenance_mode values.
		foreach ( [ 'anonymous', 'everyone' ] as $mode ) {
			$input = [
				'default_ttl' => 600, 
				'maintenance_mode' => $mode,
			];
			$output = $this->pantheon_cache->sanitize_options( $input );
			$this->assertEquals( $mode, $output['maintenance_mode'] );
		}

		// Test with invalid maintenance_mode value.
		$input = [
			'default_ttl' => 600, 
			'maintenance_mode' => 'invalid_value',
		];
		$output = $this->pantheon_cache->sanitize_options( $input );
		$this->assertEquals( 'disabled', $output['maintenance_mode'] );
	}

	/**
	 * Test sanitize_options() with empty or missing values.
	 */
	public function test_sanitize_empty_or_missing_values() {
		$_ENV['PANTHEON_ENVIRONMENT'] = 'live';

		// Test with missing keys.
		$input = [
			'default_ttl' => '',
			'maintenance_mode' => null,
		];

		$expected_output = [
			'default_ttl' => 60, // Default TTL is set to 60 on live environments.
			'maintenance_mode' => 'disabled',
		];
		$output = $this->pantheon_cache->sanitize_options( $input );
		$this->assertEquals( $expected_output, $output );

		// Test with empty maintenance_mode.
		$input = [ 
			'default_ttl' => 600, 
			'maintenance_mode' => '', 
		];
		$output = $this->pantheon_cache->sanitize_options( $input );
		$this->assertEquals( 'disabled', $output['maintenance_mode'] );
	}
	
	/**
	 * Test flush_site() with an unauthorized user.
	 */
	public function test_flush_site_unauthorized_user() {
		// Mock current_user_can to return false.
		wp_set_current_user( 0 ); // No permissions.

		$result = $this->pantheon_cache->flush_site();
		$this->assertFalse( $result );
	}

	/**
	 * Test flush_site() with an authorized user but an invalid nonce.
	 */
	public function test_flush_site_authorized_user_invalid_nonce() {
		// Mock current_user_can to return true.
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['pantheon-cache-nonce'] = 'invalid_nonce';

		$result = $this->pantheon_cache->flush_site();
		$this->assertNull( $result ); // No action should be taken.
	}

	/**
	 * Test enqueue_urls() with valid and invalid URLs.
	 */
	public function test_enqueue_urls() {
		// Test with valid URLs.
		$valid_urls = [
			'https://example.com/page1',
			'https://example.com/page2?query=value',
		];
		$this->pantheon_cache->enqueue_urls( $valid_urls );
		$expected_paths = [
			'^/page1$',
			'^/page2query\\=value$',
		];
		$this->assertEquals( $expected_paths, $this->pantheon_cache->paths );
	
		// Test with a mix of valid and invalid URLs.
		$mixed_urls = [
			'https://example.com/page3',
			false,
			null,
			123,
		];
		$this->pantheon_cache->enqueue_urls( $mixed_urls );
		$expected_paths[] = '^/page3$';  // Only the valid URL should be added.
		$this->assertEquals( $expected_paths, $this->pantheon_cache->paths );
	
		// Test with malformed URLs.
		$malformed_urls = [
			'https:///missing_host',
		];
		$this->pantheon_cache->enqueue_urls( $malformed_urls );
		// Malformed URLs should not add to the paths.
		$this->assertEquals( $expected_paths, $this->pantheon_cache->paths );
	}

	/**
	 * Test that enqueue_regex() adds regexes to the paths array.
	 */
	public function test_enqueue_regex() {
		// The initial paths array should be empty.
		$this->assertEquals( [], $this->pantheon_cache->paths );
	
		// Enqueue a regex.
		$regex = '^/products/[0-9]+$';  // Matches URLs like "/products/123".
		$this->pantheon_cache->enqueue_regex( $regex );
	
		// Now the paths array should contain our regex.
		$this->assertEquals( [ $regex ], $this->pantheon_cache->paths );
	
		// Enqueue another regex.
		$another_regex = '^/users/\\w+/posts$';  // Matches URLs like "/users/john/posts".
		$this->pantheon_cache->enqueue_regex( $another_regex );
	
		// Now the paths array should contain both regexes.
		$this->assertEquals( [ $regex, $another_regex ], $this->pantheon_cache->paths );
	}
}
