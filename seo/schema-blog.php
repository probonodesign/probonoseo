<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Blog {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('wp_head', array($this, 'output_schema'), 25);
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_post'), 10, 2);
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_schema_blog', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_blog_enabled', true);
		if ($probonoseo_enabled !== '1') {
			return;
		}
		$probonoseo_schema = $this->build_schema($post);
		if (empty($probonoseo_schema)) {
			return;
		}
		echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
	}

	public function build_schema($post) {
		$probonoseo_schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'BlogPosting',
			'headline' => get_the_title($post),
			'url' => get_permalink($post),
			'datePublished' => get_the_date('c', $post),
			'dateModified' => get_the_modified_date('c', $post)
		);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_blog_description', true);
		if (!empty($probonoseo_description)) {
			$probonoseo_schema['description'] = $probonoseo_description;
		}
		$probonoseo_section = get_post_meta($post->ID, '_probonoseo_schema_blog_section', true);
		if (!empty($probonoseo_section)) {
			$probonoseo_schema['articleSection'] = $probonoseo_section;
		}
		$probonoseo_author = get_the_author_meta('display_name', $post->post_author);
		$probonoseo_schema['author'] = array(
			'@type' => 'Person',
			'name' => $probonoseo_author
		);
		$probonoseo_publisher = get_option('probonoseo_schema_article_publisher', get_bloginfo('name'));
		$probonoseo_logo = get_option('probonoseo_schema_article_logo', '');
		$probonoseo_schema['publisher'] = array(
			'@type' => 'Organization',
			'name' => $probonoseo_publisher
		);
		if (!empty($probonoseo_logo)) {
			$probonoseo_schema['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url' => $probonoseo_logo
			);
		}
		if (has_post_thumbnail($post)) {
			$probonoseo_schema['image'] = get_the_post_thumbnail_url($post, 'large');
		}
		$probonoseo_content = wp_strip_all_tags($post->post_content);
		$probonoseo_schema['wordCount'] = mb_strlen($probonoseo_content);
		$probonoseo_schema['mainEntityOfPage'] = array(
			'@type' => 'WebPage',
			'@id' => get_permalink($post)
		);
		return $probonoseo_schema;
	}

	public function add_meta_boxes() {
		if (!$this->is_enabled()) {
			return;
		}
		$probonoseo_post_types = array('post', 'page');
		foreach ($probonoseo_post_types as $probonoseo_post_type) {
			add_meta_box(
				'probonoseo_schema_blog',
				'BlogPosting schema',
				array($this, 'render_metabox'),
				$probonoseo_post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_blog_save', 'probonoseo_schema_blog_nonce');
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_blog_enabled', true);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_blog_description', true);
		$probonoseo_section = get_post_meta($post->ID, '_probonoseo_schema_blog_section', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_blog_enabled" value="1" <?php checked($probonoseo_enabled, '1'); ?>> このページでBlogPosting schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_blog_description" rows="3" class="large-text"><?php echo esc_textarea($probonoseo_description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>カテゴリ/セクション</label>
				<input type="text" name="probonoseo_schema_blog_section" value="<?php echo esc_attr($probonoseo_section); ?>" placeholder="例: ライフスタイル、テクノロジー">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_blog_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_schema_blog_nonce'])), 'probonoseo_schema_blog_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$probonoseo_fields = array('enabled', 'description', 'section');
		foreach ($probonoseo_fields as $probonoseo_field) {
			$probonoseo_key = 'probonoseo_schema_blog_' . $probonoseo_field;
			$probonoseo_meta_key = '_probonoseo_schema_blog_' . $probonoseo_field;
			if (isset($_POST[$probonoseo_key])) {
				if ($probonoseo_field === 'description') {
					update_post_meta($post_id, $probonoseo_meta_key, sanitize_textarea_field(wp_unslash($_POST[$probonoseo_key])));
				} else {
					update_post_meta($post_id, $probonoseo_meta_key, sanitize_text_field(wp_unslash($_POST[$probonoseo_key])));
				}
			} else {
				delete_post_meta($post_id, $probonoseo_meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Blog::get_instance();