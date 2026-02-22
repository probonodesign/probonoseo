<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Breadcrumb {
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		add_shortcode('probonoseo_breadcrumb', array($this, 'render_breadcrumb'));
	}
	
	public function render_breadcrumb($atts = array()) {
		if (get_option('probonoseo_basic_breadcrumb', '1') !== '1') {
			return '';
		}
		
		if (is_front_page()) {
			return '';
		}
		
		$items = $this->get_breadcrumb_items();
		
		if (empty($items)) {
			return '';
		}
		
		$html = '<nav class="probonoseo-breadcrumb" aria-label="パンくずリスト">';
		$html .= '<ol class="probonoseo-breadcrumb-list">';
		
		foreach ($items as $index => $item) {
			$is_last = ($index === count($items) - 1);
			
			$html .= '<li class="probonoseo-breadcrumb-item">';
			
			if (!$is_last && !empty($item['url'])) {
				$html .= '<a href="' . esc_url($item['url']) . '">' . esc_html($item['name']) . '</a>';
			} else {
				$html .= '<span>' . esc_html($item['name']) . '</span>';
			}
			
			if (!$is_last) {
				$html .= '<span class="probonoseo-breadcrumb-separator"> &gt; </span>';
			}
			
			$html .= '</li>';
		}
		
		$html .= '</ol>';
		$html .= '</nav>';
		
		return $html;
	}
	
	private function get_breadcrumb_items() {
		$items = array();
		
		$items[] = array(
			'name' => 'HOME',
			'url' => home_url('/')
		);
		
		if (is_singular()) {
			$post = get_post();
			
			if ($post->post_type === 'post') {
				$categories = get_the_category($post->ID);
				if (!empty($categories)) {
					$category = $categories[0];
					
					if ($category->parent) {
						$ancestors = array_reverse(get_ancestors($category->term_id, 'category'));
						foreach ($ancestors as $ancestor_id) {
							$ancestor = get_category($ancestor_id);
							$items[] = array(
								'name' => $ancestor->name,
								'url' => get_category_link($ancestor->term_id)
							);
						}
					}
					
					$items[] = array(
						'name' => $category->name,
						'url' => get_category_link($category->term_id)
					);
				}
			} elseif ($post->post_type === 'page') {
				if ($post->post_parent) {
					$ancestors = array_reverse(get_post_ancestors($post->ID));
					foreach ($ancestors as $ancestor_id) {
						$items[] = array(
							'name' => get_the_title($ancestor_id),
							'url' => get_permalink($ancestor_id)
						);
					}
				}
			}
			
			$title = get_the_title($post);
			if (mb_strlen($title) > 30) {
				$title = mb_substr($title, 0, 30) . '...';
			}
			
			$items[] = array(
				'name' => $title,
				'url' => ''
			);
		} elseif (is_category()) {
			$category = get_queried_object();
			
			if ($category->parent) {
				$ancestors = array_reverse(get_ancestors($category->term_id, 'category'));
				foreach ($ancestors as $ancestor_id) {
					$ancestor = get_category($ancestor_id);
					$items[] = array(
						'name' => $ancestor->name,
						'url' => get_category_link($ancestor->term_id)
					);
				}
			}
			
			$items[] = array(
				'name' => $category->name,
				'url' => ''
			);
		} elseif (is_tag()) {
			$tag = get_queried_object();
			$items[] = array(
				'name' => $tag->name,
				'url' => ''
			);
		} elseif (is_post_type_archive()) {
			$post_type = get_query_var('post_type');
			if (is_array($post_type)) {
				$post_type = reset($post_type);
			}
			$post_type_obj = get_post_type_object($post_type);
			
			$items[] = array(
				'name' => $post_type_obj->labels->name,
				'url' => ''
			);
		} elseif (is_author()) {
			$author = get_queried_object();
			$items[] = array(
				'name' => $author->display_name,
				'url' => ''
			);
		} elseif (is_archive()) {
			$items[] = array(
				'name' => get_the_archive_title(),
				'url' => ''
			);
		} elseif (is_search()) {
			$items[] = array(
				'name' => '検索結果: ' . get_search_query(),
				'url' => ''
			);
		} elseif (is_404()) {
			$items[] = array(
				'name' => 'ページが見つかりません',
				'url' => ''
			);
		}
		
		return $items;
	}
}

function probonoseo_init_breadcrumb() {
	ProbonoSEO_Breadcrumb::get_instance();
}
add_action('init', 'probonoseo_init_breadcrumb');

function probonoseo_breadcrumb() {
	$breadcrumb = ProbonoSEO_Breadcrumb::get_instance();
	echo wp_kses_post($breadcrumb->render_breadcrumb());
}