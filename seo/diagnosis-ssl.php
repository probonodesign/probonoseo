<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_SSL {
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
		return get_option('probonoseo_diagnosis_pro_ssl', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'SSL証明書',
			'icon' => 'dashicons-lock',
			'items' => array()
		);

		$site_url = get_site_url();
		
		if (strpos($site_url, 'https://') !== 0) {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => 'サイトがHTTPSで構成されていません。SSL証明書の診断をスキップします。'
			);
			$this->results['status'] = 'error';
			return $this->results;
		}

		$ssl_info = $this->get_ssl_info($site_url);
		
		if ($ssl_info['error']) {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => 'SSL証明書情報を取得できませんでした: ' . $ssl_info['error']
			);
			$this->results['status'] = 'error';
			return $this->results;
		}

		$this->results['items'][] = array(
			'type' => 'info',
			'message' => sprintf('発行者: %s', $ssl_info['issuer'])
		);

		$this->results['items'][] = array(
			'type' => 'info',
			'message' => sprintf('有効期限: %s', $ssl_info['valid_to'])
		);

		$days_remaining = $ssl_info['days_remaining'];
		
		if ($days_remaining > 30) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('残り%d日 - 証明書は有効です。', $days_remaining)
			);
		} elseif ($days_remaining > 7) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('残り%d日 - まもなく期限切れです。更新を準備してください。', $days_remaining)
			);
			$this->results['status'] = 'warning';
		} elseif ($days_remaining > 0) {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('残り%d日 - 至急更新が必要です！', $days_remaining)
			);
			$this->results['status'] = 'error';
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => '証明書の有効期限が切れています！'
			);
			$this->results['status'] = 'error';
		}

		return $this->results;
	}

	private function get_ssl_info($url) {
		$parsed = wp_parse_url($url);
		$host = $parsed['host'];
		
		$result = array(
			'error' => null,
			'issuer' => '',
			'valid_from' => '',
			'valid_to' => '',
			'days_remaining' => 0
		);

		$context = stream_context_create(array(
			'ssl' => array(
				'capture_peer_cert' => true,
				'verify_peer' => false,
				'verify_peer_name' => false
			)
		));

		$socket = @stream_socket_client(
			'ssl://' . $host . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if (!$socket) {
			$result['error'] = $errstr;
			return $result;
		}

		$params = stream_context_get_params($socket);
		$cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

		if ($cert) {
			$result['issuer'] = isset($cert['issuer']['O']) ? $cert['issuer']['O'] : 'Unknown';
			$result['valid_from'] = wp_date('Y-m-d', $cert['validFrom_time_t']);
			$result['valid_to'] = wp_date('Y-m-d', $cert['validTo_time_t']);
			$result['days_remaining'] = floor(($cert['validTo_time_t'] - time()) / 86400);
		} else {
			$result['error'] = '証明書の解析に失敗しました';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Stream socket requires fclose
        fclose($socket);

		return $result;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_SSL::get_instance();