<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Search {

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

		add_action('wp_head', array($this, 'output_search_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_search_title'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_search', '1') === '1';
	}

	public function output_search_meta() {
		if (!is_search()) {
			return;
		}

		$robots = get_option('probonoseo_pro_search_robots', 'noindex, nofollow');

		if (!empty($robots)) {
			echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
		}

		$query = get_search_query();
		$count = $GLOBALS['wp_query']->found_posts;

		$description = sprintf(
			'「%s」の検索結果：%d件の記事が見つかりました。%s',
			esc_attr($query),
			$count,
			get_bloginfo('name')
		);

		echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
	}

	public function filter_search_title($title) {
		if (!is_search()) {
			return $title;
		}

		$query = get_search_query();
		$title_template = get_option('probonoseo_pro_search_title', '');

		if (!empty($title_template)) {
			$title['title'] = str_replace('%query%', $query, $title_template);
		} else {
			$title['title'] = '「' . $query . '」の検索結果';
		}

		return $title;
	}
}

ProbonoSEO_Pro_Search::get_instance();