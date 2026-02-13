<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Article {
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
		return get_option('probonoseo_schema_article', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_article_enabled', true);
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
		$probonoseo_article_type = get_post_meta($post->ID, '_probonoseo_schema_article_type', true);
		if (empty($probonoseo_article_type)) {
			$probonoseo_article_type = get_option('probonoseo_schema_article_type', 'Article');
		}
		$probonoseo_schema = array(
			'@context' => 'https://schema.org',
			'@type' => $probonoseo_article_type,
			'headline' => get_the_title($post),
			'url' => get_permalink($post),
			'datePublished' => get_the_date('c', $post),
			'dateModified' => get_the_modified_date('c', $post)
		);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_article_description', true);
		if (!empty($probonoseo_description)) {
			$probonoseo_schema['description'] = $probonoseo_description;
		} else {
			$probonoseo_excerpt = get_the_excerpt($post);
			if (!empty($probonoseo_excerpt)) {
				$probonoseo_schema['description'] = $probonoseo_excerpt;
			}
		}
		$probonoseo_author_auto = get_option('probonoseo_schema_article_author_auto', '1');
		if ($probonoseo_author_auto === '1') {
			$probonoseo_author = get_the_author_meta('display_name', $post->post_author);
			$probonoseo_schema['author'] = array(
				'@type' => 'Person',
				'name' => $probonoseo_author
			);
		} else {
			$probonoseo_author_name = get_post_meta($post->ID, '_probonoseo_schema_article_author', true);
			if (!empty($probonoseo_author_name)) {
				$probonoseo_schema['author'] = array(
					'@type' => 'Person',
					'name' => $probonoseo_author_name
				);
			}
		}
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
		$probonoseo_keywords = get_post_meta($post->ID, '_probonoseo_schema_article_keywords', true);
		if (!empty($probonoseo_keywords)) {
			$probonoseo_schema['keywords'] = $probonoseo_keywords;
		}
		$probonoseo_word_count = get_post_meta($post->ID, '_probonoseo_schema_article_wordcount', true);
		if (!empty($probonoseo_word_count)) {
			$probonoseo_schema['wordCount'] = intval($probonoseo_word_count);
		} else {
			$probonoseo_content = wp_strip_all_tags($post->post_content);
			$probonoseo_schema['wordCount'] = mb_strlen($probonoseo_content);
		}
		$probonoseo_main_entity = get_permalink($post);
		$probonoseo_schema['mainEntityOfPage'] = array(
			'@type' => 'WebPage',
			'@id' => $probonoseo_main_entity
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
				'probonoseo_schema_article',
				'Article schema',
				array($this, 'render_metabox'),
				$probonoseo_post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_article_save', 'probonoseo_schema_article_nonce');
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_article_enabled', true);
		$probonoseo_type = get_post_meta($post->ID, '_probonoseo_schema_article_type', true);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_article_description', true);
		$probonoseo_author = get_post_meta($post->ID, '_probonoseo_schema_article_author', true);
		$probonoseo_keywords = get_post_meta($post->ID, '_probonoseo_schema_article_keywords', true);
		$probonoseo_wordcount = get_post_meta($post->ID, '_probonoseo_schema_article_wordcount', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_article_enabled" value="1" <?php checked($probonoseo_enabled, '1'); ?>> このページでArticle schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>記事タイプ</label>
				<select name="probonoseo_schema_article_type">
					<option value="Article" <?php selected($probonoseo_type, 'Article'); ?>>一般記事</option>
					<option value="NewsArticle" <?php selected($probonoseo_type, 'NewsArticle'); ?>>ニュース記事</option>
					<option value="BlogPosting" <?php selected($probonoseo_type, 'BlogPosting'); ?>>ブログ記事</option>
					<option value="TechArticle" <?php selected($probonoseo_type, 'TechArticle'); ?>>技術記事</option>
					<option value="ScholarlyArticle" <?php selected($probonoseo_type, 'ScholarlyArticle'); ?>>学術記事</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>説明（空欄時は抜粋を使用）</label>
				<textarea name="probonoseo_schema_article_description" rows="3" class="large-text"><?php echo esc_textarea($probonoseo_description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>著者名（自動取得OFF時のみ使用）</label>
				<input type="text" name="probonoseo_schema_article_author" value="<?php echo esc_attr($probonoseo_author); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>キーワード（カンマ区切り）</label>
				<input type="text" name="probonoseo_schema_article_keywords" value="<?php echo esc_attr($probonoseo_keywords); ?>" class="large-text" placeholder="例: SEO, WordPress, プラグイン">
			</div>
			<div class="probonoseo-schema-row">
				<label>文字数（空欄時は自動計算）</label>
				<input type="number" name="probonoseo_schema_article_wordcount" value="<?php echo esc_attr($probonoseo_wordcount); ?>" min="0">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_article_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_schema_article_nonce'])), 'probonoseo_schema_article_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$probonoseo_fields = array('enabled', 'type', 'description', 'author', 'keywords', 'wordcount');
		foreach ($probonoseo_fields as $probonoseo_field) {
			$probonoseo_key = 'probonoseo_schema_article_' . $probonoseo_field;
			$probonoseo_meta_key = '_probonoseo_schema_article_' . $probonoseo_field;
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

ProbonoSEO_Schema_Article::get_instance();