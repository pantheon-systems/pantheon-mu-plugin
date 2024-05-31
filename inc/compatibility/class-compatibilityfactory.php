<?php
/**
 * Compatibility Factory
 *
 * @package Pantheon
 */

namespace Pantheon\Compatibility;

use function __;
use function array_filter;
use function array_key_exists;
use function esc_html_e;
use function get_option;
use function in_array;
use function time;
use function wp_kses_post;
use function wp_next_scheduled;
use function wp_schedule_event;

/**
 * Class CompatibilityFactory
 *
 * @package Pantheon\Compatibility
 */
class CompatibilityFactory {



	/**
	 * Instance of Pantheon
	 *
	 * @var CompatibilityFactory
	 */
	private static $instance;

	/**
	 * @var array $plugin_classes
	 */
	private static $plugin_classes = [];

	/**
	 * Pantheon constructor.
	 */
	private function __construct() {
		static::$plugin_classes = [
			AcceleratedMobilePages::$plugin_slug => 'Pantheon\\Compatibility\\AcceleratedMobilePages',
			Auth0::$plugin_slug => 'Pantheon\\Compatibility\\Auth0',
			Autoptimize::$plugin_slug => 'Pantheon\\Compatibility\\Autoptimize',
			BetterSearchReplace::$plugin_slug => 'Pantheon\\Compatibility\\BetterSearchReplace',
			BrokenLinkChecker::$plugin_slug => 'Pantheon\\Compatibility\\BrokenLinkChecker',
			ContactFormSeven::$plugin_slug => 'Pantheon\\Compatibility\\ContactFormSeven',
			EventEspresso::$plugin_slug => 'Pantheon\\Compatibility\\EventEspresso',
			FastVelocityMinify::$plugin_slug => 'Pantheon\\Compatibility\\FastVelocityMinify',
			ForceLogin::$plugin_slug => 'Pantheon\\Compatibility\\ForceLogin',
			OfficialFacebookPixel::$plugin_slug => 'Pantheon\\Compatibility\\OfficialFacebookPixel',
			Polylang::$plugin_slug => 'Pantheon\\Compatibility\\Polylang',
			Redirection::$plugin_slug => 'Pantheon\\Compatibility\\Redirection',
			SliderRevolution::$plugin_slug => 'Pantheon\\Compatibility\\SliderRevolution',
			TweetOldPost::$plugin_slug => 'Pantheon\\Compatibility\\TweetOldPost',
			WPRocket::$plugin_slug => 'Pantheon\\Compatibility\\WPRocket',
			WooZone::$plugin_slug => 'Pantheon\\Compatibility\\WooZone',
			YITHWoocommerce::$plugin_slug => 'Pantheon\\Compatibility\\YITHWoocommerce',
		];
		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_action( 'pantheon_cron', [ $this, 'daily_pantheon_cron' ] );
		$this->extend_site_health();
	}

	/**
	 * Extend site health admin page.
	 *
	 * @return void
	 */
	private function extend_site_health() {
		add_filter( 'site_health_navigation_tabs', [ $this, 'add_site_health_tab' ] );
		add_action( 'site_health_tab_content', [ $this, 'output_site_health_tab_content' ] );
	}

