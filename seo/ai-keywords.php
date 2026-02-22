<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Keywords {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_keywords', array($this, 'ajax_generate_keywords'));
	}
	
	public function generate_keywords($post_id) {
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
		
		if (get_option('probonoseo_pro_keywords_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI関連キーワード抽出機能が無効になっています。設定画面で有効化してください。'
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
				'content' => 'あなたは日本語SEOの専門家です。記事から重要なキーワードと関連キーワードを抽出し、SEO戦略に役立つ情報を提供してください。'
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
		
		$keywords = $this->parse_keywords_response($response['content']);
		
		if (empty($keywords)) {
			return array(
				'success' => false,
				'message' => 'キーワードの抽出に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'keywords' => $keywords
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事から関連キーワードを抽出してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【抽出ルール】\n";
		$prompt .= "1. メインキーワード：記事の中心となるキーワード（1〜2個）\n";
		$prompt .= "2. サブキーワード：メインを補完するキーワード（3〜5個）\n";
		$prompt .= "3. 関連キーワード：記事に関連する周辺キーワード（5〜10個）\n";
		$prompt .= "4. ロングテールキーワード：2〜4語の複合キーワード（3〜5個）\n";
		$prompt .= "5. 各キーワードの検索意図（情報収集/比較検討/購入意図）も判定\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---メイン---\n";
		$prompt .= "キーワード1 | 検索意図\n";
		$prompt .= "---サブ---\n";
		$prompt .= "キーワード1 | 検索意図\n";
		$prompt .= "---関連---\n";
		$prompt .= "キーワード1 | 検索意図\n";
		$prompt .= "---ロングテール---\n";
		$prompt .= "キーワード1 | 検索意図\n";
		return $prompt;
	}
	
	private function parse_keywords_response($response) {
		$keywords = array(
			'main' => array(),
			'sub' => array(),
			'related' => array(),
			'longtail' => array()
		);
		
		$current_type = null;
		$lines = explode("\n", $response);
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			if (empty($line)) {
				continue;
			}
			
			if (preg_match('/---メイン---/u', $line)) {
				$current_type = 'main';
				continue;
			} elseif (preg_match('/---サブ---/u', $line)) {
				$current_type = 'sub';
				continue;
			} elseif (preg_match('/---関連---/u', $line)) {
				$current_type = 'related';
				continue;
			} elseif (preg_match('/---ロングテール---/u', $line)) {
				$current_type = 'longtail';
				continue;
			}
			
			if ($current_type && preg_match('/^(.+?)\s*\|\s*(.+)$/u', $line, $matches)) {
				$keywords[$current_type][] = array(
					'keyword' => trim($matches[1]),
					'intent' => trim($matches[2])
				);
			} elseif ($current_type && !empty($line) && !preg_match('/^---/', $line)) {
				$keywords[$current_type][] = array(
					'keyword' => trim($line),
					'intent' => ''
				);
			}
		}
		
		return $keywords;
	}
	
	public function ajax_generate_keywords() {
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
		
		$result = $this->generate_keywords($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Keywords::get_instance();