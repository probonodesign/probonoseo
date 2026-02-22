<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_get_breadcrumb_items_only_names() {

	$list = array();
	$pos  = 1;

	if ( is_singular() ) {
		$list[] = array( $pos++, get_the_title() );
	}

	return $list;
}
