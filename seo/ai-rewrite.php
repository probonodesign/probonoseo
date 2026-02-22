<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Rewrite {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_rewrite', array($this, 'ajax_generate_rewrite'));
	}
	
	public function generate_rewrite($post_id, $style = 'improve') {
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
		
		if (get_option('probonoseo_pro_rewrite_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AIリライト提案機能が無効になっています。設定画面で有効化してください。'
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
		
		$content = mb_substr($content, 0, 3000);
		$prompt = $this->build_prompt($title, $content, $style);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語ライティングの専門家です。指定されたスタイルに従って文章をリライトしてください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 2000,
			'temperature' => 0.7
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		return array(
			'success' => true,
			'rewrite' => array(
				'style' => $style,
				'content' => $response['content']
			)
		);
	}
	
	private function build_prompt($title, $content, $style) {
		$style_instructions = array(
			'improve' => '全体的に読みやすく改善してください。文章の流れを良くし、冗長な表現を削除してください。',
			'simple' => 'より簡潔でシンプルな文章にリライトしてください。専門用語を避け、誰でも理解できる表現にしてください。',
			'professional' => 'よりプロフェッショナルでビジネス向けの文章にリライトしてください。',
			'friendly' => '親しみやすくカジュアルな文章にリライトしてください。読者に語りかけるような表現を使ってください。',
			'concise' => '要点を絞り、より短く簡潔な文章にリライトしてください。',
			'detailed' => 'より詳細で説明的な文章にリライトしてください。具体例や補足説明を追加してください。',
			'seo' => 'SEOを意識した文章にリライトしてください。キーワードを自然に含め、検索エンジンに評価されやすい構成にしてください。'
		);
		
		$instruction = isset($style_instructions[$style]) ? $style_instructions[$style] : $style_instructions['improve'];
		
		$prompt = "以下の記事をリライトしてください。\n\n";
		$prompt .= "【リライト指示】\n";
		$prompt .= $instruction . "\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "リライト後の文章のみを出力してください。説明や前置きは不要です。\n";
		return $prompt;
	}
	
	public function ajax_generate_rewrite() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$style = isset($_POST['style']) ? sanitize_text_field(wp_unslash($_POST['style'])) : 'improve';
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		$valid_styles = array('improve', 'simple', 'professional', 'friendly', 'concise', 'detailed', 'seo');
		if (!in_array($style, $valid_styles)) {
			$style = 'improve';
		}
		
		$result = $this->generate_rewrite($post_id, $style);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Rewrite::get_instance();