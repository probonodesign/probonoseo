<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_Total_Score {
	private static $instance = null;
	private $results = array();
	private $scores = array();

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
		return get_option('probonoseo_diagnosis_pro_total', '0') === '1';
	}

	public function run_diagnosis($all_results = array()) {
		$this->results = array(
			'status' => 'success',
			'title' => 'SEO総合スコア',
			'icon' => 'dashicons-awards',
			'items' => array(),
			'total_score' => 0,
			'category_scores' => array()
		);

		$category_weights = array(
			'security' => 15,
			'performance' => 20,
			'indexing' => 20,
			'mobile' => 15,
			'technical' => 15,
			'content' => 15
		);

		$this->scores = array(
			'security' => $this->calculate_security_score($all_results),
			'performance' => $this->calculate_performance_score($all_results),
			'indexing' => $this->calculate_indexing_score($all_results),
			'mobile' => $this->calculate_mobile_score($all_results),
			'technical' => $this->calculate_technical_score($all_results),
			'content' => $this->calculate_content_score()
		);

		$total_score = 0;
		foreach ($this->scores as $category => $score) {
			$weight = isset($category_weights[$category]) ? $category_weights[$category] : 10;
			$total_score += ($score * $weight / 100);
		}

		$this->results['total_score'] = round($total_score);
		$this->results['category_scores'] = $this->scores;

		if ($total_score >= 80) {
			$this->results['items'][] = array(
				'type' => 'success',
				'message' => sprintf('総合スコア: %d点 - 優秀です！', round($total_score))
			);
		} elseif ($total_score >= 60) {
			$this->results['items'][] = array(
				'type' => 'warning',
				'message' => sprintf('総合スコア: %d点 - 改善の余地があります', round($total_score))
			);
			$this->results['status'] = 'warning';
		} else {
			$this->results['items'][] = array(
				'type' => 'error',
				'message' => sprintf('総合スコア: %d点 - 改善が必要です', round($total_score))
			);
			$this->results['status'] = 'error';
		}

		$score_labels = array(
			'security' => 'セキュリティ',
			'performance' => 'パフォーマンス',
			'indexing' => 'インデックス',
			'mobile' => 'モバイル対応',
			'technical' => '技術的SEO',
			'content' => 'コンテンツ'
		);

		$this->results['items'][] = array(
			'type' => 'info',
			'message' => '--- カテゴリ別スコア ---'
		);

		foreach ($this->scores as $category => $score) {
			$label = isset($score_labels[$category]) ? $score_labels[$category] : $category;
			$type = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'error');
			$this->results['items'][] = array(
				'type' => $type,
				'message' => sprintf('%s: %d点', $label, $score)
			);
		}

		$priorities = $this->get_improvement_priorities();
		if (!empty($priorities)) {
			$this->results['items'][] = array(
				'type' => 'info',
				'message' => '--- 改善優先度 ---'
			);
			foreach ($priorities as $priority) {
				$this->results['items'][] = array(
					'type' => 'detail',
					'message' => $priority
				);
			}
		}

		return $this->results;
	}

	private function calculate_security_score($all_results) {
		$score = 100;
		
		if (strpos(get_site_url(), 'https://') !== 0) {
			$score -= 40;
		}
		
		if (isset($all_results['security']) && $all_results['security']['status'] === 'error') {
			$score -= 20;
		} elseif (isset($all_results['security']) && $all_results['security']['status'] === 'warning') {
			$score -= 10;
		}
		
		return max(0, $score);
	}

	private function calculate_performance_score($all_results) {
		$score = 100;
		
		$optimizations = array(
			'probonoseo_speed_lazy_images',
			'probonoseo_speed_minify_css',
			'probonoseo_speed_minify_js',
			'probonoseo_speed_pro_webp',
			'probonoseo_speed_pro_page_cache'
		);
		
		foreach ($optimizations as $opt) {
			if (get_option($opt, '0') !== '1') {
				$score -= 10;
			}
		}
		
		return max(0, $score);
	}

	private function calculate_indexing_score($all_results) {
		$score = 100;
		
		if (isset($all_results['crawl']) && $all_results['crawl']['status'] === 'error') {
			$score -= 30;
		} elseif (isset($all_results['crawl']) && $all_results['crawl']['status'] === 'warning') {
			$score -= 15;
		}
		
		return max(0, $score);
	}

	private function calculate_mobile_score($all_results) {
		$score = 100;
		
		if (isset($all_results['mobile']) && $all_results['mobile']['status'] === 'error') {
			$score -= 30;
		} elseif (isset($all_results['mobile']) && $all_results['mobile']['status'] === 'warning') {
			$score -= 15;
		}
		
		return max(0, $score);
	}

	private function calculate_technical_score($all_results) {
		$score = 100;
		
		if (isset($all_results['robots']) && $all_results['robots']['status'] === 'error') {
			$score -= 20;
		}
		if (isset($all_results['sitemap']) && $all_results['sitemap']['status'] === 'warning') {
			$score -= 10;
		}
		
		return max(0, $score);
	}

	private function calculate_content_score() {
		$score = 100;
		
		$posts = wp_count_posts('post');
		$published = isset($posts->publish) ? $posts->publish : 0;
		
		if ($published < 10) {
			$score -= 20;
		}
		
		return max(0, $score);
	}

	private function get_improvement_priorities() {
		$priorities = array();
		
		$sorted = $this->scores;
		asort($sorted);
		
		$labels = array(
			'security' => 'セキュリティの強化（HTTPS、セキュリティヘッダー）',
			'performance' => 'パフォーマンスの改善（速度最適化機能を有効化）',
			'indexing' => 'インデックスの改善（リンク切れ修正、サイトマップ確認）',
			'mobile' => 'モバイル対応の確認（レスポンシブデザイン）',
			'technical' => '技術的SEOの改善（robots.txt、.htaccess）',
			'content' => 'コンテンツの充実（記事数、品質向上）'
		);
		
		$count = 0;
		foreach ($sorted as $category => $score) {
			if ($score < 80 && $count < 3) {
				$priorities[] = sprintf('優先%d: %s（現在%d点）', $count + 1, $labels[$category], $score);
				$count++;
			}
		}
		
		return $priorities;
	}

	public function get_results() {
		return $this->results;
	}

	public function get_scores() {
		return $this->scores;
	}
}

ProbonoSEO_Diagnosis_Total_Score::get_instance();