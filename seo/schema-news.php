<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_News {
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
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_schema_news', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_news_enabled', true);
		if ($enabled !== '1') {
			return;
		}
		$schema = $this->build_schema($post);
		if (empty($schema)) {
			return;
		}
		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
	}

	public function build_schema($post) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'NewsArticle',
			'headline' => get_the_title($post),
			'url' => get_permalink($post),
			'datePublished' => get_the_date('c', $post),
			'dateModified' => get_the_modified_date('c', $post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_news_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$dateline = get_post_meta($post->ID, '_probonoseo_schema_news_dateline', true);
		if (!empty($dateline)) {
			$schema['dateline'] = $dateline;
		}
		$section = get_post_meta($post->ID, '_probonoseo_schema_news_section', true);
		if (!empty($section)) {
			$schema['articleSection'] = $section;
		}
		$author = get_the_author_meta('display_name', $post->post_author);
		$schema['author'] = array(
			'@type' => 'Person',
			'name' => $author
		);
		$publisher = get_option('probonoseo_schema_article_publisher', get_bloginfo('name'));
		$logo = get_option('probonoseo_schema_article_logo', '');
		$schema['publisher'] = array(
			'@type' => 'Organization',
			'name' => $publisher
		);
		if (!empty($logo)) {
			$schema['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url' => $logo
			);
		}
		if (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'large');
		}
		$schema['mainEntityOfPage'] = array(
			'@type' => 'WebPage',
			'@id' => get_permalink($post)
		);
		return $schema;
	}

	public function add_meta_boxes() {
		if (!$this->is_enabled()) {
			return;
		}
		$post_types = array('post', 'page');
		foreach ($post_types as $post_type) {
			add_meta_box(
				'probonoseo_schema_news',
				'NewsArticle schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_news_save', 'probonoseo_schema_news_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_news_enabled', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_news_description', true);
		$dateline = get_post_meta($post->ID, '_probonoseo_schema_news_dateline', true);
		$section = get_post_meta($post->ID, '_probonoseo_schema_news_section', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_news_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでNewsArticle schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_news_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>発信地（dateline）</label>
				<input type="text" name="probonoseo_schema_news_dateline" value="<?php echo esc_attr($dateline); ?>" placeholder="例: 東京">
			</div>
			<div class="probonoseo-schema-row">
				<label>セクション</label>
				<input type="text" name="probonoseo_schema_news_section" value="<?php echo esc_attr($section); ?>" placeholder="例: 政治、経済、スポーツ">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_news_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_news_nonce'], 'probonoseo_schema_news_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'description', 'dateline', 'section');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_news_' . $field;
			$meta_key = '_probonoseo_schema_news_' . $field;
			if (isset($_POST[$key])) {
				if ($field === 'description') {
					update_post_meta($post_id, $meta_key, sanitize_textarea_field(wp_unslash($_POST[$key])));
				} else {
					update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
				}
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_News::get_instance();