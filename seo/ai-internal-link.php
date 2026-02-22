<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_InternalLink {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_suggest_ai_internal', array($this, 'ajax_suggest_internal'));
	}
	
	public function suggest_internal($post_id) {
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
		
		if (get_option('probonoseo_pro_internal_link_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI内部リンク提案機能が無効になっています。設定画面で有効化してください。'
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
		
		$related_posts = $this->find_related_posts($post_id, $content);
		
		if (empty($related_posts)) {
			return array(
				'success' => false,
				'message' => '関連する記事が見つかりません。'
			);
		}
		
		$content = mb_substr($content, 0, 2000);
		$prompt = $this->build_prompt($title, $content, $related_posts);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたは日本語SEOの専門家です。記事間の関連性を分析し、効果的な内部リンク配置を提案してください。'
			),
			array(
				'role' => 'user',
				'content' => $prompt
			)
		);
		
		$response = $openai->send_request($messages, array(
			'max_tokens' => 1500,
			'temperature' => 0.5
		));
		
		if (!$response['success']) {
			return $response;
		}
		
		$links = $this->parse_internal_link_response($response['content'], $related_posts);
		
		if (empty($links)) {
			return array(
				'success' => false,
				'message' => '内部リンク提案の生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'links' => $links
		);
	}
	
	private function find_related_posts($current_post_id, $content) {
		$related = array();
		
		$words = $this->extract_keywords($content);
		
		if (empty($words)) {
			return $related;
		}
		
		$args = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => 30,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			'post__not_in' => array($current_post_id),
			's' => implode(' ', array_slice($words, 0, 5))
		);
		
		$query = new WP_Query($args);
		
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$other_content = wp_strip_all_tags(get_the_content());
				$excerpt = mb_substr($other_content, 0, 200);
				
				$related[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'url' => get_permalink(),
					'excerpt' => $excerpt
				);
			}
			wp_reset_postdata();
		}
		
		return array_slice($related, 0, 15);
	}
	
	private function extract_keywords($content) {
		$content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content);
		$words = preg_split('/\s+/', $content);
		$words = array_filter($words, function($word) {
			return mb_strlen($word) >= 2;
		});
		
		$word_count = array_count_values($words);
		arsort($word_count);
		
		return array_keys(array_slice($word_count, 0, 20));
	}
	
	private function build_prompt($title, $content, $related_posts) {
		$prompt = "以下の記事に適切な内部リンクを提案してください。\n\n";
		$prompt .= "【現在の記事】\n";
		$prompt .= "タイトル: {$title}\n";
		$prompt .= "本文（抜粋）:\n{$content}\n\n";
		$prompt .= "【リンク候補の記事一覧】\n";
		foreach ($related_posts as $index => $post) {
			$num = $index + 1;
			$prompt .= "{$num}. {$post['title']}\n";
			$prompt .= "   URL: {$post['url']}\n";
			$prompt .= "   概要: {$post['excerpt']}\n\n";
		}
		$prompt .= "【提案ルール】\n";
		$prompt .= "1. 関連性の高い記事を3〜7個選択\n";
		$prompt .= "2. 各リンクに適切なアンカーテキストを提案\n";
		$prompt .= "3. リンクを挿入すべき文脈・位置を説明\n";
		$prompt .= "4. 関連度スコア（1-10）を付与\n\n";
		$prompt .= "【出力形式】\n";
		$prompt .= "---リンク1---\n";
		$prompt .= "記事番号: [番号]\n";
		$prompt .= "アンカーテキスト: [テキスト]\n";
		$prompt .= "挿入位置: [どこにリンクを入れるべきか]\n";
		$prompt .= "関連度: [スコア]/10\n";
		$prompt .= "理由: [なぜこのリンクが有効か]\n";
		return $prompt;
	}
	
	private function parse_internal_link_response($response, $related_posts) {
		$links = array();
		
		if (preg_match_all('/---リンク\d+---\s*\n(.+?)(?=---リンク|$)/us', $response, $matches)) {
			foreach ($matches[1] as $block) {
				$link = array();
				
				if (preg_match('/記事番号[：:]\s*(\d+)/u', $block, $m)) {
					$index = intval($m[1]) - 1;
					if (isset($related_posts[$index])) {
						$link['title'] = $related_posts[$index]['title'];
						$link['url'] = $related_posts[$index]['url'];
						$link['post_id'] = $related_posts[$index]['id'];
					}
				}
				
				if (preg_match('/アンカーテキスト[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['anchor_text'] = trim($m[1]);
				}
				
				if (preg_match('/挿入位置[：:]\s*(.+?)(?=\n|$)/u', $block, $m)) {
					$link['position'] = trim($m[1]);
				}
				
				if (preg_match('/関連度[：:]\s*(\d+)/u', $block, $m)) {
					$link['relevance'] = intval($m[1]);
				}
				
				if (preg_match('/理由[：:]\s*(.+?)(?=\n---|$)/us', $block, $m)) {
					$link['reason'] = trim($m[1]);
				}
				
				if (!empty($link['url']) && !empty($link['anchor_text'])) {
					$links[] = $link;
				}
			}
		}
		
		usort($links, function($a, $b) {
			$ra = isset($a['relevance']) ? $a['relevance'] : 0;
			$rb = isset($b['relevance']) ? $b['relevance'] : 0;
			return $rb - $ra;
		});
		
		return $links;
	}
	
	public function ajax_suggest_internal() {
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
		
		$result = $this->suggest_internal($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_InternalLink::get_instance();