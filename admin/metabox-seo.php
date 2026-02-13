<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Metabox_SEO {

	private static $instance = null;
	private $is_pro = false;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->is_pro = $this->check_pro_active();

		add_action('admin_enqueue_scripts', array($this, 'enqueue_sidebar_assets'));
		add_action('save_post', array($this, 'save_seo_meta'), 10, 2);
		add_action('wp_ajax_probonoseo_load_seo_meta', array($this, 'ajax_load_seo_meta'));
		add_action('wp_ajax_probonoseo_save_seo_meta_ajax', array($this, 'ajax_save_seo_meta'));

		if ($this->is_pro) {
			add_action('wp_ajax_probonoseo_calculate_seo_score', array($this, 'ajax_calculate_seo_score'));
			add_action('wp_ajax_probonoseo_get_internal_link_suggestions', array($this, 'ajax_get_internal_links'));
		}
	}

	private function check_pro_active() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		return $license->is_pro_active();
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
			'is_pro' => $this->is_pro ? '1' : '0',
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
			'og_image' => get_post_meta($post_id, '_probonoseo_og_image', true),
			'focus_keyword' => get_post_meta($post_id, '_probonoseo_focus_keyword', true)
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

		if ($this->is_pro && isset($_POST['focus_keyword'])) {
			update_post_meta($post_id, '_probonoseo_focus_keyword', sanitize_text_field(wp_unslash($_POST['focus_keyword'])));
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

		if ($this->is_pro && isset($_POST['probonoseo_focus_keyword'])) {
			update_post_meta($post_id, '_probonoseo_focus_keyword', sanitize_text_field(wp_unslash($_POST['probonoseo_focus_keyword'])));
		}
	}

	public function ajax_calculate_seo_score() {
		check_ajax_referer('probonoseo_seo_nonce', 'nonce');

		if (!$this->is_pro) {
			wp_send_json_error(array('message' => 'Pro版が必要です'));
			return;
		}

		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
		$description = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
		$focus_keyword = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : '';
		$content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

		if (!class_exists('ProbonoSEO_Post_SEO_Checker')) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'seo/post-seo-checker.php';
		}
		if (!class_exists('ProbonoSEO_Post_SEO_Score')) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'seo/post-seo-score.php';
		}

		$checker = ProbonoSEO_Post_SEO_Checker::get_instance();
		$scorer = ProbonoSEO_Post_SEO_Score::get_instance();

		$analysis = $checker->analyze_content($content, $title, $description, $focus_keyword);
		$scores = $scorer->calculate_scores($analysis, $title, $description, $focus_keyword);
		$checklist = $checker->get_checklist($analysis, $title, $description, $focus_keyword);
		$suggestions = $checker->get_suggestions($analysis, $title, $description, $focus_keyword);

		wp_send_json_success(array(
			'scores' => $scores,
			'analysis' => $analysis,
			'checklist' => $checklist,
			'suggestions' => $suggestions
		));
	}

	public function ajax_get_internal_links() {
		check_ajax_referer('probonoseo_seo_nonce', 'nonce');

		if (!$this->is_pro) {
			wp_send_json_error(array('message' => 'Pro版が必要です'));
			return;
		}

		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$focus_keyword = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : '';
		$content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

		$args = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => 10,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			'post__not_in' => array($post_id),
			'orderby' => 'relevance',
			's' => $focus_keyword
		);

		$query = new WP_Query($args);
		$suggestions = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$suggestions[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'url' => get_permalink(),
					'excerpt' => wp_trim_words(get_the_excerpt(), 15)
				);
			}
			wp_reset_postdata();
		}

		wp_send_json_success(array('suggestions' => $suggestions));
	}
}

ProbonoSEO_Metabox_SEO::get_instance();