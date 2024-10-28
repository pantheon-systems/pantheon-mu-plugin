<?php 

/*
 * HEIC support added in 6.7 but not yet supported by ImageMagick on Pantheon.
 */
add_filter( 'image_editor_output_format', function( $output_format ) {
    if ( isset($output_format['image/heic'] ) ) {
	    unset( $output_format['image/heic'] );
    }
    return $output_format;
} );