<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_GSC {

	private static $instance = null;
	private $api_base = 'https://www.googleapis.com/webmasters/v3';

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

		add_action('wp_ajax_probonoseo_gsc_connect', array($this, 'ajax_connect'));
		add_action('wp_ajax_probonoseo_gsc_disconnect', array($this, 'ajax_disconnect'));
		add_action('wp_ajax_probonoseo_gsc_fetch_data', array($this, 'ajax_fetch_data'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_gsc', '1') === '1';
	}

	public function is_connected() {
		$probonoseo_access_token = get_option('probonoseo_gsc_access_token', '');
		$probonoseo_refresh_token = get_option('probonoseo_gsc_refresh_token', '');

		return !empty($probonoseo_access_token) && !empty($probonoseo_refresh_token);
	}

	public function get_auth_url() {
		$probonoseo_client_id = get_option('probonoseo_gsc_client_id', '');
		$probonoseo_redirect_uri = admin_url('admin.php?page=probonoseo&tab=pro&gsc_callback=1');

		if (empty($probonoseo_client_id)) {
			return '';
		}

		$probonoseo_params = array(
			'client_id' => $probonoseo_client_id,
			'redirect_uri' => $probonoseo_redirect_uri,
			'response_type' => 'code',
			'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
			'access_type' => 'offline',
			'prompt' => 'consent'
		);

		return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($probonoseo_params);
	}

	public function exchange_code_for_token($code) {
		$probonoseo_client_id = get_option('probonoseo_gsc_client_id', '');
		$probonoseo_client_secret = get_option('probonoseo_gsc_client_secret', '');
		$probonoseo_redirect_uri = admin_url('admin.php?page=probonoseo&tab=pro&gsc_callback=1');

		if (empty($probonoseo_client_id) || empty($probonoseo_client_secret)) {
			return false;
		}

		$probonoseo_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
			'body' => array(
				'code' => $code,
				'client_id' => $probonoseo_client_id,
				'client_secret' => $probonoseo_client_secret,
				'redirect_uri' => $probonoseo_redirect_uri,
				'grant_type' => 'authorization_code'
			)
		));

		if (is_wp_error($probonoseo_response)) {
			return false;
		}

		$probonoseo_body = json_decode(wp_remote_retrieve_body($probonoseo_response), true);

		if (isset($probonoseo_body['access_token'])) {
			update_option('probonoseo_gsc_access_token', $probonoseo_body['access_token']);

			if (isset($probonoseo_body['refresh_token'])) {
				update_option('probonoseo_gsc_refresh_token', $probonoseo_body['refresh_token']);
			}

			if (isset($probonoseo_body['expires_in'])) {
				update_option('probonoseo_gsc_token_expires', time() + $probonoseo_body['expires_in']);
			}

			return true;
		}

		return false;
	}

	public function refresh_access_token() {
		$probonoseo_refresh_token = get_option('probonoseo_gsc_refresh_token', '');
		$probonoseo_client_id = get_option('probonoseo_gsc_client_id', '');
		$probonoseo_client_secret = get_option('probonoseo_gsc_client_secret', '');

		if (empty($probonoseo_refresh_token) || empty($probonoseo_client_id) || empty($probonoseo_client_secret)) {
			return false;
		}

		$probonoseo_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
			'body' => array(
				'refresh_token' => $probonoseo_refresh_token,
				'client_id' => $probonoseo_client_id,
				'client_secret' => $probonoseo_client_secret,
				'grant_type' => 'refresh_token'
			)
		));

		if (is_wp_error($probonoseo_response)) {
			return false;
		}

		$probonoseo_body = json_decode(wp_remote_retrieve_body($probonoseo_response), true);

		if (isset($probonoseo_body['access_token'])) {
			update_option('probonoseo_gsc_access_token', $probonoseo_body['access_token']);

			if (isset($probonoseo_body['expires_in'])) {
				update_option('probonoseo_gsc_token_expires', time() + $probonoseo_body['expires_in']);
			}

			return true;
		}

		return false;
	}

	public function get_access_token() {
		$probonoseo_expires = get_option('probonoseo_gsc_token_expires', 0);

		if (time() > $probonoseo_expires - 300) {
			$this->refresh_access_token();
		}

		return get_option('probonoseo_gsc_access_token', '');
	}

	public function get_search_analytics($site_url, $start_date, $end_date, $dimensions = array('query')) {
		$probonoseo_access_token = $this->get_access_token();

		if (empty($probonoseo_access_token)) {
			return array('error' => 'トークンがありません');
		}

		$probonoseo_encoded_url = urlencode($site_url);
		$probonoseo_endpoint = $this->api_base . '/sites/' . $probonoseo_encoded_url . '/searchAnalytics/query';

		$probonoseo_body = array(
			'startDate' => $start_date,
			'endDate' => $end_date,
			'dimensions' => $dimensions,
			'rowLimit' => 100
		);

		$probonoseo_response = wp_remote_post($probonoseo_endpoint, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $probonoseo_access_token,
				'Content-Type' => 'application/json'
			),
			'body' => wp_json_encode($probonoseo_body)
		));

		if (is_wp_error($probonoseo_response)) {
			return array('error' => $probonoseo_response->get_error_message());
		}

		return json_decode(wp_remote_retrieve_body($probonoseo_response), true);
	}

	public function ajax_connect() {
		check_ajax_referer('probonoseo_gsc_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
		}

		if (isset($_POST['code'])) {
			$probonoseo_result = $this->exchange_code_for_token(sanitize_text_field(wp_unslash($_POST['code'])));

			if ($probonoseo_result) {
				wp_send_json_success(array('message' => '接続しました'));
			} else {
				wp_send_json_error(array('message' => '接続に失敗しました'));
			}
		}

		wp_send_json_error(array('message' => '認証コードがありません'));
	}

	public function ajax_disconnect() {
		check_ajax_referer('probonoseo_gsc_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
		}

		delete_option('probonoseo_gsc_access_token');
		delete_option('probonoseo_gsc_refresh_token');
		delete_option('probonoseo_gsc_token_expires');

		wp_send_json_success(array('message' => '接続を解除しました'));
	}

	public function ajax_fetch_data() {
		check_ajax_referer('probonoseo_gsc_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
		}

		$probonoseo_site_url = home_url('/');
		$probonoseo_end_date = wp_date('Y-m-d');
		$probonoseo_start_date = wp_date('Y-m-d', strtotime('-28 days'));

		$probonoseo_data = $this->get_search_analytics($probonoseo_site_url, $probonoseo_start_date, $probonoseo_end_date);

		if (isset($probonoseo_data['error'])) {
			wp_send_json_error(array('message' => $probonoseo_data['error']));
		}

		wp_send_json_success($probonoseo_data);
	}
}

ProbonoSEO_Pro_GSC::get_instance();