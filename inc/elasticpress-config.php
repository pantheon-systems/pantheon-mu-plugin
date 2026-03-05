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
if ( ! defined( 'EP_HOST' ) && ! empty( $_ENV['EP_HOST'] ) ) {
	define( 'EP_HOST', $_ENV['EP_HOST'] );
}

/**
 * Set EP_INDEX_PREFIX constant from environment variable if available.
 *
 * This constant identifies your ElasticPress subscription.
 */
if ( ! defined( 'EP_INDEX_PREFIX' ) && ! empty( $_ENV['EP_INDEX_PREFIX'] ) ) {
	define( 'EP_INDEX_PREFIX', $_ENV['EP_INDEX_PREFIX'] );
}

/**
 * Set EP_CREDENTIALS constant from environment variable if available.
 *
 * This constant authenticates your connection to ElasticPress.
 * Format: subscriptionID:subscriptionToken
 */
if ( ! defined( 'EP_CREDENTIALS' ) && ! empty( $_ENV['EP_CREDENTIALS'] ) ) {
	define( 'EP_CREDENTIALS', $_ENV['EP_CREDENTIALS'] );
}
