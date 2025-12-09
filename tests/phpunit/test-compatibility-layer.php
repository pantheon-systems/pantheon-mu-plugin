<?php
/**
 * Pantheon Compatibility Layer Tests
 *
 * @package pantheon
 */

use Pantheon\Compatibility\CompatibilityFactory;
use Pantheon\Compatibility\ForceLogin;
use Pantheon\Compatibility\Base;

/**
 * Stub compatibility class for testing Base::persist_data().
 */
class Stub_Compatibility_Plugin extends Base {
	protected $run_fix_everytime = true;

	public function apply_fix() {}

	public function remove_fix() {}

	public function call_persist_data( array $plugin_methods = [] ) {
		$this->persist_data( $plugin_methods );
	}
}

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

	public function test_compatibility_hooks() {
		$this->set_active_plugin( 'wp-force-login/wp-force-login.php' );
		CompatibilityFactory::get_instance();
		global $wp_filter;
		$this->assertTrue( array_key_exists( 'deactivate_wp-force-login/wp-force-login.php', $wp_filter ) );
		$hooked_functions = array_column($wp_filter['deactivate_wp-force-login/wp-force-login.php']->callbacks[10],
		'function');
		$function_names = array_column( $hooked_functions, 0 );
		$this->assertInstanceOf( ForceLogin::class, $function_names[0] );
	}

	private function set_active_plugin( $plugin ) {
		update_option( 'active_plugins', $plugin );
		wp_cache_delete( 'plugins', 'plugins' );
	}

	public function test_daily_pantheon_cron() {
		$this->set_active_plugin( 'wp-force-login/wp-force-login.php' );
		$this->compatibility_factory->daily_pantheon_cron();
		$applied_fixes = get_option( 'pantheon_applied_fixes' );

		$this->assertIsArray( $applied_fixes );
		$this->assertArrayHasKey( 'wp-force-login/wp-force-login.php', $applied_fixes );
	}

	public function test_output_compatibility_status_table() {
		$plugins = get_option( 'active_plugins' );
		foreach ( [ 'tuxedo-big-file-uploads/tuxedo_big_file_uploads.php', 'phastpress/phastpress.php' ] as $plugin ) {
			$plugins[] = $plugin;
		}
		update_option( 'active_plugins', $plugins );
		wp_cache_delete( 'plugins', 'plugins' );

		$manual_fixes = Pantheon\Site_Health\get_compatibility_manual_fixes();
		$review_fixes = Pantheon\Site_Health\get_compatibility_review_fixes();
		
		$manual_table = Pantheon\Site_Health\output_compatibility_status_table( $manual_fixes, false );

		$this->assertStringContainsString( 'Big-file-uploads', $manual_table );
		$this->assertStringContainsString( 'Manual Fix Required', $manual_table );

		$review_table = Pantheon\Site_Health\output_compatibility_status_table( $review_fixes, false, true );
		$this->assertStringContainsString( 'Phastpress', $review_table );
		$this->assertStringContainsString( 'Incompatible', $review_table );
	}

	/**
	 * Test that persist_data preserves the original timestamp across multiple calls.
	 */
	public function test_persist_data_preserves_timestamp() {
		$slug = 'test-plugin/test-plugin.php';

		delete_option( 'pantheon_applied_fixes' );
		wp_cache_delete( 'plugin_timestamp', 'pantheon_compatibility' );

		$plugin = new Stub_Compatibility_Plugin( $slug );

		$plugin->call_persist_data( [ 'run_fix_everytime' ] );
		$first_timestamp = get_option( 'pantheon_applied_fixes' )[ $slug ]['plugin_timestamp'];

		wp_cache_delete( 'plugin_timestamp', 'pantheon_compatibility' );
		sleep( 1 );

		$plugin->call_persist_data( [ 'run_fix_everytime' ] );
		$second_timestamp = get_option( 'pantheon_applied_fixes' )[ $slug ]['plugin_timestamp'];

		$this->assertSame( $first_timestamp, $second_timestamp, 'Timestamp should not change between calls' );

		delete_option( 'pantheon_applied_fixes' );
	}
}
