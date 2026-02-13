<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Rating {
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
		return get_option('probonoseo_schema_rating', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_rating_enabled', true);
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
		$item_name = get_post_meta($post->ID, '_probonoseo_schema_rating_item_name', true);
		if (empty($item_name)) {
			$item_name = get_the_title($post);
		}
		$item_type = get_post_meta($post->ID, '_probonoseo_schema_rating_item_type', true);
		if (empty($item_type)) {
			$item_type = get_option('probonoseo_schema_rating_item_type', 'Product');
		}
		$rating_value = get_post_meta($post->ID, '_probonoseo_schema_rating_value', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_rating_count', true);
		$review_count = get_post_meta($post->ID, '_probonoseo_schema_rating_review_count', true);
		if (empty($rating_value) || empty($rating_count)) {
			return array();
		}
		$best_rating = get_post_meta($post->ID, '_probonoseo_schema_rating_best', true);
		if (empty($best_rating)) {
			$best_rating = get_option('probonoseo_schema_rating_scale', '5');
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => $item_type,
			'name' => $item_name,
			'url' => get_permalink($post),
			'aggregateRating' => array(
				'@type' => 'AggregateRating',
				'ratingValue' => floatval($rating_value),
				'ratingCount' => intval($rating_count),
				'bestRating' => intval($best_rating),
				'worstRating' => 1
			)
		);
		if (!empty($review_count)) {
			$schema['aggregateRating']['reviewCount'] = intval($review_count);
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
				'probonoseo_schema_rating',
				'AggregateRating schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_rating_save', 'probonoseo_schema_rating_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_rating_enabled', true);
		$item_name = get_post_meta($post->ID, '_probonoseo_schema_rating_item_name', true);
		$item_type = get_post_meta($post->ID, '_probonoseo_schema_rating_item_type', true);
		$value = get_post_meta($post->ID, '_probonoseo_schema_rating_value', true);
		$count = get_post_meta($post->ID, '_probonoseo_schema_rating_count', true);
		$review_count = get_post_meta($post->ID, '_probonoseo_schema_rating_review_count', true);
		$best = get_post_meta($post->ID, '_probonoseo_schema_rating_best', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_rating_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでAggregateRating schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>評価対象名</label>
				<input type="text" name="probonoseo_schema_rating_item_name" value="<?php echo esc_attr($item_name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>評価対象タイプ</label>
				<select name="probonoseo_schema_rating_item_type">
					<option value="Product" <?php selected($item_type, 'Product'); ?>>商品</option>
					<option value="Service" <?php selected($item_type, 'Service'); ?>>サービス</option>
					<option value="Organization" <?php selected($item_type, 'Organization'); ?>>組織</option>
					<option value="Place" <?php selected($item_type, 'Place'); ?>>場所</option>
					<option value="CreativeWork" <?php selected($item_type, 'CreativeWork'); ?>>作品</option>
					<option value="LocalBusiness" <?php selected($item_type, 'LocalBusiness'); ?>>ローカルビジネス</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>平均評価値</label>
				<input type="number" name="probonoseo_schema_rating_value" value="<?php echo esc_attr($value); ?>" step="0.1" min="1" max="10">
			</div>
			<div class="probonoseo-schema-row">
				<label>評価数</label>
				<input type="number" name="probonoseo_schema_rating_count" value="<?php echo esc_attr($count); ?>" min="1">
			</div>
			<div class="probonoseo-schema-row">
				<label>レビュー数（任意）</label>
				<input type="number" name="probonoseo_schema_rating_review_count" value="<?php echo esc_attr($review_count); ?>" min="0">
			</div>
			<div class="probonoseo-schema-row">
				<label>最高評価値</label>
				<select name="probonoseo_schema_rating_best">
					<option value="5" <?php selected($best, '5'); ?>>5</option>
					<option value="10" <?php selected($best, '10'); ?>>10</option>
					<option value="100" <?php selected($best, '100'); ?>>100</option>
				</select>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_rating_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_rating_nonce'], 'probonoseo_schema_rating_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'item_name', 'item_type', 'value', 'count', 'review_count', 'best');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_rating_' . $field;
			$meta_key = '_probonoseo_schema_rating_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Rating::get_instance();