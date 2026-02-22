<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Duplicate {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_check_ai_duplicate', array($this, 'ajax_check_duplicate'));
	}
	
	public function check_duplicate($post_id) {
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
		
		if (get_option('probonoseo_pro_duplicate_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI重複コンテンツチェック機能が無効になっています。設定画面で有効化してください。'
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
		
		$similar_posts = $this->find_similar_posts($post_id, $content);
		$content = mb_substr($content, 0, 3000);
		$prompt = $this->build_prompt($title, $content);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたはコンテンツ分析の専門家です。文章内の重複表現や冗長な部分を検出し、オリジナリティを評価してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1000,
			'temperature' => 0.5
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$analysis = $this->parse_duplicate_response($response['content']);
		$analysis['similar_posts'] = $similar_posts;
		
		return array(
			'success' => true,
			'duplicate' => $analysis
		);
	}
	
	private function find_similar_posts($current_post_id, $content) {
		$similar = array();
		$words = $this->extract_keywords($content);
		
		if (empty($words)) {
			return $similar;
		}
		
		$args = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => 20,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			'post__not_in' => array($current_post_id),
			's' => implode(' ', array_slice($words, 0, 5))
		);
		
		$query = new WP_Query($args);
		
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$other_content = wp_strip_all_tags(get_the_content());
				$similarity = $this->calculate_similarity($content, $other_content);
				
				if ($similarity > 30) {
					$similar[] = array(
						'id' => get_the_ID(),
						'title' => get_the_title(),
						'url' => get_permalink(),
						'similarity' => $similarity
					);
				}
			}
			wp_reset_postdata();
		}
		
		usort($similar, function($a, $b) {
			return $b['similarity'] - $a['similarity'];
		});
		
		return array_slice($similar, 0, 5);
	}
	
	private function extract_keywords($content) {
		$content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content);
		$words = preg_split('/\s+/', $content);
		$words = array_filter($words, function($word) {
			return mb_strlen($word) >= 2;
		});
		
		$word_count = array_count_values($words);
		arsort($word_count);
		
		return array_keys(array_slice($word_count, 0, 20));
	}
	
	private function calculate_similarity($text1, $text2) {
		$words1 = $this->extract_keywords($text1);
		$words2 = $this->extract_keywords($text2);
		
		if (empty($words1) || empty($words2)) {
			return 0;
		}
		
		$intersection = array_intersect($words1, $words2);
		$union = array_unique(array_merge($words1, $words2));
		
		return round((count($intersection) / count($union)) * 100);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事の重複・冗長性をチェックしてください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【チェック項目】\n";
		$prompt .= "1. オリジナリティスコア（0-100）\n";
		$prompt .= "2. 記事内の重複表現の検出\n";
		$prompt .= "3. 冗長な文章の検出\n";
		$prompt .= "4. 同じ言い回しの繰り返し\n";
		$prompt .= "5. 改善提案\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---スコア---\n";
		$prompt .= "オリジナリティ: [点数]/100\n";
		$prompt .= "---重複表現---\n";
		$prompt .= "[検出された重複表現を列挙]\n";
		$prompt .= "---冗長部分---\n";
		$prompt .= "[冗長な文章を列挙]\n";
		$prompt .= "---改善提案---\n";
		$prompt .= "[具体的な改善提案]\n";
		return $prompt;
	}
	
	private function parse_duplicate_response($response) {
		$result = array(
			'originality_score' => 0,
			'duplicates' => array(),
			'redundant' => array(),
			'suggestions' => array()
		);
		
		if (preg_match('/オリジナリティ[：:]\s*(\d+)/u', $response, $matches)) {
			$result['originality_score'] = intval($matches[1]);
		}
		
		if (preg_match('/---重複表現---\s*\n(.*?)(?=---冗長部分---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['duplicates'][] = $line;
				}
			}
		}
		
		if (preg_match('/---冗長部分---\s*\n(.*?)(?=---改善提案---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['redundant'][] = $line;
				}
			}
		}
		
		if (preg_match('/---改善提案---\s*\n(.*)$/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 5) {
					$result['suggestions'][] = $line;
				}
			}
		}
		
		return $result;
	}
	
	public function ajax_check_duplicate() {
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
		
		$result = $this->check_duplicate($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Duplicate::get_instance();