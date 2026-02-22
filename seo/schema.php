<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema {
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
		if (get_option('probonoseo_basic_schema', '1') === '1') {
			add_action('wp_head', array($this, 'output_schema'), 6);
		}
	}
	
	public function output_schema() {
		if ($this->should_skip_schema()) {
			return;
		}
		
		if (is_search() || is_404()) {
			return;
		}
		
		$schemas = $this->get_all_schemas();
		
		if (count($schemas) === 1) {
			echo '<script type="application/ld+json">' . wp_json_encode($schemas[0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
		} elseif (count($schemas) > 1) {
			$graph = array(
				'@context' => 'https://schema.org',
				'@graph' => $schemas
			);
			echo '<script type="application/ld+json">' . wp_json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
		}
	}
	
	private function should_skip_schema() {
		if (function_exists('yoast_get_options')) {
			return true;
		}
		
		if (defined('RANK_MATH_VERSION')) {
			return true;
		}
		
		if (defined('AIOSEO_VERSION')) {
			return true;
		}
		
		return false;
	}
	
	private function get_all_schemas() {
		$schemas = array();
		
		$schemas[] = $this->get_website_schema();
		
		$schemas[] = $this->get_webpage_schema();
		
		if (get_option('probonoseo_basic_breadcrumb', '1') === '1') {
			$breadcrumb = $this->get_breadcrumb_schema();
			if ($breadcrumb) {
				$schemas[] = $breadcrumb;
			}
		}
		
		if (is_singular() && is_single()) {
			$author = $this->get_author_schema();
			if ($author) {
				$schemas[] = $author;
			}
		}
		
		$schemas = array_filter($schemas);
		
		return $schemas;
	}
	
	private function get_website_schema() {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'@id' => home_url('/#website'),
			'url' => home_url('/'),
			'name' => get_bloginfo('name'),
		);
		
		$description = get_bloginfo('description');
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		
		$schema['inLanguage'] = 'ja';
		
		$schema['potentialAction'] = array(
			'@type' => 'SearchAction',
			'target' => array(
				'@type' => 'EntryPoint',
				'urlTemplate' => home_url('/?s={search_term_string}')
			),
			'query-input' => 'required name=search_term_string'
		);
		
		return $schema;
	}
	
	private function get_webpage_schema() {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebPage',
			'@id' => probonoseo_get_canonical_url() . '#webpage',
			'url' => probonoseo_get_canonical_url(),
			'name' => wp_get_document_title(),
			'isPartOf' => array(
				'@id' => home_url('/#website')
			),
			'inLanguage' => 'ja',
		);
		
		if (is_singular()) {
			global $post;
			
			$schema['datePublished'] = get_the_date('c', $post);
			$schema['dateModified'] = get_the_modified_date('c', $post);
			
			if (has_post_thumbnail($post->ID)) {
				$image_url = get_the_post_thumbnail_url($post->ID, 'large');
				if ($image_url) {
					$schema['image'] = array(
						'@type' => 'ImageObject',
						'url' => $image_url
					);
				}
			}
			
			$excerpt = get_the_excerpt($post);
			if (!empty($excerpt)) {
				$schema['description'] = $excerpt;
			}
		}
		
		return $schema;
	}
	
	private function get_breadcrumb_schema() {
		$items = $this->get_breadcrumb_items();
		
		if (empty($items)) {
			return null;
		}
		
		return array(
			'@context' => 'https://schema.org',
			'@type' => 'BreadcrumbList',
			'@id' => probonoseo_get_canonical_url() . '#breadcrumb',
			'itemListElement' => $items
		);
	}
	
	private function get_breadcrumb_items() {
		$items = array();
		$pos = 1;
		
		$items[] = array(
			'@type' => 'ListItem',
			'position' => $pos++,
			'name' => get_bloginfo('name'),
			'item' => home_url('/')
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
								'@type' => 'ListItem',
								'position' => $pos++,
								'name' => $ancestor->name,
								'item' => get_category_link($ancestor->term_id)
							);
						}
					}
					
					$items[] = array(
						'@type' => 'ListItem',
						'position' => $pos++,
						'name' => $category->name,
						'item' => get_category_link($category->term_id)
					);
				}
			}
			
			$items[] = array(
				'@type' => 'ListItem',
				'position' => $pos++,
				'name' => get_the_title($post)
			);
		} elseif (is_category() || is_tag() || is_tax()) {
			$term = get_queried_object();
			
			if ($term->parent) {
				$ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy));
				foreach ($ancestors as $ancestor_id) {
					$ancestor = get_term($ancestor_id, $term->taxonomy);
					$items[] = array(
						'@type' => 'ListItem',
						'position' => $pos++,
						'name' => $ancestor->name,
						'item' => get_term_link($ancestor)
					);
				}
			}
			
			$items[] = array(
				'@type' => 'ListItem',
				'position' => $pos++,
				'name' => $term->name
			);
		} elseif (is_archive()) {
			$items[] = array(
				'@type' => 'ListItem',
				'position' => $pos++,
				'name' => get_the_archive_title()
			);
		}
		
		return $items;
	}
	
	private function get_author_schema() {
		global $post;
		
		if (!$post) {
			return null;
		}
		
		$author_id = $post->post_author;
		$author_name = get_the_author_meta('display_name', $author_id);
		
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Person',
			'@id' => get_author_posts_url($author_id) . '#author',
			'name' => $author_name,
			'url' => get_author_posts_url($author_id)
		);
		
		$description = get_the_author_meta('description', $author_id);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		
		return $schema;
	}
}

function probonoseo_init_schema() {
	ProbonoSEO_Schema::get_instance();
}
add_action('init', 'probonoseo_init_schema');