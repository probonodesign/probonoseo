<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Web_Vitals {
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
		return get_option('probonoseo_diagnosis_pro_vitals', '0') === '1';
	}

	public function run_diagnosis() {
		$this->results = array(
			'status' => 'success',
			'title' => 'Core Web Vitals',
			'icon' => 'dashicons-performance',
			'items' => array(),
			'scores' => array()
		);

		$this->results['items'][] = array(
			'type' => 'info',
			'message' => 'Core Web Vitalsは以下の3つの指標で構成されています：'
		);

		$lcp = $this->estimate_lcp();
		$this->results['scores']['lcp'] = $lcp;
		
		if ($lcp['status'] === 'good') {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('LCP (Largest Contentful Paint): %s - 良好', $lcp['estimate'])
			);
		} elseif ($lcp['status'] === 'needs_improvement') {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('LCP (Largest Contentful Paint): %s - 改善が必要', $lcp['estimate'])
			);
			if ($this->results['status'] === 'success') {
				$this->results['status'] = 'warning';
			}
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('LCP (Largest Contentful Paint): %s - 不良', $lcp['estimate'])
			);
			$this->results['status'] = 'error';
		}

		$fid = $this->estimate_fid();
		$this->results['scores']['fid'] = $fid;
		
		$this->results['items'][] = array(
			'type' => $fid['status'] === 'good' ? 'success' : ($fid['status'] === 'needs_improvement' ? 'warning' : 'error'),
			'message' => sprintf('FID (First Input Delay): %s', $fid['estimate'])
		);

		$cls = $this->estimate_cls();
		$this->results['scores']['cls'] = $cls;
		
		$this->results['items'][] = array(
			'type' => $cls['status'] === 'good' ? 'success' : ($cls['status'] === 'needs_improvement' ? 'warning' : 'error'),
			'message' => sprintf('CLS (Cumulative Layout Shift): %s', $cls['estimate'])
		);

		$this->add_improvement_suggestions();

		return $this->results;
	}

	private function estimate_lcp() {
		$issues = array();
		
		$lazy_images = get_option('probonoseo_speed_lazy_images', '1') === '1';
		$minify_css = get_option('probonoseo_speed_minify_css', '1') === '1';
		$webp = get_option('probonoseo_speed_pro_webp', '0') === '1';
		
		$score = 100;
		
		if (!$lazy_images) {
			$score -= 15;
		}
		if (!$minify_css) {
			$score -= 10;
		}
		if (!$webp) {
			$score -= 10;
		}
		
		if ($score >= 80) {
			return array('status' => 'good', 'estimate' => '推定2.5秒以下', 'score' => $score);
		} elseif ($score >= 50) {
			return array('status' => 'needs_improvement', 'estimate' => '推定2.5〜4秒', 'score' => $score);
		} else {
			return array('status' => 'poor', 'estimate' => '推定4秒以上', 'score' => $score);
		}
	}

	private function estimate_fid() {
		$minify_js = get_option('probonoseo_speed_minify_js', '1') === '1';
		$optimize_scripts = get_option('probonoseo_speed_optimize_wp_scripts', '1') === '1';
		
		$score = 100;
		
		if (!$minify_js) {
			$score -= 20;
		}
		if (!$optimize_scripts) {
			$score -= 15;
		}
		
		if ($score >= 80) {
			return array('status' => 'good', 'estimate' => '推定100ms以下', 'score' => $score);
		} elseif ($score >= 50) {
			return array('status' => 'needs_improvement', 'estimate' => '推定100〜300ms', 'score' => $score);
		} else {
			return array('status' => 'poor', 'estimate' => '推定300ms以上', 'score' => $score);
		}
	}

	private function estimate_cls() {
		$lazy_images = get_option('probonoseo_speed_lazy_images', '1') === '1';
		$responsive = get_option('probonoseo_speed_pro_responsive', '0') === '1';
		
		$score = 100;
		
		if (!$responsive) {
			$score -= 20;
		}
		
		if ($score >= 80) {
			return array('status' => 'good', 'estimate' => '推定0.1以下', 'score' => $score);
		} elseif ($score >= 50) {
			return array('status' => 'needs_improvement', 'estimate' => '推定0.1〜0.25', 'score' => $score);
		} else {
			return array('status' => 'poor', 'estimate' => '推定0.25以上', 'score' => $score);
		}
	}

	private function add_improvement_suggestions() {
		$suggestions = array();
		
		if (get_option('probonoseo_speed_pro_webp', '0') !== '1') {
			$suggestions[] = 'WebP画像変換を有効化してLCPを改善';
		}
		if (get_option('probonoseo_speed_pro_css_inline', '0') !== '1') {
			$suggestions[] = '重要CSSのインライン化でレンダリングブロックを削減';
		}
		if (get_option('probonoseo_speed_pro_page_cache', '0') !== '1') {
			$suggestions[] = 'ページキャッシュを有効化して応答速度を改善';
		}
		
		if (!empty($suggestions)) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => '改善提案:'
			);
			foreach ($suggestions as $suggestion) {
				$this->results['items'][] = array(
					'type' => 'detail',
					'message' => '→ ' . $suggestion
				);
			}
		}
	}

	public function get_results() {
		return $this->results;
	}
}

ProbonoSEO_Diagnosis_Web_Vitals::get_instance();