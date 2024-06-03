<?php
/**
 * Pantheon Compatibility Layer Tests
 *
 * @package pantheon
 */

use Pantheon\Compatibility\CompatibilityFactory;

/**
 * Pantheon Compatibility Layer Test Case
 */
class Test_Compatibility_Layer extends WP_UnitTestCase {


	/**
	 * The original active plugins.
	 *
	 * Used to restore the original active plugins after the test.
	 *
	 * @var array
	 */
	private $original_active_plugins;

	/**
	 * Compatibility Factory instance.
	 *
	 * @var CompatibilityFactory
	 */
	private $compatibility_factory;

	public function setUp(): void {
		parent::setUp();
		$this->original_active_plugins = get_option( 'active_plugins' );
		$this->compatibility_factory = CompatibilityFactory::get_instance();
	}

	public function tearDown(): void {
		parent::tearDown();
		update_option( 'active_plugins', $this->original_active_plugins );
	}

	public function test_registered_cron_schedule() {
		$this->assertIsObject( wp_get_scheduled_event( 'pantheon_cron' ) );
	}

	public function test_add_names_to_targets() {
		foreach ( CompatibilityFactory::$targets as $target ) {
			$this->assertArrayHasKey( 'name', $target );
		}
	}

	public function test_get_instance() {
		$this->assertInstanceOf( CompatibilityFactory::class, $this->compatibility_factory );
	}

	public function test_instantiate_compatibility_layers() {
		foreach ( CompatibilityFactory::$targets as $class => $plugin ) {
			$this->assertTrue( class_exists( $class ) );
		}
	}

	public function test_daily_pantheon_cron() {
		$this->set_active_plugin( 'wp-force-login/wp-force-login.php' );
		$this->compatibility_factory->daily_pantheon_cron();
		$applied_fixes = get_option( 'pantheon_applied_fixes' );

		$this->assertIsArray( $applied_fixes );
		$this->assertArrayHasKey( 'wp-force-login/wp-force-login.php', $applied_fixes );
	}

	private function set_active_plugin( $plugin ) {
		update_option( 'active_plugins', $plugin );
		wp_cache_delete( 'plugins', 'plugins' );
	}
}
