<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_get_default_ogp_image() {
	$path = get_theme_file_uri( 'default-ogp.png' );
	return $path;
}
