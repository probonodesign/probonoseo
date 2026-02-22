<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Review {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_review_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_review_metabox'));
        add_action('save_post', array($this, 'save_review_data'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_review', '0') === '1';
    }

    public function output_review_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $post_id = get_the_ID();
        $review = get_post_meta($post_id, '_probonoseo_review', true);
        if (empty($review) || empty($review['rating'])) {
            return;
        }
        $schema = $this->build_schema($review, $post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($review, $post_id) {
        $probonoseo_scale = intval(get_option('probonoseo_serp_review_scale', '5'));
        $item_type = get_option('probonoseo_serp_review_type', 'Product');
        $item_reviewed = array(
            '@type' => $item_type,
            'name' => !empty($review['item_name']) ? $review['item_name'] : get_the_title($post_id)
        );
        if (!empty($review['item_image'])) {
            $item_reviewed['image'] = $review['item_image'];
        }
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Review',
            'itemReviewed' => $item_reviewed,
            'reviewRating' => array(
                '@type' => 'Rating',
                'ratingValue' => floatval($review['rating']),
                'bestRating' => $probonoseo_scale,
                'worstRating' => 1
            ),
            'author' => array(
                '@type' => 'Person',
                'name' => !empty($review['author']) ? $review['author'] : get_the_author()
            ),
            'datePublished' => get_the_date('c', $post_id)
        );
        if (!empty($review['summary'])) {
            $schema['reviewBody'] = $review['summary'];
        }
        return $schema;
    }

    public function add_review_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        $post_types = array('post', 'page');
        foreach ($post_types as $post_type) {
            add_meta_box('probonoseo_review_metabox', 'Review schema（ProbonoSEO）', array($this, 'render_review_metabox'), $post_type, 'normal', 'default');
        }
    }

    public function render_review_metabox($post) {
        wp_nonce_field('probonoseo_review_save', 'probonoseo_review_nonce');
        $review = get_post_meta($post->ID, '_probonoseo_review', true);
        if (!is_array($review)) {
            $review = array('item_name' => '', 'item_image' => '', 'rating' => '', 'author' => '', 'summary' => '');
        }
        $probonoseo_scale = intval(get_option('probonoseo_serp_review_scale', '5'));
        echo '<div class="probonoseo-review-container">';
        echo '<div class="probonoseo-schema-row"><label>レビュー対象名</label><input type="text" name="probonoseo_review[item_name]" value="' . esc_attr($review['item_name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>評価（1〜' . esc_html($probonoseo_scale) . '）</label><input type="number" name="probonoseo_review[rating]" value="' . esc_attr($review['rating']) . '" min="1" max="' . esc_attr($probonoseo_scale) . '" step="0.1"></div>';
        echo '<div class="probonoseo-schema-row"><label>レビュアー名</label><input type="text" name="probonoseo_review[author]" value="' . esc_attr($review['author']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>画像URL</label><input type="url" name="probonoseo_review[item_image]" value="' . esc_attr($review['item_image']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>レビュー本文</label><textarea name="probonoseo_review[summary]" rows="3">' . esc_textarea($review['summary']) . '</textarea></div>';
        echo '</div>';
    }

    public function save_review_data($post_id) {
        if (!isset($_POST['probonoseo_review_nonce'])) {
            return;
        }
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_review_nonce'])), 'probonoseo_review_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (isset($_POST['probonoseo_review']) && is_array($_POST['probonoseo_review'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_review']);
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $review = array(
                'item_name' => isset($probonoseo_raw['item_name']) ? sanitize_text_field($probonoseo_raw['item_name']) : '',
                'item_image' => isset($probonoseo_raw['item_image']) ? esc_url_raw($probonoseo_raw['item_image']) : '',
                'rating' => isset($probonoseo_raw['rating']) ? sanitize_text_field($probonoseo_raw['rating']) : '',
                'author' => isset($probonoseo_raw['author']) ? sanitize_text_field($probonoseo_raw['author']) : '',
                'summary' => isset($probonoseo_raw['summary']) ? sanitize_textarea_field($probonoseo_raw['summary']) : ''
            );
            update_post_meta($post_id, '_probonoseo_review', $review);
        }
    }
}

ProbonoSEO_SERP_Review::get_instance();