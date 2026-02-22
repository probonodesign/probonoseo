<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Outline {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_outline', array($this, 'ajax_generate_outline'));
		add_action('wp_ajax_probonoseo_apply_ai_outline', array($this, 'ajax_apply_outline'));
	}
	
	public function generate_outline($post_id, $keyword = '') {
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
		
		if (get_option('probonoseo_pro_outline_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => '記事構成案生成AI機能が無効になっています。設定画面で有効化してください。'
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
		$content = mb_substr($content, 0, 1500);
		
		if (empty($keyword)) {
			$keyword = $title;
		}
		
		$prompt = $this->build_prompt($title, $keyword, $content);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家であり、記事構成のプロです。読者のニーズを満たし、検索エンジンで上位表示されやすい記事構成を提案してください。'
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
		
		$outline = $this->parse_outline_response($response['content']);
		
		if (empty($outline)) {
			return array(
				'success' => false,
				'message' => '記事構成案の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'outline' => $outline,
			'keyword' => $keyword
		);
	}
	
	private function build_prompt($title, $keyword, $content) {
		$prompt = "以下の情報を元に、SEOに最適化された記事構成案（目次）を作成してください。\n\n";
		
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【メインキーワード】\n";
		$prompt .= $keyword . "\n\n";
		
		if (!empty($content)) {
			$prompt .= "【既存の記事内容（参考）】\n";
			$prompt .= $content . "\n\n";
		}
		
		$prompt .= "【記事構成作成のルール】\n";
		$prompt .= "1. H2見出しを4〜7個程度作成\n";
		$prompt .= "2. 各H2の下に必要に応じてH3を2〜4個作成\n";
		$prompt .= "3. 読者の検索意図を満たす構成にする\n";
		$prompt .= "4. 論理的な流れで情報を整理する\n";
		$prompt .= "5. メインキーワードや関連語を自然に含める\n";
		$prompt .= "6. 導入→本論→まとめの流れを意識する\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "以下の形式で出力してください：\n";
		$prompt .= "H2: [見出しテキスト]\n";
		$prompt .= "  H3: [見出しテキスト]\n";
		$prompt .= "  H3: [見出しテキスト]\n";
		$prompt .= "H2: [見出しテキスト]\n";
		$prompt .= "...\n\n";
		$prompt .= "各見出しの後に、その見出しで書く内容を1行で簡潔に説明してください。";
		
		return $prompt;
	}
	
	private function parse_outline_response($response) {
		$outline = array();
		$lines = explode("\n", $response);
		
		$current_h2_index = -1;
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			if (empty($line)) {
				continue;
			}
			
			if (preg_match('/^H2[：:]\s*(.+?)(?:\s*[-–—]\s*(.+))?$/u', $line, $matches)) {
				$current_h2_index++;
				$outline[] = array(
					'level' => 2,
					'text' => trim($matches[1]),
					'description' => isset($matches[2]) ? trim($matches[2]) : '',
					'children' => array()
				);
			} elseif (preg_match('/^\s*H3[：:]\s*(.+?)(?:\s*[-–—]\s*(.+))?$/u', $line, $matches)) {
				if ($current_h2_index >= 0) {
					$outline[$current_h2_index]['children'][] = array(
						'level' => 3,
						'text' => trim($matches[1]),
						'description' => isset($matches[2]) ? trim($matches[2]) : ''
					);
				}
			} elseif ($current_h2_index >= 0 && !empty($outline[$current_h2_index])) {
				$last_item = end($outline);
				if (!empty($last_item) && empty($last_item['description']) && mb_strlen($line) < 100) {
					$outline[$current_h2_index]['description'] = $line;
				}
			}
		}
		
		return $outline;
	}
	
	public function generate_outline_html($outline) {
		$html = '';
		
		foreach ($outline as $h2) {
			$html .= '<!-- wp:heading -->' . "\n";
			$html .= '<h2 class="wp-block-heading">' . esc_html($h2['text']) . '</h2>' . "\n";
			$html .= '<!-- /wp:heading -->' . "\n\n";
			
			$html .= '<!-- wp:paragraph -->' . "\n";
			$html .= '<p>【ここに「' . esc_html($h2['text']) . '」の内容を記述】</p>' . "\n";
			$html .= '<!-- /wp:paragraph -->' . "\n\n";
			
			if (!empty($h2['children'])) {
				foreach ($h2['children'] as $h3) {
					$html .= '<!-- wp:heading {"level":3} -->' . "\n";
					$html .= '<h3 class="wp-block-heading">' . esc_html($h3['text']) . '</h3>' . "\n";
					$html .= '<!-- /wp:heading -->' . "\n\n";
					
					$html .= '<!-- wp:paragraph -->' . "\n";
					$html .= '<p>【ここに「' . esc_html($h3['text']) . '」の内容を記述】</p>' . "\n";
					$html .= '<!-- /wp:paragraph -->' . "\n\n";
				}
			}
		}
		
		return $html;
	}
	
	public function ajax_generate_outline() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		$result = $this->generate_outline($post_id, $keyword);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
	
	public function ajax_apply_outline() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data, sanitized after json_decode
		$outline_json = isset($_POST['outline']) ? wp_unslash($_POST['outline']) : '';
		
		if (!$post_id || empty($outline_json)) {
			wp_send_json_error(array('message' => 'パラメータが不足しています。'));
			return;
		}
		
		$outline = json_decode($outline_json, true);
		
		if (empty($outline) || !is_array($outline)) {
			wp_send_json_error(array('message' => '構成案のデータが無効です。'));
			return;
		}
		
		$post = get_post($post_id);
		if (!$post) {
			wp_send_json_error(array('message' => '投稿が見つかりません。'));
			return;
		}
		
		$outline_html = $this->generate_outline_html($outline);
		$new_content = $outline_html . "\n\n" . $post->post_content;
		
		$result = wp_update_post(array(
			'ID' => $post_id,
			'post_content' => $new_content
		), true);
		
		if (is_wp_error($result)) {
			wp_send_json_error(array('message' => '記事の更新に失敗しました。'));
			return;
		}
		
		wp_send_json_success(array('message' => '記事構成を挿入しました。'));
	}
}

ProbonoSEO_AI_Outline::get_instance();