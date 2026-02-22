<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Course {
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
		return get_option('probonoseo_schema_course', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_course_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_course_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Course',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_course_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$provider = get_post_meta($post->ID, '_probonoseo_schema_course_provider', true);
		if (empty($provider)) {
			$provider = get_option('probonoseo_schema_course_provider', '');
		}
		if (!empty($provider)) {
			$schema['provider'] = array(
				'@type' => 'Organization',
				'name' => $provider
			);
		}
		$mode = get_post_meta($post->ID, '_probonoseo_schema_course_mode', true);
		if (empty($mode)) {
			$mode = get_option('probonoseo_schema_course_mode', 'Online');
		}
		$language = get_post_meta($post->ID, '_probonoseo_schema_course_language', true);
		if (empty($language)) {
			$language = get_option('probonoseo_schema_course_language', 'ja');
		}
		$schema['inLanguage'] = $language;
		$duration = get_post_meta($post->ID, '_probonoseo_schema_course_duration', true);
		$start_date = get_post_meta($post->ID, '_probonoseo_schema_course_start', true);
		$end_date = get_post_meta($post->ID, '_probonoseo_schema_course_end', true);
		$price = get_post_meta($post->ID, '_probonoseo_schema_course_price', true);
		$currency = get_post_meta($post->ID, '_probonoseo_schema_course_currency', true);
		if (empty($currency)) {
			$currency = get_option('probonoseo_schema_course_currency', 'JPY');
		}
		$course_instance = array(
			'@type' => 'CourseInstance',
			'courseMode' => $mode
		);
		if (!empty($duration)) {
			$course_instance['duration'] = $duration;
		}
		if (!empty($start_date)) {
			$course_instance['startDate'] = $start_date;
		}
		if (!empty($end_date)) {
			$course_instance['endDate'] = $end_date;
		}
		$schema['hasCourseInstance'] = $course_instance;
		if ($price !== '' && $price !== false) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'price' => floatval($price),
				'priceCurrency' => $currency,
				'availability' => 'https://schema.org/InStock'
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
				'probonoseo_schema_course',
				'Course schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_course_save', 'probonoseo_schema_course_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_course_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_course_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_course_description', true);
		$provider = get_post_meta($post->ID, '_probonoseo_schema_course_provider', true);
		$mode = get_post_meta($post->ID, '_probonoseo_schema_course_mode', true);
		$language = get_post_meta($post->ID, '_probonoseo_schema_course_language', true);
		$duration = get_post_meta($post->ID, '_probonoseo_schema_course_duration', true);
		$start = get_post_meta($post->ID, '_probonoseo_schema_course_start', true);
		$end = get_post_meta($post->ID, '_probonoseo_schema_course_end', true);
		$price = get_post_meta($post->ID, '_probonoseo_schema_course_price', true);
		$currency = get_post_meta($post->ID, '_probonoseo_schema_course_currency', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_course_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでCourse schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>コース名</label>
				<input type="text" name="probonoseo_schema_course_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_course_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>提供者</label>
				<input type="text" name="probonoseo_schema_course_provider" value="<?php echo esc_attr($provider); ?>" placeholder="例: 〇〇スクール">
			</div>
			<div class="probonoseo-schema-row">
				<label>配信方法</label>
				<select name="probonoseo_schema_course_mode">
					<option value="Online" <?php selected($mode, 'Online'); ?>>オンライン</option>
					<option value="Onsite" <?php selected($mode, 'Onsite'); ?>>対面</option>
					<option value="Blended" <?php selected($mode, 'Blended'); ?>>ブレンド</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>言語</label>
				<select name="probonoseo_schema_course_language">
					<option value="ja" <?php selected($language, 'ja'); ?>>日本語</option>
					<option value="en" <?php selected($language, 'en'); ?>>英語</option>
					<option value="zh" <?php selected($language, 'zh'); ?>>中国語</option>
					<option value="ko" <?php selected($language, 'ko'); ?>>韓国語</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>期間（ISO 8601形式）</label>
				<input type="text" name="probonoseo_schema_course_duration" value="<?php echo esc_attr($duration); ?>" placeholder="例: PT2H（2時間）、P3M（3ヶ月）">
			</div>
			<div class="probonoseo-schema-row">
				<label>開始日</label>
				<input type="date" name="probonoseo_schema_course_start" value="<?php echo esc_attr($start); ?>">
				<label>終了日</label>
				<input type="date" name="probonoseo_schema_course_end" value="<?php echo esc_attr($end); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>価格</label>
				<input type="number" name="probonoseo_schema_course_price" value="<?php echo esc_attr($price); ?>" step="1" min="0">
				<select name="probonoseo_schema_course_currency">
					<option value="JPY" <?php selected($currency, 'JPY'); ?>>JPY</option>
					<option value="USD" <?php selected($currency, 'USD'); ?>>USD</option>
					<option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR</option>
				</select>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_course_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_course_nonce'], 'probonoseo_schema_course_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'provider', 'mode', 'language', 'duration', 'start', 'end', 'price', 'currency');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_course_' . $field;
			$meta_key = '_probonoseo_schema_course_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Course::get_instance();