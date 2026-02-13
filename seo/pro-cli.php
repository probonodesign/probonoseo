<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
	return;
}

class ProbonoSEO_Pro_CLI {

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_cli', '1') === '1';
	}

	public function status($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		$settings = array(
			'タイトル最適化' => get_option('probonoseo_basic_title', '1'),
			'メタディスクリプション' => get_option('probonoseo_basic_metadesc', '1'),
			'canonical' => get_option('probonoseo_basic_canonical', '1'),
			'OGP' => get_option('probonoseo_basic_ogp', '1'),
			'Twitterカード' => get_option('probonoseo_basic_twitter', '1'),
			'schema' => get_option('probonoseo_basic_schema', '1'),
			'パンくず' => get_option('probonoseo_basic_breadcrumb', '1')
		);

		$items = array();

		foreach ($settings as $name => $value) {
			$items[] = array(
				'設定' => $name,
				'状態' => $value === '1' ? '有効' : '無効'
			);
		}

		WP_CLI\Utils\format_items('table', $items, array('設定', '状態'));
	}

	public function enable($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		if (empty($args[0])) {
			WP_CLI::error('設定キーを指定してください');
			return;
		}

		$key = 'probonoseo_' . $args[0];
		update_option($key, '1');
		WP_CLI::success($args[0] . ' を有効にしました');
	}

	public function disable($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		if (empty($args[0])) {
			WP_CLI::error('設定キーを指定してください');
			return;
		}

		$key = 'probonoseo_' . $args[0];
		update_option($key, '0');
		WP_CLI::success($args[0] . ' を無効にしました');
	}

	public function diagnosis($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		WP_CLI::log('サイト診断を実行中...');

		if (class_exists('ProbonoSEO_Diagnosis')) {
			ProbonoSEO_Diagnosis::run_diagnosis();
			$results = ProbonoSEO_Diagnosis::get_results();

			if (empty($results)) {
				WP_CLI::success('問題は検出されませんでした');
			} else {
				foreach ($results as $result) {
					if ($result['type'] === 'error') {
						WP_CLI::warning($result['message']);
					} else {
						WP_CLI::log($result['message']);
					}
				}
			}
		} else {
			WP_CLI::error('診断機能が利用できません');
		}
	}

	public function seo($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		$post_id = isset($args[0]) ? intval($args[0]) : 0;

		if (!$post_id) {
			WP_CLI::error('投稿IDを指定してください');
			return;
		}

		$post = get_post($post_id);

		if (!$post) {
			WP_CLI::error('投稿が見つかりません');
			return;
		}

		$seo_data = array(
			array('項目', '値'),
			array('タイトル', get_the_title($post_id)),
			array('SEOタイトル', get_post_meta($post_id, '_probonoseo_seo_title', true) ?: '(未設定)'),
			array('メタD', get_post_meta($post_id, '_probonoseo_meta_description', true) ?: '(未設定)'),
			array('キーワード', get_post_meta($post_id, '_probonoseo_focus_keyword', true) ?: '(未設定)'),
			array('canonical', get_post_meta($post_id, '_probonoseo_canonical', true) ?: '(未設定)'),
			array('robots', get_post_meta($post_id, '_probonoseo_robots', true) ?: '(未設定)')
		);

		WP_CLI::log('投稿ID: ' . $post_id . ' のSEO情報');
		WP_CLI::log('');

		foreach ($seo_data as $index => $row) {
			if ($index === 0) {
				continue;
			}
			WP_CLI::log($row[0] . ': ' . $row[1]);
		}
	}

	public function export($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'probonoseo_%'"
		);

		$export = array();

		foreach ($options as $option) {
			$export[$option->option_name] = $option->option_value;
		}

		$filename = isset($assoc_args['file']) ? $assoc_args['file'] : 'probonoseo-export.json';
		file_put_contents($filename, wp_json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

		WP_CLI::success('設定を ' . $filename . ' にエクスポートしました');
	}

	public function import($args, $assoc_args) {
		if (!$this->is_enabled()) {
			WP_CLI::error('WP-CLI機能が無効です');
			return;
		}

		$filename = isset($args[0]) ? $args[0] : '';

		if (!$filename || !file_exists($filename)) {
			WP_CLI::error('インポートファイルを指定してください');
			return;
		}

		$content = file_get_contents($filename);
		$data = json_decode($content, true);

		if (!$data) {
			WP_CLI::error('JSONファイルの解析に失敗しました');
			return;
		}

		$count = 0;

		foreach ($data as $key => $value) {
			if (strpos($key, 'probonoseo_') === 0) {
				update_option($key, $value);
				$count++;
			}
		}

		WP_CLI::success($count . ' 件の設定をインポートしました');
	}
}

if (defined('WP_CLI') && WP_CLI) {
	WP_CLI::add_command('probonoseo', 'ProbonoSEO_Pro_CLI');
}