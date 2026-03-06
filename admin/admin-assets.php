<?php
if (!defined('ABSPATH')) {
	exit;
}

function probonoseo_admin_assets($hook) {

	if ($hook !== 'toplevel_page_probonoseo') {
		return;
	}

	wp_enqueue_style(
		'probonoseo-admin-style',
		PROBONOSEO_URL . 'admin/admin-style.css',
		array(),
		PROBONOSEO_VERSION
	);

	wp_enqueue_style(
		'probonoseo-switch-css',
		PROBONOSEO_URL . 'admin/switch.css',
		array(),
		PROBONOSEO_VERSION
	);

	wp_enqueue_script(
		'probonoseo-switch-js',
		PROBONOSEO_URL . 'admin/switch.js',
		array(),
		PROBONOSEO_VERSION,
		true
	);
}

add_action('admin_enqueue_scripts', 'probonoseo_admin_assets');