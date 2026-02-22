<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Fonts {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('style_loader_tag', array($this, 'optimize_google_fonts'), 10, 4);
			add_action('wp_head', array($this, 'add_font_display_swap'), 1);
		}
	}

	public function is_enabled() {
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_fonts', '0') === '1';
	}

	public function optimize_google_fonts($tag, $handle, $href, $media) {
		if (!$this->is_enabled()) {
			return $tag;
		}

		if (strpos($href, 'fonts.googleapis.com') === false) {
			return $tag;
		}

		if (strpos($href, 'display=') === false) {
			if (strpos($href, '?') !== false) {
				$href = $href . '&display=swap';
			} else {
				$href = $href . '?display=swap';
			}
			$tag = str_replace($href, $href, $tag);
		}

		$tag = str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
		$tag = str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $tag);

		$probonoseo_preload = '<link rel="preload" as="style" href="' . esc_url($href) . '">';

		return $probonoseo_preload . "\n" . $tag;
	}

	public function add_font_display_swap() {
		if (!$this->is_enabled()) {
			return;
		}

		echo '<style id="probonoseo-font-display">@font-face{font-display:swap}</style>';
	}

	public function get_used_characters($content) {
		$probonoseo_characters = array();

		$probonoseo_content = wp_strip_all_tags($content);
		$probonoseo_content = html_entity_decode($probonoseo_content, ENT_QUOTES, 'UTF-8');

		$probonoseo_length = mb_strlen($probonoseo_content, 'UTF-8');
		for ($probonoseo_i = 0; $probonoseo_i < $probonoseo_length; $probonoseo_i++) {
			$probonoseo_char = mb_substr($probonoseo_content, $probonoseo_i, 1, 'UTF-8');
			$probonoseo_characters[$probonoseo_char] = true;
		}

		return array_keys($probonoseo_characters);
	}

	public function generate_subset_url($font_family, $characters) {
		$probonoseo_text = implode('', $characters);
		$probonoseo_text = rawurlencode($probonoseo_text);

		$probonoseo_base_url = 'https://fonts.googleapis.com/css2';
		$probonoseo_url = $probonoseo_base_url . '?family=' . rawurlencode($font_family) . '&text=' . $probonoseo_text . '&display=swap';

		return $probonoseo_url;
	}
}

ProbonoSEO_Speed_Fonts::get_instance();