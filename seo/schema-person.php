<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Person {
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
		return get_option('probonoseo_schema_person', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_person_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_person_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Person',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_person_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$job = get_post_meta($post->ID, '_probonoseo_schema_person_job', true);
		if (empty($job)) {
			$job = get_option('probonoseo_schema_person_job', '');
		}
		if (!empty($job)) {
			$schema['jobTitle'] = $job;
		}
		$affiliation = get_post_meta($post->ID, '_probonoseo_schema_person_affiliation', true);
		if (empty($affiliation)) {
			$affiliation = get_option('probonoseo_schema_person_affiliation', '');
		}
		if (!empty($affiliation)) {
			$schema['affiliation'] = array(
				'@type' => 'Organization',
				'name' => $affiliation
			);
		}
		$email = get_post_meta($post->ID, '_probonoseo_schema_person_email', true);
		if (!empty($email)) {
			$schema['email'] = $email;
		}
		$birthdate = get_post_meta($post->ID, '_probonoseo_schema_person_birthdate', true);
		if (!empty($birthdate)) {
			$schema['birthDate'] = $birthdate;
		}
		$nationality = get_post_meta($post->ID, '_probonoseo_schema_person_nationality', true);
		if (!empty($nationality)) {
			$schema['nationality'] = array(
				'@type' => 'Country',
				'name' => $nationality
			);
		}
		$social = get_post_meta($post->ID, '_probonoseo_schema_person_social', true);
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
				'probonoseo_schema_person',
				'Person schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_person_save', 'probonoseo_schema_person_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_person_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_person_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_person_description', true);
		$job = get_post_meta($post->ID, '_probonoseo_schema_person_job', true);
		$affiliation = get_post_meta($post->ID, '_probonoseo_schema_person_affiliation', true);
		$email = get_post_meta($post->ID, '_probonoseo_schema_person_email', true);
		$birthdate = get_post_meta($post->ID, '_probonoseo_schema_person_birthdate', true);
		$nationality = get_post_meta($post->ID, '_probonoseo_schema_person_nationality', true);
		$social = get_post_meta($post->ID, '_probonoseo_schema_person_social', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_person_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでPerson schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>氏名</label>
				<input type="text" name="probonoseo_schema_person_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>プロフィール</label>
				<textarea name="probonoseo_schema_person_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>職業</label>
				<input type="text" name="probonoseo_schema_person_job" value="<?php echo esc_attr($job); ?>" placeholder="例: ライター、エンジニア">
			</div>
			<div class="probonoseo-schema-row">
				<label>所属</label>
				<input type="text" name="probonoseo_schema_person_affiliation" value="<?php echo esc_attr($affiliation); ?>" placeholder="例: 株式会社〇〇">
			</div>
			<div class="probonoseo-schema-row">
				<label>メールアドレス</label>
				<input type="email" name="probonoseo_schema_person_email" value="<?php echo esc_attr($email); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>生年月日</label>
				<input type="date" name="probonoseo_schema_person_birthdate" value="<?php echo esc_attr($birthdate); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>国籍</label>
				<input type="text" name="probonoseo_schema_person_nationality" value="<?php echo esc_attr($nationality); ?>" placeholder="例: 日本">
			</div>
			<div class="probonoseo-schema-row">
				<label>SNSプロフィール（1行に1URL）</label>
				<textarea name="probonoseo_schema_person_social" rows="4" class="large-text" placeholder="https://twitter.com/example&#10;https://linkedin.com/in/example"><?php echo esc_textarea($social); ?></textarea>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_person_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_person_nonce'], 'probonoseo_schema_person_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'job', 'affiliation', 'email', 'birthdate', 'nationality', 'social');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_person_' . $field;
			$meta_key = '_probonoseo_schema_person_' . $field;
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

ProbonoSEO_Schema_Person::get_instance();