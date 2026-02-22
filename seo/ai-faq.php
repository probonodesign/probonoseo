<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_FAQ {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_faq', array($this, 'ajax_generate_faq'));
		add_action('wp_ajax_probonoseo_apply_ai_faq', array($this, 'ajax_apply_faq'));
	}
	
	public function generate_faq($post_id, $count = 5) {
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
		
		if (get_option('probonoseo_pro_faq_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'FAQ自動生成機能が無効になっています。設定画面で有効化してください。'
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
		$count = max(3, min(10, intval($count)));
		
		$prompt = $this->build_prompt($title, $content, $count);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。記事の内容に基づいて、読者が疑問に思いそうなQ&Aを作成してください。FAQ構造化データとしても使用できる形式で出力してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1500,
			'temperature' => 0.7
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$faqs = $this->parse_faq_response($response['content']);
		
		if (empty($faqs)) {
			return array(
				'success' => false,
				'message' => 'FAQの生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'faqs' => $faqs,
			'schema' => $this->generate_faq_schema($faqs)
		);
	}
	
	private function build_prompt($title, $content, $count) {
		$prompt = "以下の記事に基づいて、よくある質問（FAQ）を{$count}個作成してください。\n\n";
		
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		
		$prompt .= "【FAQ作成のルール】\n";
		$prompt .= "1. 読者が実際に疑問に思いそうな質問を選ぶ\n";
		$prompt .= "2. 質問は「〜ですか？」「〜でしょうか？」で終わる\n";
		$prompt .= "3. 回答は50〜150文字で簡潔かつ具体的に\n";
		$prompt .= "4. 記事の内容に基づいた正確な回答にする\n";
		$prompt .= "5. 検索されやすいキーワードを自然に含める\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "以下の形式で出力してください：\n";
		$prompt .= "---\n";
		$prompt .= "Q: 質問文\n";
		$prompt .= "A: 回答文\n";
		$prompt .= "---\n";
		
		return $prompt;
	}
	
	private function parse_faq_response($response) {
		$faqs = array();
		$blocks = preg_split('/---+/', $response);
		
		foreach ($blocks as $block) {
			$block = trim($block);
			if (empty($block)) {
				continue;
			}
			
			$question = '';
			$answer = '';
			
			if (preg_match('/Q[：:]\s*(.+?)(?=\nA[：:]|$)/us', $block, $m)) {
				$question = trim($m[1]);
			}
			
			if (preg_match('/A[：:]\s*(.+?)$/us', $block, $m)) {
				$answer = trim($m[1]);
			}
			
			if (!empty($question) && !empty($answer)) {
				$faqs[] = array(
					'question' => $question,
					'answer' => $answer
				);
			}
		}
		
		return $faqs;
	}
	
	private function generate_faq_schema($faqs) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'FAQPage',
			'mainEntity' => array()
		);
		
		foreach ($faqs as $faq) {
			$schema['mainEntity'][] = array(
				'@type' => 'Question',
				'name' => $faq['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text' => $faq['answer']
				)
			);
		}
		
		return $schema;
	}
	
	public function generate_faq_html($faqs) {
		$html = '<!-- wp:group {"className":"probonoseo-faq-section"} -->' . "\n";
		$html .= '<div class="wp-block-group probonoseo-faq-section">' . "\n";
		
		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">よくある質問</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";
		
		foreach ($faqs as $index => $faq) {
			$html .= '<!-- wp:group {"className":"probonoseo-faq-item"} -->' . "\n";
			$html .= '<div class="wp-block-group probonoseo-faq-item">' . "\n";
			
			$html .= '<!-- wp:heading {"level":3,"className":"probonoseo-faq-question"} -->' . "\n";
			$html .= '<h3 class="wp-block-heading probonoseo-faq-question">Q. ' . esc_html($faq['question']) . '</h3>' . "\n";
			$html .= '<!-- /wp:heading -->' . "\n";
			
			$html .= '<!-- wp:paragraph {"className":"probonoseo-faq-answer"} -->' . "\n";
			$html .= '<p class="probonoseo-faq-answer">A. ' . esc_html($faq['answer']) . '</p>' . "\n";
			$html .= '<!-- /wp:paragraph -->' . "\n";
			
			$html .= '</div>' . "\n";
			$html .= '<!-- /wp:group -->' . "\n\n";
		}
		
		$html .= '</div>' . "\n";
		$html .= '<!-- /wp:group -->';
		
		return $html;
	}
	
	public function ajax_generate_faq() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$count = isset($_POST['count']) ? intval($_POST['count']) : 5;
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		$result = $this->generate_faq($post_id, $count);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
	
	public function ajax_apply_faq() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data, sanitized after json_decode
		$faqs_json = isset($_POST['faqs']) ? wp_unslash($_POST['faqs']) : '';
		$include_schema = isset($_POST['include_schema']) ? sanitize_text_field(wp_unslash($_POST['include_schema'])) === 'true' : true;
		
		if (!$post_id || empty($faqs_json)) {
			wp_send_json_error(array('message' => 'パラメータが不足しています。'));
			return;
		}
		
		$faqs = json_decode($faqs_json, true);
		
		if (empty($faqs) || !is_array($faqs)) {
			wp_send_json_error(array('message' => 'FAQデータが無効です。'));
			return;
		}
		
		$post = get_post($post_id);
		if (!$post) {
			wp_send_json_error(array('message' => '投稿が見つかりません。'));
			return;
		}
		
		$faq_html = $this->generate_faq_html($faqs);
		$new_content = $post->post_content . "\n\n" . $faq_html;
		
		$result = wp_update_post(array(
			'ID' => $post_id,
			'post_content' => $new_content
		), true);
		
		if (is_wp_error($result)) {
			wp_send_json_error(array('message' => '記事の更新に失敗しました。'));
			return;
		}
		
		if ($include_schema) {
			$schema = $this->generate_faq_schema($faqs);
			update_post_meta($post_id, '_probonoseo_faq_schema', $schema);
		}
		
		wp_send_json_success(array('message' => 'FAQを挿入しました。'));
	}
}

ProbonoSEO_AI_FAQ::get_instance();