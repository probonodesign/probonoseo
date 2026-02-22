<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_clean_text( $text ) {

	$text = wp_strip_all_tags( $text );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( $text );
}

function probonoseo_trim_length( $text, $len = 120 ) {
	return mb_substr( $text, 0, $len );
}
