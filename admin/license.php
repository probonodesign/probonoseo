<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_get_license_key() {
	$probonoseo_key = get_option( 'probonoseo_license_key', '' );
	return $probonoseo_key;
}

function probonoseo_save_license_key( $probonoseo_key ) {
	update_option( 'probonoseo_license_key', sanitize_text_field( $probonoseo_key ) );
}

function probonoseo_handle_license_post() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Legacy function, license handling done via AJAX
	if ( isset( $_POST['probonoseo_save_license'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Legacy function, license handling done via AJAX
		if ( isset( $_POST['probonoseo_license_key'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Legacy function, license handling done via AJAX
			probonoseo_save_license_key( sanitize_text_field( wp_unslash( $_POST['probonoseo_license_key'] ) ) );
		}
	}
}

add_action( 'admin_init', 'probonoseo_handle_license_post' );