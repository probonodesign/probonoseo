<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_InternalLinks {
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
		if (get_option('probonoseo_internal_prev_next', '1') === '1') {
			add_filter('the_content', array($this, 'add_prev_next_links'));
		}
		
		if (get_option('probonoseo_internal_related', '1') === '1') {
			add_filter('the_content', array($this, 'add_related_posts'));
		}
		
		if (get_option('probonoseo_internal_nofollow', '1') === '1') {
			add_filter('the_content', array($this, 'remove_internal_nofollow'));
		}
	}
	
	public function add_prev_next_links($content) {
		if (!is_singular('post')) {
			return $content;
		}
		
		$prev_post = get_previous_post();
		$next_post = get_next_post();
		
		if (!$prev_post && !$next_post) {
			return $content;
		}
		
		$html = '<div class="probonoseo-prev-next-links">';
		
		if ($prev_post) {
			$prev_title = get_the_title($prev_post);
			if (get_option('probonoseo_internal_category_format', '1') === '1') {
				$prev_title = $this->format_title($prev_title);
			}
			$html .= '<div class="probonoseo-prev-link">';
			$html .= '<a href="' . esc_url(get_permalink($prev_post)) . '" rel="prev">';
			$html .= '<span class="probonoseo-link-label">前の記事</span>';
			$html .= '<span class="probonoseo-link-title">' . esc_html($prev_title) . '</span>';
			$html .= '</a>';
			$html .= '</div>';
		}
		
		if ($next_post) {
			$next_title = get_the_title($next_post);
			if (get_option('probonoseo_internal_category_format', '1') === '1') {
				$next_title = $this->format_title($next_title);
			}
			$html .= '<div class="probonoseo-next-link">';
			$html .= '<a href="' . esc_url(get_permalink($next_post)) . '" rel="next">';
			$html .= '<span class="probonoseo-link-label">次の記事</span>';
			$html .= '<span class="probonoseo-link-title">' . esc_html($next_title) . '</span>';
			$html .= '</a>';
			$html .= '</div>';
		}
		
		$html .= '</div>';
		
		return $content . $html;
	}
	
	public function add_related_posts($content) {
		if (!is_singular('post')) {
			return $content;
		}
		
		$related_posts = $this->get_related_posts();
		
		if (empty($related_posts)) {
			return $content;
		}
		
		$html = '<div class="probonoseo-related-posts">';
		$html .= '<h3 class="probonoseo-related-title">関連記事</h3>';
		$html .= '<ul class="probonoseo-related-list">';
		
		foreach ($related_posts as $post) {
			$title = get_the_title($post);
			if (get_option('probonoseo_internal_category_format', '1') === '1') {
				$title = $this->format_title($title);
			}
			
			$html .= '<li class="probonoseo-related-item">';
			$html .= '<a href="' . esc_url(get_permalink($post)) . '">';
			
			if (has_post_thumbnail($post->ID)) {
				$html .= '<span class="probonoseo-related-thumbnail">';
				$html .= get_the_post_thumbnail($post->ID, 'thumbnail');
				$html .= '</span>';
			}
			
			$html .= '<span class="probonoseo-related-text">' . esc_html($title) . '</span>';
			$html .= '</a>';
			$html .= '</li>';
		}
		
		$html .= '</ul>';
		$html .= '</div>';
		
		return $content . $html;
	}
	
	private function get_related_posts() {
		global $post;
		
		$related_posts = array();
		
		if (get_option('probonoseo_internal_category', '1') === '1') {
			$categories = get_the_category($post->ID);
			if (!empty($categories)) {
				$category_ids = array();
				foreach ($categories as $category) {
					$category_ids[] = $category->term_id;
				}
				
				$args = array(
					'category__in' => $category_ids,
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
					'post__not_in' => array($post->ID),
					'posts_per_page' => 5,
					'orderby' => 'rand'
				);
				
				$related_posts = get_posts($args);
			}
		}
		
		if (empty($related_posts) && get_option('probonoseo_internal_tag_logic', '1') === '1') {
			$tags = get_the_tags($post->ID);
			if ($tags) {
				$tag_ids = array();
				foreach ($tags as $tag) {
					$tag_ids[] = $tag->term_id;
				}
				
				$args = array(
					'tag__in' => $tag_ids,
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
					'post__not_in' => array($post->ID),
					'posts_per_page' => 5,
					'orderby' => 'rand'
				);
				
				$related_posts = get_posts($args);
			}
		}
		
		return $related_posts;
	}
	
	public function remove_internal_nofollow($content) {
		$home_url = home_url();
		
		$content = preg_replace_callback(
			'/<a\s+([^>]*?)href=["\'](' . preg_quote($home_url, '/') . '[^"\']*)["\']([^>]*?)>/i',
			function($matches) {
				$before = $matches[1];
				$url = $matches[2];
				$after = $matches[3];
				
				$before = preg_replace('/\s*rel=["\'][^"\']*nofollow[^"\']*["\']\s*/i', ' ', $before);
				$after = preg_replace('/\s*rel=["\'][^"\']*nofollow[^"\']*["\']\s*/i', ' ', $after);
				
				return '<a ' . trim($before) . ' href="' . $url . '" ' . trim($after) . '>';
			},
			$content
		);
		
		return $content;
	}
	
	private function format_title($title) {
		if (mb_strlen($title) > 30) {
			return mb_substr($title, 0, 30) . '...';
		}
		return $title;
	}
	
	public function get_child_pages($parent_id = 0) {
		if (get_option('probonoseo_internal_child_pages', '1') !== '1') {
			return array();
		}
		
		if ($parent_id === 0) {
			$parent_id = get_the_ID();
		}
		
		$args = array(
			'post_type' => 'page',
			'post_parent' => $parent_id,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'posts_per_page' => -1
		);
		
		return get_posts($args);
	}
}

function probonoseo_init_internal_links() {
	ProbonoSEO_InternalLinks::get_instance();
}
add_action('init', 'probonoseo_init_internal_links');

function probonoseo_get_child_pages($parent_id = 0) {
	$internal_links = ProbonoSEO_InternalLinks::get_instance();
	return $internal_links->get_child_pages($parent_id);
}