<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_HowTo {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_howto_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_howto_metabox'));
        add_action('save_post', array($this, 'save_howto_data'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_howto', '0') === '1';
    }

    public function output_howto_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $post_id = get_the_ID();
        $howto = get_post_meta($post_id, '_probonoseo_howto', true);
        if (empty($howto) || empty($howto['steps'])) {
            return;
        }
        $schema = $this->build_schema($howto, $post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($howto, $post_id) {
        $steps = array();
        foreach ($howto['steps'] as $probonoseo_i => $step) {
            if (empty($step['name'])) {
                continue;
            }
            $step_data = array(
                '@type' => 'HowToStep',
                'position' => $probonoseo_i + 1,
                'name' => $step['name'],
                'text' => !empty($step['text']) ? $step['text'] : $step['name']
            );
            if (!empty($step['image'])) {
                $step_data['image'] = $step['image'];
            }
            $steps[] = $step_data;
        }
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => !empty($howto['name']) ? $howto['name'] : get_the_title($post_id),
            'description' => !empty($howto['description']) ? $howto['description'] : get_the_excerpt($post_id),
            'step' => $steps
        );
        if (get_option('probonoseo_serp_howto_time', '1') === '1' && !empty($howto['total_time'])) {
            $schema['totalTime'] = 'PT' . intval($howto['total_time']) . 'M';
        }
        if (get_option('probonoseo_serp_howto_cost', '1') === '1' && !empty($howto['estimated_cost'])) {
            $schema['estimatedCost'] = array(
                '@type' => 'MonetaryAmount',
                'currency' => 'JPY',
                'value' => $howto['estimated_cost']
            );
        }
        return $schema;
    }

    public function add_howto_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        $post_types = array('post', 'page');
        foreach ($post_types as $post_type) {
            add_meta_box('probonoseo_howto_metabox', 'HowTo schema（ProbonoSEO）', array($this, 'render_howto_metabox'), $post_type, 'normal', 'default');
        }
    }

    public function render_howto_metabox($post) {
        wp_nonce_field('probonoseo_howto_save', 'probonoseo_howto_nonce');
        $howto = get_post_meta($post->ID, '_probonoseo_howto', true);
        if (!is_array($howto)) {
            $howto = array('name' => '', 'description' => '', 'total_time' => '', 'estimated_cost' => '', 'steps' => array(array('name' => '', 'text' => '', 'image' => '')));
        }
        echo '<div class="probonoseo-howto-container">';
        echo '<div class="probonoseo-schema-row"><label>タイトル</label><input type="text" name="probonoseo_howto[name]" value="' . esc_attr($howto['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_howto[description]" rows="2">' . esc_textarea($howto['description']) . '</textarea></div>';
        echo '<div class="probonoseo-schema-row"><label>所要時間（分）</label><input type="number" name="probonoseo_howto[total_time]" value="' . esc_attr($howto['total_time']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>費用（円）</label><input type="number" name="probonoseo_howto[estimated_cost]" value="' . esc_attr($howto['estimated_cost']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>ステップ</label></div>';
        echo '<div id="probonoseo-howto-steps">';
        foreach ($howto['steps'] as $probonoseo_i => $step) {
            echo '<div class="probonoseo-howto-step" style="background:#f9f9f9;padding:10px;margin-bottom:10px;border-radius:4px;">';
            echo '<strong>ステップ ' . esc_html($probonoseo_i + 1) . '</strong><br>';
            echo '<input type="text" name="probonoseo_howto[steps][' . esc_attr($probonoseo_i) . '][name]" value="' . esc_attr($step['name']) . '" placeholder="ステップ名" style="width:100%;margin:5px 0;">';
            echo '<textarea name="probonoseo_howto[steps][' . esc_attr($probonoseo_i) . '][text]" rows="2" placeholder="詳細説明" style="width:100%;margin:5px 0;">' . esc_textarea($step['text']) . '</textarea>';
            echo '<input type="url" name="probonoseo_howto[steps][' . esc_attr($probonoseo_i) . '][image]" value="' . esc_attr($step['image']) . '" placeholder="画像URL（任意）" style="width:100%;margin:5px 0;">';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function save_howto_data($post_id) {
        if (!isset($_POST['probonoseo_howto_nonce'])) {
            return;
        }
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_howto_nonce'])), 'probonoseo_howto_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (isset($_POST['probonoseo_howto']) && is_array($_POST['probonoseo_howto'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_howto']);
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $howto = array(
                'name' => isset($probonoseo_raw['name']) ? sanitize_text_field($probonoseo_raw['name']) : '',
                'description' => isset($probonoseo_raw['description']) ? sanitize_textarea_field($probonoseo_raw['description']) : '',
                'total_time' => isset($probonoseo_raw['total_time']) ? sanitize_text_field($probonoseo_raw['total_time']) : '',
                'estimated_cost' => isset($probonoseo_raw['estimated_cost']) ? sanitize_text_field($probonoseo_raw['estimated_cost']) : '',
                'steps' => array()
            );
            if (isset($probonoseo_raw['steps']) && is_array($probonoseo_raw['steps'])) {
                foreach ($probonoseo_raw['steps'] as $step) {
                    $howto['steps'][] = array(
                        'name' => sanitize_text_field($step['name']),
                        'text' => sanitize_textarea_field($step['text']),
                        'image' => esc_url_raw($step['image'])
                    );
                }
            }
            update_post_meta($post_id, '_probonoseo_howto', $howto);
        }
    }
}

ProbonoSEO_SERP_HowTo::get_instance();