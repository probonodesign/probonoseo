<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Title {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_title', array($this, 'ajax_generate_title'));
		add_action('wp_ajax_probonoseo_apply_ai_title', array($this, 'ajax_apply_title'));
	}
	
	public function generate_title_suggestions($post_id) {
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
		
		if (get_option('probonoseo_pro_title_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'タイトル提案AI機能が無効になっています。設定画面で有効化してください。'
			);
		}
		
		$openai = ProbonoSEO_OpenAI_API::get_instance();
		if (!$openai->is_api_key_set()) {
			return array(
				'success' => false,
				'message' => 'OpenAI APIキーが設定されていません。'
			);
		}
		
		$current_title = $post->post_title;
		$content = wp_strip_all_tags($post->post_content);
		$content = mb_substr($content, 0, 2000);
		$categories = $this->get_post_categories($post_id);
		$tags = $this->get_post_tags($post_id);
		
		$prompt = $this->build_prompt($current_title, $content, $categories, $tags);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。検索エンジンで上位表示されやすく、クリックされやすい魅力的なタイトルを提案してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 500,
			'temperature' => 0.8
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$titles = $this->parse_title_response($response['content']);
		
		if (empty($titles)) {
			return array(
				'success' => false,
				'message' => 'タイトルの生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'titles' => $titles,
			'current_title' => $current_title
		);
	}
	
	private function build_prompt($current_title, $content, $categories, $tags) {
		$prompt = "以下の記事に対して、SEOに最適化された魅力的な日本語タイトルを3パターン提案してください。\n\n";
		
		$prompt .= "【現在のタイトル】\n";
		$prompt .= $current_title . "\n\n";
		
		$prompt .= "【記事の内容（冒頭部分）】\n";
		$prompt .= $content . "\n\n";
		
		if (!empty($categories)) {
			$prompt .= "【カテゴリ】\n";
			$prompt .= implode(', ', $categories) . "\n\n";
		}
		
		if (!empty($tags)) {
			$prompt .= "【タグ】\n";
			$prompt .= implode(', ', $tags) . "\n\n";
		}
		
		$prompt .= "【タイトル作成のルール】\n";
		$prompt .= "1. 文字数は30〜60文字（全角）が理想\n";
		$prompt .= "2. 検索キーワードを自然に含める\n";
		$prompt .= "3. 読者の興味を引く表現を使う\n";
		$prompt .= "4. 数字や具体性を入れると効果的\n";
		$prompt .= "5. 【】や「」などの記号を適切に活用\n\n";
		
		$prompt .= "【出力形式】\n";
		$prompt .= "以下の形式で3つのタイトル案を出力してください：\n";
		$prompt .= "1. [タイトル案1]\n";
		$prompt .= "2. [タイトル案2]\n";
		$prompt .= "3. [タイトル案3]\n\n";
		$prompt .= "各タイトルの後に、そのタイトルの特徴を1行で説明してください。";
		
		return $prompt;
	}
	
	private function parse_title_response($response) {
		$titles = array();
		$lines = explode("\n", $response);
		
		$current_title = null;
		$current_description = '';
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			if (empty($line)) {
				continue;
			}
			
			if (preg_match('/^[1-3][\.\)]\s*(.+)$/', $line, $matches)) {
				if ($current_title !== null) {
					$titles[] = array(
						'title' => $current_title,
						'description' => trim($current_description),
						'length' => mb_strlen($current_title)
					);
				}
				$current_title = trim($matches[1]);
				$current_title = preg_replace('/^\[(.+)\]$/', '$1', $current_title);
				$current_title = preg_replace('/^「(.+)」$/', '$1', $current_title);
				$current_description = '';
			} elseif ($current_title !== null && !preg_match('/^[1-3][\.\)]/', $line)) {
				$current_description .= $line . ' ';
			}
		}
		
		if ($current_title !== null) {
			$titles[] = array(
				'title' => $current_title,
				'description' => trim($current_description),
				'length' => mb_strlen($current_title)
			);
		}
		
		return array_slice($titles, 0, 3);
	}
	
	private function get_post_categories($post_id) {
		$categories = get_the_category($post_id);
		$names = array();
		
		if ($categories) {
			foreach ($categories as $cat) {
				$names[] = $cat->name;
			}
		}
		
		return $names;
	}
	
	private function get_post_tags($post_id) {
		$tags = get_the_tags($post_id);
		$names = array();
		
		if ($tags) {
			foreach ($tags as $tag) {
				$names[] = $tag->name;
			}
		}
		
		return $names;
	}
	
	public function ajax_generate_title() {
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
		
		$result = $this->generate_title_suggestions($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
	
	public function ajax_apply_title() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$new_title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
		
		if (!$post_id || empty($new_title)) {
			wp_send_json_error(array('message' => 'パラメータが不足しています。'));
			return;
		}
		
		$result = wp_update_post(array(
			'ID' => $post_id,
			'post_title' => $new_title
		), true);
		
		if (is_wp_error($result)) {
			wp_send_json_error(array('message' => 'タイトルの更新に失敗しました。'));
			return;
		}
		
		wp_send_json_success(array('message' => 'タイトルを更新しました。'));
	}
}

ProbonoSEO_AI_Title::get_instance();