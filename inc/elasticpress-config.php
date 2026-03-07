<?php
/**
 * ElasticPress Configuration
 *
 * Automatically configure ElasticPress constants from environment variables
 * for sites with Elasticsearch activated on Performance+Elite site plans.
 *
 * @package pantheon
 */

/**
 * Set EP_HOST constant from environment variable if available.
 *
 * This constant specifies the ElasticPress service endpoint.
 */
if ( ! defined( 'EP_HOST' ) && ! empty( $_ENV['PANTHEON_SEARCH_HOST'] ) ) {
	define( 'EP_HOST', $_ENV['PANTHEON_SEARCH_HOST'] );
}

/**
 * Set EP_INDEX_PREFIX constant from environment variable if available.
 *
 * This constant identifies your ElasticPress subscription.
 */
if ( ! defined( 'EP_INDEX_PREFIX' ) && ! empty( $_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] ) ) {
	define( 'EP_INDEX_PREFIX', $_ENV['PANTHEON_SEARCH_ENDPOINT_ID'] );
}

/**
 * Set EP_CREDENTIALS constant from environment variable if available.
 *
 * This constant authenticates your connection to ElasticPress.
 * Format: subscriptionID:subscriptionToken
 */
if ( ! defined( 'EP_CREDENTIALS' ) && ! empty( $_ENV['PANTHEON_SEARCH_CREDENTIALS'] ) ) {
	define( 'EP_CREDENTIALS', $_ENV['PANTHEON_SEARCH_CREDENTIALS'] );
}

/**
 * Force HTTPS scheme for home and siteurl options during ElasticPress CLI syncs.
 *
 * When WP-CLI runs via Terminus, $_SERVER['HTTP_HOST'] is not set, so
 * wp-config-pantheon.php skips defining WP_HOME/WP_SITEURL. WordPress
 * falls back to database values which may use http:// scheme. This causes
 * ElasticPress to index content with http:// URLs, leading to mixed content
 * and broken images on the HTTPS frontend.
 *
 * All Pantheon environments enforce HTTPS, so http:// is never correct.
 *
 * Use the 'pantheon_elasticpress_force_https_in_cli' filter to disable
 * this behavior:
 *
 *     add_filter( 'pantheon_elasticpress_force_https_in_cli', '__return_false' );
 *
 * @see https://getpantheon.atlassian.net/browse/SITE-5401
 */
/**
 * Replace http:// with https:// in a URL string.
 *
 * @param string $url The option value.
 * @return string The URL with https:// scheme.
 */
function _pantheon_ep_force_https_url( $url ) {
	if ( is_string( $url ) && strpos( $url, 'http://' ) === 0 ) {
		return 'https://' . substr( $url, 7 );
	}
	return $url;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_hook( 'before_invoke:elasticpress', function () {
		/**
		 * Filter whether to force HTTPS for home/siteurl during ElasticPress CLI commands.
		 *
		 * @param bool $force_https Whether to force HTTPS. Default true.
		 */
		if ( ! apply_filters( 'pantheon_elasticpress_force_https_in_cli', true ) ) {
			return;
		}

		if ( ! defined( 'WP_HOME' ) || strpos( WP_HOME, 'http://' ) === 0 ) {
			add_filter( 'option_home', '_pantheon_ep_force_https_url' );
		}
		if ( ! defined( 'WP_SITEURL' ) || strpos( WP_SITEURL, 'http://' ) === 0 ) {
			add_filter( 'option_siteurl', '_pantheon_ep_force_https_url' );
		}
	} );
}
