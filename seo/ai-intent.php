<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Intent {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_analyze_ai_intent', array($this, 'ajax_analyze_intent'));
	}
	
	public function analyze_intent($post_id) {
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
		
		if (get_option('probonoseo_pro_intent_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI検索意図分析機能が無効になっています。設定画面で有効化してください。'
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
				'content' => 'あなたは日本語SEOと検索意図分析の専門家です。記事の内容から、ユーザーがどのような検索意図でこの記事にたどり着くかを分析してください。'
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
		
		$intent = $this->parse_intent_response($response['content']);
		
		if (empty($intent['primary_intent'])) {
			return array(
				'success' => false,
				'message' => '検索意図の分析に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'intent' => $intent
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事にたどり着くユーザーの検索意図を分析してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【分析項目】\n";
		$prompt .= "1. 主要な検索意図（情報収集型/ナビゲーション型/トランザクション型/比較検討型）\n";
		$prompt .= "2. ユーザーのニーズ（この記事で満たしたいこと、3〜5個）\n";
		$prompt .= "3. 想定される検索クエリ（3〜5個）\n";
		$prompt .= "4. 検索意図への対応度（0-100点）\n";
		$prompt .= "5. 不足している情報（ユーザーが期待するが記事にない情報）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---主要意図---\n";
		$prompt .= "[意図タイプ]\n";
		$prompt .= "---ユーザーニーズ---\n";
		$prompt .= "[ニーズ1]\n";
		$prompt .= "[ニーズ2]\n";
		$prompt .= "---想定クエリ---\n";
		$prompt .= "[クエリ1]\n";
		$prompt .= "[クエリ2]\n";
		$prompt .= "---対応度---\n";
		$prompt .= "[点数]/100\n";
		$prompt .= "---不足情報---\n";
		$prompt .= "[不足1]\n";
		$prompt .= "[不足2]\n";
		return $prompt;
	}
	
	private function parse_intent_response($response) {
		$result = array(
			'primary_intent' => '',
			'user_needs' => array(),
			'search_queries' => array(),
			'match_score' => 0,
			'missing_info' => array()
		);
		
		if (preg_match('/---主要意図---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['primary_intent'] = trim($matches[1]);
		}
		
		if (preg_match('/---ユーザーニーズ---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['user_needs'][] = $line;
				}
			}
		}
		
		if (preg_match('/---想定クエリ---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['search_queries'][] = $line;
				}
			}
		}
		
		if (preg_match('/---対応度---\s*\n(\d+)/u', $response, $matches)) {
			$result['match_score'] = intval($matches[1]);
		}
		
		if (preg_match('/---不足情報---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['missing_info'][] = $line;
				}
			}
		}
		
		return $result;
	}
	
	public function ajax_analyze_intent() {
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
		
		$result = $this->analyze_intent($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Intent::get_instance();