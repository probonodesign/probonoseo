<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wpseo_json_ld_output', '__return_empty_array', 20 );
add_filter( 'rank_math/json_ld', '__return_empty_array', 20 );
