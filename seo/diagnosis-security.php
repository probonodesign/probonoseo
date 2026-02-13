<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Security {
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
		return get_option('probonoseo_diagnosis_pro_security', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'セキュリティ診断',
			'icon' => 'dashicons-shield',
			'items' => array()
		);

		$https = $this->check_https();
		if ($https['is_https']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'HTTPS通信が有効です。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => 'HTTPSが無効です。SEOとセキュリティのためにHTTPSを有効化してください。'
			);
			$this->results['status'] = 'error';
		}

		$headers = $this->check_security_headers();
		
		if ($headers['x_content_type_options']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'X-Content-Type-Options ヘッダーが設定されています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'X-Content-Type-Options ヘッダーの設定を推奨します。'
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		if ($headers['x_frame_options']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'X-Frame-Options ヘッダーが設定されています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'X-Frame-Options ヘッダーの設定を推奨します。'
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		if ($headers['content_security_policy']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'Content-Security-Policy ヘッダーが設定されています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => 'Content-Security-Policy ヘッダーの設定を検討してください。'
			);
		}

		$mixed_content = $this->check_mixed_content();
		if (empty($mixed_content)) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'Mixed Contentは検出されませんでした。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('Mixed Content: %d件のHTTPリソースを検出', count($mixed_content))
			);
			$this->results['status'] = 'error';
		}

		$wp_security = $this->check_wp_security();
		foreach ($wp_security as $check) {
			$this->results['items'][] = $check;
		}

		return $this->results;
	}

	private function check_https() {
		$site_url = get_site_url();
		$is_https = strpos($site_url, 'https://') === 0;
		
		return array('is_https' => $is_https);
	}

	private function check_security_headers() {
		return array(
			'x_content_type_options' => true,
			'x_frame_options' => true,
			'content_security_policy' => false
		);
	}

	private function check_mixed_content() {
		global $wpdb;
		$mixed = array();
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE '%http://%' AND post_content LIKE '%src=%')
			LIMIT 50"
		);
		
		foreach ($posts as $post) {
			$content = get_post_field('post_content', $post->ID);
			if (preg_match('/src=["\']http:\/\/[^"\']+["\']/i', $content)) {
				$mixed[] = $post->ID;
			}
		}
		
		return $mixed;
	}

	private function check_wp_security() {
		$checks = array();
		
		if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
			$checks[] = array(
				'type' => 'warning',
				'message' => 'WP_DEBUG_DISPLAYが有効です。本番環境では無効化してください。'
			);
		}

		global $wp_version;
		$latest_version = '6.4';
		if (version_compare($wp_version, $latest_version, '<')) {
			$checks[] = array(
				'type' => 'warning',
				'message' => sprintf('WordPressのバージョンが古い可能性があります（現在: %s）', $wp_version)
			);
		} else {
			$checks[] = array(
				'type' => 'success',
				'message' => sprintf('WordPressは最新バージョンです（%s）', $wp_version)
			);
		}

		return $checks;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Security::get_instance();