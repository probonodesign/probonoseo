<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Manage_Advanced {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('wp_ajax_probonoseo_backup_settings', array($this, 'handle_backup'));
		add_action('wp_ajax_probonoseo_restore_settings', array($this, 'handle_restore'));
		add_action('wp_ajax_probonoseo_test_notification', array($this, 'handle_test_notification'));
	}

	public function handle_backup() {
		check_ajax_referer('probonoseo_manage_advanced', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}
		
		$probonoseo_backup = $this->create_backup();
		
		if ($probonoseo_backup) {
			wp_send_json_success(array(
				'message' => 'バックアップを作成しました',
				'backup_id' => $probonoseo_backup['id'],
				'date' => $probonoseo_backup['date']
			));
		} else {
			wp_send_json_error(array('message' => 'バックアップの作成に失敗しました'));
		}
	}

	public function create_backup() {
		$probonoseo_keys = $this->get_all_option_keys();
		$probonoseo_settings = array();
		
		foreach ($probonoseo_keys as $probonoseo_key) {
			$probonoseo_settings[$probonoseo_key] = get_option($probonoseo_key, '');
		}
		
		$probonoseo_backup = array(
			'id' => uniqid('backup_'),
			'date' => current_time('mysql'),
			'version' => PROBONOSEO_VERSION,
			'settings' => $probonoseo_settings
		);
		
		$probonoseo_backups = get_option('probonoseo_backups', array());
		array_unshift($probonoseo_backups, $probonoseo_backup);
		
		$probonoseo_backups = array_slice($probonoseo_backups, 0, 10);
		
		update_option('probonoseo_backups', $probonoseo_backups);
		
		return $probonoseo_backup;
	}

	public function handle_restore() {
		check_ajax_referer('probonoseo_manage_advanced', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}
		
		$probonoseo_backup_id = isset($_POST['backup_id']) ? sanitize_text_field(wp_unslash($_POST['backup_id'])) : '';
		
		if (empty($probonoseo_backup_id)) {
			wp_send_json_error(array('message' => 'バックアップIDが指定されていません'));
			return;
		}
		
		$probonoseo_result = $this->restore_backup($probonoseo_backup_id);
		
		if ($probonoseo_result) {
			wp_send_json_success(array('message' => '設定を復元しました'));
		} else {
			wp_send_json_error(array('message' => 'バックアップが見つかりません'));
		}
	}

	public function restore_backup($backup_id) {
		$probonoseo_backups = get_option('probonoseo_backups', array());
		
		foreach ($probonoseo_backups as $probonoseo_backup) {
			if ($probonoseo_backup['id'] === $backup_id) {
				foreach ($probonoseo_backup['settings'] as $probonoseo_key => $probonoseo_value) {
					update_option($probonoseo_key, $probonoseo_value);
				}
				return true;
			}
		}
		
		return false;
	}

	public function get_backups() {
		return get_option('probonoseo_backups', array());
	}

	public function delete_backup($backup_id) {
		$probonoseo_backups = get_option('probonoseo_backups', array());
		
		$probonoseo_backups = array_filter($probonoseo_backups, function($backup) use ($backup_id) {
			return $backup['id'] !== $backup_id;
		});
		
		update_option('probonoseo_backups', array_values($probonoseo_backups));
		
		return true;
	}

	public function get_statistics() {
		$probonoseo_stats = array(
			'total_posts' => 0,
			'optimized_posts' => 0,
			'ai_usage_count' => 0,
			'diagnosis_count' => 0,
			'last_diagnosis' => ''
		);
		
		$probonoseo_posts = wp_count_posts('post');
		$probonoseo_stats['total_posts'] = isset($probonoseo_posts->publish) ? $probonoseo_posts->publish : 0;
		
		global $wpdb;
		
		$probonoseo_cache_key = 'probonoseo_optimized_count';
		$probonoseo_optimized = wp_cache_get($probonoseo_cache_key, 'probonoseo');
		
		if ($probonoseo_optimized === false) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$probonoseo_optimized = $wpdb->get_var(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_probonoseo_%'"
			);
			wp_cache_set($probonoseo_cache_key, $probonoseo_optimized, 'probonoseo', 3600);
		}
		$probonoseo_stats['optimized_posts'] = $probonoseo_optimized ? $probonoseo_optimized : 0;
		
		$probonoseo_stats['ai_usage_count'] = get_option('probonoseo_ai_usage_count', 0);
		$probonoseo_stats['diagnosis_count'] = get_option('probonoseo_diagnosis_count', 0);
		$probonoseo_stats['last_diagnosis'] = get_option('probonoseo_last_diagnosis_date', '未実行');
		
		return $probonoseo_stats;
	}

	public function send_notification($type, $message) {
		$probonoseo_email_enabled = get_option('probonoseo_notify_email_enabled', '0') === '1';
		$probonoseo_slack_enabled = get_option('probonoseo_notify_slack_enabled', '0') === '1';
		
		$probonoseo_results = array('email' => false, 'slack' => false);
		
		if ($probonoseo_email_enabled) {
			$probonoseo_email = get_option('probonoseo_notify_email', get_option('admin_email'));
			$probonoseo_subject = sprintf('[ProbonoSEO] %s通知', $type);
			$probonoseo_results['email'] = wp_mail($probonoseo_email, $probonoseo_subject, $message);
		}
		
		if ($probonoseo_slack_enabled) {
			$probonoseo_webhook_url = get_option('probonoseo_notify_slack_webhook', '');
			if (!empty($probonoseo_webhook_url)) {
				$probonoseo_results['slack'] = $this->send_slack_notification($probonoseo_webhook_url, $message);
			}
		}
		
		return $probonoseo_results;
	}

	private function send_slack_notification($webhook_url, $message) {
		$probonoseo_payload = wp_json_encode(array(
			'text' => $message,
			'username' => 'ProbonoSEO',
			'icon_emoji' => ':chart_with_upwards_trend:'
		));
		
		$probonoseo_response = wp_remote_post($webhook_url, array(
			'body' => $probonoseo_payload,
			'headers' => array('Content-Type' => 'application/json'),
			'timeout' => 10
		));
		
		return !is_wp_error($probonoseo_response) && wp_remote_retrieve_response_code($probonoseo_response) === 200;
	}

	public function handle_test_notification() {
		check_ajax_referer('probonoseo_manage_advanced', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}
		
		$probonoseo_type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'email';
		$probonoseo_message = 'ProbonoSEOからのテスト通知です。正常に設定されています。';
		
		if ($probonoseo_type === 'email') {
			$probonoseo_email = get_option('probonoseo_notify_email', get_option('admin_email'));
			$probonoseo_result = wp_mail($probonoseo_email, '[ProbonoSEO] テスト通知', $probonoseo_message);
		} else {
			$probonoseo_webhook_url = get_option('probonoseo_notify_slack_webhook', '');
			$probonoseo_result = $this->send_slack_notification($probonoseo_webhook_url, $probonoseo_message);
		}
		
		if ($probonoseo_result) {
			wp_send_json_success(array('message' => 'テスト通知を送信しました'));
		} else {
			wp_send_json_error(array('message' => '通知の送信に失敗しました'));
		}
	}

	public function check_user_capability($capability = 'probonoseo_full') {
		$probonoseo_access_control = get_option('probonoseo_access_control', 'manage_options');
		
		if ($probonoseo_access_control === 'manage_options') {
			return current_user_can('manage_options');
		}
		
		$probonoseo_allowed_roles = get_option('probonoseo_allowed_roles', array('administrator'));
		$probonoseo_user = wp_get_current_user();
		
		foreach ($probonoseo_allowed_roles as $probonoseo_role) {
			if (in_array($probonoseo_role, (array) $probonoseo_user->roles, true)) {
				return true;
			}
		}
		
		return false;
	}

	public function is_debug_mode() {
		return get_option('probonoseo_debug_mode', '0') === '1';
	}

	public function log($message, $type = 'info') {
		if (!$this->is_debug_mode()) {
			return;
		}
		
		$probonoseo_log_file = WP_CONTENT_DIR . '/probonoseo-debug.log';
		$probonoseo_timestamp = current_time('mysql');
		$probonoseo_log_entry = sprintf("[%s] [%s] %s\n", $probonoseo_timestamp, strtoupper($type), $message);
		
		$probonoseo_existing_content = '';
		if (file_exists($probonoseo_log_file)) {
			$probonoseo_existing_content = file_get_contents($probonoseo_log_file);
		}
		file_put_contents($probonoseo_log_file, $probonoseo_existing_content . $probonoseo_log_entry);
	}

	public function get_debug_log($lines = 100) {
		$probonoseo_log_file = WP_CONTENT_DIR . '/probonoseo-debug.log';
		
		if (!file_exists($probonoseo_log_file)) {
			return array();
		}
		
		$probonoseo_content = file_get_contents($probonoseo_log_file);
		$probonoseo_all_lines = explode("\n", $probonoseo_content);
		
		return array_slice($probonoseo_all_lines, -$lines);
	}

	public function clear_debug_log() {
		$probonoseo_log_file = WP_CONTENT_DIR . '/probonoseo-debug.log';
		
		if (file_exists($probonoseo_log_file)) {
			wp_delete_file($probonoseo_log_file);
		}
		
		return true;
	}

	private function get_all_option_keys() {
		$probonoseo_free_keys = array(
			'probonoseo_basic_title', 'probonoseo_title_separator', 'probonoseo_title_sitename',
			'probonoseo_title_h1_check', 'probonoseo_title_category', 'probonoseo_title_duplicate',
			'probonoseo_title_symbols', 'probonoseo_basic_metadesc', 'probonoseo_meta_extraction',
			'probonoseo_meta_keywords', 'probonoseo_meta_summary', 'probonoseo_meta_forbidden',
			'probonoseo_meta_length', 'probonoseo_meta_duplicate', 'probonoseo_basic_canonical',
			'probonoseo_canonical_auto', 'probonoseo_canonical_slash', 'probonoseo_canonical_merge',
			'probonoseo_canonical_params', 'probonoseo_basic_ogp', 'probonoseo_ogp_title',
			'probonoseo_ogp_desc', 'probonoseo_ogp_image_auto', 'probonoseo_ogp_image_fixed',
			'probonoseo_ogp_facebook', 'probonoseo_ogp_line', 'probonoseo_ogp_thumbnail',
			'probonoseo_ogp_size_detect', 'probonoseo_ogp_alt', 'probonoseo_ogp_japanese_url',
			'probonoseo_basic_twitter', 'probonoseo_basic_schema', 'probonoseo_basic_breadcrumb',
			'probonoseo_internal_prev_next', 'probonoseo_internal_category', 'probonoseo_internal_child_pages',
			'probonoseo_internal_related', 'probonoseo_internal_tag_logic', 'probonoseo_internal_nofollow',
			'probonoseo_internal_category_format', 'probonoseo_speed_lazy_images', 'probonoseo_speed_lazy_iframes',
			'probonoseo_speed_minify_css', 'probonoseo_speed_minify_js', 'probonoseo_speed_optimize_wp_scripts',
			'probonoseo_article_heading_check', 'probonoseo_article_alt_check', 'probonoseo_article_image_count',
			'probonoseo_article_word_count', 'probonoseo_article_category_match', 'probonoseo_article_tag_duplicate',
			'probonoseo_diagnosis_title_duplicate', 'probonoseo_diagnosis_meta_duplicate', 'probonoseo_diagnosis_speed',
			'probonoseo_meta_cleanup', 'probonoseo_gsc_verify'
		);
		
		$probonoseo_pro_keys = array(
			'probonoseo_pro_title_ai', 'probonoseo_pro_heading_ai', 'probonoseo_pro_outline_ai',
			'probonoseo_pro_body_ai', 'probonoseo_pro_summary_ai', 'probonoseo_pro_faq_ai',
			'probonoseo_diagnosis_pro_index', 'probonoseo_diagnosis_pro_crawl', 'probonoseo_diagnosis_pro_mobile',
			'probonoseo_diagnosis_pro_vitals', 'probonoseo_diagnosis_pro_security', 'probonoseo_diagnosis_pro_ssl',
			'probonoseo_diagnosis_pro_sitemap', 'probonoseo_diagnosis_pro_robots', 'probonoseo_diagnosis_pro_htaccess',
			'probonoseo_diagnosis_pro_performance', 'probonoseo_diagnosis_pro_total', 'probonoseo_diagnosis_pro_pdf'
		);
		
		return array_merge($probonoseo_free_keys, $probonoseo_pro_keys);
	}
}

ProbonoSEO_Manage_Advanced::get_instance();