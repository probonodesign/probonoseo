<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Hints {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_action('wp_head', array($this, 'output_preload_hints'), 2);
			add_filter('wp_resource_hints', array($this, 'add_resource_hints'), 10, 2);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_hints', '1') === '1';
	}

	public function output_preload_hints() {
		if (!$this->is_enabled()) {
			return;
		}

		$this->preload_lcp_image();
		$this->preload_critical_fonts();
	}

	private function preload_lcp_image() {
		if (is_singular()) {
			$post_id = get_the_ID();
			$thumbnail_id = get_post_thumbnail_id($post_id);

			if ($thumbnail_id) {
				$image_src = wp_get_attachment_image_src($thumbnail_id, 'large');
				if ($image_src) {
					echo '<link rel="preload" as="image" href="' . esc_url($image_src[0]) . '">' . "\n";
				}
			}
		}
	}

	private function preload_critical_fonts() {
		$fonts = array();

		if (!empty($fonts)) {
			foreach ($fonts as $font) {
				echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url($font) . '" crossorigin>' . "\n";
			}
		}
	}

	public function add_resource_hints($urls, $relation_type) {
		if (!$this->is_enabled()) {
			return $urls;
		}

		if ($relation_type === 'prefetch') {
			if (is_singular() && !is_front_page()) {
				$urls[] = home_url('/');
			}

			$next_post = get_next_post();
			if ($next_post) {
				$urls[] = get_permalink($next_post);
			}
		}

		return $urls;
	}

	public function get_preload_resources() {
		$resources = array();

		$theme_style = get_stylesheet_uri();
		$resources[] = array(
			'href' => $theme_style,
			'as' => 'style',
		);

		return $resources;
	}
}

ProbonoSEO_Speed_Hints::get_instance();