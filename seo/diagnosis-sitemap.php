<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Sitemap {
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
		return get_option('probonoseo_diagnosis_pro_sitemap', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'サイトマップ診断',
			'icon' => 'dashicons-networking',
			'items' => array()
		);

		$sitemap_url = home_url('/wp-sitemap.xml');
		$sitemap_exists = $this->check_sitemap_exists($sitemap_url);
		
		if (!$sitemap_exists) {
			$sitemap_url = home_url('/sitemap.xml');
			$sitemap_exists = $this->check_sitemap_exists($sitemap_url);
		}

		if (!$sitemap_exists) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'サイトマップが見つかりませんでした。XMLサイトマップの生成を推奨します。'
			);
			$this->results['status'] = 'warning';
			return $this->results;
		}

		$this->results['items'][] = array(
			'type' => 'success',
			'message' => sprintf('サイトマップを検出: %s', $sitemap_url)
		);

		$sitemap_content = $this->get_sitemap_content($sitemap_url);
		
		if ($sitemap_content) {
			$url_count = $this->count_urls($sitemap_content);
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => sprintf('サイトマップ内のURL数: %d', $url_count)
			);

			if ($url_count > 50000) {
				$this->results['items'][] = array(
					'type' => 'warning',
					'message' => 'サイトマップのURL数が50,000を超えています。分割を検討してください。'
				);
				if ($this->results['status'] === 'success') {
					$this->results['status'] = 'warning';
				}
			}

			$duplicates = $this->check_duplicates($sitemap_content);
			if ($duplicates > 0) {
				$this->results['items'][] = array(
					'type' => 'warning',
					'message' => sprintf('重複URL: %d件検出', $duplicates)
				);
				if ($this->results['status'] === 'success') {
					$this->results['status'] = 'warning';
				}
			} else {
				$this->results['items'][] = array(
					'type' => 'success',
					'message' => 'URLの重複はありません。'
				);
			}

			$noindex_in_sitemap = $this->check_noindex_in_sitemap($sitemap_content);
			if ($noindex_in_sitemap > 0) {
				$this->results['items'][] = array(
					'type' => 'warning',
					'message' => sprintf('noindex設定のページがサイトマップに含まれています: %d件', $noindex_in_sitemap)
				);
				if ($this->results['status'] === 'success') {
					$this->results['status'] = 'warning';
				}
			}
		}

		$robots_sitemap = $this->check_robots_sitemap();
		if ($robots_sitemap) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'robots.txtにサイトマップURLが記載されています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => 'robots.txtにサイトマップURLを追加することを推奨します。'
			);
		}

		return $this->results;
	}

	private function check_sitemap_exists($url) {
		$response = wp_remote_head($url, array('timeout' => 10));
		if (is_wp_error($response)) {
			return false;
		}
		$code = wp_remote_retrieve_response_code($response);
		return $code === 200;
	}

	private function get_sitemap_content($url) {
		$response = wp_remote_get($url, array('timeout' => 30));
		if (is_wp_error($response)) {
			return false;
		}
		return wp_remote_retrieve_body($response);
	}

	private function count_urls($content) {
		return substr_count($content, '<loc>');
	}

	private function check_duplicates($content) {
		preg_match_all('/<loc>([^<]+)<\/loc>/i', $content, $matches);
		if (empty($matches[1])) {
			return 0;
		}
		$urls = $matches[1];
		$unique = array_unique($urls);
		return count($urls) - count($unique);
	}

	private function check_noindex_in_sitemap($content) {
		return 0;
	}

	private function check_robots_sitemap() {
		$robots_url = home_url('/robots.txt');
		$response = wp_remote_get($robots_url, array('timeout' => 10));
		if (is_wp_error($response)) {
			return false;
		}
		$body = wp_remote_retrieve_body($response);
		return stripos($body, 'sitemap:') !== false;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Sitemap::get_instance();