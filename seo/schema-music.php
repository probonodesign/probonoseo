<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Music {
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
		return get_option('probonoseo_schema_music', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_music_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_music_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'MusicAlbum',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$artist = get_post_meta($post->ID, '_probonoseo_schema_music_artist', true);
		if (!empty($artist)) {
			$schema['byArtist'] = array(
				'@type' => 'MusicGroup',
				'name' => $artist
			);
		}
		$date = get_post_meta($post->ID, '_probonoseo_schema_music_date', true);
		if (!empty($date)) {
			$schema['datePublished'] = $date;
		}
		$album_type = get_post_meta($post->ID, '_probonoseo_schema_music_type', true);
		if (empty($album_type)) {
			$album_type = get_option('probonoseo_schema_music_type', 'MusicAlbum');
		}
		$schema['@type'] = $album_type;
		$genre = get_post_meta($post->ID, '_probonoseo_schema_music_genre', true);
		if (!empty($genre)) {
			$schema['genre'] = $genre;
		}
		$tracks = get_post_meta($post->ID, '_probonoseo_schema_music_tracks', true);
		if (!empty($tracks)) {
			$schema['numTracks'] = intval($tracks);
		}
		$label = get_post_meta($post->ID, '_probonoseo_schema_music_label', true);
		if (!empty($label)) {
			$schema['recordLabel'] = array(
				'@type' => 'Organization',
				'name' => $label
			);
		}
		if (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'large');
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
				'probonoseo_schema_music',
				'MusicAlbum schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_music_save', 'probonoseo_schema_music_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_music_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_music_name', true);
		$artist = get_post_meta($post->ID, '_probonoseo_schema_music_artist', true);
		$date = get_post_meta($post->ID, '_probonoseo_schema_music_date', true);
		$type = get_post_meta($post->ID, '_probonoseo_schema_music_type', true);
		$genre = get_post_meta($post->ID, '_probonoseo_schema_music_genre', true);
		$tracks = get_post_meta($post->ID, '_probonoseo_schema_music_tracks', true);
		$label = get_post_meta($post->ID, '_probonoseo_schema_music_label', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_music_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでMusicAlbum schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>アルバム名</label>
				<input type="text" name="probonoseo_schema_music_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>アーティスト名</label>
				<input type="text" name="probonoseo_schema_music_artist" value="<?php echo esc_attr($artist); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>発売日</label>
				<input type="date" name="probonoseo_schema_music_date" value="<?php echo esc_attr($date); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>タイプ</label>
				<select name="probonoseo_schema_music_type">
					<option value="MusicAlbum" <?php selected($type, 'MusicAlbum'); ?>>アルバム</option>
					<option value="SingleRelease" <?php selected($type, 'SingleRelease'); ?>>シングル</option>
					<option value="EPRelease" <?php selected($type, 'EPRelease'); ?>>EP</option>
					<option value="CompilationAlbum" <?php selected($type, 'CompilationAlbum'); ?>>コンピレーション</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>ジャンル</label>
				<input type="text" name="probonoseo_schema_music_genre" value="<?php echo esc_attr($genre); ?>" placeholder="例: J-POP, ロック">
			</div>
			<div class="probonoseo-schema-row">
				<label>収録曲数</label>
				<input type="number" name="probonoseo_schema_music_tracks" value="<?php echo esc_attr($tracks); ?>" min="1">
			</div>
			<div class="probonoseo-schema-row">
				<label>レコードレーベル</label>
				<input type="text" name="probonoseo_schema_music_label" value="<?php echo esc_attr($label); ?>">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_music_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_music_nonce'], 'probonoseo_schema_music_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'artist', 'date', 'type', 'genre', 'tracks', 'label');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_music_' . $field;
			$meta_key = '_probonoseo_schema_music_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Music::get_instance();