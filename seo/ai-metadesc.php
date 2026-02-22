<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_MetaDesc {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_metadesc', array($this, 'ajax_generate_metadesc'));
		add_action('wp_ajax_probonoseo_apply_ai_metadesc', array($this, 'ajax_apply_metadesc'));
	}
	
	public function generate_metadesc($post_id) {
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
		
		if (get_option('probonoseo_pro_metadesc_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => '高品質メタディスクリプション生成機能が無効になっています。設定画面で有効化してください。'
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
		$content = mb_substr($content, 0, 3000);
		
		$prompt = $this->build_prompt($title, $content);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。検索結果でクリックされやすい、魅力的なメタディスクリプションを作成してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 300,
			'temperature' => 0.7
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$metadesc = $this->parse_metadesc_response($response['content']);
		
		if (empty($metadesc)) {
			return array(
				'success' => false,
				'message' => 'メタディスクリプションの生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'metadesc' => array(
				'text' => $metadesc,
				'length' => mb_strlen($metadesc)
			)
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事に対して、SEOに最適化されたメタディスクリプションを1つ作成してください。\n\n";
		
		$prompt .= "【タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【記事の内容】\n";
		$prompt .= $content . "\n\n";
		
		$prompt .= "【メタディスクリプション作成のルール】\n";
		$prompt .= "1. 文字数は70〜120文字（全角）が理想\n";
		$prompt .= "2. 記事の要点を簡潔にまとめる\n";
		$prompt .= "3. 読者がクリックしたくなる表現を使う\n";
		$prompt .= "4. 重要なキーワードを自然に含める\n";
		$prompt .= "5. 「〜について解説」「〜をご紹介」などの定型文を避ける\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "メタディスクリプションのテキストのみを出力してください。説明や注釈は不要です。";
		
		return $prompt;
	}
	
	private function parse_metadesc_response($response) {
		$metadesc = trim($response);
		$metadesc = preg_replace('/^[「『](.+)[」』]$/', '$1', $metadesc);
		$metadesc = preg_replace('/^メタディスクリプション[：:]\s*/', '', $metadesc);
		
		if (mb_strlen($metadesc) > 160) {
			$metadesc = mb_substr($metadesc, 0, 157) . '...';
		}
		
		return $metadesc;
	}
	
	public function ajax_generate_metadesc() {
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
		
		$result = $this->generate_metadesc($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
	
	public function ajax_apply_metadesc() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$metadesc = isset($_POST['metadesc']) ? sanitize_textarea_field(wp_unslash($_POST['metadesc'])) : '';
		
		if (!$post_id || empty($metadesc)) {
			wp_send_json_error(array('message' => 'パラメータが不足しています。'));
			return;
		}
		
		update_post_meta($post_id, '_probonoseo_metadesc', $metadesc);
		
		wp_send_json_success(array('message' => 'メタディスクリプションを保存しました。'));
	}
}

ProbonoSEO_AI_MetaDesc::get_instance();