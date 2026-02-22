<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Canonical {
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
		if (get_option('probonoseo_basic_canonical', '1') === '1') {
			add_action('wp_head', array($this, 'output_canonical'), 2);
		}
	}
	
	public function output_canonical() {
		if ($this->should_skip_canonical()) {
			return;
		}
		
		if (get_option('probonoseo_canonical_auto', '1') !== '1') {
			return;
		}
		
		$url = $this->get_canonical_url();
		
		if (!empty($url)) {
			echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
		}
	}
	
	private function should_skip_canonical() {
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
	
	public function get_canonical_url() {
		$url = '';
		
		if (is_singular()) {
			$url = get_permalink();
		} elseif (is_home() || is_front_page()) {
			$url = home_url('/');
		} elseif (is_category() || is_tag() || is_tax()) {
			$term = get_queried_object();
			if ($term) {
				$url = get_term_link($term);
			}
		} elseif (is_post_type_archive()) {
			$post_type = get_query_var('post_type');
			if (is_array($post_type)) {
				$post_type = reset($post_type);
			}
			$url = get_post_type_archive_link($post_type);
		} elseif (is_author()) {
			$author = get_queried_object();
			if ($author) {
				$url = get_author_posts_url($author->ID);
			}
		} elseif (is_search()) {
			return '';
		} elseif (is_404()) {
			return '';
		}
		
		if (is_paged() && get_query_var('paged') > 1) {
			return '';
		}
		
		if (empty($url) || is_wp_error($url)) {
			return '';
		}
		
		if (get_option('probonoseo_canonical_params', '1') === '1') {
			$url = $this->remove_url_parameters($url);
		}
		
		if (get_option('probonoseo_canonical_slash', '1') === '1') {
			$url = $this->normalize_trailing_slash($url);
		}
		
		if (get_option('probonoseo_canonical_merge', '1') === '1') {
			$url = $this->merge_duplicate_urls($url);
		}
		
		return $url;
	}
	
	private function remove_url_parameters($url) {
		$allowed_params = array('p', 'page_id', 'preview', 'preview_id', 'preview_nonce');
		
		$parsed = wp_parse_url($url);
		
		if (!isset($parsed['query'])) {
			return $url;
		}
		
		parse_str($parsed['query'], $params);
		
		$filtered_params = array();
		foreach ($params as $key => $value) {
			if (in_array($key, $allowed_params, true)) {
				$filtered_params[$key] = $value;
			}
		}
		
		$base_url = $parsed['scheme'] . '://' . $parsed['host'];
		
		if (isset($parsed['port']) && $parsed['port'] != 80 && $parsed['port'] != 443) {
			$base_url .= ':' . $parsed['port'];
		}
		
		if (isset($parsed['path'])) {
			$base_url .= $parsed['path'];
		}
		
		if (!empty($filtered_params)) {
			$base_url .= '?' . http_build_query($filtered_params);
		}
		
		if (isset($parsed['fragment'])) {
			$base_url .= '#' . $parsed['fragment'];
		}
		
		return $base_url;
	}
	
	private function normalize_trailing_slash($url) {
		if (is_singular()) {
			return untrailingslashit($url);
		} else {
			return trailingslashit($url);
		}
	}
	
	private function merge_duplicate_urls($url) {
		$parsed = wp_parse_url($url);
		
		if (!isset($parsed['host'])) {
			return $url;
		}
		
		$host = $parsed['host'];
		
		$host = preg_replace('/^www\./i', '', $host);
		
		$protocol = is_ssl() ? 'https://' : 'http://';
		
		$clean_url = $protocol . $host;
		
		if (isset($parsed['port']) && $parsed['port'] != 80 && $parsed['port'] != 443) {
			$clean_url .= ':' . $parsed['port'];
		}
		
		if (isset($parsed['path'])) {
			$clean_url .= $parsed['path'];
		}
		
		if (isset($parsed['query'])) {
			$clean_url .= '?' . $parsed['query'];
		}
		
		if (isset($parsed['fragment'])) {
			$clean_url .= '#' . $parsed['fragment'];
		}
		
		return $clean_url;
	}
}

function probonoseo_init_canonical() {
	ProbonoSEO_Canonical::get_instance();
}
add_action('init', 'probonoseo_init_canonical');

function probonoseo_get_canonical_url() {
	$canonical = ProbonoSEO_Canonical::get_instance();
	return $canonical->get_canonical_url();
}