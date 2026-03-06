<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Metabox_SEO {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue_sidebar_assets'));
		add_action('save_post', array($this, 'save_seo_meta'), 10, 2);
		add_action('wp_ajax_probonoseo_load_seo_meta', array($this, 'ajax_load_seo_meta'));
		add_action('wp_ajax_probonoseo_save_seo_meta_ajax', array($this, 'ajax_save_seo_meta'));
	}

	public function enqueue_sidebar_assets($hook) {
		if (!in_array($hook, array('post.php', 'post-new.php'))) return;

		wp_enqueue_media();

		wp_enqueue_style(
			'probonoseo-seo-sidebar',
			plugin_dir_url(__FILE__) . 'gutenberg-sidebar-seo.css',
			array(),
			PROBONOSEO_VERSION
		);

		wp_enqueue_script(
			'probonoseo-seo-sidebar',
			plugin_dir_url(__FILE__) . 'gutenberg-sidebar-seo.js',
			array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'jquery'),
			PROBONOSEO_VERSION,
			true
		);

		wp_localize_script('probonoseo-seo-sidebar', 'probonoseoSEOSidebar', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('probonoseo_seo_nonce'),
			'post_id' => get_the_ID(),
			'home_url' => home_url(),
			'domain' => wp_parse_url(home_url(), PHP_URL_HOST)
		));
	}

	public function ajax_load_seo_meta() {
		check_ajax_referer('probonoseo_seo_nonce', 'nonce');

		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		if (!current_user_can('edit_post', $post_id)) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}

		wp_send_json_success(array(
			'custom_title' => get_post_meta($post_id, '_probonoseo_custom_title', true),
			'custom_description' => get_post_meta($post_id, '_probonoseo_custom_description', true),
			'og_title' => get_post_meta($post_id, '_probonoseo_og_title', true),
			'og_description' => get_post_meta($post_id, '_probonoseo_og_description', true),
			'og_image' => get_post_meta($post_id, '_probonoseo_og_image', true)
		));
	}

	public function ajax_save_seo_meta() {
		check_ajax_referer('probonoseo_seo_nonce', 'nonce');

		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		if (!current_user_can('edit_post', $post_id)) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}

		$fields = array(
			'custom_title' => '_probonoseo_custom_title',
			'custom_description' => '_probonoseo_custom_description',
			'og_title' => '_probonoseo_og_title',
			'og_description' => '_probonoseo_og_description'
		);

		foreach ($fields as $post_key => $meta_key) {
			if (isset($_POST[$post_key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$post_key])));
			}
		}

		if (isset($_POST['og_image'])) {
			update_post_meta($post_id, '_probonoseo_og_image', esc_url_raw(wp_unslash($_POST['og_image'])));
		}

		wp_send_json_success(array('message' => '保存しました'));
	}

	public function save_seo_meta($post_id, $post) {
		if (!isset($_POST['probonoseo_seo_nonce'])) return;
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_seo_nonce'])), 'probonoseo_seo_metabox')) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!current_user_can('edit_post', $post_id)) return;

		$fields = array(
			'probonoseo_custom_title' => '_probonoseo_custom_title',
			'probonoseo_custom_description' => '_probonoseo_custom_description',
			'probonoseo_og_title' => '_probonoseo_og_title',
			'probonoseo_og_description' => '_probonoseo_og_description'
		);

		foreach ($fields as $post_key => $meta_key) {
			if (isset($_POST[$post_key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$post_key])));
			}
		}

		if (isset($_POST['probonoseo_og_image'])) {
			update_post_meta($post_id, '_probonoseo_og_image', esc_url_raw(wp_unslash($_POST['probonoseo_og_image'])));
		}
	}
}

ProbonoSEO_Metabox_SEO::get_instance();