<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Attachment {

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

		$redirect = get_option('probonoseo_pro_attachment_redirect', '1');

		if ($redirect === '1') {
			add_action('template_redirect', array($this, 'redirect_attachment'));
		} else {
			add_action('wp_head', array($this, 'output_attachment_meta'), 5);
			add_filter('document_title_parts', array($this, 'filter_attachment_title'));
		}
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_attachment', '1') === '1';
	}

	public function redirect_attachment() {
		if (!is_attachment()) {
			return;
		}

		global $post;

		if ($post && $post->post_parent) {
			$parent_url = get_permalink($post->post_parent);
			wp_safe_redirect($parent_url, 301);
			exit;
		}

		wp_safe_redirect(home_url('/'), 301);
		exit;
	}

	public function output_attachment_meta() {
		if (!is_attachment()) {
			return;
		}

		$robots = get_option('probonoseo_pro_attachment_robots', 'noindex');

		if (!empty($robots)) {
			echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
		}

		global $post;
		$description = '';

		if ($post) {
			$caption = $post->post_excerpt;
			$alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);

			if (!empty($caption)) {
				$description = $caption;
			} elseif (!empty($alt)) {
				$description = $alt;
			} else {
				$description = $post->post_title . ' - ' . get_bloginfo('name');
			}
		}

		if (!empty($description)) {
			echo '<meta name="description" content="' . esc_attr(mb_substr($description, 0, 120)) . '">' . "\n";
		}
	}

	public function filter_attachment_title($title) {
		if (!is_attachment()) {
			return $title;
		}

		global $post;

		if ($post) {
			$alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);

			if (!empty($alt)) {
				$title['title'] = $alt;
			}
		}

		return $title;
	}
}

ProbonoSEO_Pro_Attachment::get_instance();