<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed {
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
		if (get_option('probonoseo_speed_lazy_images', '1') === '1') {
			add_filter('the_content', array($this, 'add_lazy_loading_images'));
			add_filter('post_thumbnail_html', array($this, 'add_lazy_loading_thumbnail'));
		}
		
		if (get_option('probonoseo_speed_lazy_iframes', '1') === '1') {
			add_filter('the_content', array($this, 'add_lazy_loading_iframes'));
		}
		
		if (get_option('probonoseo_speed_minify_css', '1') === '1') {
			add_filter('style_loader_tag', array($this, 'minify_css_output'), 10, 2);
		}
		
		if (get_option('probonoseo_speed_minify_js', '1') === '1') {
			add_filter('script_loader_tag', array($this, 'minify_js_output'), 10, 2);
		}
		
		if (get_option('probonoseo_speed_optimize_wp_scripts', '1') === '1') {
			add_action('wp_enqueue_scripts', array($this, 'optimize_wp_scripts'), 100);
			add_action('wp_head', array($this, 'remove_emoji_scripts'), 1);
		}
	}
	
	public function add_lazy_loading_images($content) {
		if (is_admin() || is_feed()) {
			return $content;
		}
		
		$content = preg_replace_callback(
			'/<img([^>]+?)src=["\']([^"\']+)["\']([^>]*?)>/i',
			function($matches) {
				$before = $matches[1];
				$src = $matches[2];
				$after = $matches[3];
				
				if (strpos($before . $after, 'loading=') !== false) {
					return $matches[0];
				}
				
				return '<img' . $before . 'src="' . $src . '"' . $after . ' loading="lazy">';
			},
			$content
		);
		
		return $content;
	}
	
	public function add_lazy_loading_thumbnail($html) {
		if (strpos($html, 'loading=') !== false) {
			return $html;
		}
		
		return str_replace('<img', '<img loading="lazy"', $html);
	}
	
	public function add_lazy_loading_iframes($content) {
		if (is_admin() || is_feed()) {
			return $content;
		}
		
		$content = preg_replace_callback(
			'/<iframe([^>]+?)>/i',
			function($matches) {
				$attrs = $matches[1];
				
				if (strpos($attrs, 'loading=') !== false) {
					return $matches[0];
				}
				
				return '<iframe' . $attrs . ' loading="lazy">';
			},
			$content
		);
		
		return $content;
	}
	
	public function minify_css_output($html, $handle) {
		return $html;
	}
	
	public function minify_js_output($html, $handle) {
		return $html;
	}
	
	public function optimize_wp_scripts() {
		if (!is_admin()) {
			wp_deregister_script('wp-embed');
			
			global $wp_scripts;
			if (isset($wp_scripts->registered['jquery'])) {
				$jquery_dependencies = $wp_scripts->registered['jquery']->deps;
				if (($key = array_search('jquery-migrate', $jquery_dependencies)) !== false) {
					unset($jquery_dependencies[$key]);
					$wp_scripts->registered['jquery']->deps = $jquery_dependencies;
				}
			}
		}
	}
	
	public function remove_emoji_scripts() {
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		
		add_filter('tiny_mce_plugins', array($this, 'disable_emojis_tinymce'));
		add_filter('wp_resource_hints', array($this, 'disable_emojis_dns_prefetch'), 10, 2);
	}
	
	public function disable_emojis_tinymce($plugins) {
		if (is_array($plugins)) {
			return array_diff($plugins, array('wpemoji'));
		}
		return array();
	}
	
	public function disable_emojis_dns_prefetch($urls, $relation_type) {
		if ('dns-prefetch' === $relation_type) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
			$urls = array_diff($urls, array($emoji_svg_url));
		}
		return $urls;
	}
}

function probonoseo_init_speed() {
	ProbonoSEO_Speed::get_instance();
}
add_action('init', 'probonoseo_init_speed');