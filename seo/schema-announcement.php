<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Announcement {
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
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_schema_announcement', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_announce_enabled', true);
		if ($probonoseo_enabled !== '1') {
			return;
		}
		$probonoseo_schema = $this->build_schema($post);
		if (empty($probonoseo_schema)) {
			return;
		}
		echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
	}

	public function build_schema($post) {
		$probonoseo_schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'SpecialAnnouncement',
			'name' => get_the_title($post),
			'url' => get_permalink($post),
			'datePosted' => get_the_date('c', $post)
		);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_announce_description', true);
		if (!empty($probonoseo_description)) {
			$probonoseo_schema['text'] = $probonoseo_description;
		}
		$probonoseo_category = get_post_meta($post->ID, '_probonoseo_schema_announce_category', true);
		if (empty($probonoseo_category)) {
			$probonoseo_category = get_option('probonoseo_schema_announcement_category', 'DiseasePrevention');
		}
		$probonoseo_schema['category'] = 'https://www.wikidata.org/wiki/Q81068910';
		$probonoseo_expires_days = get_option('probonoseo_schema_announcement_expires', '30');
		$probonoseo_expires_date = get_post_meta($post->ID, '_probonoseo_schema_announce_expires', true);
		if (!empty($probonoseo_expires_date)) {
			$probonoseo_schema['expires'] = $probonoseo_expires_date;
		} else {
			$probonoseo_post_date = strtotime($post->post_date);
			$probonoseo_expires = wp_date('Y-m-d', $probonoseo_post_date + (intval($probonoseo_expires_days) * 86400));
			$probonoseo_schema['expires'] = $probonoseo_expires;
		}
		$probonoseo_area = get_post_meta($post->ID, '_probonoseo_schema_announce_area', true);
		if (!empty($probonoseo_area)) {
			$probonoseo_schema['spatialCoverage'] = array(
				'@type' => 'Place',
				'name' => $probonoseo_area
			);
		}
		$probonoseo_source_url = get_post_meta($post->ID, '_probonoseo_schema_announce_source', true);
		if (!empty($probonoseo_source_url)) {
			$probonoseo_schema['mainEntityOfPage'] = $probonoseo_source_url;
		}
		$probonoseo_quarantine = get_post_meta($post->ID, '_probonoseo_schema_announce_quarantine', true);
		if (!empty($probonoseo_quarantine)) {
			$probonoseo_schema['quarantineGuidelines'] = $probonoseo_quarantine;
		}
		$probonoseo_news_update = get_post_meta($post->ID, '_probonoseo_schema_announce_news', true);
		if (!empty($probonoseo_news_update)) {
			$probonoseo_schema['newsUpdatesAndGuidelines'] = $probonoseo_news_update;
		}
		$probonoseo_publisher = get_option('probonoseo_schema_article_publisher', get_bloginfo('name'));
		$probonoseo_schema['announcementLocation'] = array(
			'@type' => 'CivicStructure',
			'name' => $probonoseo_publisher
		);
		return $probonoseo_schema;
	}

	public function add_meta_boxes() {
		if (!$this->is_enabled()) {
			return;
		}
		$probonoseo_post_types = array('post', 'page');
		foreach ($probonoseo_post_types as $probonoseo_post_type) {
			add_meta_box(
				'probonoseo_schema_announce',
				'SpecialAnnouncement schema',
				array($this, 'render_metabox'),
				$probonoseo_post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_announce_save', 'probonoseo_schema_announce_nonce');
		$probonoseo_enabled = get_post_meta($post->ID, '_probonoseo_schema_announce_enabled', true);
		$probonoseo_description = get_post_meta($post->ID, '_probonoseo_schema_announce_description', true);
		$probonoseo_category = get_post_meta($post->ID, '_probonoseo_schema_announce_category', true);
		$probonoseo_expires = get_post_meta($post->ID, '_probonoseo_schema_announce_expires', true);
		$probonoseo_area = get_post_meta($post->ID, '_probonoseo_schema_announce_area', true);
		$probonoseo_source = get_post_meta($post->ID, '_probonoseo_schema_announce_source', true);
		$probonoseo_quarantine = get_post_meta($post->ID, '_probonoseo_schema_announce_quarantine', true);
		$probonoseo_news = get_post_meta($post->ID, '_probonoseo_schema_announce_news', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_announce_enabled" value="1" <?php checked($probonoseo_enabled, '1'); ?>> このページでSpecialAnnouncement schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>告知内容</label>
				<textarea name="probonoseo_schema_announce_description" rows="3" class="large-text"><?php echo esc_textarea($probonoseo_description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>カテゴリ</label>
				<select name="probonoseo_schema_announce_category">
					<option value="DiseasePrevention" <?php selected($probonoseo_category, 'DiseasePrevention'); ?>>感染症予防</option>
					<option value="GovernmentBenefits" <?php selected($probonoseo_category, 'GovernmentBenefits'); ?>>政府支援</option>
					<option value="HealthcareService" <?php selected($probonoseo_category, 'HealthcareService'); ?>>医療サービス</option>
					<option value="EventSchedule" <?php selected($probonoseo_category, 'EventSchedule'); ?>>イベント予定</option>
					<option value="SchoolClosure" <?php selected($probonoseo_category, 'SchoolClosure'); ?>>学校閉鎖</option>
					<option value="TransportationService" <?php selected($probonoseo_category, 'TransportationService'); ?>>交通サービス</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>有効期限</label>
				<input type="date" name="probonoseo_schema_announce_expires" value="<?php echo esc_attr($probonoseo_expires); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>対象地域</label>
				<input type="text" name="probonoseo_schema_announce_area" value="<?php echo esc_attr($probonoseo_area); ?>" placeholder="例: 東京都、日本全国">
			</div>
			<div class="probonoseo-schema-row">
				<label>情報ソースURL</label>
				<input type="url" name="probonoseo_schema_announce_source" value="<?php echo esc_attr($probonoseo_source); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>隔離ガイドラインURL</label>
				<input type="url" name="probonoseo_schema_announce_quarantine" value="<?php echo esc_attr($probonoseo_quarantine); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>ニュース更新URL</label>
				<input type="url" name="probonoseo_schema_announce_news" value="<?php echo esc_attr($probonoseo_news); ?>" class="large-text">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_announce_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_schema_announce_nonce'])), 'probonoseo_schema_announce_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$probonoseo_fields = array('enabled', 'description', 'category', 'expires', 'area', 'source', 'quarantine', 'news');
		foreach ($probonoseo_fields as $probonoseo_field) {
			$probonoseo_key = 'probonoseo_schema_announce_' . $probonoseo_field;
			$probonoseo_meta_key = '_probonoseo_schema_announce_' . $probonoseo_field;
			if (isset($_POST[$probonoseo_key])) {
				if ($probonoseo_field === 'description') {
					update_post_meta($post_id, $probonoseo_meta_key, sanitize_textarea_field(wp_unslash($_POST[$probonoseo_key])));
				} else {
					update_post_meta($post_id, $probonoseo_meta_key, sanitize_text_field(wp_unslash($_POST[$probonoseo_key])));
				}
			} else {
				delete_post_meta($post_id, $probonoseo_meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Announcement::get_instance();