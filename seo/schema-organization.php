<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Organization {
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
		return get_option('probonoseo_schema_organization', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_org_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_org_name', true);
		if (empty($name)) {
			$name = get_bloginfo('name');
		}
		$org_type = get_post_meta($post->ID, '_probonoseo_schema_org_type', true);
		if (empty($org_type)) {
			$org_type = get_option('probonoseo_schema_org_type', 'Organization');
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => $org_type,
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_org_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$logo = get_post_meta($post->ID, '_probonoseo_schema_org_logo', true);
		if (!empty($logo)) {
			$schema['logo'] = $logo;
		}
		$founded = get_post_meta($post->ID, '_probonoseo_schema_org_founded', true);
		if (empty($founded)) {
			$founded = get_option('probonoseo_schema_org_founded', '');
		}
		if (!empty($founded)) {
			$schema['foundingDate'] = $founded;
		}
		$address = get_post_meta($post->ID, '_probonoseo_schema_org_address', true);
		if (empty($address)) {
			$address = get_option('probonoseo_schema_org_address', '');
		}
		if (!empty($address)) {
			$schema['address'] = array(
				'@type' => 'PostalAddress',
				'streetAddress' => $address
			);
		}
		$phone = get_post_meta($post->ID, '_probonoseo_schema_org_phone', true);
		if (!empty($phone)) {
			$schema['telephone'] = $phone;
		}
		$email = get_post_meta($post->ID, '_probonoseo_schema_org_email', true);
		if (!empty($email)) {
			$schema['email'] = $email;
		}
		$social = get_post_meta($post->ID, '_probonoseo_schema_org_social', true);
		if (!empty($social)) {
			$social_urls = array_filter(array_map('trim', explode("\n", $social)));
			if (!empty($social_urls)) {
				$schema['sameAs'] = $social_urls;
			}
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
				'probonoseo_schema_org',
				'Organization schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_org_save', 'probonoseo_schema_org_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_org_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_org_name', true);
		$type = get_post_meta($post->ID, '_probonoseo_schema_org_type', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_org_description', true);
		$logo = get_post_meta($post->ID, '_probonoseo_schema_org_logo', true);
		$founded = get_post_meta($post->ID, '_probonoseo_schema_org_founded', true);
		$address = get_post_meta($post->ID, '_probonoseo_schema_org_address', true);
		$phone = get_post_meta($post->ID, '_probonoseo_schema_org_phone', true);
		$email = get_post_meta($post->ID, '_probonoseo_schema_org_email', true);
		$social = get_post_meta($post->ID, '_probonoseo_schema_org_social', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_org_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでOrganization schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>組織名</label>
				<input type="text" name="probonoseo_schema_org_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はサイト名を使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>組織タイプ</label>
				<select name="probonoseo_schema_org_type">
					<option value="Organization" <?php selected($type, 'Organization'); ?>>一般組織</option>
					<option value="Corporation" <?php selected($type, 'Corporation'); ?>>株式会社</option>
					<option value="EducationalOrganization" <?php selected($type, 'EducationalOrganization'); ?>>教育機関</option>
					<option value="GovernmentOrganization" <?php selected($type, 'GovernmentOrganization'); ?>>政府機関</option>
					<option value="LocalBusiness" <?php selected($type, 'LocalBusiness'); ?>>ローカルビジネス</option>
					<option value="MedicalOrganization" <?php selected($type, 'MedicalOrganization'); ?>>医療機関</option>
					<option value="NGO" <?php selected($type, 'NGO'); ?>>NGO</option>
					<option value="PerformingGroup" <?php selected($type, 'PerformingGroup'); ?>>パフォーマンスグループ</option>
					<option value="SportsOrganization" <?php selected($type, 'SportsOrganization'); ?>>スポーツ組織</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_org_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>ロゴURL</label>
				<input type="url" name="probonoseo_schema_org_logo" value="<?php echo esc_attr($logo); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>設立年</label>
				<input type="text" name="probonoseo_schema_org_founded" value="<?php echo esc_attr($founded); ?>" placeholder="例: 2020">
			</div>
			<div class="probonoseo-schema-row">
				<label>所在地</label>
				<input type="text" name="probonoseo_schema_org_address" value="<?php echo esc_attr($address); ?>" class="large-text" placeholder="例: 東京都渋谷区〇〇1-2-3">
			</div>
			<div class="probonoseo-schema-row">
				<label>電話番号</label>
				<input type="tel" name="probonoseo_schema_org_phone" value="<?php echo esc_attr($phone); ?>" placeholder="例: 03-1234-5678">
			</div>
			<div class="probonoseo-schema-row">
				<label>メールアドレス</label>
				<input type="email" name="probonoseo_schema_org_email" value="<?php echo esc_attr($email); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>SNSプロフィール（1行に1URL）</label>
				<textarea name="probonoseo_schema_org_social" rows="4" class="large-text" placeholder="https://twitter.com/example&#10;https://facebook.com/example"><?php echo esc_textarea($social); ?></textarea>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_org_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_org_nonce'], 'probonoseo_schema_org_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'type', 'description', 'logo', 'founded', 'address', 'phone', 'email', 'social');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_org_' . $field;
			$meta_key = '_probonoseo_schema_org_' . $field;
			if (isset($_POST[$key])) {
				if ($field === 'social' || $field === 'description') {
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

ProbonoSEO_Schema_Organization::get_instance();