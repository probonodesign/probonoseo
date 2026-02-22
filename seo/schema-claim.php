<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Schema_Claim {
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
		return get_option('probonoseo_schema_claim', '0') === '1';
	}

	public function output_schema() {
		if (!$this->is_enabled()) {
			return;
		}
		if (!is_singular()) {
			return;
		}
		global $post;
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_claim_enabled', true);
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
		$claim_reviewed = get_post_meta($post->ID, '_probonoseo_schema_claim_text', true);
		if (empty($claim_reviewed)) {
			return array();
		}
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'ClaimReview',
			'url' => get_permalink($post),
			'datePublished' => get_the_date('c', $post),
			'claimReviewed' => $claim_reviewed
		);
		$claim_author = get_post_meta($post->ID, '_probonoseo_schema_claim_author', true);
		if (!empty($claim_author)) {
			$schema['itemReviewed'] = array(
				'@type' => 'Claim',
				'author' => array(
					'@type' => 'Person',
					'name' => $claim_author
				)
			);
		}
		$claim_date = get_post_meta($post->ID, '_probonoseo_schema_claim_date', true);
		if (!empty($claim_date) && isset($schema['itemReviewed'])) {
			$schema['itemReviewed']['datePublished'] = $claim_date;
		}
		$claim_url = get_post_meta($post->ID, '_probonoseo_schema_claim_url', true);
		if (!empty($claim_url) && isset($schema['itemReviewed'])) {
			$schema['itemReviewed']['appearance'] = array(
				'@type' => 'OpinionNewsArticle',
				'url' => $claim_url
			);
		}
		$org = get_post_meta($post->ID, '_probonoseo_schema_claim_org', true);
		if (empty($org)) {
			$org = get_option('probonoseo_schema_claim_org', get_bloginfo('name'));
		}
		$schema['author'] = array(
			'@type' => 'Organization',
			'name' => $org
		);
		$rating = get_post_meta($post->ID, '_probonoseo_schema_claim_rating', true);
		if (empty($rating)) {
			$rating = get_option('probonoseo_schema_claim_rating', 'True');
		}
		$rating_map = array(
			'True' => array('value' => 5, 'text' => '真実'),
			'Mostly True' => array('value' => 4, 'text' => 'ほぼ真実'),
			'Half True' => array('value' => 3, 'text' => '半分真実'),
			'Mostly False' => array('value' => 2, 'text' => 'ほぼ虚偽'),
			'False' => array('value' => 1, 'text' => '虚偽')
		);
		$rating_data = isset($rating_map[$rating]) ? $rating_map[$rating] : $rating_map['True'];
		$schema['reviewRating'] = array(
			'@type' => 'Rating',
			'ratingValue' => $rating_data['value'],
			'bestRating' => 5,
			'worstRating' => 1,
			'alternateName' => $rating_data['text']
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
				'probonoseo_schema_claim',
				'ClaimReview schema',
				array($this, 'render_metabox'),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('probonoseo_schema_claim_save', 'probonoseo_schema_claim_nonce');
		$enabled = get_post_meta($post->ID, '_probonoseo_schema_claim_enabled', true);
		$text = get_post_meta($post->ID, '_probonoseo_schema_claim_text', true);
		$author = get_post_meta($post->ID, '_probonoseo_schema_claim_author', true);
		$date = get_post_meta($post->ID, '_probonoseo_schema_claim_date', true);
		$url = get_post_meta($post->ID, '_probonoseo_schema_claim_url', true);
		$org = get_post_meta($post->ID, '_probonoseo_schema_claim_org', true);
		$rating = get_post_meta($post->ID, '_probonoseo_schema_claim_rating', true);
		?>
		<div class="probonoseo-schema-metabox">
			<div class="probonoseo-schema-row">
				<label><input type="checkbox" name="probonoseo_schema_claim_enabled" value="1" <?php checked($enabled, '1'); ?>> このページでClaimReview schemaを出力する</label>
			</div>
			<div class="probonoseo-schema-row">
				<label>検証対象の主張</label>
				<textarea name="probonoseo_schema_claim_text" rows="3" class="large-text" placeholder="ファクトチェック対象の主張内容"><?php echo esc_textarea($text); ?></textarea>
			</div>
			<div class="probonoseo-schema-row">
				<label>主張者</label>
				<input type="text" name="probonoseo_schema_claim_author" value="<?php echo esc_attr($author); ?>" placeholder="例: 〇〇氏">
			</div>
			<div class="probonoseo-schema-row">
				<label>主張日</label>
				<input type="date" name="probonoseo_schema_claim_date" value="<?php echo esc_attr($date); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>主張元URL</label>
				<input type="url" name="probonoseo_schema_claim_url" value="<?php echo esc_attr($url); ?>" class="large-text">
			</div>
			<div class="probonoseo-schema-row">
				<label>検証組織名</label>
				<input type="text" name="probonoseo_schema_claim_org" value="<?php echo esc_attr($org); ?>">
			</div>
			<div class="probonoseo-schema-row">
				<label>検証結果</label>
				<select name="probonoseo_schema_claim_rating">
					<option value="True" <?php selected($rating, 'True'); ?>>真実</option>
					<option value="Mostly True" <?php selected($rating, 'Mostly True'); ?>>ほぼ真実</option>
					<option value="Half True" <?php selected($rating, 'Half True'); ?>>半分真実</option>
					<option value="Mostly False" <?php selected($rating, 'Mostly False'); ?>>ほぼ虚偽</option>
					<option value="False" <?php selected($rating, 'False'); ?>>虚偽</option>
				</select>
			</div>
		</div>
		<?php
	}

	public function save_post($post_id, $post) {
		if (!isset($_POST['probonoseo_schema_claim_nonce'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification
		if (!wp_verify_nonce($_POST['probonoseo_schema_claim_nonce'], 'probonoseo_schema_claim_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$fields = array('enabled', 'text', 'author', 'date', 'url', 'org', 'rating');
		foreach ($fields as $field) {
			$key = 'probonoseo_schema_claim_' . $field;
			$meta_key = '_probonoseo_schema_claim_' . $field;
			if (isset($_POST[$key])) {
				if ($field === 'text') {
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

ProbonoSEO_Schema_Claim::get_instance();