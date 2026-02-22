<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_OGP {
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
		if (get_option('probonoseo_basic_ogp', '0') === '1') {
			add_action('wp_head', array($this, 'output_ogp'), 4);
		}
	}
	
	public function output_ogp() {
		if ($this->should_skip_ogp()) {
			return;
		}
		
		$probonoseo_data = $this->get_ogp_data();
		
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr($probonoseo_data['title']) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr($probonoseo_data['desc']) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url($probonoseo_data['url']) . '">' . "\n";
		
		if (!empty($probonoseo_data['image'])) {
			echo '<meta property="og:image" content="' . esc_url($probonoseo_data['image']) . '">' . "\n";
			
			if (get_option('probonoseo_ogp_alt', '1') === '1') {
				$probonoseo_alt = $this->get_image_alt($probonoseo_data['image']);
				if (!empty($probonoseo_alt)) {
					echo '<meta property="og:image:alt" content="' . esc_attr($probonoseo_alt) . '">' . "\n";
				}
			}
		}
		
		if (get_option('probonoseo_ogp_facebook', '1') === '1') {
			echo '<meta property="og:locale" content="ja_JP">' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
		}
		
		if (get_option('probonoseo_ogp_line', '1') === '1' && is_singular()) {
			echo '<meta property="og:updated_time" content="' . esc_attr(get_the_modified_time('c')) . '">' . "\n";
		}
	}
	
	private function should_skip_ogp() {
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
	
	private function get_ogp_data() {
		$probonoseo_title = $this->get_ogp_title();
		$probonoseo_desc = $this->get_ogp_description();
		$probonoseo_image = $this->get_ogp_image();
		$probonoseo_url = probonoseo_get_canonical_url();
		
		if (get_option('probonoseo_ogp_japanese_url', '1') === '1') {
			$probonoseo_url = $this->encode_japanese_url($probonoseo_url);
		}
		
		return array(
			'title' => $probonoseo_title,
			'desc' => $probonoseo_desc,
			'image' => $probonoseo_image,
			'url' => $probonoseo_url,
		);
	}
	
	private function get_ogp_title() {
		if (get_option('probonoseo_ogp_title', '1') !== '1') {
			return wp_get_document_title();
		}
		
		$probonoseo_title = '';
		
		if (is_singular()) {
			$probonoseo_title = get_the_title();
		} elseif (is_home() || is_front_page()) {
			$probonoseo_title = get_bloginfo('name');
		} elseif (is_category()) {
			$probonoseo_title = single_cat_title('', false);
		} elseif (is_tag()) {
			$probonoseo_title = single_tag_title('', false);
		} elseif (is_archive()) {
			$probonoseo_title = get_the_archive_title();
		}
		
		if (mb_strlen($probonoseo_title) > 60) {
			$probonoseo_title = mb_substr($probonoseo_title, 0, 60) . '...';
		}
		
		return $probonoseo_title;
	}
	
	private function get_ogp_description() {
		if (get_option('probonoseo_ogp_desc', '1') !== '1') {
			return get_bloginfo('description');
		}
		
		$probonoseo_description = '';
		
		if (is_singular()) {
			global $post;
			
			if (!empty($post->post_excerpt)) {
				$probonoseo_description = $post->post_excerpt;
			} else {
				$probonoseo_content = wp_strip_all_tags($post->post_content);
				$probonoseo_content = strip_shortcodes($probonoseo_content);
				$probonoseo_content = preg_replace('/\s+/', ' ', $probonoseo_content);
				$probonoseo_description = mb_substr($probonoseo_content, 0, 100);
			}
		} elseif (is_home() || is_front_page()) {
			$probonoseo_description = get_bloginfo('description');
		} elseif (is_category()) {
			$probonoseo_category = get_queried_object();
			$probonoseo_description = !empty($probonoseo_category->description) ? $probonoseo_category->description : $probonoseo_category->name . 'に関する記事一覧です。';
		} elseif (is_tag()) {
			$probonoseo_tag = get_queried_object();
			$probonoseo_description = !empty($probonoseo_tag->description) ? $probonoseo_tag->description : $probonoseo_tag->name . 'タグの記事一覧です。';
		}
		
		if (mb_strlen($probonoseo_description) > 100) {
			$probonoseo_description = mb_substr($probonoseo_description, 0, 100) . '...';
		}
		
		return $probonoseo_description;
	}
	
	private function get_ogp_image() {
		$probonoseo_image = '';
		
		if (get_option('probonoseo_ogp_image_auto', '1') === '1' && is_singular()) {
			global $post;
			
			if (has_post_thumbnail($post->ID)) {
				$probonoseo_image = get_the_post_thumbnail_url($post->ID, 'large');
			} else {
				$probonoseo_image = $this->get_first_content_image($post->post_content);
			}
		}
		
		if (empty($probonoseo_image) && get_option('probonoseo_ogp_image_fixed', '0') === '1') {
			$probonoseo_image = get_option('probonoseo_fixed_ogp_image_url', '');
		}
		
		if (!empty($probonoseo_image) && get_option('probonoseo_ogp_size_detect', '1') === '1') {
			$this->check_image_size($probonoseo_image);
		}
		
		if (!empty($probonoseo_image) && get_option('probonoseo_ogp_thumbnail', '1') === '1') {
			$probonoseo_image = $this->optimize_image_url($probonoseo_image);
		}
		
		return $probonoseo_image;
	}
	
	private function get_first_content_image($content) {
		preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $probonoseo_matches);
		
		if (!empty($probonoseo_matches[1])) {
			return $probonoseo_matches[1];
		}
		
		return '';
	}
	
	private function check_image_size($image_url) {
		$probonoseo_image_path = str_replace(home_url(), ABSPATH, $image_url);
		
		if (file_exists($probonoseo_image_path)) {
			$probonoseo_size = @getimagesize($probonoseo_image_path);
			if ($probonoseo_size) {
				$probonoseo_width = $probonoseo_size[0];
				$probonoseo_height = $probonoseo_size[1];
				
				if ($probonoseo_width < 1200 || $probonoseo_height < 630) {
					if (current_user_can('edit_posts')) {
						echo '<!-- ProbonoSEO Warning: OG image size is ' . esc_html($probonoseo_width) . 'x' . esc_html($probonoseo_height) . 'px. Recommended: 1200x630px -->' . "\n";
					}
				}
			}
		}
	}
	
	private function optimize_image_url($url) {
		if (strpos($url, home_url()) === 0) {
			return $url;
		}
		
		if (strpos($url, '/') === 0) {
			return home_url($url);
		}
		
		return $url;
	}
	
	private function get_image_alt($image_url) {
		$probonoseo_image_id = attachment_url_to_postid($image_url);
		
		if ($probonoseo_image_id) {
			$probonoseo_alt = get_post_meta($probonoseo_image_id, '_wp_attachment_image_alt', true);
			return $probonoseo_alt;
		}
		
		return '';
	}
	
	private function encode_japanese_url($url) {
		$probonoseo_parsed = wp_parse_url($url);
		
		if (!isset($probonoseo_parsed['path'])) {
			return $url;
		}
		
		$probonoseo_path_parts = explode('/', $probonoseo_parsed['path']);
		$probonoseo_encoded_parts = array();
		
		foreach ($probonoseo_path_parts as $probonoseo_part) {
			if (preg_match('/[ぁ-んァ-ヶー一-龯]/u', $probonoseo_part)) {
				$probonoseo_encoded_parts[] = rawurlencode($probonoseo_part);
			} else {
				$probonoseo_encoded_parts[] = $probonoseo_part;
			}
		}
		
		$probonoseo_parsed['path'] = implode('/', $probonoseo_encoded_parts);
		
		$probonoseo_scheme = isset($probonoseo_parsed['scheme']) ? $probonoseo_parsed['scheme'] . '://' : '';
		$probonoseo_host = isset($probonoseo_parsed['host']) ? $probonoseo_parsed['host'] : '';
		$probonoseo_port = isset($probonoseo_parsed['port']) ? ':' . $probonoseo_parsed['port'] : '';
		$probonoseo_path = isset($probonoseo_parsed['path']) ? $probonoseo_parsed['path'] : '';
		$probonoseo_query = isset($probonoseo_parsed['query']) ? '?' . $probonoseo_parsed['query'] : '';
		$probonoseo_fragment = isset($probonoseo_parsed['fragment']) ? '#' . $probonoseo_parsed['fragment'] : '';
		
		return $probonoseo_scheme . $probonoseo_host . $probonoseo_port . $probonoseo_path . $probonoseo_query . $probonoseo_fragment;
	}
}

function probonoseo_init_ogp() {
	ProbonoSEO_OGP::get_instance();
}
add_action('init', 'probonoseo_init_ogp');