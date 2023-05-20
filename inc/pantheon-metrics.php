<?php
/**
 * If this is a Pantheon site, show a notice about low cache hit ratio
 *
 * @package pantheon
 */

// If on Pantheon...
if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
	add_action( 'admin_notices', '_pantheon_metrics_notice' );
	add_action( 'network_admin_notices', '_pantheon_metrics_notice' );
}

/**
 * Fetch traffic metrics from the Pantheon API
 *
 * @return array
 */
function _pantheon_get_metrics() {
	$metrics = get_transient( '_pantheon_metrics' );
	if ( $metrics != false ) {
		return $metrics;
	}
	if ( true == get_transient( '_pantheon_metrics_no_retry' ) ) {
		return false;
	}
	$url = 'https:/api.live.getpantheon.com/sites/self/environments/live/traffic?duration=28d';
	$req = pantheon_curl( $url, NULL, 8443 );
	if( 200 != $req['status-code'] || ! $req['body'] ) {
		// Don't retry for two minutes
		set_transient( '_pantheon_metrics_no_retry', true, 120 );
		return false;
	}
	$metrics = json_decode( $req['body'], TRUE );
	set_transient( '_pantheon_metrics', $metrics['timeseries'], 86400);
	return $metrics['timeseries'];
}

/**
 * Get the most recent day's cache hit ratio
 *
 * @return int Cache hit ratio, or false on error
 */
function _pantheon_get_cache_hit_ratio() {
	$metrics = _pantheon_get_metrics();
	if ( ! $metrics ) {
		return false;
	}
	$last_day = array_pop($metrics);
	if ( ! $last_day['pages_served'] ) {
		return false;
	}
	return number_format( $last_day['cache_hits'] / $last_day['pages_served'] * 100, 0);
}

/**
 * Add a notice of low cache hit ratio on the dashboard and site health pages
 *
 * @return void
 */
function _pantheon_metrics_notice() {
	$cache_hit_ratio = _pantheon_get_cache_hit_ratio();
	if($cache_hit_ratio > 50 || $cache_hit_ratio === false) {
		return false;
	}
	$screen = get_current_screen();
	if ( 'dashboard' === $screen->id || 'site-health' === $screen->id ) {
	?>
		<div class="update-nag notice notice-warning is-dismissible" style="display: table;">
			<p style="font-size: 14px; margin: 0;">
				<?php
				// Translators: %s is a URL to the user's Pantheon Dashboard.
				echo wp_kses_post( sprintf( __( 'The live site is currently only serving %d%% of traffic from the GCDN cache. See more details in the <a href="%s">Metrics tab</a> in your Pantheon dashboard.', 'pantheon-systems' ), $cache_hit_ratio, 'https://dashboard.pantheon.io/sites/' . $_ENV['PANTHEON_SITE'] . '#live/metrics' ) );
				?>
			</p>
		</div>
		<?php
	}
}
