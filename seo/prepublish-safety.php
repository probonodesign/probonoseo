<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'transition_post_status', 'probonoseo_prepublish_check', 10, 3 );

function probonoseo_prepublish_check( $new, $old, $post ) {

	if ( $new !== 'publish' ) {
		return;
	}

	if ( ! $post || $post->post_status !== 'publish' ) {
		return;
	}

	$title   = get_the_title( $post->ID );
	$content = wp_strip_all_tags( $post->post_content );

	if ( strlen( $title ) < 5 || strlen( $content ) < 50 ) {
		return;
	}
}
