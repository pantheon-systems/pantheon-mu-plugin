<?php
/**
 * Plugin Name: Pantheon MU Plugin Loader
 * Description: Loads the MU plugins required to run the site
 * Author: Pantheon
 * Author URI: https://pantheon.io
 * Version: 1.0
 * License: MIT
 */

if ( ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) ) {
	return;
}

// Add your mu-plugins here.
$mu_plugins = [
	'bedrock-disallow-indexing/bedrock-disallow-indexing.php',
	'lh-hsts/lh-hsts.php',
	'pantheon-advanced-page-cache/pantheon-advanced-page-cache.php',
	'pantheon-wp-muplugin/pantheon.php',
	'wp-native-php-sessions/pantheon-sessions.php',
];

foreach ( $mu_plugins as $file ) {
	$path = dirname(__FILE__) . 'loader.php/' . $file;

	if ( file_exists( $path ) ) {
		include_once $path;
	} else {
		error_log( sprintf( 'Tried to load %s but the file was not found.', $file ) );
	}
}
unset( $file );

add_action(
	'pre_current_active_plugins', function () use ( $mu_plugins ) {
		global $plugins, $wp_list_table;

		// Add our own mu-plugins to the page.
		foreach ( $mu_plugins as $plugin_file ) {
			$plugin_data = get_plugin_data( WPMU_PLUGIN_DIR . "/$plugin_file", false, false ); // Do not apply markup/translate as it'll be cached.

			if ( empty( $plugin_data['Name'] ) ) {
				$plugin_data['Name'] = $plugin_file;
			}

			$plugins['mustuse'][ $plugin_file ] = $plugin_data;
		}

		// Recount totals.
		$GLOBALS['totals']['mustuse'] = count( $plugins['mustuse'] );

		// Only apply the rest if we're actually looking at the page.
		if ( 'mustuse' !== $GLOBALS['status'] ) {
			return;
		}

		// Reset the list table's data.
		$wp_list_table->items = $plugins['mustuse'];
		foreach ( $wp_list_table->items as $plugin_file => $plugin_data ) {
			$wp_list_table->items[ $plugin_file ] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
		}

		$total_this_page = $GLOBALS['totals']['mustuse'];

		if ( $GLOBALS['orderby'] ) {
			uasort( $wp_list_table->items, [ $wp_list_table, '_order_callback' ] );
		}

		// Force showing all plugins
		// See https://core.trac.wordpress.org/ticket/27110 .
		$plugins_per_page = $total_this_page;

		$wp_list_table->set_pagination_args(
			[
				'total_items' => $total_this_page,
				'per_page'    => $plugins_per_page,
			]
		);
	}
);

add_action(
	'network_admin_plugin_action_links', function ( $actions, $plugin_file, $plugin_data, $context ) use ( $mu_plugins ) {
		if ( $context !== 'mustuse' || ! in_array( $plugin_file, $mu_plugins, true ) ) {
			return;
		}

		$actions[] = sprintf( '<span style="color:#333">File: <code>%s</code></span>', $plugin_file );

		return $actions;
	}, 10, 4
);
