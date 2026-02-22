<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Performance {
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
		return get_option('probonoseo_diagnosis_pro_performance', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'パフォーマンス総合診断',
			'icon' => 'dashicons-dashboard',
			'items' => array()
		);

		$page_size = $this->estimate_page_size();
		
		if ($page_size < 1000000) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('推定ページサイズ: %s - 良好', $this->format_bytes($page_size))
			);
		} elseif ($page_size < 3000000) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('推定ページサイズ: %s - やや大きいです', $this->format_bytes($page_size))
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('推定ページサイズ: %s - 大きすぎます', $this->format_bytes($page_size))
			);
			$this->results['status'] = 'error';
		}

		$active_plugins = $this->count_active_plugins();
		if ($active_plugins <= 10) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('有効プラグイン数: %d - 適切です', $active_plugins)
			);
		} elseif ($active_plugins <= 20) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('有効プラグイン数: %d - やや多いです', $active_plugins)
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('有効プラグイン数: %d - 多すぎます。不要なプラグインを無効化してください', $active_plugins)
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		}

		$php_version = phpversion();
		if (version_compare($php_version, '8.0', '>=')) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('PHPバージョン: %s - 最新です', $php_version)
			);
		} elseif (version_compare($php_version, '7.4', '>=')) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('PHPバージョン: %s - PHP 8.x へのアップグレードを推奨', $php_version)
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('PHPバージョン: %s - アップグレードが必要です', $php_version)
			);
		}

		$memory_limit = ini_get('memory_limit');
		$memory_bytes = $this->convert_to_bytes($memory_limit);
		if ($memory_bytes >= 256 * 1024 * 1024) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('メモリ上限: %s - 十分です', $memory_limit)
			);
		} else {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('メモリ上限: %s - 256M以上を推奨', $memory_limit)
			);
		}

		$optimization_status = $this->check_optimization_status();
		foreach ($optimization_status as $item) {
			$this->results['items'][] = $item;
		}

		return $this->results;
	}

	private function estimate_page_size() {
		$size = 50000;
		
		$uploads = wp_upload_dir();
		$upload_path = $uploads['basedir'];
		
		return $size;
	}

	private function count_active_plugins() {
		$plugins = get_option('active_plugins', array());
		return count($plugins);
	}

	private function convert_to_bytes($value) {
		$value = trim($value);
		$last = strtolower($value[strlen($value) - 1]);
		$value = (int) $value;
		
		switch ($last) {
			case 'g':
				$value *= 1024;
			case 'm':
				$value *= 1024;
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}

	private function format_bytes($bytes) {
		if ($bytes >= 1048576) {
			return round($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			return round($bytes / 1024, 2) . ' KB';
		}
		return $bytes . ' bytes';
	}

	private function check_optimization_status() {
		$items = array();
		
		$optimizations = array(
			'probonoseo_speed_lazy_images' => '画像遅延読み込み',
			'probonoseo_speed_minify_css' => 'CSS圧縮',
			'probonoseo_speed_minify_js' => 'JS圧縮',
			'probonoseo_speed_pro_webp' => 'WebP変換',
			'probonoseo_speed_pro_page_cache' => 'ページキャッシュ'
		);
		
		$enabled = 0;
		foreach ($optimizations as $key => $name) {
			if (get_option($key, '0') === '1') {
				$enabled++;
			}
		}
		
		$items[] = array(
			'type' => $enabled >= 3 ? 'success' : 'info',
			'message' => sprintf('速度最適化機能: %d/%d 有効', $enabled, count($optimizations))
		);
		
		return $items;
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Performance::get_instance();