<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_CPT {

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

		add_action('wp_head', array($this, 'output_cpt_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_cpt_title'));
		add_action('add_meta_boxes', array($this, 'add_cpt_metabox'));
		add_action('save_post', array($this, 'save_cpt_meta'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_cpt', '1') === '1';
	}

	public function get_custom_post_types() {
		$args = array(
			'public' => true,
			'_builtin' => false
		);
		return get_post_types($args, 'objects');
	}

	public function output_cpt_meta() {
		if (!is_singular()) {
			return;
		}

		$post_type = get_post_type();
		$builtin = array('post', 'page', 'attachment');

		if (in_array($post_type, $builtin)) {
			return;
		}

		$post_id = get_the_ID();
		$meta_title = get_post_meta($post_id, '_probonoseo_cpt_title', true);
		$meta_desc = get_post_meta($post_id, '_probonoseo_cpt_description', true);
		$meta_robots = get_post_meta($post_id, '_probonoseo_cpt_robots', true);

		if (!empty($meta_desc)) {
			echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
		}

		if (!empty($meta_robots)) {
			echo '<meta name="robots" content="' . esc_attr($meta_robots) . '">' . "\n";
		}
	}

	public function filter_cpt_title($title) {
		if (!is_singular()) {
			return $title;
		}

		$post_type = get_post_type();
		$builtin = array('post', 'page', 'attachment');

		if (in_array($post_type, $builtin)) {
			return $title;
		}

		$post_id = get_the_ID();
		$meta_title = get_post_meta($post_id, '_probonoseo_cpt_title', true);

		if (!empty($meta_title)) {
			$title['title'] = $meta_title;
		}

		return $title;
	}

	public function add_cpt_metabox() {
		$cpts = $this->get_custom_post_types();

		foreach ($cpts as $cpt) {
			add_meta_box(
				'probonoseo_cpt_seo',
				'ProbonoSEO - カスタム投稿SEO',
				array($this, 'render_metabox'),
				$cpt->name,
				'normal',
				'high'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_cpt_meta', 'probonoseo_cpt_nonce');

		$meta_title = get_post_meta($post->ID, '_probonoseo_cpt_title', true);
		$meta_desc = get_post_meta($post->ID, '_probonoseo_cpt_description', true);
		$meta_robots = get_post_meta($post->ID, '_probonoseo_cpt_robots', true);

		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th><label for="probonoseo_cpt_title">SEOタイトル</label></th>';
		echo '<td><input type="text" id="probonoseo_cpt_title" name="probonoseo_cpt_title" value="' . esc_attr($meta_title) . '" class="large-text"></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th><label for="probonoseo_cpt_description">メタディスクリプション</label></th>';
		echo '<td><textarea id="probonoseo_cpt_description" name="probonoseo_cpt_description" rows="3" class="large-text">' . esc_textarea($meta_desc) . '</textarea></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th><label for="probonoseo_cpt_robots">robots設定</label></th>';
		echo '<td>';
		echo '<select id="probonoseo_cpt_robots" name="probonoseo_cpt_robots">';
		echo '<option value=""' . selected($meta_robots, '', false) . '>デフォルト</option>';
		echo '<option value="noindex"' . selected($meta_robots, 'noindex', false) . '>noindex</option>';
		echo '<option value="nofollow"' . selected($meta_robots, 'nofollow', false) . '>nofollow</option>';
		echo '<option value="noindex, nofollow"' . selected($meta_robots, 'noindex, nofollow', false) . '>noindex, nofollow</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	public function save_cpt_meta($post_id) {
		if (!isset($_POST['probonoseo_cpt_nonce'])) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_cpt_nonce'], 'probonoseo_cpt_meta')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['probonoseo_cpt_title'])) {
			update_post_meta($post_id, '_probonoseo_cpt_title', sanitize_text_field(wp_unslash($_POST['probonoseo_cpt_title'])));
		}

		if (isset($_POST['probonoseo_cpt_description'])) {
			update_post_meta($post_id, '_probonoseo_cpt_description', sanitize_textarea_field(wp_unslash($_POST['probonoseo_cpt_description'])));
		}

		if (isset($_POST['probonoseo_cpt_robots'])) {
			update_post_meta($post_id, '_probonoseo_cpt_robots', sanitize_text_field(wp_unslash($_POST['probonoseo_cpt_robots'])));
		}
	}
}

ProbonoSEO_Pro_CPT::get_instance();