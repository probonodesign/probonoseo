<?php
if (!defined('ABSPATH')) {
	exit;
}

function probonoseo_admin_menu() {
	$cap = 'manage_options';

	add_menu_page(
		'ProbonoSEO',
		'ProbonoSEO',
		$cap,
		'probonoseo',
		'probonoseo_admin_page',
		'dashicons-chart-line',
		82
	);

	add_submenu_page(
		'probonoseo',
		'設定',
		'設定',
		$cap,
		'probonoseo',
		'probonoseo_admin_page'
	);
}
add_action('admin_menu', 'probonoseo_admin_menu');

function probonoseo_admin_assets($hook) {

	if (strpos($hook, 'probonoseo') === false) {
		return;
	}

	wp_enqueue_style(
		'probonoseo-admin-style',
		PROBONOSEO_URL . 'admin/admin-style.css',
		array(),
		PROBONOSEO_VERSION
	);

	wp_enqueue_style(
		'probonoseo-switch-style',
		PROBONOSEO_URL . 'admin/switch.css',
		array(),
		PROBONOSEO_VERSION
	);

	wp_enqueue_script(
		'probonoseo-switch-script',
		PROBONOSEO_URL . 'admin/switch.js',
		array('jquery'),
		PROBONOSEO_VERSION,
		true
	);
}
add_action('admin_enqueue_scripts', 'probonoseo_admin_assets');

function probonoseo_admin_page() {
	require_once PROBONOSEO_PATH . 'admin/admin-page.php';
}
