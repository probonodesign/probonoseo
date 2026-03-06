<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Body {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_body', array($this, 'ajax_generate_body'));
	}
	
	public function generate_body_text($post_id, $heading, $context = '') {
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
		
		if (get_option('probonoseo_pro_body_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => '説明文改善AI機能が無効になっています。設定画面で有効化してください。'
			);
		}
		
		$openai = ProbonoSEO_OpenAI_API::get_instance();
		if (!$openai->is_api_key_set()) {
			return array(
				'success' => false,
				'message' => 'OpenAI APIキーが設定されていません。'
			);
		}
		
		if (empty($heading)) {
			return array(
				'success' => false,
				'message' => '見出しを指定してください。'
			);
		}
		
		$title = $post->post_title;
		$prompt = $this->build_prompt($title, $heading, $context);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語の文章作成の専門家です。読みやすく、情報価値の高い本文を作成してください。SEOを意識しつつも、読者にとって有益な内容を優先してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1000,
			'temperature' => 0.7
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$body_text = $this->format_body_text($response['content']);
		
		if (empty($body_text)) {
			return array(
				'success' => false,
				'message' => '本文の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'body' => array(
				'text' => $body_text,
				'html' => $this->convert_to_blocks($body_text),
				'word_count' => mb_strlen(wp_strip_all_tags($body_text))
			)
		);
	}
	
	private function build_prompt($title, $heading, $context) {
		$prompt = "以下の見出しに対する本文を作成してください。\n\n";
		
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【対象の見出し】\n";
		$prompt .= $heading . "\n\n";
		
		if (!empty($context)) {
			$prompt .= "【追加の指示・文脈】\n";
			$prompt .= $context . "\n\n";
		}
		
		$prompt .= "【本文作成のルール】\n";
		$prompt .= "1. 2〜4段落程度で作成（合計200〜400文字）\n";
		$prompt .= "2. 見出しの内容に沿った具体的な情報を提供\n";
		$prompt .= "3. 読みやすい文章（1文40文字以内が理想）\n";
		$prompt .= "4. 専門用語は必要に応じて簡潔に説明\n";
		$prompt .= "5. 箇条書きは使わず、自然な文章で表現\n";
		$prompt .= "6. 「〜です。」「〜ます。」で統一\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "本文のみを出力してください。段落は空行で区切ってください。";
		
		return $prompt;
	}
	
	private function format_body_text($response) {
		$text = trim($response);
		$text = preg_replace('/^[「『](.+)[」』]$/s', '$1', $text);
		$text = preg_replace('/^(本文|以下|出力)[：:]\s*/u', '', $text);
		
		return $text;
	}
	
	private function convert_to_blocks($text) {
		$paragraphs = preg_split('/\n\s*\n/', $text);
		$html = '';
		
		foreach ($paragraphs as $p) {
			$p = trim($p);
			if (!empty($p)) {
				$p = str_replace("\n", '<br>', $p);
				$html .= '<!-- wp:paragraph -->' . "\n";
				$html .= '<p>' . esc_html($p) . '</p>' . "\n";
				$html .= '<!-- /wp:paragraph -->' . "\n\n";
			}
		}
		
		return $html;
	}
	
	public function ajax_generate_body() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$heading = isset($_POST['heading']) ? sanitize_text_field(wp_unslash($_POST['heading'])) : '';
		$context = isset($_POST['context']) ? sanitize_textarea_field(wp_unslash($_POST['context'])) : '';
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		if (empty($heading)) {
			wp_send_json_error(array('message' => '見出しを入力してください。'));
			return;
		}
		
		$result = $this->generate_body_text($post_id, $heading, $context);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Body::get_instance();