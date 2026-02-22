<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Robots {
	private static $instance = null;
	private $results = array();

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_diagnosis_pro_robots', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'robots.txt診断',
			'icon' => 'dashicons-admin-generic',
			'items' => array()
		);

		$robots_url = home_url('/robots.txt');
		$response = wp_remote_get($robots_url, array('timeout' => 10));
		
		if (is_wp_error($response)) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'robots.txtにアクセスできませんでした。'
			);
			$this->results['status'] = 'warning';
			return $this->results;
		}

		$code = wp_remote_retrieve_response_code($response);
		if ($code !== 200) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => 'robots.txtが存在しません（WordPressのデフォルト設定が使用されます）。'
			);
			return $this->results;
		}

		$content = wp_remote_retrieve_body($response);
		
		$this->results['items'][] = array(
			'type' => 'success',
			'message' => 'robots.txtが存在します。'
		);

		$issues = $this->analyze_robots($content);
		
		foreach ($issues as $issue) {
			$this->results['items'][] = $issue;
			if ($issue['type'] === 'error') {
				$this->results['status'] = 'error';
			} elseif ($issue['type'] === 'warning' && $this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		if (empty($issues)) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'robots.txtに問題は検出されませんでした。'
			);
		}

		return $this->results;
	}

	private function analyze_robots($content) {
		$issues = array();
		$lines = explode("\n", $content);

		if (stripos($content, 'disallow: /') !== false && preg_match('/disallow:\s*\/\s*$/mi', $content)) {
			$issues[] = array(
				'type' => 'error',
				'message' => '「Disallow: /」が設定されています。サイト全体がクロールされなくなります！'
			);
		}

		$important_paths = array(
			'/wp-content/uploads/' => 'アップロード画像',
			'/wp-content/themes/' => 'テーマファイル',
			'/*.css' => 'CSSファイル',
			'/*.js' => 'JavaScriptファイル'
		);

		foreach ($important_paths as $path => $name) {
			if (stripos($content, 'disallow: ' . $path) !== false) {
				$issues[] = array(
					'type' => 'warning',
					'message' => sprintf('%sがブロックされています（%s）。Googleのレンダリングに影響する可能性があります。', $name, $path)
				);
			}
		}

		if (stripos($content, 'sitemap:') === false) {
			$issues[] = array(
				'type' => 'info',
				'message' => 'サイトマップURLの記載を推奨します（Sitemap: https://...）'
			);
		}

		foreach ($lines as $line_num => $line) {
			$line = trim($line);
			if (empty($line) || strpos($line, '#') === 0) {
				continue;
			}
			
			if (!preg_match('/^(user-agent|disallow|allow|sitemap|crawl-delay|host):/i', $line)) {
				$issues[] = array(
					'type' => 'warning',
					'message' => sprintf('行%d: 不正な構文の可能性があります「%s」', $line_num + 1, substr($line, 0, 50))
				);
			}
		}

		return $issues;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Robots::get_instance();