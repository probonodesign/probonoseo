<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_PWA {

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

		add_action('wp_head', array($this, 'output_pwa_meta'));
		add_action('init', array($this, 'register_manifest_route'));
		add_action('init', array($this, 'register_sw_route'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_pwa', '0') === '1';
	}

	public function output_pwa_meta() {
		$theme_color = get_option('probonoseo_pro_pwa_theme_color', '#4a90e2');
		$app_name = get_option('probonoseo_pro_pwa_app_name', get_bloginfo('name'));

		echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . "\n";
		echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
		echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr($app_name) . '">' . "\n";
		echo '<link rel="manifest" href="' . esc_url(home_url('/probonoseo-manifest.json')) . '">' . "\n";

		$icon = get_option('probonoseo_pro_pwa_icon', '');

		if (!empty($icon)) {
			echo '<link rel="apple-touch-icon" href="' . esc_url($icon) . '">' . "\n";
		}
	}

	public function register_manifest_route() {
		add_rewrite_rule('^probonoseo-manifest\.json$', 'index.php?probonoseo_manifest=1', 'top');
		add_filter('query_vars', function($vars) {
			$vars[] = 'probonoseo_manifest';
			return $vars;
		});
		add_action('template_redirect', array($this, 'serve_manifest'));
	}

	public function register_sw_route() {
		add_rewrite_rule('^probonoseo-sw\.js$', 'index.php?probonoseo_sw=1', 'top');
		add_filter('query_vars', function($vars) {
			$vars[] = 'probonoseo_sw';
			return $vars;
		});
		add_action('template_redirect', array($this, 'serve_service_worker'));
	}

	public function serve_manifest() {
		if (!get_query_var('probonoseo_manifest')) {
			return;
		}

		header('Content-Type: application/json');

		$app_name = get_option('probonoseo_pro_pwa_app_name', get_bloginfo('name'));
		$short_name = get_option('probonoseo_pro_pwa_short_name', mb_substr($app_name, 0, 12));
		$theme_color = get_option('probonoseo_pro_pwa_theme_color', '#4a90e2');
		$bg_color = get_option('probonoseo_pro_pwa_bg_color', '#ffffff');
		$icon = get_option('probonoseo_pro_pwa_icon', '');

		$manifest = array(
			'name' => $app_name,
			'short_name' => $short_name,
			'start_url' => home_url('/'),
			'display' => 'standalone',
			'theme_color' => $theme_color,
			'background_color' => $bg_color,
			'icons' => array()
		);

		if (!empty($icon)) {
			$manifest['icons'][] = array(
				'src' => $icon,
				'sizes' => '192x192',
				'type' => 'image/png'
			);
			$manifest['icons'][] = array(
				'src' => $icon,
				'sizes' => '512x512',
				'type' => 'image/png'
			);
		}

		echo wp_json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		exit;
	}

	public function serve_service_worker() {
		if (!get_query_var('probonoseo_sw')) {
			return;
		}

		header('Content-Type: application/javascript');
		header('Service-Worker-Allowed: /');

		$cache_name = 'probonoseo-cache-v1';

		echo "const CACHE_NAME = '" . esc_js($cache_name) . "';\n";
		echo "const urlsToCache = [\n";
		echo "  '/'\n";
		echo "];\n\n";
		echo "self.addEventListener('install', function(event) {\n";
		echo "  event.waitUntil(\n";
		echo "    caches.open(CACHE_NAME)\n";
		echo "      .then(function(cache) {\n";
		echo "        return cache.addAll(urlsToCache);\n";
		echo "      })\n";
		echo "  );\n";
		echo "});\n\n";
		echo "self.addEventListener('fetch', function(event) {\n";
		echo "  event.respondWith(\n";
		echo "    caches.match(event.request)\n";
		echo "      .then(function(response) {\n";
		echo "        if (response) {\n";
		echo "          return response;\n";
		echo "        }\n";
		echo "        return fetch(event.request);\n";
		echo "      }\n";
		echo "    )\n";
		echo "  );\n";
		echo "});\n";

		exit;
	}
}

ProbonoSEO_Pro_PWA::get_instance();