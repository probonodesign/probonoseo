<?php
if (!defined('ABSPATH')) {
	exit;
}

function probonoseo_ogp_title() {
	return probonoseo_get_ogp_title();
}

function probonoseo_ogp_description() {
	return probonoseo_get_ogp_description();
}

function probonoseo_ogp_image() {
	return probonoseo_get_ogp_image();
}

function probonoseo_ogp_url() {
	return probonoseo_get_canonical_url();
}

function probonoseo_output_open_graph() {
	if (is_admin()) {
		return;
	}

	$title = probonoseo_ogp_title();
	$desc = probonoseo_ogp_description();
	$image = probonoseo_ogp_image();
	$url = probonoseo_ogp_url();

	echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";

	if ($image) {
		echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
	}

	echo '<meta property="og:type" content="website">' . "\n";
}