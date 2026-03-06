<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Competitor_Core {
	
	private static $instance = null;
	private $cache_duration = 3600;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_save_competitors', array($this, 'ajax_save_competitors'));
		add_action('wp_ajax_probonoseo_run_competitor_analysis', array($this, 'ajax_run_analysis'));
	}
	
	public function ajax_save_competitors() {
		check_ajax_referer('probonoseo_competitor_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$competitors = isset($_POST['competitors']) ? array_map('esc_url_raw', wp_unslash($_POST['competitors'])) : array();
		$target_url = isset($_POST['target_url']) ? esc_url_raw(wp_unslash($_POST['target_url'])) : '';
		
		$competitors = array_filter($competitors, function($url) {
			return !empty($url) && filter_var($url, FILTER_VALIDATE_URL);
		});
		
		$competitors = array_slice($competitors, 0, 10);
		
		update_option('probonoseo_competitors', $competitors);
		update_option('probonoseo_target_url', $target_url);
		
		wp_send_json_success(array('message' => '保存しました。'));
	}
	
	public function ajax_run_analysis() {
		check_ajax_referer('probonoseo_competitor_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			wp_send_json_error(array('message' => 'Pro版の機能です。'));
			return;
		}
		
		if (get_option('probonoseo_competitor_enabled', '1') !== '1') {
			wp_send_json_error(array('message' => '競合分析機能が無効になっています。'));
			return;
		}
		
		$competitors = isset($_POST['competitors']) ? array_map('esc_url_raw', wp_unslash($_POST['competitors'])) : array();
		$target_url = isset($_POST['target_url']) ? esc_url_raw(wp_unslash($_POST['target_url'])) : '';
		$options = isset($_POST['options']) ? array_map('sanitize_text_field', wp_unslash($_POST['options'])) : array();
		
		if (empty($competitors)) {
			wp_send_json_error(array('message' => '競合サイトを1件以上登録してください。'));
			return;
		}
		
		$results = array();
		
		if (!empty($target_url)) {
			$results['target'] = $this->analyze_url($target_url, $options);
			$results['target']['url'] = $target_url;
			$results['target']['is_target'] = true;
		}
		
		foreach ($competitors as $url) {
			$data = $this->analyze_url($url, $options);
			$data['url'] = $url;
			$data['is_target'] = false;
			$results['competitors'][] = $data;
		}
		
		$analyzer = ProbonoSEO_Competitor_Analyzer::get_instance();
		$results = $analyzer->calculate_scores($results);
		
		update_option('probonoseo_last_analysis', $results);
		update_option('probonoseo_last_analysis_time', time());
		
		$html = $this->render_results($results);
		
		wp_send_json_success(array('html' => $html, 'data' => $results));
	}
	
	public function analyze_url($url, $options = array()) {
		$cache_key = 'probonoseo_analysis_' . md5($url);
		$cached = get_transient($cache_key);
		
		if ($cached !== false) {
			return $cached;
		}
		
		$html = $this->fetch_url($url);
		
		if ($html === false) {
			return array(
				'error' => true,
				'message' => 'URLの取得に失敗しました。'
			);
		}
		
		$result = array(
			'error' => false
		);
		
		$result['title'] = $this->extract_title($html);
		$result['meta_description'] = $this->extract_meta_description($html);
		$result['headings'] = $this->extract_headings($html);
		$result['word_count'] = $this->count_words($html);
		$result['image_count'] = $this->count_images($html);
		$result['internal_links'] = $this->count_internal_links($html, $url);
		$result['external_links'] = $this->count_external_links($html, $url);
		$result['schema'] = $this->extract_schema($html);
		$result['viewport'] = $this->check_viewport($html);
		$result['keywords'] = $this->extract_keywords($html);
		
		set_transient($cache_key, $result, $this->cache_duration);
		
		return $result;
	}
	
	private function fetch_url($url) {
		$args = array(
			'timeout' => 30,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'sslverify' => false
		);
		
		$response = wp_remote_get($url, $args);
		
		if (is_wp_error($response)) {
			return false;
		}
		
		$code = wp_remote_retrieve_response_code($response);
		if ($code !== 200) {
			return false;
		}
		
		return wp_remote_retrieve_body($response);
	}
	
	private function extract_title($html) {
		if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $matches)) {
			return trim(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
		}
		return '';
	}
	
	private function extract_meta_description($html) {
		if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
			return trim(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
		}
		if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\'][^>]*>/i', $html, $matches)) {
			return trim(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
		}
		return '';
	}
	
	private function extract_headings($html) {
		$headings = array(
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array()
		);
		
		for ($probonoseo_i = 1; $probonoseo_i <= 6; $probonoseo_i++) {
			if (preg_match_all('/<h' . $probonoseo_i . '[^>]*>([^<]+)<\/h' . $probonoseo_i . '>/i', $html, $matches)) {
				foreach ($matches[1] as $text) {
					$headings['h' . $probonoseo_i][] = trim(wp_strip_all_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8')));
				}
			}
		}
		
		return $headings;
	}
	
	private function count_words($html) {
		$html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
		$html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
		$html = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $html);
		$html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
		$html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
		$html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
		
		$text = wp_strip_all_tags($html);
		$text = preg_replace('/\s+/', '', $text);
		
		return mb_strlen($text);
	}
	
	private function count_images($html) {
		preg_match_all('/<img[^>]+>/i', $html, $matches);
		return count($matches[0]);
	}
	
	private function count_internal_links($html, $base_url) {
		$host = wp_parse_url($base_url, PHP_URL_HOST);
		$count = 0;
		
		if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
			foreach ($matches[1] as $link) {
				$link_host = wp_parse_url($link, PHP_URL_HOST);
				if ($link_host === $host || $link_host === null) {
					$count++;
				}
			}
		}
		
		return $count;
	}
	
	private function count_external_links($html, $base_url) {
		$host = wp_parse_url($base_url, PHP_URL_HOST);
		$count = 0;
		
		if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
			foreach ($matches[1] as $link) {
				$link_host = wp_parse_url($link, PHP_URL_HOST);
				if ($link_host !== null && $link_host !== $host) {
					$count++;
				}
			}
		}
		
		return $count;
	}
	
	private function extract_schema($html) {
		$schemas = array();
		
		if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>([^<]+)<\/script>/i', $html, $matches)) {
			foreach ($matches[1] as $json) {
				$data = json_decode($json, true);
				if ($data) {
					if (isset($data['@type'])) {
						$schemas[] = $data['@type'];
					} elseif (isset($data['@graph'])) {
						foreach ($data['@graph'] as $item) {
							if (isset($item['@type'])) {
								$schemas[] = $item['@type'];
							}
						}
					}
				}
			}
		}
		
		return array_unique($schemas);
	}
	
	private function check_viewport($html) {
		return (bool) preg_match('/<meta[^>]+name=["\']viewport["\'][^>]*>/i', $html);
	}
	
	private function extract_keywords($html) {
		$html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
		$html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
		
		$text = wp_strip_all_tags($html);
		$text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
		$text = preg_replace('/\s+/', ' ', $text);
		
		$words = preg_split('/\s+/', $text);
		$word_count = array();
		
		foreach ($words as $word) {
			$word = trim($word);
			if (mb_strlen($word) >= 2) {
				if (!isset($word_count[$word])) {
					$word_count[$word] = 0;
				}
				$word_count[$word]++;
			}
		}
		
		arsort($word_count);
		
		return array_slice($word_count, 0, 20, true);
	}
	
	private function render_results($results) {
		ob_start();
		?>
		<div class="probonoseo-results-container">
			
			<?php if (isset($results['target']) && !$results['target']['error']) : ?>
				<div class="probonoseo-result-section">
					<h4 class="probonoseo-result-section-title">
						<span class="dashicons dashicons-admin-home"></span> 自サイト
					</h4>
					<div class="probonoseo-site-card target">
						<div class="probonoseo-site-url"><?php echo esc_html($results['target']['url']); ?></div>
						<div class="probonoseo-site-title"><?php echo esc_html($results['target']['title']); ?></div>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if (!empty($results['competitors'])) : ?>
				<div class="probonoseo-result-section">
					<h4 class="probonoseo-result-section-title">
						<span class="dashicons dashicons-groups"></span> 競合サイト比較
					</h4>
					
					<div class="probonoseo-comparison-table-wrap">
						<table class="probonoseo-comparison-table">
							<thead>
								<tr>
									<th>項目</th>
									<?php if (isset($results['target'])) : ?>
										<th class="target-col">自サイト</th>
									<?php endif; ?>
									<?php foreach ($results['competitors'] as $probonoseo_index => $comp) : ?>
										<th>競合<?php echo esc_html($probonoseo_index + 1); ?></th>
									<?php endforeach; ?>
									<th>平均</th>
								</tr>
							</thead>
							<tbody>
								<?php echo wp_kses_post($this->render_comparison_rows($results)); ?>
							</tbody>
						</table>
					</div>
				</div>
				
				<?php if (isset($results['scores'])) : ?>
					<div class="probonoseo-result-section">
						<h4 class="probonoseo-result-section-title">
							<span class="dashicons dashicons-chart-pie"></span> 総合スコア
						</h4>
						<div class="probonoseo-score-wrap">
							<?php foreach ($results['scores'] as $key => $score) : 
								$probonoseo_class = '';
								if ($score >= 70) {
									$probonoseo_class = 'good';
								} elseif ($score >= 40) {
									$probonoseo_class = 'warning';
								} else {
									$probonoseo_class = 'bad';
								}
								$probonoseo_is_target = (strpos($key, '自サイト') !== false);
							?>
								<div class="probonoseo-score-item <?php echo $probonoseo_is_target ? 'target' : ''; ?>">
									<div class="probonoseo-score-label"><?php echo esc_html($key); ?></div>
									<div class="probonoseo-score-value <?php echo esc_attr($probonoseo_class); ?>"><?php echo esc_html($score); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				
			<?php endif; ?>
			
		</div>
		<?php
		return ob_get_clean();
	}
	
	private function render_comparison_rows($results) {
		$rows = array(
			'title_length' => 'タイトル文字数',
			'meta_length' => 'メタD文字数',
			'word_count' => '本文文字数',
			'image_count' => '画像数',
			'h1_count' => 'H1数',
			'h2_count' => 'H2数',
			'h3_count' => 'H3数',
			'internal_links' => '内部リンク数',
			'external_links' => '外部リンク数',
			'schema_count' => 'schema数'
		);
		
		ob_start();
		
		foreach ($rows as $key => $label) {
			echo '<tr>';
			echo '<td>' . esc_html($label) . '</td>';
			
			$values = array();
			
			if (isset($results['target'])) {
				$val = $this->get_metric_value($results['target'], $key);
				$values[] = $val;
				echo '<td class="target-col">' . esc_html($val) . '</td>';
			}
			
			foreach ($results['competitors'] as $comp) {
				if ($comp['error']) {
					echo '<td>-</td>';
				} else {
					$val = $this->get_metric_value($comp, $key);
					$values[] = $val;
					echo '<td>' . esc_html($val) . '</td>';
				}
			}
			
			$avg = count($values) > 0 ? round(array_sum($values) / count($values)) : 0;
			echo '<td><strong>' . esc_html($avg) . '</strong></td>';
			
			echo '</tr>';
		}
		
		return ob_get_clean();
	}
	
	private function get_metric_value($data, $key) {
		switch ($key) {
			case 'title_length':
				return mb_strlen($data['title'] ?? '');
			case 'meta_length':
				return mb_strlen($data['meta_description'] ?? '');
			case 'word_count':
				return $data['word_count'] ?? 0;
			case 'image_count':
				return $data['image_count'] ?? 0;
			case 'h1_count':
				return count($data['headings']['h1'] ?? array());
			case 'h2_count':
				return count($data['headings']['h2'] ?? array());
			case 'h3_count':
				return count($data['headings']['h3'] ?? array());
			case 'internal_links':
				return $data['internal_links'] ?? 0;
			case 'external_links':
				return $data['external_links'] ?? 0;
			case 'schema_count':
				return count($data['schema'] ?? array());
			default:
				return 0;
		}
	}
}

ProbonoSEO_Competitor_Core::get_instance();