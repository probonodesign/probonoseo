<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Podcast {
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
		add_action('wp_ajax_probonoseo_save_podcast', array($this, 'ajax_save'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_serp_podcast', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_podcast_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_podcast_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'PodcastSeries',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_podcast_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		} else {
			$excerpt = get_the_excerpt($post);
			if (!empty($excerpt)) {
				$schema['description'] = $excerpt;
			}
		}
		$image = get_post_meta($post->ID, '_probonoseo_schema_podcast_image', true);
		if (!empty($image)) {
			$schema['image'] = $image;
		} elseif (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'full');
		}
		$author = get_post_meta($post->ID, '_probonoseo_schema_podcast_author', true);
		if (!empty($author)) {
			$schema['author'] = array('@type' => 'Person', 'name' => $author);
		}
		$publisher = get_post_meta($post->ID, '_probonoseo_schema_podcast_publisher', true);
		if (!empty($publisher)) {
			$schema['publisher'] = array('@type' => 'Organization', 'name' => $publisher);
		}
		$feed_url = get_post_meta($post->ID, '_probonoseo_schema_podcast_feed_url', true);
		if (!empty($feed_url)) {
			$schema['webFeed'] = $feed_url;
		}
		$language = get_post_meta($post->ID, '_probonoseo_schema_podcast_language', true);
		if (!empty($language)) {
			$schema['inLanguage'] = $language;
		}
		$genre = get_post_meta($post->ID, '_probonoseo_schema_podcast_genre', true);
		if (!empty($genre)) {
			$schema['genre'] = $genre;
		}
		$episode_count = get_post_meta($post->ID, '_probonoseo_schema_podcast_episode_count', true);
		if (!empty($episode_count)) {
			$schema['numberOfEpisodes'] = intval($episode_count);
		}
		return $schema;
	}

	public function add_meta_boxes() {
		if (!$this->is_enabled()) {
			return;
		}
		$post_types = array('post', 'page');
		foreach ($post_types as $post_type) {
			add_meta_box(
				'probonoseo_schema_podcast_metabox',
				'Podcast schema（ポッドキャスト）',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_podcast_save', 'probonoseo_schema_podcast_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_podcast_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_podcast_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_podcast_description', true);
		$author = get_post_meta($post->ID, '_probonoseo_schema_podcast_author', true);
		$publisher = get_post_meta($post->ID, '_probonoseo_schema_podcast_publisher', true);
		$feed_url = get_post_meta($post->ID, '_probonoseo_schema_podcast_feed_url', true);
		$language = get_post_meta($post->ID, '_probonoseo_schema_podcast_language', true);
		$genre = get_post_meta($post->ID, '_probonoseo_schema_podcast_genre', true);
		$episode_count = get_post_meta($post->ID, '_probonoseo_schema_podcast_episode_count', true);
		$image = get_post_meta($post->ID, '_probonoseo_schema_podcast_image', true);
		?>
		<div class="probonoseo-schema-container">
			<div class="probonoseo-schema-grid">
				<div class="probonoseo-schema-row probonoseo-schema-grid-full">
					<label><input type="checkbox" name="probonoseo_schema_podcast_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでPodcast schemaを出力する</label>
				</div>
				<div class="probonoseo-schema-row">
					<label>ポッドキャスト名</label>
					<input type="text" name="probonoseo_schema_podcast_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
				</div>
				<div class="probonoseo-schema-row">
					<label>著者</label>
					<input type="text" name="probonoseo_schema_podcast_author" value="<?php echo esc_attr($author); ?>">
				</div>
				<div class="probonoseo-schema-row">
					<label>配信元</label>
					<input type="text" name="probonoseo_schema_podcast_publisher" value="<?php echo esc_attr($publisher); ?>">
				</div>
				<div class="probonoseo-schema-row">
					<label>フィードURL</label>
					<input type="url" name="probonoseo_schema_podcast_feed_url" value="<?php echo esc_attr($feed_url); ?>" class="large-text">
				</div>
				<div class="probonoseo-schema-row">
					<label>言語</label>
					<input type="text" name="probonoseo_schema_podcast_language" value="<?php echo esc_attr($language); ?>" placeholder="例: ja">
				</div>
				<div class="probonoseo-schema-row">
					<label>ジャンル</label>
					<input type="text" name="probonoseo_schema_podcast_genre" value="<?php echo esc_attr($genre); ?>">
				</div>
				<div class="probonoseo-schema-row">
					<label>エピソード数</label>
					<input type="number" name="probonoseo_schema_podcast_episode_count" value="<?php echo esc_attr($episode_count); ?>" min="0">
				</div>
				<div class="probonoseo-schema-row">
					<label>画像URL</label>
					<input type="url" name="probonoseo_schema_podcast_image" value="<?php echo esc_attr($image); ?>" class="large-text">
				</div>
				<div class="probonoseo-schema-row probonoseo-schema-grid-full">
					<label>説明</label>
					<textarea name="probonoseo_schema_podcast_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
				</div>
			</div>
			<div class="probonoseo-schema-save-row">
				<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="<?php echo esc_attr($post->ID); ?>" data-action="probonoseo_save_podcast" data-nonce="<?php echo esc_attr(wp_create_nonce('probonoseo_save_podcast')); ?>">保存</button>
				<span class="probonoseo-schema-save-msg"></span>
			</div>
		</div>
		<?php
	}

	public function ajax_save() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_podcast')) {
			wp_send_json_error();
		}
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		if (!$post_id) {
			wp_send_json_error();
		}
		$fields = array('enabled', 'name', 'description', 'author', 'publisher', 'feed_url', 'language', 'genre', 'episode_count', 'image');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_podcast_' . $field;
			$meta_key = '_probonoseo_schema_podcast_' . $field;
			if (isset($_POST[$key]) && $_POST[$key] !== '') {
				if ($field === 'description') {
					update_post_meta($post_id, $meta_key, sanitize_textarea_field(wp_unslash($_POST[$key])));
				} else {
					update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
				}
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
		wp_send_json_success();
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_podcast_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_schema_podcast_nonce'])), 'probonoseo_schema_podcast_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'author', 'publisher', 'feed_url', 'language', 'genre', 'episode_count', 'image');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_podcast_' . $field;
			$meta_key = '_probonoseo_schema_podcast_' . $field;
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

ProbonoSEO_Schema_Podcast::get_instance();