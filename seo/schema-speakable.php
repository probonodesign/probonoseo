<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Speakable {
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
		return get_option('probonoseo_schema_speakable', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_speakable_enabled', true);
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
		$target = get_post_meta($post->ID, '_probonoseo_schema_speakable_target', true);
		if (empty($target)) {
			$target = get_option('probonoseo_schema_speakable_target', 'headline');
		}
		$css_selectors = array();
		if ($target === 'headline' || $target === 'both') {
			$css_selectors[] = '.entry-title';
			$css_selectors[] = 'h1';
		}
		if ($target === 'summary' || $target === 'both') {
			$css_selectors[] = '.entry-summary';
			$css_selectors[] = '.excerpt';
		}
		if ($target === 'custom') {
			$custom_selector = get_post_meta($post->ID, '_probonoseo_schema_speakable_selector', true);
			if (empty($custom_selector)) {
				$custom_selector = get_option('probonoseo_schema_speakable_selector', '');
			}
			if (!empty($custom_selector)) {
				$css_selectors = array_map('trim', explode(',', $custom_selector));
			}
		}
		if (empty($css_selectors)) {
			return array();
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebPage',
			'name' => get_the_title($post),
			'url' => get_permalink($post),
			'speakable' => array(
				'@type' => 'SpeakableSpecification',
				'cssSelector' => $css_selectors
			)
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
				'probonoseo_schema_speakable',
				'Speakable schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_speakable_save', 'probonoseo_schema_speakable_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_speakable_enabled', true);
		$target = get_post_meta($post->ID, '_probonoseo_schema_speakable_target', true);
		$selector = get_post_meta($post->ID, '_probonoseo_schema_speakable_selector', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_speakable_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでSpeakable schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>読み上げ対象</label>
				<select name="probonoseo_schema_speakable_target">
					<option value="headline" <?php selected($target, 'headline'); ?>>タイトルのみ</option>
					<option value="summary" <?php selected($target, 'summary'); ?>>要約のみ</option>
					<option value="both" <?php selected($target, 'both'); ?>>タイトル＋要約</option>
					<option value="custom" <?php selected($target, 'custom'); ?>>カスタム指定</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>CSSセレクタ（カスタム時、カンマ区切り）</label>
				<input type="text" name="probonoseo_schema_speakable_selector" value="<?php echo esc_attr($selector); ?>" class="large-text" placeholder="例: .speakable-content, .intro">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_speakable_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_speakable_nonce'], 'probonoseo_schema_speakable_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'target', 'selector');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_speakable_' . $field;
			$meta_key = '_probonoseo_schema_speakable_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Speakable::get_instance();