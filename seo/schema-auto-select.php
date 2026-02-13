<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Auto_Select {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('wp_head', array($this, 'output_schema'), 24);
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_schema_auto_select', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$schema_type = $this->detect_schema_type($post);
		if (empty($schema_type)) {
			return;
		}
		$schema = $this->build_schema($post, $schema_type);
		if (empty($schema)) {
			return;
		}
		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
	}

	public function detect_schema_type($post) {
		$priority = get_option('probonoseo_schema_auto_priority', 'content');
		if ($priority === 'posttype') {
			return $this->detect_by_posttype($post);
		}
		if ($priority === 'category') {
			return $this->detect_by_category($post);
		}
		return $this->detect_by_content($post);
	}

	private function detect_by_posttype($post) {
		$post_type = get_post_type($post);
		$type_map = array(
			'post' => 'BlogPosting',
			'page' => 'WebPage',
			'product' => 'Product',
			'event' => 'Event'
		);
		if (isset($type_map[$post_type])) {
			return $type_map[$post_type];
		}
		return get_option('probonoseo_schema_auto_fallback', 'Article');
	}

	private function detect_by_category($post) {
		$categories = get_the_category($post->ID);
		if (empty($categories)) {
			return get_option('probonoseo_schema_auto_fallback', 'Article');
		}
		$category_map = array(
			'news' => 'NewsArticle',
			'blog' => 'BlogPosting',
			'recipe' => 'Recipe',
			'product' => 'Product',
			'event' => 'Event',
			'how-to' => 'HowTo',
			'faq' => 'FAQPage',
			'review' => 'Review',
			'book' => 'Book',
			'movie' => 'Movie',
			'music' => 'MusicAlbum',
			'software' => 'SoftwareApplication',
			'course' => 'Course',
			'podcast' => 'PodcastSeries'
		);
		foreach ($categories as $category) {
			$slug = strtolower($category->slug);
			foreach ($category_map as $key => $type) {
				if (strpos($slug, $key) !== false) {
					return $type;
				}
			}
		}
		return get_option('probonoseo_schema_auto_fallback', 'Article');
	}

	private function detect_by_content($post) {
		$content = strtolower($post->post_content);
		$title = strtolower($post->post_title);
		$patterns = array(
			'Recipe' => array('レシピ', '材料', '作り方', '調理', 'recipe', 'ingredients'),
			'HowTo' => array('方法', '手順', 'やり方', 'ステップ', 'how to', 'step'),
			'FAQPage' => array('よくある質問', 'faq', 'q&a', '質問と回答'),
			'Review' => array('レビュー', '評価', 'おすすめ', 'review', 'rating'),
			'Event' => array('イベント', '開催', '日時', '会場', 'event'),
			'Product' => array('価格', '購入', '商品', '製品', 'product', 'price'),
			'NewsArticle' => array('ニュース', '速報', '発表', 'news', 'breaking'),
			'Book' => array('書籍', '本', '著者', 'book', 'author', 'isbn'),
			'Movie' => array('映画', '上映', '監督', 'movie', 'film', 'director'),
			'SoftwareApplication' => array('アプリ', 'ソフト', 'ダウンロード', 'app', 'software', 'download'),
			'Course' => array('コース', '講座', '学習', 'course', 'learn', 'training')
		);
		foreach ($patterns as $type => $keywords) {
			foreach ($keywords as $keyword) {
				if (strpos($content, $keyword) !== false || strpos($title, $keyword) !== false) {
					return $type;
				}
			}
		}
		return get_option('probonoseo_schema_auto_fallback', 'Article');
	}

	public function build_schema($post, $type) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => $type,
			'name' => get_the_title($post),
			'url' => get_permalink($post),
			'datePublished' => get_the_date('c', $post),
			'dateModified' => get_the_modified_date('c', $post)
		);
		$excerpt = get_the_excerpt($post);
		if (!empty($excerpt)) {
			$schema['description'] = $excerpt;
		}
		$author = get_the_author_meta('display_name', $post->post_author);
		$schema['author'] = array(
			'@type' => 'Person',
			'name' => $author
		);
		$publisher = get_option('probonoseo_schema_article_publisher', get_bloginfo('name'));
		$schema['publisher'] = array(
			'@type' => 'Organization',
			'name' => $publisher
		);
		$logo = get_option('probonoseo_schema_article_logo', '');
		if (!empty($logo)) {
			$schema['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url' => $logo
			);
		}
		if (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'large');
		}
		$schema['mainEntityOfPage'] = array(
			'@type' => 'WebPage',
			'@id' => get_permalink($post)
		);
		return $schema;
	}
}

ProbonoSEO_Schema_Auto_Select::get_instance();