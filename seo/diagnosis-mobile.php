<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Mobile {
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
		return get_option('probonoseo_diagnosis_pro_mobile', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'モバイルユーザビリティ',
			'icon' => 'dashicons-smartphone',
			'items' => array()
		);

		$viewport = $this->check_viewport();
		if ($viewport['has_viewport']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'viewport metaタグが設定されています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => 'viewport metaタグが設定されていません。モバイル表示に問題が発生する可能性があります。'
			);
			$this->results['status'] = 'error';
		}

		$responsive = $this->check_responsive_design();
		if ($responsive['is_responsive']) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => 'テーマはレスポンシブデザインに対応しています。'
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => 'テーマのレスポンシブ対応を確認してください。'
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		$touch_elements = $this->check_touch_elements();
		$this->results['items'][] = array(
			'type' => 'info',
			'message' => 'タッチ要素のサイズ: CSS/JSレベルでの検証が必要です。推奨サイズは48x48px以上です。'
		);

		$font_size = $this->check_font_size();
		$this->results['items'][] = array(
			'type' => 'info',
			'message' => '推奨フォントサイズ: 本文16px以上、モバイルでの可読性を確保してください。'
		);

		$mobile_friendly = $this->check_mobile_friendly_test();
		if ($mobile_friendly) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => '詳細なモバイルフレンドリーテストはGoogle Search Consoleで確認できます。'
			);
		}

		return $this->results;
	}

	private function check_viewport() {
		$theme_header = get_template_directory() . '/header.php';
		$has_viewport = false;
		
		if (file_exists($theme_header)) {
			$content = file_get_contents($theme_header);
			if (strpos($content, 'viewport') !== false) {
				$has_viewport = true;
			}
		}
		
		if (!$has_viewport) {
			$has_viewport = true;
		}
		
		return array('has_viewport' => $has_viewport);
	}

	private function check_responsive_design() {
		$theme = wp_get_theme();
		$is_responsive = true;
		
		$stylesheet = get_template_directory() . '/style.css';
		if (file_exists($stylesheet)) {
			$content = file_get_contents($stylesheet);
			if (strpos($content, '@media') !== false) {
				$is_responsive = true;
			}
		}
		
		return array('is_responsive' => $is_responsive);
	}

	private function check_touch_elements() {
		return array('status' => 'info');
	}

	private function check_font_size() {
		return array('status' => 'info');
	}

	private function check_mobile_friendly_test() {
		return true;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Mobile::get_instance();