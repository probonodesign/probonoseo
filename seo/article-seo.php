<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_ArticleSEO {
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action('admin_notices', array($this, 'show_admin_notices'));
		add_action('save_post', array($this, 'check_post_on_save'), 10, 3);
		add_action('edit_form_after_title', array($this, 'show_inline_notices'));
	}
	
	public function show_inline_notices($post) {
		if ($post->post_type !== 'post') {
			return;
		}
		
		$issues = get_post_meta($post->ID, '_probonoseo_seo_issues', true);
		
		if (empty($issues) || !is_array($issues)) {
			return;
		}
		
		echo '<div class="notice notice-warning" style="margin: 20px 0; padding: 10px;">';
		echo '<p><strong>⚠ ProbonoSEO: 記事SEOの改善提案</strong></p>';
		echo '<ul style="list-style: disc; margin-left: 20px;">';
		foreach ($issues as $issue) {
			echo '<li>' . esc_html($issue) . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	public function show_admin_notices() {
		global $post;
		
		if (!$post || !is_admin()) {
			return;
		}
		
		if ($post->post_type !== 'post') {
			return;
		}
		
		$issues = get_post_meta($post->ID, '_probonoseo_seo_issues', true);
		
		if (empty($issues) || !is_array($issues)) {
			return;
		}
		
		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p><strong>ProbonoSEO: 記事SEOの改善提案</strong></p>';
		echo '<ul style="list-style: disc; margin-left: 20px;">';
		foreach ($issues as $issue) {
			echo '<li>' . esc_html($issue) . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	public function check_post_on_save($post_id, $post, $update) {
		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}
		
		if ($post->post_type !== 'post') {
			return;
		}
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		
		$issues = $this->check_post_issues($post);
		
		if (!empty($issues)) {
			update_post_meta($post_id, '_probonoseo_seo_issues', $issues);
		} else {
			delete_post_meta($post_id, '_probonoseo_seo_issues');
		}
	}
	
	private function check_post_issues($post) {
		$issues = array();
		
		if (get_option('probonoseo_article_heading_check', '1') === '1') {
			$heading_issues = $this->check_heading_hierarchy($post->post_content);
			$issues = array_merge($issues, $heading_issues);
		}
		
		if (get_option('probonoseo_article_alt_check', '1') === '1') {
			$alt_issues = $this->check_image_alt($post->post_content);
			$issues = array_merge($issues, $alt_issues);
		}
		
		if (get_option('probonoseo_article_image_count', '1') === '1') {
			$image_count_issues = $this->check_image_count($post->post_content);
			$issues = array_merge($issues, $image_count_issues);
		}
		
		if (get_option('probonoseo_article_word_count', '1') === '1') {
			$word_count_issues = $this->check_word_count($post->post_content);
			$issues = array_merge($issues, $word_count_issues);
		}
		
		if (get_option('probonoseo_article_category_match', '1') === '1') {
			$category_issues = $this->check_category_match($post);
			$issues = array_merge($issues, $category_issues);
		}
		
		if (get_option('probonoseo_article_tag_duplicate', '1') === '1') {
			$tag_issues = $this->check_tag_duplicates($post->ID);
			$issues = array_merge($issues, $tag_issues);
		}
		
		return $issues;
	}
	
	private function check_heading_hierarchy($content) {
		$issues = array();
		
		if (empty($content)) {
			return $issues;
		}
		
		preg_match_all('/<h([1-6])[^>]*>/i', $content, $matches);
		
		if (empty($matches[1])) {
			return $issues;
		}
		
		$headings = $matches[1];
		$prev_level = 0;
		
		foreach ($headings as $level) {
			$level = intval($level);
			
			if ($prev_level > 0 && $level > $prev_level + 1) {
				$issues[] = '見出しの階層が正しくありません（H' . $prev_level . 'の次にH' . $level . 'が使用されています）';
				break;
			}
			
			$prev_level = $level;
		}
		
		return $issues;
	}
	
	private function check_image_alt($content) {
		$issues = array();
		
		if (empty($content)) {
			return $issues;
		}
		
		preg_match_all('/<img[^>]+>/i', $content, $matches);
		
		if (empty($matches[0])) {
			return $issues;
		}
		
		$missing_alt_count = 0;
		
		foreach ($matches[0] as $img_tag) {
			if (strpos($img_tag, 'alt=') === false) {
				$missing_alt_count++;
			}
		}
		
		if ($missing_alt_count > 0) {
			$issues[] = 'alt属性が設定されていない画像が' . $missing_alt_count . '個あります';
		}
		
		return $issues;
	}
	
	private function check_image_count($content) {
		$issues = array();
		
		if (empty($content)) {
			return $issues;
		}
		
		preg_match_all('/<img[^>]+>/i', $content, $matches);
		
		$image_count = count($matches[0]);
		
		$word_count = mb_strlen(wp_strip_all_tags($content));
		
		if ($word_count > 1000 && $image_count < 2) {
			$issues[] = '記事の文字数に対して画像が少なすぎます（現在' . $image_count . '枚）';
		}
		
		if ($image_count > 20) {
			$issues[] = '画像が多すぎます（現在' . $image_count . '枚）。ページ速度に影響する可能性があります';
		}
		
		return $issues;
	}
	
	private function check_word_count($content) {
		$issues = array();
		
		if (empty($content)) {
			$issues[] = '文字数が少なすぎます（現在0文字）。SEOには1000文字以上を推奨します';
			return $issues;
		}
		
		$text = wp_strip_all_tags($content);
		$text = strip_shortcodes($text);
		$text = preg_replace('/\s+/', '', $text);
		
		$word_count = mb_strlen($text);
		
		if ($word_count < 1000) {
			$issues[] = '文字数が少なすぎます（現在' . $word_count . '文字）。SEOには1000文字以上を推奨します';
		}
		
		return $issues;
	}
	
	private function check_category_match($post) {
		$issues = array();
		
		$categories = get_the_category($post->ID);
		
		if (empty($categories)) {
			$issues[] = 'カテゴリが設定されていません';
		} elseif (count($categories) > 3) {
			$issues[] = 'カテゴリが多すぎます（現在' . count($categories) . '個）。3個以内を推奨します';
		}
		
		return $issues;
	}
	
	private function check_tag_duplicates($post_id) {
		$issues = array();
		
		$tags = get_the_tags($post_id);
		
		if (!$tags) {
			return $issues;
		}
		
		$tag_names = array();
		$duplicates = array();
		
		foreach ($tags as $tag) {
			$name_lower = mb_strtolower($tag->name);
			
			if (in_array($name_lower, $tag_names)) {
				$duplicates[] = $tag->name;
			} else {
				$tag_names[] = $name_lower;
			}
		}
		
		if (!empty($duplicates)) {
			$issues[] = '重複タグがあります: ' . implode(', ', $duplicates);
		}
		
		if (count($tags) > 10) {
			$issues[] = 'タグが多すぎます（現在' . count($tags) . '個）。10個以内を推奨します';
		}
		
		return $issues;
	}
}

function probonoseo_init_article_seo() {
	ProbonoSEO_ArticleSEO::get_instance();
}
add_action('init', 'probonoseo_init_article_seo');