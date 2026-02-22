<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Gap {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_analyze_ai_gap', array($this, 'ajax_analyze_gap'));
	}
	
	public function analyze_gap($post_id) {
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
		
		if (get_option('probonoseo_pro_gap_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AIコンテンツギャップ分析機能が無効になっています。設定画面で有効化してください。'
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
				'content' => 'あなたは日本語SEOコンテンツ分析の専門家です。記事に不足しているトピックや情報を特定し、コンテンツの網羅性を高めるための提案を行ってください。'
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
		
		$gap = $this->parse_gap_response($response['content']);
		
		if (empty($gap['missing_topics'])) {
			return array(
				'success' => false,
				'message' => 'コンテンツギャップの分析に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'gap' => $gap
		);
	}
	
	private function build_prompt($title, $content) {
		$prompt = "以下の記事のコンテンツギャップを分析してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【分析項目】\n";
		$prompt .= "1. 不足しているトピック（このテーマで一般的に期待される内容で記事にないもの、3〜7個）\n";
		$prompt .= "2. 追加すべき具体例・事例\n";
		$prompt .= "3. 追加すべきデータ・統計\n";
		$prompt .= "4. FAQ候補（読者が持ちそうな疑問、3〜5個）\n";
		$prompt .= "5. 網羅性スコア（0-100点）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---不足トピック---\n";
		$prompt .= "[トピック1]\n";
		$prompt .= "[トピック2]\n";
		$prompt .= "---追加事例---\n";
		$prompt .= "[事例提案1]\n";
		$prompt .= "[事例提案2]\n";
		$prompt .= "---追加データ---\n";
		$prompt .= "[データ提案1]\n";
		$prompt .= "[データ提案2]\n";
		$prompt .= "---FAQ候補---\n";
		$prompt .= "[質問1]\n";
		$prompt .= "[質問2]\n";
		$prompt .= "---網羅性スコア---\n";
		$prompt .= "[点数]/100\n";
		return $prompt;
	}
	
	private function parse_gap_response($response) {
		$result = array(
			'missing_topics' => array(),
			'example_suggestions' => array(),
			'data_suggestions' => array(),
			'faq_candidates' => array(),
			'coverage_score' => 0
		);
		
		if (preg_match('/---不足トピック---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['missing_topics'][] = $line;
				}
			}
		}
		
		if (preg_match('/---追加事例---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['example_suggestions'][] = $line;
				}
			}
		}
		
		if (preg_match('/---追加データ---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['data_suggestions'][] = $line;
				}
			}
		}
		
		if (preg_match('/---FAQ候補---\s*\n(.+?)(?=---|$)/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line)) {
					$result['faq_candidates'][] = $line;
				}
			}
		}
		
		if (preg_match('/---網羅性スコア---\s*\n(\d+)/u', $response, $matches)) {
			$result['coverage_score'] = intval($matches[1]);
		}
		
		return $result;
	}
	
	public function ajax_analyze_gap() {
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
		
		$result = $this->analyze_gap($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Gap::get_instance();