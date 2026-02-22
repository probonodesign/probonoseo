<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Caption {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_ai_caption', array($this, 'ajax_generate_caption'));
	}
	
	public function generate_caption($post_id) {
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
		
		if (get_option('probonoseo_pro_caption_ai', '0') !== '1') {
			return array(
				'success' => false,
				'message' => 'AI画像キャプション生成機能が無効になっています。設定画面で有効化してください。'
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
		$content = $post->post_content;
		$text_content = wp_strip_all_tags($content);
		
		$images = $this->extract_images($post_id, $content);
		
		if (empty($images)) {
			return array(
				'success' => false,
				'message' => '記事内に画像が見つかりません。'
			);
		}
		
		$text_content = mb_substr($text_content, 0, 2000);
		$prompt = $this->build_prompt($title, $text_content, $images);
		
		$messages = array(
			array(
				'role' => 'system',
				'content' => 'あなたはSEOに精通したコンテンツライターです。記事の文脈を考慮して、画像に適切なキャプションとalt属性を生成してください。'
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
		
		$captions = $this->parse_caption_response($response['content'], $images);
		
		if (empty($captions)) {
			return array(
				'success' => false,
				'message' => 'キャプションの生成に失敗しました。'
			);
		}
		
		return array(
			'success' => true,
			'captions' => $captions
		);
	}
	
	private function extract_images($post_id, $content) {
		$images = array();
		
		$thumbnail_id = get_post_thumbnail_id($post_id);
		if ($thumbnail_id) {
			$thumbnail_url = wp_get_attachment_url($thumbnail_id);
			$thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
			$images[] = array(
				'id' => $thumbnail_id,
				'url' => $thumbnail_url,
				'current_alt' => $thumbnail_alt,
				'filename' => basename($thumbnail_url),
				'type' => 'アイキャッチ画像'
			);
		}
		
		if (preg_match_all('/<img[^>]+>/i', $content, $matches)) {
			foreach ($matches[0] as $img_tag) {
				$src = '';
				$alt = '';
				$attachment_id = 0;
				
				if (preg_match('/src=["\']([^"\']+)["\']/i', $img_tag, $src_match)) {
					$src = $src_match[1];
				}
				
				if (preg_match('/alt=["\']([^"\']*)["\']?/i', $img_tag, $alt_match)) {
					$alt = $alt_match[1];
				}
				
				if (preg_match('/wp-image-(\d+)/i', $img_tag, $id_match)) {
					$attachment_id = intval($id_match[1]);
				}
				
				if (!empty($src)) {
					$already_exists = false;
					foreach ($images as $existing) {
						if ($existing['url'] === $src) {
							$already_exists = true;
							break;
						}
					}
					
					if (!$already_exists) {
						$images[] = array(
							'id' => $attachment_id,
							'url' => $src,
							'current_alt' => $alt,
							'filename' => basename(wp_parse_url($src, PHP_URL_PATH)),
							'type' => '本文画像'
						);
					}
				}
			}
		}
		
		return array_slice($images, 0, 10);
	}
	
	private function build_prompt($title, $content, $images) {
		$prompt = "以下の記事に含まれる画像に対して、SEOに最適化されたキャプションとalt属性を生成してください。\n\n";
		$prompt .= "【記事タイトル】\n";
		$prompt .= $title . "\n\n";
		$prompt .= "【記事本文（抜粋）】\n";
		$prompt .= $content . "\n\n";
		$prompt .= "【画像一覧】\n";
		foreach ($images as $index => $image) {
			$num = $index + 1;
			$prompt .= "画像{$num}: {$image['type']}\n";
			$prompt .= "  ファイル名: {$image['filename']}\n";
			if (!empty($image['current_alt'])) {
				$prompt .= "  現在のalt: {$image['current_alt']}\n";
			}
		}
		$prompt .= "\n";
		$prompt .= "【生成ルール】\n";
		$prompt .= "1. キャプション: 画像の内容を説明しつつ記事との関連性を示す（20〜50文字）\n";
		$prompt .= "2. alt属性: 画像の内容を簡潔に説明（10〜30文字）、キーワードを自然に含める\n";
		$prompt .= "3. 記事のテーマ・文脈に合った表現を使用\n";
		$prompt .= "4. 各画像ごとに生成\n\n";
		$prompt .= "【出力形式】\n";
		foreach ($images as $index => $image) {
			$num = $index + 1;
			$prompt .= "---画像{$num}---\n";
			$prompt .= "キャプション: [キャプション文]\n";
			$prompt .= "alt: [alt属性文]\n";
		}
		return $prompt;
	}
	
	private function parse_caption_response($response, $images) {
		$captions = array();
		
		foreach ($images as $index => $image) {
			$num = $index + 1;
			$pattern = "/---画像{$num}---\s*\nキャプション[：:]\s*(.+?)\nalt[：:]\s*(.+?)(?=\n---|$)/us";
			
			if (preg_match($pattern, $response, $matches)) {
				$captions[] = array(
					'image_id' => $image['id'],
					'image_url' => $image['url'],
					'filename' => $image['filename'],
					'type' => $image['type'],
					'caption' => trim($matches[1]),
					'alt' => trim($matches[2])
				);
			}
		}
		
		return $captions;
	}
	
	public function ajax_generate_caption() {
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
		
		$result = $this->generate_caption($post_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result);
		}
	}
}

ProbonoSEO_AI_Caption::get_instance();