<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Book {
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
		return get_option('probonoseo_schema_book', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_book_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_book_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Book',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$author = get_post_meta($post->ID, '_probonoseo_schema_book_author', true);
		if (!empty($author)) {
			$schema['author'] = array(
				'@type' => 'Person',
				'name' => $author
			);
		}
		$isbn = get_post_meta($post->ID, '_probonoseo_schema_book_isbn', true);
		if (!empty($isbn)) {
			$schema['isbn'] = $isbn;
		}
		$publisher = get_post_meta($post->ID, '_probonoseo_schema_book_publisher', true);
		if (!empty($publisher)) {
			$schema['publisher'] = array(
				'@type' => 'Organization',
				'name' => $publisher
			);
		}
		$date = get_post_meta($post->ID, '_probonoseo_schema_book_date', true);
		if (!empty($date)) {
			$schema['datePublished'] = $date;
		}
		$format = get_post_meta($post->ID, '_probonoseo_schema_book_format', true);
		if (empty($format)) {
			$format = get_option('probonoseo_schema_book_format', 'Paperback');
		}
		$schema['bookFormat'] = 'https://schema.org/' . $format;
		$language = get_post_meta($post->ID, '_probonoseo_schema_book_language', true);
		if (empty($language)) {
			$language = get_option('probonoseo_schema_book_language', 'ja');
		}
		$schema['inLanguage'] = $language;
		$pages = get_post_meta($post->ID, '_probonoseo_schema_book_pages', true);
		if (!empty($pages)) {
			$schema['numberOfPages'] = intval($pages);
		}
		if (has_post_thumbnail($post)) {
			$schema['image'] = get_the_post_thumbnail_url($post, 'large');
		}
		$rating = get_post_meta($post->ID, '_probonoseo_schema_book_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_book_rating_count', true);
		if (!empty($rating) && !empty($rating_count)) {
			$schema['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => floatval($rating),
				'ratingCount' => intval($rating_count),
				'bestRating' => 5,
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
				'probonoseo_schema_book',
				'Book schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_book_save', 'probonoseo_schema_book_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_book_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_book_name', true);
		$author = get_post_meta($post->ID, '_probonoseo_schema_book_author', true);
		$isbn = get_post_meta($post->ID, '_probonoseo_schema_book_isbn', true);
		$publisher = get_post_meta($post->ID, '_probonoseo_schema_book_publisher', true);
		$date = get_post_meta($post->ID, '_probonoseo_schema_book_date', true);
		$format = get_post_meta($post->ID, '_probonoseo_schema_book_format', true);
		$language = get_post_meta($post->ID, '_probonoseo_schema_book_language', true);
		$pages = get_post_meta($post->ID, '_probonoseo_schema_book_pages', true);
		$rating = get_post_meta($post->ID, '_probonoseo_schema_book_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_book_rating_count', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_book_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでBook schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>書籍名</label>
				<input type="text" name="probonoseo_schema_book_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>著者名</label>
				<input type="text" name="probonoseo_schema_book_author" value="<?php echo esc_attr($author); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>ISBN</label>
				<input type="text" name="probonoseo_schema_book_isbn" value="<?php echo esc_attr($isbn); ?>" placeholder="例: 978-4-xxx-xxxxx-x">
			</div>
			<div class="probonoseo-schema-row">
				<label>出版社</label>
				<input type="text" name="probonoseo_schema_book_publisher" value="<?php echo esc_attr($publisher); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>出版日</label>
				<input type="date" name="probonoseo_schema_book_date" value="<?php echo esc_attr($date); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>フォーマット</label>
				<select name="probonoseo_schema_book_format">
					<option value="Hardcover" <?php selected($format, 'Hardcover'); ?>>ハードカバー</option>
					<option value="Paperback" <?php selected($format, 'Paperback'); ?>>ペーパーバック</option>
					<option value="EBook" <?php selected($format, 'EBook'); ?>>電子書籍</option>
					<option value="AudioBook" <?php selected($format, 'AudioBook'); ?>>オーディオブック</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>言語</label>
				<select name="probonoseo_schema_book_language">
					<option value="ja" <?php selected($language, 'ja'); ?>>日本語</option>
					<option value="en" <?php selected($language, 'en'); ?>>英語</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>ページ数</label>
				<input type="number" name="probonoseo_schema_book_pages" value="<?php echo esc_attr($pages); ?>" min="1">
			</div>
			<div class="probonoseo-schema-row">
				<label>評価（1-5）</label>
				<input type="number" name="probonoseo_schema_book_rating" value="<?php echo esc_attr($rating); ?>" step="0.1" min="1" max="5">
				<label>レビュー数</label>
				<input type="number" name="probonoseo_schema_book_rating_count" value="<?php echo esc_attr($rating_count); ?>" min="0">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_book_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_book_nonce'], 'probonoseo_schema_book_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'author', 'isbn', 'publisher', 'date', 'format', 'language', 'pages', 'rating', 'rating_count');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_book_' . $field;
			$meta_key = '_probonoseo_schema_book_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Book::get_instance();