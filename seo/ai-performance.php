<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Performance {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_predict_ai_performance', array($this, 'ajax_predict_performance'));
	}
	
	public function predict_performance($post_id) {
		$post = get_post($post_id);
		
		if (!$post) {
			return array(
				'success' => false,
				'message' => '投稿が見つかりません。'
			);
		}
		
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return array(
				'success' => false,
				'message' => 'Pro版の機能です。ライセンスを有効化してください。'
			);
		}
		
		if (get_option('probonoseo_pro_performance_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI記事パフォーマンス予測機能が無効になっています。設定画面で有効化してください。'
			);
		}
		
		$openai = ProbonoSEO_OpenAI_API::get_instance();
		if (!$openai->is_api_key_set()) {
			return array(
				'success' => false,
				'message' => 'OpenAI APIキーが設定されていません。'
			);
		}
		
		$title = $post->post_title;
		$content = wp_strip_all_tags($post->post_content);
		
		if (mb_strlen($content) < 100) {
			return array(
				'success' => false,
				'message' => '記事の内容が短すぎます（100文字以上必要）。'
			);
		}
		
		$seo_data = $this->collect_seo_data($post_id, $post);
		
		$content = mb_substr($content, 0, 3000);
		$prompt = $this->build_prompt($title, $content, $seo_data);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOとコンテンツマーケティングの専門家です。記事のSEOパフォーマンスを多角的に分析し、検索順位とトラフィックの予測を行ってください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1500,
			'temperature' => 0.5
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$performance = $this->parse_performance_response($response['content']);
		$performance['seo_data'] = $seo_data;
		
		return array(
			'success' => true,
			'performance' => $performance
		);
	}
	
	private function collect_seo_data($post_id, $post) {
		$content = wp_strip_all_tags($post->post_content);
		
		$data = array(
			'char_count' => mb_strlen($content),
			'word_count' => $this->count_japanese_words($content),
			'has_meta_description' => false,
			'meta_description_length' => 0,
			'title_length' => mb_strlen($post->post_title),
			'heading_count' => 0,
			'image_count' => 0,
			'internal_link_count' => 0,
			'external_link_count' => 0
		);
		
		$meta_desc = get_post_meta($post_id, '_probonoseo_metadesc', true);
		if (!empty($meta_desc)) {
			$data['has_meta_description'] = true;
			$data['meta_description_length'] = mb_strlen($meta_desc);
		}
		
		$raw_content = $post->post_content;
		$data['heading_count'] = preg_match_all('/<h[1-6][^>]*>/i', $raw_content);
		$data['image_count'] = preg_match_all('/<img[^>]+>/i', $raw_content);
		
		$site_url = home_url();
		if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $raw_content, $matches)) {
			foreach ($matches[1] as $href) {
				if (strpos($href, $site_url) === 0 || strpos($href, '/') === 0) {
					$data['internal_link_count']++;
				} elseif (strpos($href, 'http') === 0) {
					$data['external_link_count']++;
				}
			}
		}
		
		return $data;
	}
	
	private function count_japanese_words($content) {
		$content = preg_replace('/\s+/', '', $content);
		return mb_strlen($content);
	}
	
	private function build_prompt($title, $content, $seo_data) {
		$prompt = "以下の記事のSEOパフォーマンスを予測してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【SEO基本データ】\n";
		$prompt .= "- 文字数: " . $seo_data['char_count'] . "文字\n";
		$prompt .= "- タイトル長: " . $seo_data['title_length'] . "文字\n";
		$prompt .= "- メタディスクリプション: " . ($seo_data['has_meta_description'] ? $seo_data['meta_description_length'] . "文字" : "未設定") . "\n";
		$prompt .= "- 見出し数: " . $seo_data['heading_count'] . "個\n";
		$prompt .= "- 画像数: " . $seo_data['image_count'] . "個\n";
		$prompt .= "- 内部リンク: " . $seo_data['internal_link_count'] . "個\n";
		$prompt .= "- 外部リンク: " . $seo_data['external_link_count'] . "個\n\n";
		$prompt .= "【評価項目】\n";
		$prompt .= "1. 総合SEOスコア（0-100点）\n";
		$prompt .= "2. 検索順位予測（想定キーワードでの順位帯）\n";
		$prompt .= "3. 月間トラフィック予測（PV目安）\n";
		$prompt .= "4. 強み（3〜5個）\n";
		$prompt .= "5. 弱み・改善点（3〜5個）\n";
		$prompt .= "6. 優先改善アクション（3個）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---総合スコア---\n";
		$prompt .= "[点数]/100\n";
		$prompt .= "---順位予測---\n";
		$prompt .= "[順位帯]\n";
		$prompt .= "---トラフィック予測---\n";
		$prompt .= "[月間PV予測]\n";
		$prompt .= "---強み---\n";
		$prompt .= "[強み1]\n";
		$prompt .= "[強み2]\n";
		$prompt .= "---弱み---\n";
		$prompt .= "[弱み1]\n";
		$prompt .= "[弱み2]\n";
		$prompt .= "---優先アクション---\n";
		$prompt .= "[アクション1]\n";
		$prompt .= "[アクション2]\n";
		$prompt .= "[アクション3]\n";
		return $prompt;
	}
	
	private function parse_performance_response($response) {
		$result = array(
			'overall_score' => 0,
			'ranking_potential' => '',
			'traffic_estimate' => '',
			'strengths' => array(),
			'weaknesses' => array(),
			'recommendations' => array()
		);
		
		if (preg_match('/---総合スコア---\s*\n(\d+)/u', $response, $matches)) {
			$result['overall_score'] = intval($matches[1]);
		}
		
		if (preg_match('/---順位予測---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['ranking_potential'] = trim($matches[1]);
		}
		
		if (preg_match('/---トラフィック予測---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['traffic_estimate'] = trim($matches[1]);
		}
		
		if (preg_match('/---強み---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['strengths'][] = $line;
				}
			}
		}
		
		if (preg_match('/---弱み---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['weaknesses'][] = $line;
				}
			}
		}
		
		if (preg_match('/---優先アクション---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['recommendations'][] = $line;
				}
			}
		}
		
		return $result;
	}
	
	public function ajax_predict_performance() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		$result = $this->predict_performance($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Performance::get_instance();