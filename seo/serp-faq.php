<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_FAQ {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_faq_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_faq_metabox'));
        add_action('save_post', array($this, 'save_faq_data'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_faq', '0') === '1';
    }

    public function output_faq_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }

        $post_id = get_the_ID();
        $faqs = $this->get_faqs($post_id);

        if (empty($faqs)) {
            return;
        }

        $schema = $this->build_schema($faqs);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    public function get_faqs($post_id) {
        $faqs = get_post_meta($post_id, '_probonoseo_faqs', true);
        if (!is_array($faqs)) {
            $faqs = array();
        }

        if (empty($faqs) && get_option('probonoseo_serp_faq_auto', '0') === '1') {
            $faqs = $this->auto_extract_faqs($post_id);
        }

        $limit = intval(get_option('probonoseo_serp_faq_limit', '10'));
        if ($limit > 0 && count($faqs) > $limit) {
            $faqs = array_slice($faqs, 0, $limit);
        }

        return $faqs;
    }

    private function auto_extract_faqs($post_id) {
        $content = get_post_field('post_content', $post_id);
        $faqs = array();

        preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>\s*<p>(.*?)<\/p>/is', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $question = wp_strip_all_tags($match[1]);
            $answer = wp_strip_all_tags($match[2]);

            if (mb_strpos($question, '？') !== false || mb_strpos($question, '?') !== false) {
                $faqs[] = array(
                    'question' => $question,
                    'answer' => $answer
                );
            }
        }

        return $faqs;
    }

    private function build_schema($faqs) {
        $main_entity = array();

        foreach ($faqs as $faq) {
            if (empty($faq['question']) || empty($faq['answer'])) {
                continue;
            }

            $main_entity[] = array(
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                )
            );
        }

        return array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $main_entity
        );
    }

    public function add_faq_metabox() {
        if (!$this->is_enabled()) {
            return;
        }

        $post_types = array('post', 'page');
        foreach ($post_types as $post_type) {
            add_meta_box(
                'probonoseo_faq_metabox',
                'FAQ schema（ProbonoSEO）',
                array($this, 'render_faq_metabox'),
                $post_type,
                'normal',
                'default'
            );
        }
    }

    public function render_faq_metabox($post) {
        wp_nonce_field('probonoseo_faq_save', 'probonoseo_faq_nonce');
        $faqs = get_post_meta($post->ID, '_probonoseo_faqs', true);
        if (!is_array($faqs)) {
            $faqs = array(array('question' => '', 'answer' => ''));
        }
        echo '<div class="probonoseo-faq-container">';
        echo '<div id="probonoseo-faq-list">';
        foreach ($faqs as $i => $faq) {
            echo '<div class="probonoseo-faq-item" data-index="' . esc_attr($i) . '">';
            echo '<div class="probonoseo-faq-header">';
            echo '<span class="probonoseo-faq-num">' . esc_html($i + 1) . '</span>';
            echo '<button type="button" class="probonoseo-faq-remove" onclick="probonoseoRemoveFaq(this)">×</button>';
            echo '</div>';
            echo '<div class="probonoseo-faq-field">';
            echo '<label>質問</label>';
            echo '<input type="text" name="probonoseo_faq[' . esc_attr($i) . '][question]" value="' . esc_attr($faq['question']) . '">';
            echo '</div>';
            echo '<div class="probonoseo-faq-field">';
            echo '<label>回答</label>';
            echo '<textarea name="probonoseo_faq[' . esc_attr($i) . '][answer]" rows="3">' . esc_textarea($faq['answer']) . '</textarea>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<button type="button" class="button" onclick="probonoseoAddFaq()">+ FAQを追加</button>';
        echo '</div>';
    }

    public function save_faq_data($post_id) {
        if (!isset($_POST['probonoseo_faq_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_faq_nonce'])), 'probonoseo_faq_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (isset($_POST['probonoseo_faq']) && is_array($_POST['probonoseo_faq'])) {
            $faqs = array();
            $raw_faqs = wp_unslash($_POST['probonoseo_faq']);
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            foreach ($raw_faqs as $faq) {
                $question = isset($faq['question']) ? sanitize_text_field($faq['question']) : '';
                $answer = isset($faq['answer']) ? sanitize_textarea_field($faq['answer']) : '';
                if (!empty($question) || !empty($answer)) {
                    $faqs[] = array(
                        'question' => $question,
                        'answer' => $answer
                    );
                }
            }
            update_post_meta($post_id, '_probonoseo_faqs', $faqs);
        }
    }
}

ProbonoSEO_SERP_FAQ::get_instance();