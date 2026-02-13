<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Summary {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_summary', array($this, 'ajax_generate_summary'));
	}
	
	public function generate_summary($post_id, $style = 'points') {
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
		
		if (get_option('probonoseo_pro_summary_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => '要点サマリー生成機能が無効になっています。設定画面で有効化してください。'
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
		$prompt = $this->build_prompt($title, $content, $style);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語の文章要約の専門家です。記事の重要なポイントを抽出し、読者が素早く内容を把握できる要約を作成してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 800,
			'temperature' => 0.5
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$summary = $this->parse_summary_response($response['content'], $style);
		
		if (empty($summary)) {
			return array(
				'success' => false,
				'message' => '要約の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'summary' => $summary,
			'style' => $style
		);
	}
	
	private function build_prompt($title, $content, $style) {
		$prompt = "以下の記事を要約してください。\n\n";
		
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		
		$prompt .= "【記事本文】\n";
		$prompt .= $content . "\n\n";
		
		if ($style === 'points') {
			$prompt .= "【要約形式】\n";
			$prompt .= "箇条書き形式（3〜5個のポイント）で要約してください。\n";
			$prompt .= "各ポイントは30〜50文字程度で簡潔に。\n\n";
			$prompt .= "【出力形式】\n";
			$prompt .= "・ポイント1\n";
			$prompt .= "・ポイント2\n";
			$prompt .= "・ポイント3\n";
		} elseif ($style === 'paragraph') {
			$prompt .= "【要約形式】\n";
			$prompt .= "1段落の文章（100〜150文字）で要約してください。\n";
			$prompt .= "記事の結論や最も重要なメッセージを含めてください。\n\n";
			$prompt .= "【出力形式】\n";
			$prompt .= "要約文のみを出力してください。";
		} else {
			$prompt .= "【要約形式】\n";
			$prompt .= "「この記事でわかること」として3〜5項目を挙げてください。\n\n";
			$prompt .= "【出力形式】\n";
			$prompt .= "1. 〜がわかる\n";
			$prompt .= "2. 〜がわかる\n";
			$prompt .= "3. 〜がわかる\n";
		}
		
		return $prompt;
	}
	
	private function parse_summary_response($response, $style) {
		$response = trim($response);
		
		if ($style === 'points' || $style === 'learn') {
			$items = array();
			$lines = explode("\n", $response);
			
			foreach ($lines as $line) {
				$line = trim($line);
				$line = preg_replace('/^[・\-\*\d+\.]+\s*/', '', $line);
				$line = trim($line);
				
				if (!empty($line) && mb_strlen($line) > 5) {
					$items[] = $line;
				}
			}
			
			return array(
				'type' => $style,
				'items' => array_slice($items, 0, 5),
				'html' => $this->generate_list_html($items, $style)
			);
		} else {
			$text = preg_replace('/^[「『](.+)[」』]$/s', '$1', $response);
			
			return array(
				'type' => 'paragraph',
				'text' => $text,
				'html' => $this->generate_paragraph_html($text)
			);
		}
	}
	
	private function generate_list_html($items, $style) {
		$html = '<!-- wp:group {"className":"probonoseo-summary-box"} -->' . "\n";
		$html .= '<div class="wp-block-group probonoseo-summary-box">' . "\n";
		
		if ($style === 'learn') {
			$html .= '<!-- wp:heading {"level":3} -->' . "\n";
			$html .= '<h3 class="wp-block-heading">この記事でわかること</h3>' . "\n";
			$html .= '<!-- /wp:heading -->' . "\n";
		} else {
			$html .= '<!-- wp:heading {"level":3} -->' . "\n";
			$html .= '<h3 class="wp-block-heading">記事のポイント</h3>' . "\n";
			$html .= '<!-- /wp:heading -->' . "\n";
		}
		
		$html .= '<!-- wp:list -->' . "\n";
		$html .= '<ul class="wp-block-list">' . "\n";
		
		foreach ($items as $item) {
			$html .= '<li>' . esc_html($item) . '</li>' . "\n";
		}
		
		$html .= '</ul>' . "\n";
		$html .= '<!-- /wp:list -->' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<!-- /wp:group -->';
		
		return $html;
	}
	
	private function generate_paragraph_html($text) {
		$html = '<!-- wp:group {"className":"probonoseo-summary-box"} -->' . "\n";
		$html .= '<div class="wp-block-group probonoseo-summary-box">' . "\n";
		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . esc_html($text) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<!-- /wp:group -->';
		
		return $html;
	}
	
	public function ajax_generate_summary() {
		check_ajax_referer('probonoseo_ai_nonce', 'nonce');
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$style = isset($_POST['style']) ? sanitize_text_field(wp_unslash($_POST['style'])) : 'points';
		
		if (!$post_id) {
			wp_send_json_error(array('message' => '投稿IDが指定されていません。'));
			return;
		}
		
		$allowed_styles = array('points', 'paragraph', 'learn');
		if (!in_array($style, $allowed_styles)) {
			$style = 'points';
		}
		
		$result = $this->generate_summary($post_id, $style);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Summary::get_instance();