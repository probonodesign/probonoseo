<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Multisite {

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

		if (!is_multisite()) {
			return;
		}

		add_action('network_admin_menu', array($this, 'add_network_menu'));
		add_action('network_admin_edit_probonoseo_network_save', array($this, 'save_network_settings'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_multisite', '1') === '1';
	}

	public function add_network_menu() {
		add_submenu_page(
			'settings.php',
			'ProbonoSEO ネットワーク設定',
			'ProbonoSEO',
			'manage_network_options',
			'probonoseo-network',
			array($this, 'render_network_page')
		);
	}

	public function render_network_page() {
		$global_settings = get_site_option('probonoseo_network_settings', array());

		echo '<div class="wrap">';
		echo '<h1>ProbonoSEO ネットワーク設定</h1>';

		echo '<form method="post" action="' . esc_url(network_admin_url('edit.php?action=probonoseo_network_save')) . '">';
		wp_nonce_field('probonoseo_network_settings', 'probonoseo_network_nonce');

		echo '<table class="form-table">';

		echo '<tr>';
		echo '<th><label for="probonoseo_network_inherit">設定の継承</label></th>';
		echo '<td>';
		echo '<select id="probonoseo_network_inherit" name="probonoseo_network_inherit">';
		$inherit = isset($global_settings['inherit']) ? $global_settings['inherit'] : 'none';
		echo '<option value="none"' . selected($inherit, 'none', false) . '>サイトごとに独立</option>';
		echo '<option value="all"' . selected($inherit, 'all', false) . '>全サイトにネットワーク設定を適用</option>';
		echo '<option value="default"' . selected($inherit, 'default', false) . '>新規サイトのデフォルトのみ</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th><label for="probonoseo_network_license">ネットワークライセンス</label></th>';
		echo '<td>';
		$license = isset($global_settings['license']) ? $global_settings['license'] : '';
		echo '<input type="text" id="probonoseo_network_license" name="probonoseo_network_license" value="' . esc_attr($license) . '" class="regular-text">';
		echo '<p class="description">ネットワーク全体で使用するライセンスキー</p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';

		submit_button('設定を保存');

		echo '</form>';
		echo '</div>';
	}

	public function save_network_settings() {
		if (!current_user_can('manage_network_options')) {
			wp_die('権限がありません');
		}

		if (!isset($_POST['probonoseo_network_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_network_nonce'])), 'probonoseo_network_settings')) {
			wp_die('認証に失敗しました');
		}

		$settings = array(
			'inherit' => isset($_POST['probonoseo_network_inherit']) ? sanitize_text_field(wp_unslash($_POST['probonoseo_network_inherit'])) : 'none',
			'license' => isset($_POST['probonoseo_network_license']) ? sanitize_text_field(wp_unslash($_POST['probonoseo_network_license'])) : ''
		);

		update_site_option('probonoseo_network_settings', $settings);

		wp_safe_redirect(add_query_arg(array(
			'page' => 'probonoseo-network',
			'updated' => 'true'
		), network_admin_url('settings.php')));
		exit;
	}
}

ProbonoSEO_Pro_Multisite::get_instance();