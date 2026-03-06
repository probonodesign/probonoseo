<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_AMP {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('init', array($this, 'init'));
	}

	public function init() {
		if (!$this->is_enabled()) {
			return;
		}

		add_action('wp_head', array($this, 'output_amp_link'));
		add_filter('amp_post_template_meta_parts', array($this, 'amp_meta_parts'));
		add_action('amp_post_template_head', array($this, 'amp_head_meta'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_amp', '1') === '1';
	}

	public function is_amp_plugin_active() {
		return function_exists('amp_is_request') || function_exists('is_amp_endpoint');
	}

	public function is_amp_request() {
		if (function_exists('amp_is_request')) {
			return amp_is_request();
		}
		if (function_exists('is_amp_endpoint')) {
			return is_amp_endpoint();
		}
		return false;
	}

	public function output_amp_link() {
		if (!is_singular()) {
			return;
		}

		if ($this->is_amp_request()) {
			return;
		}

		if (!$this->is_amp_plugin_active()) {
			return;
		}

		$post_id = get_the_ID();
		$amp_url = $this->get_amp_url($post_id);

		if ($amp_url) {
			echo '<link rel="amphtml" href="' . esc_url($amp_url) . '">' . "\n";
		}
	}

	public function get_amp_url($post_id) {
		if (function_exists('amp_get_permalink')) {
			return amp_get_permalink($post_id);
		}

		$permalink = get_permalink($post_id);

		if (get_option('permalink_structure')) {
			return trailingslashit($permalink) . 'amp/';
		}

		return add_query_arg('amp', '1', $permalink);
	}

	public function amp_meta_parts($parts) {
		return $parts;
	}

	public function amp_head_meta() {
		if (!$this->is_amp_request()) {
			return;
		}

		$post_id = get_the_ID();

		if (!$post_id) {
			return;
		}

		$canonical = get_permalink($post_id);
		echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

		$meta_desc = get_post_meta($post_id, '_probonoseo_meta_description', true);

		if (empty($meta_desc)) {
			$content = get_post_field('post_content', $post_id);
			$meta_desc = mb_substr(wp_strip_all_tags($content), 0, 120);
		}

		if (!empty($meta_desc)) {
			echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
		}

		$this->output_amp_schema($post_id);
	}

	private function output_amp_schema($post_id) {
		$post = get_post($post_id);

		if (!$post) {
			return;
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Article',
			'headline' => get_the_title($post_id),
			'datePublished' => get_the_date('c', $post_id),
			'dateModified' => get_the_modified_date('c', $post_id),
			'author' => array(
				'@type' => 'Person',
				'name' => get_the_author_meta('display_name', $post->post_author)
			),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => get_bloginfo('name')
			)
		);

		$thumbnail_id = get_post_thumbnail_id($post_id);

		if ($thumbnail_id) {
			$image = wp_get_attachment_image_src($thumbnail_id, 'full');
			if ($image) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url' => $image[0],
					'width' => $image[1],
					'height' => $image[2]
				);
			}
		}

		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
	}
}

ProbonoSEO_Pro_AMP::get_instance();