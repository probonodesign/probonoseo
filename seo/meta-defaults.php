<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_get_default_meta_description( $context = '' ) {

	if ( $context === 'home' ) {
		return get_bloginfo( 'description' );
	}

	if ( $context === 'search' ) {
		return 'Search results';
	}

	if ( $context === '404' ) {
		return 'Page not found';
	}

	return '';
}
