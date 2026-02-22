<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_CSS_Inline {

	private static $instance = null;
	private $critical_css = '';

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_action('wp_head', array($this, 'output_critical_css'), 1);
			add_filter('style_loader_tag', array($this, 'defer_non_critical_css'), 10, 4);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_css_inline', '0') === '1';
	}

	public function output_critical_css() {
		if (!$this->is_enabled()) {
			return;
		}

		$critical_css = $this->get_critical_css();

		if (empty($critical_css)) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS output
        echo '<style id="probonoseo-critical-css">' . $this->minify_css($critical_css) . '</style>';
	}

	private function get_critical_css() {
		$cache_key = 'probonoseo_critical_css_' . md5(get_template());
		$cached = get_transient($cache_key);

		if ($cached !== false) {
			return $cached;
		}

		$critical_css = $this->extract_critical_css();
		set_transient($cache_key, $critical_css, DAY_IN_SECONDS);

		return $critical_css;
	}

	private function extract_critical_css() {
		$css = '';

		$theme_css_path = get_template_directory() . '/style.css';
		if (file_exists($theme_css_path)) {
			$theme_css = file_get_contents($theme_css_path);
			$css .= $this->extract_above_fold_css($theme_css);
		}

		return $css;
	}

	private function extract_above_fold_css($css) {
		$critical_selectors = array(
			'html', 'body', 'header', 'nav', '.header', '.navigation', '.nav',
			'.site-header', '.main-navigation', '.menu', 'h1', 'h2', 'p',
			'.container', '.wrapper', '.content', 'main', 'article',
			'.hero', '.banner', '.jumbotron', 'img', 'a', 'button',
		);

		$critical_css = '';

		foreach ($critical_selectors as $selector) {
			$pattern = '/(?:^|\})\s*(' . preg_quote($selector, '/') . '[^{]*)\{([^}]+)\}/i';
			if (preg_match_all($pattern, $css, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					$critical_css .= $match[1] . '{' . $match[2] . '}';
				}
			}
		}

		return $critical_css;
	}

	private function minify_css($css) {
		$css = preg_replace('/\/\*.*?\*\//s', '', $css);
		$css = preg_replace('/\s+/', ' ', $css);
		$css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
		$css = preg_replace('/;}/', '}', $css);
		return trim($css);
	}

	public function defer_non_critical_css($tag, $handle, $href, $media) {
		if (!$this->is_enabled()) {
			return $tag;
		}

		if (is_admin()) {
			return $tag;
		}

		$critical_handles = array('admin-bar');
		if (in_array($handle, $critical_handles)) {
			return $tag;
		}

		$tag = str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
		$tag = str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $tag);

		return $tag;
	}
}

ProbonoSEO_Speed_CSS_Inline::get_instance();