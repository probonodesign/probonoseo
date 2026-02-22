<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Heading {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_heading', array($this, 'ajax_generate_heading'));
		add_action('wp_ajax_probonoseo_apply_ai_heading', array($this, 'ajax_apply_heading'));
	}
	
	public function generate_heading_suggestions($post_id) {
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
		
		if (get_option('probonoseo_pro_heading_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => '見出し改善AI機能が無効になっています。設定画面で有効化してください。'
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
		$content = $post->post_content;
		$current_headings = $this->extract_headings($content);
		
		if (empty($current_headings)) {
			return array(
				'success' => false,
				'message' => '記事内に見出し（H2/H3）が見つかりません。'
			);
		}
		
		$prompt = $this->build_prompt($title, $current_headings);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。記事の見出し（H2/H3）を、読みやすく検索エンジンに最適化された形に改善提案してください。'
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
		
		$suggestions = $this->parse_heading_response($response['content'], $current_headings);
		
		if (empty($suggestions)) {
			return array(
				'success' => false,
				'message' => '見出し提案の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'suggestions' => $suggestions,
			'current_headings' => $current_headings
		);
	}
	
	private function extract_headings($content) {
		$headings = array();
		
		preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER);
		
		foreach ($matches as $index => $match) {
			$level = intval($match[1]);
			$text = wp_strip_all_tags($match[2]);
			$text = trim($text);
			
			if (!empty($text)) {
				$headings[] = array(
					'index' => $index,
					'level' => $level,
					'text' => $text,
					'original_html' => $match[0]
				);
			}
		}
		
		return $headings;
	}
	
	private function build_prompt($title, $headings) {
		$prompt = "以下の記事の見出し構成を改善してください。\n\n";
		
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【現在の見出し構成】\n";
		foreach ($headings as $h) {
			$indent = $h['level'] === 3 ? '  ' : '';
			$prompt .= $indent . "H" . $h['level'] . ": " . $h['text'] . "\n";
		}
		$prompt .= "\n";
		
		$prompt .= "【見出し改善のルール】\n";
		$prompt .= "1. 読者が内容を予測できる具体的な表現にする\n";
		$prompt .= "2. 検索キーワードを自然に含める\n";
		$prompt .= "3. 長すぎる見出しは簡潔に（20〜40文字が理想）\n";
		$prompt .= "4. 不自然な語順や冗長な表現を修正\n";
		$prompt .= "5. 読者の疑問や関心を引く表現を使う\n";
		$prompt .= "6. 見出しの階層構造（H2→H3）を維持する\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "各見出しに対して、以下の形式で改善案を出力してください：\n";
		$prompt .= "---\n";
		$prompt .= "番号: [見出し番号（0から開始）]\n";
		$prompt .= "現在: [現在の見出し]\n";
		$prompt .= "提案: [改善後の見出し]\n";
		$prompt .= "理由: [改善理由を1行で]\n";
		$prompt .= "---\n\n";
		$prompt .= "改善が不要な見出しは「提案: （変更なし）」としてください。";
		
		return $prompt;
	}
	
	private function parse_heading_response($response, $current_headings) {
		$suggestions = array();
		$blocks = preg_split('/---+/', $response);
		
		foreach ($blocks as $block) {
			$block = trim($block);
			if (empty($block)) {
				continue;
			}
			
			$index = null;
			$current = '';
			$suggested = '';
			$reason = '';
			
			if (preg_match('/番号[：:]\s*(\d+)/u', $block, $m)) {
				$index = intval($m[1]);
			}
			
			if (preg_match('/現在[：:]\s*(.+?)(?=\n|提案|$)/us', $block, $m)) {
				$current = trim($m[1]);
			}
			
			if (preg_match('/提案[：:]\s*(.+?)(?=\n|理由|$)/us', $block, $m)) {
				$suggested = trim($m[1]);
			}
			
			if (preg_match('/理由[：:]\s*(.+?)$/us', $block, $m)) {
				$reason = trim($m[1]);
			}
			
			if ($index !== null && isset($current_headings[$index])) {
				$is_changed = $suggested !== '（変更なし）' && $suggested !== $current_headings[$index]['text'];
				
				$suggestions[] = array(
					'index' => $index,
					'level' => $current_headings[$index]['level'],
					'current' => $current_headings[$index]['text'],
					'suggested' => $is_changed ? $suggested : $current_headings[$index]['text'],
					'reason' => $reason,
					'is_changed' => $is_changed,
					'original_html' => $current_headings[$index]['original_html']
				);
			}
		}
		
		usort($suggestions, function($a, $b) {
			return $a['index'] - $b['index'];
		});
		
		return $suggestions;
	}
	
	public function ajax_generate_heading() {
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
		
		$result = $this->generate_heading_suggestions($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
	
	public function ajax_apply_heading() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$original_html = isset($_POST['original_html']) ? wp_kses_post(wp_unslash($_POST['original_html'])) : '';
		$new_text = isset($_POST['new_text']) ? sanitize_text_field(wp_unslash($_POST['new_text'])) : '';
		$level = isset($_POST['level']) ? intval($_POST['level']) : 2;
		
		if (!$post_id || empty($original_html) || empty($new_text)) {
			wp_send_json_error(array('message' => 'パラメータが不足しています。'));
			return;
		}
		
		$post = get_post($post_id);
		if (!$post) {
			wp_send_json_error(array('message' => '投稿が見つかりません。'));
			return;
		}
		
		$new_html = '<h' . $level . '>' . esc_html($new_text) . '</h' . $level . '>';
		$new_content = str_replace($original_html, $new_html, $post->post_content);
		
		if ($new_content === $post->post_content) {
			wp_send_json_error(array('message' => '見出しの置換に失敗しました。'));
			return;
		}
		
		$result = wp_update_post(array(
			'ID' => $post_id,
			'post_content' => $new_content
		), true);
		
		if (is_wp_error($result)) {
			wp_send_json_error(array('message' => '見出しの更新に失敗しました。'));
			return;
		}
		
		wp_send_json_success(array(
			'message' => '見出しを更新しました。',
			'new_html' => $new_html
		));
	}
}

ProbonoSEO_AI_Heading::get_instance();