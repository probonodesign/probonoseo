<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Movie {
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
		return get_option('probonoseo_schema_movie', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_movie_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_movie_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Movie',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_movie_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$director = get_post_meta($post->ID, '_probonoseo_schema_movie_director', true);
		if (!empty($director)) {
			$schema['director'] = array(
				'@type' => 'Person',
				'name' => $director
			);
		}
		$actors = get_post_meta($post->ID, '_probonoseo_schema_movie_actors', true);
		if (!empty($actors)) {
			$actor_list = array_map('trim', explode(',', $actors));
			$schema['actor'] = array();
			foreach ($actor_list as $actor) {
				$schema['actor'][] = array(
					'@type' => 'Person',
					'name' => $actor
				);
			}
		}
		$date = get_post_meta($post->ID, '_probonoseo_schema_movie_date', true);
		if (!empty($date)) {
			$schema['datePublished'] = $date;
		}
		$duration = get_post_meta($post->ID, '_probonoseo_schema_movie_duration', true);
		if (!empty($duration)) {
			$schema['duration'] = $duration;
		}
		$genre = get_post_meta($post->ID, '_probonoseo_schema_movie_genre', true);
		if (!empty($genre)) {
			$schema['genre'] = $genre;
		}
		$language = get_post_meta($post->ID, '_probonoseo_schema_movie_language', true);
		if (empty($language)) {
			$language = get_option('probonoseo_schema_movie_language', 'ja');
		}
		$schema['inLanguage'] = $language;
		$country = get_post_meta($post->ID, '_probonoseo_schema_movie_country', true);
		if (empty($country)) {
			$country = get_option('probonoseo_schema_movie_country', 'JP');
		}
		$schema['countryOfOrigin'] = array(
			'@type' => 'Country',
			'name' => $country
		);
		if (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'large');
		}
		$rating = get_post_meta($post->ID, '_probonoseo_schema_movie_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_movie_rating_count', true);
		if (!empty($rating) && !empty($rating_count)) {
			$schema['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => floatval($rating),
				'ratingCount' => intval($rating_count),
				'bestRating' => 10,
				'worstRating' => 1
			);
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
				'probonoseo_schema_movie',
				'Movie schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_movie_save', 'probonoseo_schema_movie_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_movie_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_movie_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_movie_description', true);
		$director = get_post_meta($post->ID, '_probonoseo_schema_movie_director', true);
		$actors = get_post_meta($post->ID, '_probonoseo_schema_movie_actors', true);
		$date = get_post_meta($post->ID, '_probonoseo_schema_movie_date', true);
		$duration = get_post_meta($post->ID, '_probonoseo_schema_movie_duration', true);
		$genre = get_post_meta($post->ID, '_probonoseo_schema_movie_genre', true);
		$language = get_post_meta($post->ID, '_probonoseo_schema_movie_language', true);
		$country = get_post_meta($post->ID, '_probonoseo_schema_movie_country', true);
		$rating = get_post_meta($post->ID, '_probonoseo_schema_movie_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_movie_rating_count', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_movie_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでMovie schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>映画タイトル</label>
				<input type="text" name="probonoseo_schema_movie_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>あらすじ</label>
				<textarea name="probonoseo_schema_movie_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>監督名</label>
				<input type="text" name="probonoseo_schema_movie_director" value="<?php echo esc_attr($director); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>出演者（カンマ区切り）</label>
				<input type="text" name="probonoseo_schema_movie_actors" value="<?php echo esc_attr($actors); ?>" class="large-text" placeholder="例: 山田太郎, 鈴木花子">
			</div>
			<div class="probonoseo-schema-row">
				<label>公開日</label>
				<input type="date" name="probonoseo_schema_movie_date" value="<?php echo esc_attr($date); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>上映時間（ISO 8601形式）</label>
				<input type="text" name="probonoseo_schema_movie_duration" value="<?php echo esc_attr($duration); ?>" placeholder="例: PT2H30M（2時間30分）">
			</div>
			<div class="probonoseo-schema-row">
				<label>ジャンル</label>
				<input type="text" name="probonoseo_schema_movie_genre" value="<?php echo esc_attr($genre); ?>" placeholder="例: アクション, SF">
			</div>
			<div class="probonoseo-schema-row">
				<label>言語</label>
				<select name="probonoseo_schema_movie_language">
					<option value="ja" <?php selected($language, 'ja'); ?>>日本語</option>
					<option value="en" <?php selected($language, 'en'); ?>>英語</option>
				</select>
				<label>製作国</label>
				<select name="probonoseo_schema_movie_country">
					<option value="JP" <?php selected($country, 'JP'); ?>>日本</option>
					<option value="US" <?php selected($country, 'US'); ?>>アメリカ</option>
					<option value="GB" <?php selected($country, 'GB'); ?>>イギリス</option>
					<option value="KR" <?php selected($country, 'KR'); ?>>韓国</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>評価（1-10）</label>
				<input type="number" name="probonoseo_schema_movie_rating" value="<?php echo esc_attr($rating); ?>" step="0.1" min="1" max="10">
				<label>レビュー数</label>
				<input type="number" name="probonoseo_schema_movie_rating_count" value="<?php echo esc_attr($rating_count); ?>" min="0">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_movie_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_movie_nonce'], 'probonoseo_schema_movie_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'director', 'actors', 'date', 'duration', 'genre', 'language', 'country', 'rating', 'rating_count');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_movie_' . $field;
			$meta_key = '_probonoseo_schema_movie_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Movie::get_instance();