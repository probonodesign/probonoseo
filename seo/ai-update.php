<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Update {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_suggest_ai_update', array($this, 'ajax_suggest_update'));
	}
	
	public function suggest_update($post_id) {
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
		
		if (get_option('probonoseo_pro_update_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AIコンテンツ更新提案機能が無効になっています。設定画面で有効化してください。'
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
		$post_date = $post->post_date;
		$modified_date = $post->post_modified;
		
		if (mb_strlen($content) < 100) {
			return array(
				'success' => false,
				'message' => '記事の内容が短すぎます（100文字以上必要）。'
			);
		}
		
		$content = mb_substr($content, 0, 4000);
		$prompt = $this->build_prompt($title, $content, $post_date, $modified_date);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOコンテンツ管理の専門家です。記事の鮮度と価値を維持するための更新提案を行ってください。'
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
		
		$updates = $this->parse_update_response($response['content']);
		
		return array(
			'success' => true,
			'updates' => $updates
		);
	}
	
	private function build_prompt($title, $content, $post_date, $modified_date) {
		$post_date_formatted = wp_date('Y年m月d日', strtotime($post_date));
		$modified_date_formatted = wp_date('Y年m月d日', strtotime($modified_date));
		$current_date = wp_date('Y年m月d日');
		
		$prompt = "以下の記事の更新提案を行ってください。\n\n";
		$prompt .= "【記事情報】\n";
		$prompt .= "タイトル: {$title}\n";
		$prompt .= "公開日: {$post_date_formatted}\n";
		$prompt .= "最終更新日: {$modified_date_formatted}\n";
		$prompt .= "現在の日付: {$current_date}\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【分析項目】\n";
		$prompt .= "1. 古くなっている可能性のある情報（年号、バージョン、統計データなど）\n";
		$prompt .= "2. 追加すべき最新情報\n";
		$prompt .= "3. 削除または修正すべき情報\n";
		$prompt .= "4. 構成・構造の改善提案\n";
		$prompt .= "5. 更新優先度（高/中/低）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---古い情報---\n";
		$prompt .= "[指摘1]\n";
		$prompt .= "[指摘2]\n";
		$prompt .= "---追加提案---\n";
		$prompt .= "[追加1]\n";
		$prompt .= "[追加2]\n";
		$prompt .= "---修正提案---\n";
		$prompt .= "[修正1]\n";
		$prompt .= "[修正2]\n";
		$prompt .= "---構成改善---\n";
		$prompt .= "[改善1]\n";
		$prompt .= "[改善2]\n";
		$prompt .= "---更新優先度---\n";
		$prompt .= "[優先度]: [理由]\n";
		return $prompt;
	}
	
	private function parse_update_response($response) {
		$result = array(
			'outdated' => array(),
			'additions' => array(),
			'modifications' => array(),
			'structure' => array(),
			'priority' => '',
			'priority_reason' => ''
		);
		
		if (preg_match('/---古い情報---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['outdated'][] = $line;
				}
			}
		}
		
		if (preg_match('/---追加提案---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['additions'][] = $line;
				}
			}
		}
		
		if (preg_match('/---修正提案---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['modifications'][] = $line;
				}
			}
		}
		
		if (preg_match('/---構成改善---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 3) {
					$result['structure'][] = $line;
				}
			}
		}
		
		if (preg_match('/---更新優先度---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$priority_line = trim($matches[1]);
			if (preg_match('/^(高|中|低)[：:]\s*(.+)$/u', $priority_line, $m)) {
				$result['priority'] = $m[1];
				$result['priority_reason'] = trim($m[2]);
			} elseif (preg_match('/(高|中|低)/u', $priority_line, $m)) {
				$result['priority'] = $m[1];
				$result['priority_reason'] = $priority_line;
			}
		}
		
		return $result;
	}
	
	public function ajax_suggest_update() {
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
		
		$result = $this->suggest_update($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Update::get_instance();