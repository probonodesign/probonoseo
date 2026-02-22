<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_Taxonomy {

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

		$taxonomies = $this->get_custom_taxonomies();

		foreach ($taxonomies as $taxonomy) {
			add_action($taxonomy->name . '_add_form_fields', array($this, 'add_taxonomy_fields'));
			add_action($taxonomy->name . '_edit_form_fields', array($this, 'edit_taxonomy_fields'));
			add_action('created_' . $taxonomy->name, array($this, 'save_taxonomy_fields'));
			add_action('edited_' . $taxonomy->name, array($this, 'save_taxonomy_fields'));
		}

		add_action('wp_head', array($this, 'output_taxonomy_meta'), 5);
		add_filter('document_title_parts', array($this, 'filter_taxonomy_title'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_taxonomy', '1') === '1';
	}

	public function get_custom_taxonomies() {
		$args = array(
			'public' => true,
			'_builtin' => false
		);
		return get_taxonomies($args, 'objects');
	}

	public function add_taxonomy_fields($taxonomy) {
		echo '<div class="form-field">';
		echo '<label for="probonoseo_tax_title">SEOタイトル</label>';
		echo '<input type="text" id="probonoseo_tax_title" name="probonoseo_tax_title" value="">';
		echo '</div>';

		echo '<div class="form-field">';
		echo '<label for="probonoseo_tax_description">メタディスクリプション</label>';
		echo '<textarea id="probonoseo_tax_description" name="probonoseo_tax_description" rows="3"></textarea>';
		echo '</div>';

		echo '<div class="form-field">';
		echo '<label for="probonoseo_tax_robots">robots設定</label>';
		echo '<select id="probonoseo_tax_robots" name="probonoseo_tax_robots">';
		echo '<option value="">デフォルト</option>';
		echo '<option value="noindex">noindex</option>';
		echo '<option value="nofollow">nofollow</option>';
		echo '<option value="noindex, nofollow">noindex, nofollow</option>';
		echo '</select>';
		echo '</div>';
	}

	public function edit_taxonomy_fields($term) {
		$meta_title = get_term_meta($term->term_id, '_probonoseo_tax_title', true);
		$meta_desc = get_term_meta($term->term_id, '_probonoseo_tax_description', true);
		$meta_robots = get_term_meta($term->term_id, '_probonoseo_tax_robots', true);

		echo '<tr class="form-field">';
		echo '<th><label for="probonoseo_tax_title">SEOタイトル</label></th>';
		echo '<td><input type="text" id="probonoseo_tax_title" name="probonoseo_tax_title" value="' . esc_attr($meta_title) . '" class="large-text"></td>';
		echo '</tr>';

		echo '<tr class="form-field">';
		echo '<th><label for="probonoseo_tax_description">メタディスクリプション</label></th>';
		echo '<td><textarea id="probonoseo_tax_description" name="probonoseo_tax_description" rows="3" class="large-text">' . esc_textarea($meta_desc) . '</textarea></td>';
		echo '</tr>';

		echo '<tr class="form-field">';
		echo '<th><label for="probonoseo_tax_robots">robots設定</label></th>';
		echo '<td>';
		echo '<select id="probonoseo_tax_robots" name="probonoseo_tax_robots">';
		echo '<option value=""' . selected($meta_robots, '', false) . '>デフォルト</option>';
		echo '<option value="noindex"' . selected($meta_robots, 'noindex', false) . '>noindex</option>';
		echo '<option value="nofollow"' . selected($meta_robots, 'nofollow', false) . '>nofollow</option>';
		echo '<option value="noindex, nofollow"' . selected($meta_robots, 'noindex, nofollow', false) . '>noindex, nofollow</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
	}

	public function save_taxonomy_fields($term_id) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
		if (isset($_POST['probonoseo_tax_title'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_term_meta($term_id, '_probonoseo_tax_title', sanitize_text_field(wp_unslash($_POST['probonoseo_tax_title'])));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
		if (isset($_POST['probonoseo_tax_description'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_term_meta($term_id, '_probonoseo_tax_description', sanitize_textarea_field(wp_unslash($_POST['probonoseo_tax_description'])));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
		if (isset($_POST['probonoseo_tax_robots'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core
			update_term_meta($term_id, '_probonoseo_tax_robots', sanitize_text_field(wp_unslash($_POST['probonoseo_tax_robots'])));
		}
	}

	public function output_taxonomy_meta() {
		if (!is_tax()) {
			return;
		}

		$term = get_queried_object();

		if (!$term || !isset($term->term_id)) {
			return;
		}

		$builtin = array('category', 'post_tag');

		if (in_array($term->taxonomy, $builtin)) {
			return;
		}

		$meta_desc = get_term_meta($term->term_id, '_probonoseo_tax_description', true);
		$meta_robots = get_term_meta($term->term_id, '_probonoseo_tax_robots', true);

		if (!empty($meta_desc)) {
			echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
		}

		if (!empty($meta_robots)) {
			echo '<meta name="robots" content="' . esc_attr($meta_robots) . '">' . "\n";
		}
	}

	public function filter_taxonomy_title($title) {
		if (!is_tax()) {
			return $title;
		}

		$term = get_queried_object();

		if (!$term || !isset($term->term_id)) {
			return $title;
		}

		$builtin = array('category', 'post_tag');

		if (in_array($term->taxonomy, $builtin)) {
			return $title;
		}

		$meta_title = get_term_meta($term->term_id, '_probonoseo_tax_title', true);

		if (!empty($meta_title)) {
			$title['title'] = $meta_title;
		}

		return $title;
	}
}

ProbonoSEO_Pro_Taxonomy::get_instance();