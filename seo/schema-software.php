<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Software {
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
		return get_option('probonoseo_schema_software', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_software_enabled', true);
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
		$name = get_post_meta($post->ID, '_probonoseo_schema_software_name', true);
		if (empty($name)) {
			$name = get_the_title($post);
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'SoftwareApplication',
			'name' => $name,
			'url' => get_permalink($post)
		);
		$description = get_post_meta($post->ID, '_probonoseo_schema_software_description', true);
		if (!empty($description)) {
			$schema['description'] = $description;
		}
		$category = get_post_meta($post->ID, '_probonoseo_schema_software_category', true);
		if (empty($category)) {
			$category = get_option('probonoseo_schema_software_category', 'GameApplication');
		}
		$schema['applicationCategory'] = $category;
		$os = get_post_meta($post->ID, '_probonoseo_schema_software_os', true);
		if (empty($os)) {
			$os = get_option('probonoseo_schema_software_os', 'All');
		}
		$schema['operatingSystem'] = $os;
		$version = get_post_meta($post->ID, '_probonoseo_schema_software_version', true);
		if (!empty($version)) {
			$schema['softwareVersion'] = $version;
		}
		$download_url = get_post_meta($post->ID, '_probonoseo_schema_software_download', true);
		if (!empty($download_url)) {
			$schema['downloadUrl'] = $download_url;
		}
		$screenshot = get_post_meta($post->ID, '_probonoseo_schema_software_screenshot', true);
		if (!empty($screenshot)) {
			$schema['screenshot'] = $screenshot;
		} elseif (has_post_thumbnail($post)) {
			$schema['screenshot'] = get_the_post_thumbnail_url($post, 'large');
		}
		$price = get_post_meta($post->ID, '_probonoseo_schema_software_price', true);
		$currency = get_post_meta($post->ID, '_probonoseo_schema_software_currency', true);
		if (empty($currency)) {
			$currency = get_option('probonoseo_schema_software_currency', 'JPY');
		}
		if ($price !== '' && $price !== false) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'price' => floatval($price),
				'priceCurrency' => $currency
			);
		}
		$rating = get_post_meta($post->ID, '_probonoseo_schema_software_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_software_rating_count', true);
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
				'probonoseo_schema_software',
				'SoftwareApplication schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_software_save', 'probonoseo_schema_software_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_software_enabled', true);
		$name = get_post_meta($post->ID, '_probonoseo_schema_software_name', true);
		$description = get_post_meta($post->ID, '_probonoseo_schema_software_description', true);
		$category = get_post_meta($post->ID, '_probonoseo_schema_software_category', true);
		$os = get_post_meta($post->ID, '_probonoseo_schema_software_os', true);
		$version = get_post_meta($post->ID, '_probonoseo_schema_software_version', true);
		$download = get_post_meta($post->ID, '_probonoseo_schema_software_download', true);
		$screenshot = get_post_meta($post->ID, '_probonoseo_schema_software_screenshot', true);
		$price = get_post_meta($post->ID, '_probonoseo_schema_software_price', true);
		$currency = get_post_meta($post->ID, '_probonoseo_schema_software_currency', true);
		$rating = get_post_meta($post->ID, '_probonoseo_schema_software_rating', true);
		$rating_count = get_post_meta($post->ID, '_probonoseo_schema_software_rating_count', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_software_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでSoftwareApplication schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>アプリ名</label>
				<input type="text" name="probonoseo_schema_software_name" value="<?php echo esc_attr($name); ?>" class="large-text" placeholder="空欄時はページタイトルを使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>説明</label>
				<textarea name="probonoseo_schema_software_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>カテゴリ</label>
				<select name="probonoseo_schema_software_category">
					<?php
					$categories = array('GameApplication', 'SocialNetworkingApplication', 'TravelApplication', 'ShoppingApplication', 'SportsApplication', 'LifestyleApplication', 'BusinessApplication', 'DesignApplication', 'DeveloperApplication', 'EducationalApplication', 'HealthApplication', 'FinanceApplication', 'SecurityApplication', 'BrowserApplication', 'CommunicationApplication', 'EntertainmentApplication', 'MultimediaApplication', 'UtilitiesApplication', 'ReferenceApplication');
					foreach ($categories as $cat) {
						$probonoseo_selected = ($category === $cat) ? ' selected' : '';
						echo '<option value="' . esc_attr($cat) . '"' . esc_attr($probonoseo_selected) . '>' . esc_html($cat) . '</option>';
					}
					?>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>対応OS</label>
				<input type="text" name="probonoseo_schema_software_os" value="<?php echo esc_attr($os); ?>" placeholder="例: Windows, macOS, iOS, Android">
			</div>
			<div class="probonoseo-schema-row">
				<label>バージョン</label>
				<input type="text" name="probonoseo_schema_software_version" value="<?php echo esc_attr($version); ?>" placeholder="例: 1.0.0">
			</div>
			<div class="probonoseo-schema-row">
				<label>ダウンロードURL</label>
				<input type="url" name="probonoseo_schema_software_download" value="<?php echo esc_attr($download); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>スクリーンショットURL</label>
				<input type="url" name="probonoseo_schema_software_screenshot" value="<?php echo esc_attr($screenshot); ?>" class="large-text" placeholder="空欄時はアイキャッチ画像を使用">
			</div>
			<div class="probonoseo-schema-row">
				<label>価格</label>
				<input type="number" name="probonoseo_schema_software_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" placeholder="0で無料">
				<select name="probonoseo_schema_software_currency">
					<option value="JPY" <?php selected($currency, 'JPY'); ?>>JPY</option>
					<option value="USD" <?php selected($currency, 'USD'); ?>>USD</option>
					<option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR</option>
				</select>
			</div>
			<div class="probonoseo-schema-row">
				<label>評価（1-5）</label>
				<input type="number" name="probonoseo_schema_software_rating" value="<?php echo esc_attr($rating); ?>" step="0.1" min="1" max="5">
				<label>レビュー数</label>
				<input type="number" name="probonoseo_schema_software_rating_count" value="<?php echo esc_attr($rating_count); ?>" min="0">
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_software_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_schema_software_nonce'])), 'probonoseo_schema_software_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'name', 'description', 'category', 'os', 'version', 'download', 'screenshot', 'price', 'currency', 'rating', 'rating_count');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_software_' . $field;
			$meta_key = '_probonoseo_schema_software_' . $field;
			if (isset($_POST[$key])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$key])));
			} else {
				delete_post_meta($post_id, $meta_key);
			}
		}
	}
}

ProbonoSEO_Schema_Software::get_instance();