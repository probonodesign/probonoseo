<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Image {
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
		return get_option('probonoseo_schema_image', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_image_enabled', true);
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
		$image_url = get_post_meta($post->ID, '_probonoseo_schema_image_url', true);
		if (empty($image_url) && has_post_thumbnail($post)) {
			$image_url = get_the_post_thumbnail_url($post, 'full');
		}
		if (empty($image_url)) {
			return array();
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'ImageObject',
			'url' => $image_url,
			'contentUrl' => $image_url
		);
		$name = get_post_meta($post->ID, '_probonoseo_schema_image_name', true);
		if (!empty($name)) {
			$schema['name'] = $name;
		}
		$caption = get_post_meta($post->ID, '_probonoseo_schema_image_caption', true);
		if (!empty($caption)) {
			$schema['caption'] = $caption;
		}
		$description = get_post_meta($post->ID, '_probonoseo_schema_image_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$copyright = get_post_meta($post->ID, '_probonoseo_schema_image_copyright', true);
		if (empty($copyright)) {
			$copyright = get_option('probonoseo_schema_image_copyright', get_bloginfo('name'));
		}
		$schema['copyrightHolder'] = array(
			'@type' => 'Organization',
			'name' => $copyright
		);
		$license = get_post_meta($post->ID, '_probonoseo_schema_image_license', true);
		if (empty($license)) {
			$license = get_option('probonoseo_schema_image_license', '');
		}
		if (!empty($license)) {
			$schema['license'] = $license;
		}
		$width = get_post_meta($post->ID, '_probonoseo_schema_image_width', true);
		$height = get_post_meta($post->ID, '_probonoseo_schema_image_height', true);
		if (!empty($width)) {
			$schema['width'] = intval($width);
		}
		if (!empty($height)) {
			$schema['height'] = intval($height);
		}
		$upload_date = get_post_meta($post->ID, '_probonoseo_schema_image_date', true);
		if (!empty($upload_date)) {
			$schema['uploadDate'] = $upload_date;
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
				'probonoseo_schema_image',
				'ImageObject schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_image_save', 'probonoseo_schema_image_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_image_enabled', true);
		$url = get_post_meta($post->ID, '_probonoseo_schema_image_url', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_image_name', true);
		$caption = get_post_meta($post->ID, '_probonoseo_schema_image_caption', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_image_description', true);
		$copyright = get_post_meta($post->ID, '_probonoseo_schema_image_copyright', true);
		$license = get_post_meta($post->ID, '_probonoseo_schema_image_license', true);
		$width = get_post_meta($post->ID, '_probonoseo_schema_image_width', true);
		$height = get_post_meta($post->ID, '_probonoseo_schema_image_height', true);
		$date = get_post_meta($post->ID, '_probonoseo_schema_image_date', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_image_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでImageObject schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>画像URL（空欄時はアイキャッチ）</label>
				<input type="url" name="probonoseo_schema_image_url" value="<?php echo esc_attr($url); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>画像名</label>
				<input type="text" name="probonoseo_schema_image_name" value="<?php echo esc_attr($name); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>キャプション</label>
				<input type="text" name="probonoseo_schema_image_caption" value="<?php echo esc_attr($caption); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_image_description" rows="2" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>著作権者</label>
				<input type="text" name="probonoseo_schema_image_copyright" value="<?php echo esc_attr($copyright); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>ライセンスURL</label>
				<input type="url" name="probonoseo_schema_image_license" value="<?php echo esc_attr($license); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>幅（px）</label>
				<input type="number" name="probonoseo_schema_image_width" value="<?php echo esc_attr($width); ?>" min="1">
				<label>高さ（px）</label>
				<input type="number" name="probonoseo_schema_image_height" value="<?php echo esc_attr($height); ?>" min="1">
			</div>
			<div class="probonoseo-schema-row">
				<label>アップロード日</label>
				<input type="date" name="probonoseo_schema_image_date" value="<?php echo esc_attr($date); ?>">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_image_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_image_nonce'], 'probonoseo_schema_image_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'url', 'name', 'caption', 'description', 'copyright', 'license', 'width', 'height', 'date');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_image_' . $field;
			$meta_key = '_probonoseo_schema_image_' . $field;
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

ProbonoSEO_Schema_Image::get_instance();