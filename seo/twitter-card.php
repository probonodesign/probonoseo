<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Twitter {
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
		if (get_option('probonoseo_basic_twitter', '0') === '1') {
			add_action('wp_head', array($this, 'output_twitter_card'), 5);
		}
	}
	
	public function output_twitter_card() {
		if ($this->should_skip_twitter()) {
			return;
		}
		
		$data = $this->get_twitter_data();
		
		$card_type = !empty($data['image']) ? 'summary_large_image' : 'summary';
		
		echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr($data['title']) . '">' . "\n";
		
		if (!empty($data['desc'])) {
			echo '<meta name="twitter:description" content="' . esc_attr($data['desc']) . '">' . "\n";
		}
		
		if (!empty($data['image'])) {
			echo '<meta name="twitter:image" content="' . esc_url($data['image']) . '">' . "\n";
		}
	}
	
	private function should_skip_twitter() {
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
	
	private function get_twitter_data() {
		$ogp_instance = ProbonoSEO_OGP::get_instance();
		
		$title = $this->get_twitter_title();
		$desc = $this->get_twitter_description();
		$image = $this->get_twitter_image();
		
		return array(
			'title' => $title,
			'desc' => $desc,
			'image' => $image,
		);
	}
	
	private function get_twitter_title() {
		return wp_get_document_title();
	}
	
	private function get_twitter_description() {
		if (is_singular()) {
			global $post;
			
			if (!empty($post->post_excerpt)) {
				return $post->post_excerpt;
			}
			
			$content = wp_strip_all_tags($post->post_content);
			$content = strip_shortcodes($content);
			$content = preg_replace('/\s+/', ' ', $content);
			return mb_substr($content, 0, 100);
		}
		
		return get_bloginfo('description');
	}
	
	private function get_twitter_image() {
		if (is_singular() && has_post_thumbnail()) {
			return get_the_post_thumbnail_url(get_the_ID(), 'large');
		}
		
		if (get_option('probonoseo_ogp_image_fixed', '0') === '1') {
			return get_option('probonoseo_fixed_ogp_image_url', '');
		}
		
		return '';
	}
}

function probonoseo_init_twitter() {
	ProbonoSEO_Twitter::get_instance();
}
add_action('init', 'probonoseo_init_twitter');