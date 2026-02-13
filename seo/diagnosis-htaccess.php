<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Htaccess {
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
		return get_option('probonoseo_diagnosis_pro_htaccess', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => '.htaccess診断',
			'icon' => 'dashicons-admin-tools',
			'items' => array()
		);

		$htaccess_path = ABSPATH . '.htaccess';
		
		if (!file_exists($htaccess_path)) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => '.htaccessファイルが存在しません（Nginxサーバーの可能性があります）。'
			);
			return $this->results;
		}

		if (!is_readable($htaccess_path)) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => '.htaccessファイルを読み取れません。'
			);
			$this->results['status'] = 'warning';
			return $this->results;
		}

		$content = file_get_contents($htaccess_path);
		
		$this->results['items'][] = array(
			'type' => 'success',
			'message' => '.htaccessファイルを検出しました。'
		);

		$analysis = $this->analyze_htaccess($content);
		
		foreach ($analysis as $item) {
			$this->results['items'][] = $item;
			if ($item['type'] === 'error') {
				$this->results['status'] = 'error';
			} elseif ($item['type'] === 'warning' && $this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		return $this->results;
	}

	private function analyze_htaccess($content) {
		$analysis = array();

		if (stripos($content, 'RewriteEngine On') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'URL書き換え（mod_rewrite）が有効です。'
			);
		}

		if (preg_match('/RewriteCond.*HTTPS.*off/i', $content) && stripos($content, 'RewriteRule') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'HTTPSリダイレクトが設定されています。'
			);
		} else {
			$analysis[] = array(
				'type' => 'info',
				'message' => 'HTTPSリダイレクト設定の追加を検討してください。'
			);
		}

		if (stripos($content, 'Header set X-Content-Type-Options') !== false ||
			stripos($content, 'Header always set X-Content-Type-Options') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'X-Content-Type-Optionsヘッダーが設定されています。'
			);
		}

		if (stripos($content, 'Header set X-Frame-Options') !== false ||
			stripos($content, 'Header always set X-Frame-Options') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'X-Frame-Optionsヘッダーが設定されています。'
			);
		}

		if (stripos($content, 'ExpiresByType') !== false || stripos($content, 'ExpiresDefault') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'ブラウザキャッシュ設定（Expires）が有効です。'
			);
		} else {
			$analysis[] = array(
				'type' => 'info',
				'message' => 'ブラウザキャッシュ設定の追加でパフォーマンスを改善できます。'
			);
		}

		if (stripos($content, 'mod_deflate') !== false || stripos($content, 'AddOutputFilterByType DEFLATE') !== false) {
			$analysis[] = array(
				'type' => 'success',
				'message' => 'Gzip圧縮が有効です。'
			);
		} else {
			$analysis[] = array(
				'type' => 'info',
				'message' => 'Gzip圧縮の有効化でページサイズを削減できます。'
			);
		}

		$redirect_count = preg_match_all('/Redirect(Match)?\s+(301|302|permanent|temp)/i', $content, $matches);
		$rewrite_count = preg_match_all('/RewriteRule.*\[.*R=(301|302)/i', $content, $matches2);
		$total_redirects = $redirect_count + $rewrite_count;
		
		if ($total_redirects > 0) {
			$analysis[] = array(
				'type' => 'info',
				'message' => sprintf('リダイレクトルール: %d件設定されています。', $total_redirects)
			);
		}

		return $analysis;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Htaccess::get_instance();