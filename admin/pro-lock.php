<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_is_pro() {
	$key = get_option( 'probonoseo_license_key', '' );
	if ( $key && strlen( $key ) > 10 ) {
		return true;
	}
	return false;
}

function probonoseo_pro_filter( $value ) {
	if ( probonoseo_is_pro() ) {
		return true;
	}
	return false;
}

add_filter( 'probonoseo_is_pro_active', 'probonoseo_pro_filter' );
?>
