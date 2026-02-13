<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_404 {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('init', array($this, 'init'));
	}

	public function init() {
		if (!$this->is_enabled()) {
			return;
		}

		add_action('wp_head', array($this, 'output_404_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_404_title'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_404', '1') === '1';
	}

	public function output_404_meta() {
		if (!is_404()) {
			return;
		}

		echo '<meta name="robots" content="noindex, nofollow">' . "\n";

		$description = get_option('probonoseo_pro_404_desc', '');

		if (empty($description)) {
			$description = 'お探しのページは見つかりませんでした。URLをご確認いただくか、' . get_bloginfo('name') . 'のトップページからお探しください。';
		}

		echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
	}

	public function filter_404_title($title) {
		if (!is_404()) {
			return $title;
		}

		$custom_title = get_option('probonoseo_pro_404_title', '');

		if (!empty($custom_title)) {
			$title['title'] = $custom_title;
		} else {
			$title['title'] = 'ページが見つかりません（404エラー）';
		}

		return $title;
	}
}

ProbonoSEO_Pro_404::get_instance();