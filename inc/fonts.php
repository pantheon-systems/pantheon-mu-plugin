<?php

/**
 * Pantheon mu-plugin customizations to the WP_Font_Library added in WordPress 6.5
 *
 * @package pantheon
 */

namespace Pantheon\Fonts;

/**
 * Kick off our customizations to the WP_Font_Library.
 */
function bootstrap() {
	if ( pantheon_modify_fonts_dir() ) {
		// Use the new font_dir filter added in WordPress 6.5. See https://github.com/WordPress/gutenberg/pull/57697.
		add_filter( 'font_dir', __NAMESPACE__ . '\\pantheon_font_dir' );
	}
}
add_action( 'init', __NAMESPACE__ . '\\bootstrap' );

/**
 * Get the value of the pantheon_modify_fonts_dir filter.
 * By default, this should return true (we're filtering).
 *
 * @return bool Whether to modify the fonts directory.
 */
function pantheon_modify_fonts_dir() {
	/**
	 * Modify the fonts directory.
	 *
	 * By default, this is set to true, so we can override the default fonts directory from wp-content/fonts to wp-content/uploads/fonts.
	 *
	 * Use the filter to set to false and use the default WordPress behavior (committing fonts to your repository and pushing from dev -> test -> live).
	 *
	 * @param bool $modify_fonts_dir Whether to modify the fonts directory.
	 */
	return apply_filters( 'pantheon_modify_fonts_dir', true );
}

/**
 * Define a custom font directory for the WP Font Library.
 * Default to {WP_CONTENT_DIR}/uploads/fonts.
 *
 * @param array $defaults The default settings for the font directory.
 */
function pantheon_font_dir( $defaults ) {
	$wp_upload_dir = wp_get_upload_dir();
	$uploads_basedir = $wp_upload_dir['basedir'];
	$uploads_baseurl = $wp_upload_dir['baseurl'];

	/**
	 * Make the fonts directory filterable.
	 *
	 * By default, this is set to wp-content/uploads/fonts. Use this filter to set the fonts directory to any other location.
	 *
	 * @param string $font_dir The new font directory.
	 */
	$font_dir = apply_filters( 'pantheon_fonts_dir', $uploads_basedir . '/fonts' );
	// Generate the URL for the font directory from the font dir.
	$font_url = str_replace( $uploads_basedir, $uploads_baseurl, $font_dir );

	$defaults['path'] = $font_dir;
	$defaults['url'] = $font_url;
	$defaults['basedir'] = $font_dir;
	$defaults['baseurl'] = $font_url;

	return $defaults;
}
