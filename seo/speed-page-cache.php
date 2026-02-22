<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Page_Cache {

	private static $instance = null;
	private $cache_dir = '';
	private $is_cacheable = true;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->cache_dir = WP_CONTENT_DIR . '/cache/probonoseo/';

		if ($this->is_enabled()) {
			add_action('init', array($this, 'init_cache'), 0);
			add_action('shutdown', array($this, 'save_cache'), 999);
			add_action('save_post', array($this, 'clear_post_cache'));
			add_action('comment_post', array($this, 'clear_post_cache_on_comment'));
			add_action('switch_theme', array($this, 'clear_all_cache'));
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_page_cache', '0') === '1';
	}

	public function init_cache() {
		if (!$this->is_enabled()) {
			return;
		}

		if ($this->should_skip_cache()) {
			$this->is_cacheable = false;
			return;
		}

		$cached_content = $this->get_cached_page();
		if ($cached_content !== false) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Cached HTML page output
			echo $cached_content;
			echo '<!-- Cached by ProbonoSEO -->';
			exit;
		}

		ob_start();
	}

	private function should_skip_cache() {
		if (is_admin()) {
			return true;
		}

		if (is_user_logged_in()) {
			return true;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Early return for non-GET
		if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Cache check, no form processing
		if (!empty($_GET)) {
			return true;
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			return true;
		}

		if (defined('DOING_CRON') && DOING_CRON) {
			return true;
		}

		$exclude_patterns = get_option('probonoseo_speed_pro_cache_exclude', '');
		if (!empty($exclude_patterns)) {
			$patterns = array_filter(array_map('trim', explode("\n", $exclude_patterns)));
			$request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

			foreach ($patterns as $pattern) {
				if (strpos($request_uri, $pattern) !== false) {
					return true;
				}
			}
		}

		return false;
	}

	private function get_cache_file_path() {
		$url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
		$cache_key = md5($host . $url);
		return $this->cache_dir . $cache_key . '.html';
	}

	private function get_cached_page() {
		$cache_file = $this->get_cache_file_path();

		if (!file_exists($cache_file)) {
			return false;
		}

		$expiry = (int) get_option('probonoseo_speed_pro_cache_expiry', 3600);
		$file_time = filemtime($cache_file);

		if (time() - $file_time > $expiry) {
			wp_delete_file($cache_file);
			return false;
		}

		return file_get_contents($cache_file);
	}

	public function save_cache() {
		if (!$this->is_enabled() || !$this->is_cacheable) {
			return;
		}

		if (!ob_get_level()) {
			return;
		}

		$content = ob_get_contents();

		if (empty($content)) {
			return;
		}

		if (http_response_code() !== 200) {
			return;
		}

		if (!is_dir($this->cache_dir)) {
			wp_mkdir_p($this->cache_dir);
		}

		$cache_file = $this->get_cache_file_path();
		file_put_contents($cache_file, $content);
	}

	public function clear_post_cache($post_id) {
		$permalink = get_permalink($post_id);
		$url = str_replace(home_url(), '', $permalink);
		$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
		$cache_key = md5($host . $url);
		$cache_file = $this->cache_dir . $cache_key . '.html';

		if (file_exists($cache_file)) {
			wp_delete_file($cache_file);
		}

		$home_cache = $this->cache_dir . md5($host . '/') . '.html';
		if (file_exists($home_cache)) {
			wp_delete_file($home_cache);
		}
	}

	public function clear_post_cache_on_comment($comment_id) {
		$comment = get_comment($comment_id);
		if ($comment) {
			$this->clear_post_cache($comment->comment_post_ID);
		}
	}

	public function clear_all_cache() {
		if (!is_dir($this->cache_dir)) {
			return;
		}

		$files = glob($this->cache_dir . '*.html');
		if ($files) {
			foreach ($files as $file) {
				wp_delete_file($file);
			}
		}
	}

	public function get_cache_stats() {
		$stats = array(
			'total_files' => 0,
			'total_size' => 0,
		);

		if (!is_dir($this->cache_dir)) {
			return $stats;
		}

		$files = glob($this->cache_dir . '*.html');
		if ($files) {
			$stats['total_files'] = count($files);
			foreach ($files as $file) {
				$stats['total_size'] += filesize($file);
			}
		}

		return $stats;
	}
}

ProbonoSEO_Speed_Page_Cache::get_instance();