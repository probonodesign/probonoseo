<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Author {

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

		add_action('wp_head', array($this, 'output_author_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_author_title'));
		add_action('show_user_profile', array($this, 'add_author_seo_fields'));
		add_action('edit_user_profile', array($this, 'add_author_seo_fields'));
		add_action('personal_options_update', array($this, 'save_author_seo_fields'));
		add_action('edit_user_profile_update', array($this, 'save_author_seo_fields'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_author', '1') === '1';
	}

	public function add_author_seo_fields($user) {
		$meta_title = get_user_meta($user->ID, '_probonoseo_author_title', true);
		$meta_desc = get_user_meta($user->ID, '_probonoseo_author_description', true);
		$meta_robots = get_user_meta($user->ID, '_probonoseo_author_robots', true);

		echo '<h3>ProbonoSEO - 著者アーカイブSEO設定</h3>';
		echo '<table class="form-table">';

		echo '<tr>';
		echo '<th><label for="probonoseo_author_title">SEOタイトル</label></th>';
		echo '<td><input type="text" id="probonoseo_author_title" name="probonoseo_author_title" value="' . esc_attr($meta_title) . '" class="regular-text"></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th><label for="probonoseo_author_description">メタディスクリプション</label></th>';
		echo '<td><textarea id="probonoseo_author_description" name="probonoseo_author_description" rows="3" cols="50">' . esc_textarea($meta_desc) . '</textarea></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th><label for="probonoseo_author_robots">robots設定</label></th>';
		echo '<td>';
		echo '<select id="probonoseo_author_robots" name="probonoseo_author_robots">';
		echo '<option value=""' . selected($meta_robots, '', false) . '>デフォルト</option>';
		echo '<option value="noindex"' . selected($meta_robots, 'noindex', false) . '>noindex</option>';
		echo '<option value="nofollow"' . selected($meta_robots, 'nofollow', false) . '>nofollow</option>';
		echo '<option value="noindex, nofollow"' . selected($meta_robots, 'noindex, nofollow', false) . '>noindex, nofollow</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';
	}

	public function save_author_seo_fields($user_id) {
		if (!current_user_can('edit_user', $user_id)) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core user profile update
		if (isset($_POST['probonoseo_author_title'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_user_meta($user_id, '_probonoseo_author_title', sanitize_text_field(wp_unslash($_POST['probonoseo_author_title'])));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core user profile update
		if (isset($_POST['probonoseo_author_description'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_user_meta($user_id, '_probonoseo_author_description', sanitize_textarea_field(wp_unslash($_POST['probonoseo_author_description'])));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core user profile update
		if (isset($_POST['probonoseo_author_robots'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_user_meta($user_id, '_probonoseo_author_robots', sanitize_text_field(wp_unslash($_POST['probonoseo_author_robots'])));
		}
	}

	public function output_author_meta() {
		if (!is_author()) {
			return;
		}

		$author = get_queried_object();

		if (!$author || !isset($author->ID)) {
			return;
		}

		$meta_desc = get_user_meta($author->ID, '_probonoseo_author_description', true);
		$meta_robots = get_user_meta($author->ID, '_probonoseo_author_robots', true);

		if (empty($meta_desc)) {
			$bio = get_the_author_meta('description', $author->ID);
			if (!empty($bio)) {
				$meta_desc = mb_substr(wp_strip_all_tags($bio), 0, 120);
			}
		}

		if (!empty($meta_desc)) {
			echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
		}

		if (!empty($meta_robots)) {
			echo '<meta name="robots" content="' . esc_attr($meta_robots) . '">' . "\n";
		}
	}

	public function filter_author_title($title) {
		if (!is_author()) {
			return $title;
		}

		$author = get_queried_object();

		if (!$author || !isset($author->ID)) {
			return $title;
		}

		$meta_title = get_user_meta($author->ID, '_probonoseo_author_title', true);

		if (!empty($meta_title)) {
			$title['title'] = $meta_title;
		}

		return $title;
	}
}

ProbonoSEO_Pro_Author::get_instance();