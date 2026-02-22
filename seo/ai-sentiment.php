<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Sentiment {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_analyze_ai_sentiment', array($this, 'ajax_analyze_sentiment'));
	}
	
	public function analyze_sentiment($post_id) {
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
		
		if (get_option('probonoseo_pro_sentiment_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI感情分析機能が無効になっています。設定画面で有効化してください。'
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
		
		$content = mb_substr($content, 0, 4000);
		$prompt = $this->build_prompt($title, $content);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは文章の感情分析の専門家です。記事のトーンや感情傾向を分析し、読者への影響を評価してください。'
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
		
		$sentiment = $this->parse_sentiment_response($response['content']);
		
		return array(
			'success' => true,
			'sentiment' => $sentiment
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事の感情分析を行ってください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【分析項目】\n";
		$prompt .= "1. 全体のトーン（ポジティブ/ニュートラル/ネガティブ）\n";
		$prompt .= "2. 感情傾向（楽観的/悲観的/客観的/情熱的/冷静など）\n";
		$prompt .= "3. 説得力スコア（0-100点）\n";
		$prompt .= "4. 信頼性スコア（0-100点）\n";
		$prompt .= "5. 読者への期待される影響\n";
		$prompt .= "6. トーン改善の提案\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---トーン---\n";
		$prompt .= "[トーン判定]\n";
		$prompt .= "---感情傾向---\n";
		$prompt .= "[傾向]\n";
		$prompt .= "---説得力---\n";
		$prompt .= "[点数]/100\n";
		$prompt .= "---信頼性---\n";
		$prompt .= "[点数]/100\n";
		$prompt .= "---読者影響---\n";
		$prompt .= "[影響分析]\n";
		$prompt .= "---改善提案---\n";
		$prompt .= "[提案]\n";
		return $prompt;
	}
	
	private function parse_sentiment_response($response) {
		$result = array(
			'tone' => '',
			'emotion' => '',
			'persuasion_score' => 0,
			'trust_score' => 0,
			'reader_impact' => '',
			'suggestions' => array()
		);
		
		if (preg_match('/---トーン---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['tone'] = trim($matches[1]);
		}
		
		if (preg_match('/---感情傾向---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['emotion'] = trim($matches[1]);
		}
		
		if (preg_match('/---説得力---\s*\n(\d+)/u', $response, $matches)) {
			$result['persuasion_score'] = intval($matches[1]);
		}
		
		if (preg_match('/---信頼性---\s*\n(\d+)/u', $response, $matches)) {
			$result['trust_score'] = intval($matches[1]);
		}
		
		if (preg_match('/---読者影響---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['reader_impact'] = trim($matches[1]);
		}
		
		if (preg_match('/---改善提案---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['suggestions'][] = $line;
				}
			}
		}
		
		return $result;
	}
	
	public function ajax_analyze_sentiment() {
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
		
		$result = $this->analyze_sentiment($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Sentiment::get_instance();