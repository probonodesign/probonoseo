<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Index_Status {
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
		return get_option('probonoseo_diagnosis_pro_index', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'インデックスステータス',
			'icon' => 'dashicons-visibility',
			'items' => array()
		);

		$gsc_connected = get_option('probonoseo_gsc_access_token', '');
		
		if (empty($gsc_connected)) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'Google Search Consoleが連携されていません。Pro専用強化タブでGSCを連携してください。'
			);
			$this->results['status'] = 'warning';
			return $this->results;
		}

		$indexed_pages = $this->get_indexed_pages();
		$total_pages = $this->get_total_pages();
		
		$this->results['items'][] = array(
			'type' => 'info',
			'message' => sprintf('公開ページ数: %d', $total_pages)
		);

		if ($indexed_pages !== false) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => sprintf('インデックス済みページ数: %d', $indexed_pages)
			);
			
			$index_rate = $total_pages > 0 ? round(($indexed_pages / $total_pages) * 100, 1) : 0;
			
			if ($index_rate >= 90) {
				$this->results['items'][] = array(
					'type' => 'success',
					'message' => sprintf('インデックス率: %s%% - 良好です', $index_rate)
				);
			} elseif ($index_rate >= 70) {
				$this->results['items'][] = array(
					'type' => 'warning',
					'message' => sprintf('インデックス率: %s%% - 一部のページがインデックスされていません', $index_rate)
				);
				$this->results['status'] = 'warning';
			} else {
				$this->results['items'][] = array(
					'type' => 'error',
					'message' => sprintf('インデックス率: %s%% - 多くのページがインデックスされていません', $index_rate)
				);
				$this->results['status'] = 'error';
			}
		} else {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => 'GSC APIからインデックス数を取得できませんでした。手動でSearch Consoleを確認してください。'
			);
		}

		$noindex_pages = $this->check_noindex_pages();
		if (!empty($noindex_pages)) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => sprintf('noindex設定されているページ: %d件', count($noindex_pages))
			);
		}

		return $this->results;
	}

	private function get_indexed_pages() {
		$access_token = get_option('probonoseo_gsc_access_token', '');
		if (empty($access_token)) {
			return false;
		}

		$site_url = home_url();
		
		return false;
	}

	private function get_total_pages() {
		$count = 0;
		
		$posts = wp_count_posts('post');
		$count += isset($posts->publish) ? $posts->publish : 0;
		
		$pages = wp_count_posts('page');
		$count += isset($pages->publish) ? $pages->publish : 0;
		
		$custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'names');
		foreach ($custom_post_types as $cpt) {
			$cpt_count = wp_count_posts($cpt);
			$count += isset($cpt_count->publish) ? $cpt_count->publish : 0;
		}
		
		return $count;
	}

	private function check_noindex_pages() {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$noindex_pages = $wpdb->get_results(
			"SELECT post_id FROM {$wpdb->postmeta} 
			WHERE meta_key = '_probonoseo_noindex' AND meta_value = '1'
			LIMIT 100"
		);
		
		return $noindex_pages;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Index_Status::get_instance();