	/**
	 * Get instance of Pantheon
	 *
	 * @return CompatibilityFactory
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function add_site_health_tab( $tabs ) {
		$tabs['compatibility'] = 'Pantheon Compatibility';

		return $tabs;
	}

	public function output_site_health_tab_content( $tab ) {
		if ( 'compatibility' !== $tab ) {
			return;
		}
		?>

		<div class="health-check-body health-check-compatibility-tab hide-if-no-js">
			<h2>
				<?php esc_html_e( 'Pantheon Compatibility' ); ?>
			</h2>

			<p>
				<?php
				printf(
				/* translators: %s: URL to Site Health Status page. */
					(
					'This page lists active plugins that have known compatibility issues with Pantheon\'s infrastructure. For additional details, see the <a href="%s" target="_blank">Known Issues</a> page.'
					),
					esc_url( 'https://docs.pantheon.io/plugins-known-issues' )
				);
				?>
			</p>

			<div id="health-check-compatibility" class="health-check-accordion">

				<?php

				$info = [
					'automatic' => [
						'label' => __( 'Automatic Fixes' ),
						'description' => __( 'Compatibility with the following plugins has been automatically added.' ),
						'fields' => get_option( 'pantheon_applied_fixes' ),
						'show_count' => true,
					],
					'manual' => [
						'label' => __( 'Manual Fixes' ),
						'description' => __( 'Compatibility with the following plugins needs to be manually applied.' ),
						'fields' => $this->get_manual_fixes(),
						'show_count' => true,
					],
					'notes' => [
						'label' => __( 'Needs Review' ),
						'description' => __( 'Compatibility with the following plugins needs to be reviewed.' ),
						'fields' => $this->get_review_fixes(),
						'show_count' => true,
					],
				];
				foreach ( $info as $section => $details ) :
					if ( empty( $details['fields'] ) ) {
						continue;
					}

					?>
					<h3 class="health-check-accordion-heading">
						<button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" type="button">
					<span class="title">
						<?php echo esc_html( $details['label'] ); ?>
						<?php

						if ( isset( $details['show_count'] ) && $details['show_count'] ) {
							printf(
								'(%s)',
								esc_html( number_format_i18n( count( $details['fields'] ) ) )
							);
						}

						?>
					</span>
							<span class="icon"></span>
						</button>
					</h3>

					<div id="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" class="health-check-accordion-panel" hidden="hidden">
						<?php

						if ( ! empty( $details['description'] ) ) {
							printf( '<p>%s</p>', esc_html( $details['description'] ) );
						}

						?>
						<table class="widefat striped health-check-table" role="presentation">
							<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin' ); ?></th>
								<th><?php esc_html_e( 'Compatibility Status' ); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php

							foreach ( $details['fields'] as $field ) {
								$values = '<ul>';
								$values .= '<li><b>' . ucfirst( $field['plugin_status'] ) . '</b></li>';
								$values .= '<li>' . $field['plugin_message'] . '</li>';
								$values .= '</ul>';
								printf(
									'<tr><td>%s</td><td>%s</td></tr>',
									esc_html( $field['plugin_name'] ),
									wp_kses_post( $values )
								);
							}

							?>
							</tbody>
						</table>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	private function get_manual_fixes() {
		$plugins = [
			'big-file-uploads' => [
				'plugin_name' => 'Big File Uploads',
				'plugin_status' => 'Manual Fix Required',
				'plugin_slug' => 'tuxedo-big-file-uploads/tuxedo_big_file_uploads.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#big-file-uploads" target="_blank">here</a>.',
			],
			'jetpack' => [
				'plugin_name' => 'Jetpack',
				'plugin_status' => 'Manual Fix Required',
				'plugin_slug' => 'jetpack/jetpack.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#jetpack" target="_blank">here</a>.',
			],
			'wordfence' => [
				'plugin_name' => 'Wordfence',
				'plugin_status' => 'Manual Fix Required',
				'plugin_slug' => 'wordfence/wordfence.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wordfence" target="_blank">here</a>.',
			],
			'wpml' => [
				'plugin_name' => 'WPML - The WordPress Multilingual Plugin',
				'plugin_status' => 'Manual Fix Required',
				'plugin_slug' => 'sitepress-multilingual-cms/sitepress.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wpml-the-wordpress-multilingual-plugin" target="_blank">here</a>.',
			],
		];

		return array_filter($plugins, static function ( $plugin ) {
			return in_array( $plugin['plugin_slug'], get_option( 'active_plugins' ), true );
		});
	}

	private function get_review_fixes() {
		$plugins = [
			'raptive-ads' => [
				'plugin_name' => 'Raptive Ads',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'raptive-ads/adthrive-ads.php',
				'plugin_message' => 'Read more about the issue <a href="https://help.raptive.com/hc/en-us/articles/360031132752-Should-I-update-my-AdThrive-Ads-plugin" target="_blank">here</a>.',
			],
			'all-in-one-wp-migration' => [
				'plugin_name' => 'All-in-One WP Migration',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'all-in-one-wp-migration/all-in-one-wp-migration.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#all-in-one-wp-migration" target="_blank">here</a>.',
			],
			'bookly' => [
				'plugin_name' => 'Bookly',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'bookly-responsive-appointment-booking-tool/main.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#bookly" target="_blank">here</a>.',
			],
			'coming-soon' => [
				'plugin_name' => 'Coming Soon',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'coming-soon/coming-soon.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#coming-soon" target="_blank">here</a>.',
			],
			'disable-json-api' => [
				'plugin_name' => 'Disable REST API and Require JWT / OAuth Authentication',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'disable-json-api/disable-json-api.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#disable-rest-api-and-require-jwt--oauth-authentication" target="_blank">here</a>.',
			],
			'divi-builder' => [
				'plugin_name' => 'Divi WordPress Theme & Visual Page Builder',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'divi-builder/divi-builder.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#divi-wordpress-theme--visual-page-builder" target="_blank">here</a>.',
			],
			'elementor' => [
				'plugin_name' => 'Elementor',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'elementor/elementor.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#elementor" target="_blank">here</a>.',
			],
			'facetwp' => [
				'plugin_name' => 'FacetWP',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'facetwp/index.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#facetwp" target="_blank">here</a>.',
			],
			'cookie-law-info' => [
				'plugin_name' => 'GDPR Cookie Consent',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'cookie-law-info/cookie-law-info.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#gdpr-cookie-consent" target="_blank">here</a>.',
			],
			'h5p' => [
				'plugin_name' => 'H5P',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'h5p/h5p.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#h5p" target="_blank">here</a>.',
			],
			'hm-require-login' => [
				'plugin_name' => 'HM Require Login',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'hm-require-login/hm-require-login.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#hm-require-login" target="_blank">here</a>.',
			],
			'hummingbird-performance' => [
				'plugin_name' => 'Hummingbird',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'hummingbird-performance/wp-hummingbird.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#hummingbird" target="_blank">here</a>.',
			],
			'hyperdb' => [
				'plugin_name' => 'HyperDB',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'hyperdb/db.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#hyperdb" target="_blank">here</a>.',
			],
			'iwp-client' => [
				'plugin_name' => 'InfiniteWP',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'iwp-client/init.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#infinitewp" target="_blank">here</a>.',
			],
			'instashow' => [
				'plugin_name' => 'Instashow',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'instashow/instashow.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#instashow" target="_blank">here</a>.',
			],
			'wp-maintenance-mode' => [
				'plugin_name' => 'Maintenance Mode',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'wp-maintenance-mode/wp-maintenance-mode.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#maintenance-mode" target="_blank">here</a>.',
			],
			'worker' => [
				'plugin_name' => 'ManageWP Worker',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'worker/init.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#managewp-worker" target="_blank">here</a>.',
			],
			'monarch' => [
				'plugin_name' => 'Monarch Social Sharing',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'monarch/monarch.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#monarch-social-sharing" target="_blank">here</a>.',
			],
			'new-relic' => [
				'plugin_name' => 'New Relic Reporting for WordPress',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'new-relic/new-relic.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#new-relic-reporting-for-wordpress" target="_blank">here</a>.',
			],
			'object-sync-for-salesforce' => [
				'plugin_name' => 'Object Sync for Salesforce',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'object-sync-for-salesforce/object-sync-for-salesforce.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#object-sync-for-salesforce" target="_blank">here</a>.',
			],
			'one-click-demo-import' => [
				'plugin_name' => 'One Click Demo Import',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'one-click-demo-import/one-click-demo-import.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#one-click-demo-import" target="_blank">here</a>.',
			],
			'posts-to-posts' => [
				'plugin_name' => 'Posts 2 Posts',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'posts-to-posts/posts-to-posts.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#posts-2-posts" target="_blank">here</a>.',
			],
			'query-monitor' => [
				'plugin_name' => 'Query Monitor',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'query-monitor/query-monitor.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#query-monitor" target="_blank">here</a>.',
			],
			'site24x7' => [
				'plugin_name' => 'Site24x7',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'site24x7/site24x7.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#site24x7" target="_blank">here</a>.',
			],
			'wp-smush-pro' => [
				'plugin_name' => 'Smush Pro',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'wp-smush-pro/wp-smush-pro.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#smush-pro" target="_blank">here</a>.',
			],
			'better-wp-security' => [
				'plugin_name' => 'Solid Security (Previously: iThemes Security)',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'better-wp-security/better-wp-security.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#solid-security-previously-ithemes-security" target="_blank">here</a>.',
			],
			'unbounce' => [
				'plugin_name' => 'Unbounce Landing Pages',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'unbounce/unbounce.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#unbounce-landing-pages" target="_blank">here</a>.',
			],
			'unyson' => [
				'plugin_name' => 'Unyson Theme Framework',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'unyson/unyson.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#unyson-theme-framework" target="_blank">here</a>.',
			],
			'updraftplus' => [
				'plugin_name' => 'Updraft / Updraft Plus Backup',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'updraftplus/updraftplus.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#updraft--updraft-plus-backup" target="_blank">here</a>.',
			],
			'weather-station' => [
				'plugin_name' => 'Weather Station',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'weather-station/weather-station.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#weather-station" target="_blank">here</a>.',
			],
			'webp-express' => [
				'plugin_name' => 'WebP Express',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'webp-express/webp-express.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#webp-express" target="_blank">here</a>.',
			],
			'woocommerce' => [
				'plugin_name' => 'WooCommerce',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'woocommerce/woocommerce.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#woocommerce" target="_blank">here</a>.',
			],
			'download-manager' => [
				'plugin_name' => 'WordPress Download Manager',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'download-manager/download-manager.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wordpress-download-manager" target="_blank">here</a>.',
			],
			'wp-all-import' => [
				'plugin_name' => 'WP All Import / Export',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'wp-all-import/wp-all-import.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wp-all-import--export" target="_blank">here</a>.',
			],
			'wp-migrate-db' => [
				'plugin_name' => 'WP Migrate DB',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'wp-migrate-db/wp-migrate-db.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wp-migrate-db" target="_blank">here</a>.',
			],
			'wp-phpmyadmin' => [
				'plugin_name' => 'WP phpMyAdmin',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'wp-phpmyadmin/wp-phpmyadmin.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wp-phpmyadmin" target="_blank">here</a>.',
			],
			'wp-reset' => [
				'plugin_name' => 'WP Reset',
				'plugin_status' => 'Incompatible',
				'plugin_slug' => 'wp-reset/wp-reset.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wp-reset" target="_blank">here</a>.',
			],
			'wp-ban' => [
				'plugin_name' => 'WP-Ban',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'wp-ban/wp-ban.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wp-ban" target="_blank">here</a>.',
			],
			'wpfront-notification-bar' => [
				'plugin_name' => 'WPFront Notification Bar',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'wpfront-notification-bar/wpfront-notification-bar.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#wpfront-notification-bar" target="_blank">here</a>.',
			],
			'yoast-seo' => [
				'plugin_name' => 'Yoast SEO',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'wordpress-seo/wp-seo.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#yoast-seo" target="_blank">here</a>.',
			],
			'yoast-indexables' => [
				'plugin_name' => 'Yoast Indexables',
				'plugin_status' => 'Partial Compatibility',
				'plugin_slug' => 'yoast-seo/wp-seo.php',
				'plugin_message' => 'Read more about the issue <a href="https://docs.pantheon.io/plugins-known-issues#yoast-indexables" target="_blank">here</a>.',
			],
		];

		return array_filter($plugins, static function ( $plugin ) {
			return in_array( $plugin['plugin_slug'], get_option( 'active_plugins' ), true );
		});
	}

	/**
	 * Method to initialize plugin.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function init() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->load_plugins();

		if ( ! wp_next_scheduled( 'pantheon_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'pantheon_cron' );
		}
	}

	/**
	 * Load all the plugins
	 *
	 * @return void
	 */
	private function load_plugins() {
		$plugin_classes = $this->get_plugin_classes();

		// Loop through each plugin and include its file and instantiate its class.
		foreach ( $plugin_classes as $class ) {
			new $class();
		}
	}

	/**
	 * Get all the plugin classes
	 *
	 * @return array
	 */
	public function get_plugin_classes() {
		return static::$plugin_classes;
	}

	/**
	 * Daily cron job to apply fixes.
	 *
	 * @return void
	 */
	public function daily_pantheon_cron() {
		// get list of active plugins.
		$active_plugins = get_option( 'active_plugins' );

		$plugin_classes = $this->get_plugin_classes();

		// get list of applied fixes.
		$pantheon_applied_fixes = get_option( 'pantheon_applied_fixes' );

		// filter list of active plugins by fix availability & fix status.
		$active_plugins = array_filter($active_plugins,
			static function ( $plugin ) use ( $plugin_classes, $pantheon_applied_fixes ) {
				return array_key_exists( $plugin, $plugin_classes ) && ! array_key_exists($plugin,
				$pantheon_applied_fixes);
			}
		);
		array_map(static function ( $plugin ) use ( $plugin_classes ) {
			$plugin_class = $plugin_classes[ $plugin ];
			( new $plugin_class() )->apply_fix();
		}, $active_plugins);
	}
}
