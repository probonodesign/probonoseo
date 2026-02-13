<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Readability {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_check_ai_readability', array($this, 'ajax_check_readability'));
	}
	
	public function check_readability($post_id) {
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
		
		if (get_option('probonoseo_pro_readability_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI読みやすさチェック機能が無効になっています。設定画面で有効化してください。'
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
		$basic_stats = $this->calculate_basic_stats($content);
		$prompt = $this->build_prompt($title, $content, $basic_stats);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語文章の読みやすさを分析する専門家です。文章の読みやすさを多角的に評価し、具体的な改善提案を行ってください。'
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
		
		$analysis = $this->parse_readability_response($response['content']);
		$analysis['basic_stats'] = $basic_stats;
		
		return array(
			'success' => true,
			'readability' => $analysis
		);
	}
	
	private function calculate_basic_stats($content) {
		$sentences = preg_split('/[。！？\.\!\?]+/u', $content, -1, PREG_SPLIT_NO_EMPTY);
		$sentence_count = count($sentences);
		$total_chars = mb_strlen($content);
		$avg_sentence_length = $sentence_count > 0 ? round($total_chars / $sentence_count, 1) : 0;
		$paragraphs = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
		$paragraph_count = count($paragraphs);
		$kanji_count = preg_match_all('/[\x{4E00}-\x{9FFF}]/u', $content);
		$hiragana_count = preg_match_all('/[\x{3040}-\x{309F}]/u', $content);
		$katakana_count = preg_match_all('/[\x{30A0}-\x{30FF}]/u', $content);
		$kanji_ratio = $total_chars > 0 ? round(($kanji_count / $total_chars) * 100, 1) : 0;
		
		return array(
			'total_chars' => $total_chars,
			'sentence_count' => $sentence_count,
			'avg_sentence_length' => $avg_sentence_length,
			'paragraph_count' => $paragraph_count,
			'kanji_ratio' => $kanji_ratio,
			'kanji_count' => $kanji_count,
			'hiragana_count' => $hiragana_count,
			'katakana_count' => $katakana_count
		);
	}
	
	private function build_prompt($title, $content, $stats) {
		$prompt = "以下の記事の読みやすさを分析してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【基本統計】\n";
		$prompt .= "- 総文字数: " . $stats['total_chars'] . "文字\n";
		$prompt .= "- 文の数: " . $stats['sentence_count'] . "文\n";
		$prompt .= "- 平均文長: " . $stats['avg_sentence_length'] . "文字\n";
		$prompt .= "- 漢字率: " . $stats['kanji_ratio'] . "%\n\n";
		$prompt .= "【評価項目】\n";
		$prompt .= "1. 総合読みやすさスコア（0-100点）\n";
		$prompt .= "2. 文の長さ評価（短すぎ/適切/長すぎ）\n";
		$prompt .= "3. 漢字・ひらがなバランス評価\n";
		$prompt .= "4. 接続詞の使い方評価\n";
		$prompt .= "5. 段落構成評価\n";
		$prompt .= "6. 専門用語の使用評価\n";
		$prompt .= "7. 想定読者層（小学生/中学生/高校生/一般成人/専門家）\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---スコア---\n";
		$prompt .= "総合: [点数]/100\n";
		$prompt .= "---評価---\n";
		$prompt .= "文の長さ: [評価]\n";
		$prompt .= "漢字バランス: [評価]\n";
		$prompt .= "接続詞: [評価]\n";
		$prompt .= "段落構成: [評価]\n";
		$prompt .= "専門用語: [評価]\n";
		$prompt .= "想定読者: [読者層]\n";
		$prompt .= "---改善提案---\n";
		$prompt .= "[具体的な改善提案を3〜5個]\n";
		return $prompt;
	}
	
	private function parse_readability_response($response) {
		$result = array(
			'score' => 0,
			'evaluations' => array(),
			'target_audience' => '',
			'suggestions' => array()
		);
		
		if (preg_match('/総合[：:]\s*(\d+)/u', $response, $matches)) {
			$result['score'] = intval($matches[1]);
		}
		
		$eval_patterns = array(
			'sentence_length' => '/文の長さ[：:]\s*(.+?)(?=\n|$)/u',
			'kanji_balance' => '/漢字バランス[：:]\s*(.+?)(?=\n|$)/u',
			'conjunctions' => '/接続詞[：:]\s*(.+?)(?=\n|$)/u',
			'paragraph_structure' => '/段落構成[：:]\s*(.+?)(?=\n|$)/u',
			'technical_terms' => '/専門用語[：:]\s*(.+?)(?=\n|$)/u'
		);
		
		foreach ($eval_patterns as $key => $pattern) {
			if (preg_match($pattern, $response, $matches)) {
				$result['evaluations'][$key] = trim($matches[1]);
			}
		}
		
		if (preg_match('/想定読者[：:]\s*(.+?)(?=\n|$)/u', $response, $matches)) {
			$result['target_audience'] = trim($matches[1]);
		}
		
		if (preg_match('/---改善提案---\s*\n(.*)$/us', $response, $matches)) {
			$lines = explode("\n", trim($matches[1]));
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				if (!empty($line) && mb_strlen($line) > 5) {
					$result['suggestions'][] = $line;
				}
			}
		}
		
		return $result;
	}
	
	public function ajax_check_readability() {
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
		
		$result = $this->check_readability($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Readability::get_instance();