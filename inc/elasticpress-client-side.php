<?php
/**
 * ElasticPress Client-Side Search Configuration
 *
 * Routes browser-originated search requests (Autosuggest and Instant Results)
 * directly to the public ElasticPress.io endpoint, bypassing the internal
 * mtlsproxy which is only accessible server-side.
 *
 * Server-side operations (indexing, admin queries, WP_Query integration)
 * continue to route through the mtlsproxy for authenticated access.
 *
 * @package pantheonex
 */

namespace Pantheon\ElasticPress;

if ( ! defined( 'EP_DIRECT_HOST' ) && ! empty( $_ENV['EP_DIRECT_HOST'] ) ) {
	define( 'EP_DIRECT_HOST', $_ENV['EP_DIRECT_HOST'] );
}

if ( ! defined( 'EP_DIRECT_HOST' ) ) {
	return;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\override_autosuggest_endpoint', 5 );
add_filter( 'ep_instant_results_search_endpoint', __NAMESPACE__ . '\\filter_instant_results_endpoint', 10, 2 );

/**
 * Define EP_AUTOSUGGEST_ENDPOINT to point at the direct ElasticPress.io host.
 *
 * Runs at plugins_loaded so that the ElasticPress plugin and its Indexables
 * registry are available.
 */
function override_autosuggest_endpoint() {
	if ( defined( 'EP_AUTOSUGGEST_ENDPOINT' ) ) {
		return;
	}

	if ( ! class_exists( '\\ElasticPress\\Indexables' ) ) {
		return;
	}

	$post_indexable = \ElasticPress\Indexables::factory()->get( 'post' );
	if ( ! $post_indexable ) {
		return;
	}

	$index = $post_indexable->get_index_name();
	define( 'EP_AUTOSUGGEST_ENDPOINT', EP_DIRECT_HOST . '/' . $index . '/autosuggest' );
}

/**
 * Filter the Instant Results search endpoint to use the direct ElasticPress.io URL.
 *
 * @param string $endpoint The default endpoint path.
 * @param string $index    The Elasticsearch index name.
 * @return string The full direct ElasticPress.io endpoint URL.
 */
function filter_instant_results_endpoint( $endpoint, $index ) {
	return EP_DIRECT_HOST . '/api/v1/search/posts/' . $index;
}
