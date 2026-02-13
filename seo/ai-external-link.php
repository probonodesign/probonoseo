<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_ExternalLink {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_suggest_ai_external', array($this, 'ajax_suggest_external'));
	}
	
	public function suggest_external($post_id) {
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
		
		if (get_option('probonoseo_pro_external_link_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI外部リンク提案機能が無効になっています。設定画面で有効化してください。'
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
		$prompt = $this->build_prompt($title, $content);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。記事の信頼性と価値を高めるための外部リンク先を提案してください。権威性のある情報源を優先してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1200,
			'temperature' => 0.5
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$links = $this->parse_external_link_response($response['content']);
		
		if (empty($links)) {
			return array(
				'success' => false,
				'message' => '外部リンク提案の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'links' => $links
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事に追加すべき外部リンク先を提案してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【提案ルール】\n";
		$prompt .= "1. 公式サイト、政府機関、学術機関、業界団体など権威性の高いサイトを優先\n";
		$prompt .= "2. 記事の主張を裏付けるデータソースや統計情報\n";
		$prompt .= "3. 読者が詳しく知りたい場合の参考資料\n";
		$prompt .= "4. 日本語サイトを優先（必要に応じて英語サイトも可）\n";
		$prompt .= "5. 3〜7個程度提案\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---リンク1---\n";
		$prompt .= "サイト名: [サイト名]\n";
		$prompt .= "ドメイン: [example.com]\n";
		$prompt .= "種別: [公式/政府/学術/メディア/その他]\n";
		$prompt .= "推奨理由: [なぜこのリンクが有効か]\n";
		$prompt .= "リンクすべき箇所: [記事内のどの部分にリンクすべきか]\n";
		return $prompt;
	}
	
	private function parse_external_link_response($response) {
		$links = array();
		
		if (preg_match_all('/---リンク\d+---\s*\n(.+?)(?=---リンク|$)/us', $response, $matches)) {
			foreach ($matches[1] as $block) {
				$link = array();
				
				if (preg_match('/サイト名[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['title'] = trim($m[1]);
				}
				
				if (preg_match('/ドメイン[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['domain'] = trim($m[1]);
				}
				
				if (preg_match('/種別[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['type'] = trim($m[1]);
				}
				
				if (preg_match('/推奨理由[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['reason'] = trim($m[1]);
				}
				
				if (preg_match('/リンクすべき箇所[：:]\s*(.+?)(?=\n---|$)/us', $block, $m)) {
					$link['position'] = trim($m[1]);
				}
				
				if (!empty($link['title']) && !empty($link['domain'])) {
					$links[] = $link;
				}
			}
		}
		
		return $links;
	}
	
	public function ajax_suggest_external() {
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
		
		$result = $this->suggest_external($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_ExternalLink::get_instance();