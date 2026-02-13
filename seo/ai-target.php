<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Target {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_analyze_ai_target', array($this, 'ajax_analyze_target'));
	}
	
	public function analyze_target($post_id) {
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
		
		if (get_option('probonoseo_pro_target_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AIターゲット読者分析機能が無効になっています。設定画面で有効化してください。'
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
				'content' => 'あなたは日本語コンテンツマーケティングの専門家です。記事の内容からターゲット読者像を詳細に分析してください。'
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
		
		$target = $this->parse_target_response($response['content']);
		
		if (empty($target['age_group'])) {
			return array(
				'success' => false,
				'message' => 'ターゲット読者の分析に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'target' => $target
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事のターゲット読者を分析してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【分析項目】\n";
		$prompt .= "1. 想定年齢層（10代/20代/30代/40代/50代以上/全年齢）\n";
		$prompt .= "2. 知識レベル（初心者/中級者/上級者/専門家）\n";
		$prompt .= "3. 興味・関心分野（3〜5個）\n";
		$prompt .= "4. 想定される悩み・課題（3〜5個）\n";
		$prompt .= "5. 職業・立場の想定\n";
		$prompt .= "6. 読者のゴール（この記事で何を得たいか）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---年齢層---\n";
		$prompt .= "[年齢層]\n";
		$prompt .= "---知識レベル---\n";
		$prompt .= "[レベル]\n";
		$prompt .= "---興味関心---\n";
		$prompt .= "[興味1]\n";
		$prompt .= "[興味2]\n";
		$prompt .= "---悩み課題---\n";
		$prompt .= "[悩み1]\n";
		$prompt .= "[悩み2]\n";
		$prompt .= "---職業立場---\n";
		$prompt .= "[職業・立場]\n";
		$prompt .= "---読者ゴール---\n";
		$prompt .= "[ゴール]\n";
		return $prompt;
	}
	
	private function parse_target_response($response) {
		$result = array(
			'age_group' => '',
			'knowledge_level' => '',
			'interests' => array(),
			'challenges' => array(),
			'occupation' => '',
			'goals' => ''
		);
		
		if (preg_match('/---年齢層---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['age_group'] = trim($matches[1]);
		}
		
		if (preg_match('/---知識レベル---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['knowledge_level'] = trim($matches[1]);
		}
		
		if (preg_match('/---興味関心---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['interests'][] = $line;
				}
			}
		}
		
		if (preg_match('/---悩み課題---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['challenges'][] = $line;
				}
			}
		}
		
		if (preg_match('/---職業立場---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['occupation'] = trim($matches[1]);
		}
		
		if (preg_match('/---読者ゴール---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$result['goals'] = trim($matches[1]);
		}
		
		return $result;
	}
	
	public function ajax_analyze_target() {
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
		
		$result = $this->analyze_target($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Target::get_instance();