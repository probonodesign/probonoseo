<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Date {

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

		add_action('wp_head', array($this, 'output_date_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_date_title'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_date', '1') === '1';
	}

	public function output_date_meta() {
		if (!is_date()) {
			return;
		}

		$robots = get_option('probonoseo_pro_date_robots', 'noindex');
		$desc_template = get_option('probonoseo_pro_date_desc', '');

		if (!empty($robots)) {
			echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
		}

		$description = $this->generate_date_description($desc_template);

		if (!empty($description)) {
			echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
		}
	}

	public function filter_date_title($title) {
		if (!is_date()) {
			return $title;
		}

		$title_template = get_option('probonoseo_pro_date_title', '');

		if (!empty($title_template)) {
			$title['title'] = $this->parse_date_template($title_template);
		}

		return $title;
	}

	private function generate_date_description($template) {
		if (empty($template)) {
			$site_name = get_bloginfo('name');

			if (is_year()) {
				return $site_name . 'の' . get_the_date('Y年') . 'のアーカイブです。';
			} elseif (is_month()) {
				return $site_name . 'の' . get_the_date('Y年n月') . 'のアーカイブです。';
			} elseif (is_day()) {
				return $site_name . 'の' . get_the_date('Y年n月j日') . 'のアーカイブです。';
			}
		}

		return $this->parse_date_template($template);
	}

	private function parse_date_template($template) {
		$replacements = array(
			'%year%' => get_the_date('Y'),
			'%month%' => get_the_date('n'),
			'%month_name%' => get_the_date('F'),
			'%day%' => get_the_date('j'),
			'%site_name%' => get_bloginfo('name')
		);

		return str_replace(array_keys($replacements), array_values($replacements), $template);
	}
}

ProbonoSEO_Pro_Date::get_instance();