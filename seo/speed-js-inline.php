<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_JS_Inline {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('script_loader_tag', array($this, 'inline_small_scripts'), 10, 3);
			add_action('wp_head', array($this, 'output_inline_js'), 5);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_js_inline', '0') === '1';
	}

	public function output_inline_js() {
		if (!$this->is_enabled()) {
			return;
		}

		$inline_js = $this->get_critical_js();

		if (empty($inline_js)) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JS output
        echo '<script id="probonoseo-critical-js">' . $inline_js . '</script>';
	}

	private function get_critical_js() {
		$js = '';

		$js .= "document.documentElement.className=document.documentElement.className.replace('no-js','js');";

		return $js;
	}

	public function inline_small_scripts($tag, $handle, $src) {
		if (!$this->is_enabled()) {
			return $tag;
		}

		if (is_admin()) {
			return $tag;
		}

		$inline_threshold = 2048;

		$excluded_handles = array('jquery', 'jquery-core', 'jquery-migrate', 'wp-embed');
		if (in_array($handle, $excluded_handles)) {
			return $tag;
		}

		if (strpos($src, site_url()) === false) {
			return $tag;
		}

		$file_path = str_replace(site_url('/'), ABSPATH, $src);
		$file_path = preg_replace('/\?.*$/', '', $file_path);

		if (!file_exists($file_path)) {
			return $tag;
		}

		$file_size = filesize($file_path);
		if ($file_size > $inline_threshold) {
			return $tag;
		}

		$js_content = file_get_contents($file_path);
		if (empty($js_content)) {
			return $tag;
		}

		return '<script id="' . esc_attr($handle) . '-inline-js">' . $js_content . '</script>';
	}
}

ProbonoSEO_Speed_JS_Inline::get_instance();