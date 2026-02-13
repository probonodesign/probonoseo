<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Dataset {
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
		return get_option('probonoseo_schema_dataset', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_dataset_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_dataset_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Dataset',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_dataset_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$creator = get_post_meta($post->ID, '_probonoseo_schema_dataset_creator', true);
		if (empty($creator)) {
			$creator = get_option('probonoseo_schema_dataset_creator', get_bloginfo('name'));
		}
		$schema['creator'] = array(
			'@type' => 'Organization',
			'name' => $creator
		);
		$keywords = get_post_meta($post->ID, '_probonoseo_schema_dataset_keywords', true);
		if (!empty($keywords)) {
			$schema['keywords'] = array_map('trim', explode(',', $keywords));
		}
		$license = get_post_meta($post->ID, '_probonoseo_schema_dataset_license', true);
		if (!empty($license)) {
			$schema['license'] = $license;
		}
		$temporal = get_post_meta($post->ID, '_probonoseo_schema_dataset_temporal', true);
		if (!empty($temporal)) {
			$schema['temporalCoverage'] = $temporal;
		}
		$spatial = get_post_meta($post->ID, '_probonoseo_schema_dataset_spatial', true);
		if (!empty($spatial)) {
			$schema['spatialCoverage'] = $spatial;
		}
		$download_url = get_post_meta($post->ID, '_probonoseo_schema_dataset_download', true);
		$format = get_post_meta($post->ID, '_probonoseo_schema_dataset_format', true);
		if (empty($format)) {
			$format = get_option('probonoseo_schema_dataset_format', 'CSV');
		}
		if (!empty($download_url)) {
			$schema['distribution'] = array(
				'@type' => 'DataDownload',
				'encodingFormat' => $format,
				'contentUrl' => $download_url
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
				'probonoseo_schema_dataset',
				'Dataset schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_dataset_save', 'probonoseo_schema_dataset_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_dataset_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_dataset_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_dataset_description', true);
		$creator = get_post_meta($post->ID, '_probonoseo_schema_dataset_creator', true);
		$keywords = get_post_meta($post->ID, '_probonoseo_schema_dataset_keywords', true);
		$license = get_post_meta($post->ID, '_probonoseo_schema_dataset_license', true);
		$temporal = get_post_meta($post->ID, '_probonoseo_schema_dataset_temporal', true);
		$spatial = get_post_meta($post->ID, '_probonoseo_schema_dataset_spatial', true);
		$download = get_post_meta($post->ID, '_probonoseo_schema_dataset_download', true);
		$format = get_post_meta($post->ID, '_probonoseo_schema_dataset_format', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_dataset_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでDataset schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>データセット名</label>
				<input type="text" name="probonoseo_schema_dataset_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_dataset_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>作成者/提供者</label>
				<input type="text" name="probonoseo_schema_dataset_creator" value="<?php echo esc_attr($creator); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>キーワード（カンマ区切り）</label>
				<input type="text" name="probonoseo_schema_dataset_keywords" value="<?php echo esc_attr($keywords); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>ライセンスURL</label>
				<input type="url" name="probonoseo_schema_dataset_license" value="<?php echo esc_attr($license); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>期間（temporalCoverage）</label>
				<input type="text" name="probonoseo_schema_dataset_temporal" value="<?php echo esc_attr($temporal); ?>" placeholder="例: 2020/2025">
			</div>
			<div class="probonoseo-schema-row">
				<label>対象地域</label>
				<input type="text" name="probonoseo_schema_dataset_spatial" value="<?php echo esc_attr($spatial); ?>" placeholder="例: 日本">
			</div>
			<div class="probonoseo-schema-row">
				<label>ダウンロードURL</label>
				<input type="url" name="probonoseo_schema_dataset_download" value="<?php echo esc_attr($download); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>ファイル形式</label>
				<select name="probonoseo_schema_dataset_format">
					<option value="CSV" <?php selected($format, 'CSV'); ?>>CSV</option>
					<option value="JSON" <?php selected($format, 'JSON'); ?>>JSON</option>
					<option value="XML" <?php selected($format, 'XML'); ?>>XML</option>
					<option value="Excel" <?php selected($format, 'Excel'); ?>>Excel</option>
					<option value="PDF" <?php selected($format, 'PDF'); ?>>PDF</option>
				</select>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_dataset_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_dataset_nonce'], 'probonoseo_schema_dataset_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'creator', 'keywords', 'license', 'temporal', 'spatial', 'download', 'format');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_dataset_' . $field;
			$meta_key = '_probonoseo_schema_dataset_' . $field;
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

ProbonoSEO_Schema_Dataset::get_instance();