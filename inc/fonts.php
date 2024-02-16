<?php
/**
 * Pantheon mu-plugin customizations to the WP_Font_Library added in WordPress 6.5
 *
 * @package pantheon
 */

namespace Pantheon\Fonts;

/**
 * Store the value of wp_get_upload_dir() in a global variable.
 * This is to resolve an infinite loop when wp_get_upload_dir is used inside
 * our filter of font_dir (because upload_dir is also being filtered).
 *
 * @var array $wp_upload_dir The value of wp_get_upload_dir().
 * @see https://developer.wordpress.org/reference/functions/wp_get_upload_dir/
 */
$_pantheon_upload_dir = wp_get_upload_dir(); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Kick off our customizations to the WP_Font_Library.
 */
function bootstrap() {
	// Use the new font_dir filter added in WordPress 6.5. See https://github.com/WordPress/gutenberg/pull/57697.
	add_filter( 'font_dir', __NAMESPACE__ . '\\pantheon_font_dir', 9 );
}
add_action( 'init', __NAMESPACE__ . '\\bootstrap' );

/**
 * Define a custom font directory for the WP Font Library.
 * Default to {WP_CONTENT_DIR}/uploads/fonts.
 *
 * @param array $defaults The default settings for the font directory.
 */
function pantheon_font_dir( $defaults ) {
	global $_pantheon_upload_dir;
	// Set our font directory.
	$font_dir = $_pantheon_upload_dir['basedir'] . '/fonts';
	$font_url = $_pantheon_upload_dir['baseurl'] . '/fonts';

	$defaults['path'] = $font_dir;
	$defaults['url'] = $font_url;
	$defaults['basedir'] = $font_dir;
	$defaults['baseurl'] = $font_url;

	return $defaults;
}
