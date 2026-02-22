<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'probonoseo_output_breadcrumb_schema_only', 99 );

function probonoseo_output_breadcrumb_schema_only() {

	if ( is_admin() ) {
		return;
	}

}
