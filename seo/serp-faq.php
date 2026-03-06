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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_probonoseo_save_faq', array($this, 'ajax_save_faq'));
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        wp_enqueue_style('probonoseo-serp-metabox', plugin_dir_url(dirname(__FILE__)) . 'admin/serp-metabox.css', array(), PROBONOSEO_VERSION);
        wp_enqueue_script('probonoseo-serp-metabox', plugin_dir_url(dirname(__FILE__)) . 'admin/serp-metabox.js', array('jquery'), PROBONOSEO_VERSION, true);
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
                $faqs[] = array('question' => $question, 'answer' => $answer);
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
                'acceptedAnswer' => array('@type' => 'Answer', 'text' => $faq['answer'])
            );
        }
        return array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $main_entity);
    }

    public function add_faq_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        foreach (array('post', 'page') as $post_type) {
            add_meta_box('probonoseo_faq_metabox', 'FAQ schema（よくある質問）', array($this, 'render_faq_metabox'), $post_type, 'normal', 'default');
        }
    }

    public function render_faq_metabox($post) {
        wp_nonce_field('probonoseo_faq_save', 'probonoseo_faq_nonce');
        $faqs = get_post_meta($post->ID, '_probonoseo_faqs', true);
        if (!is_array($faqs)) {
            $faqs = array(array('question' => '', 'answer' => ''));
        }
        echo '<div class="probonoseo-schema-container">';
        echo '<div id="probonoseo-faq-list">';
        foreach ($faqs as $i => $faq) {
            echo '<div class="probonoseo-schema-faq-item">';
            echo '<div class="probonoseo-schema-faq-header">';
            echo '<span class="probonoseo-schema-faq-num">' . esc_html($i + 1) . '</span>';
            echo '<button type="button" class="probonoseo-schema-faq-remove" onclick="probonoseoRemoveFaq(this)">×</button>';
            echo '</div>';
            echo '<div class="probonoseo-schema-grid">';
            echo '<div class="probonoseo-schema-row"><label>質問</label><input type="text" name="probonoseo_faq[' . esc_attr($i) . '][question]" value="' . esc_attr($faq['question']) . '"></div>';
            echo '<div class="probonoseo-schema-row"><label>回答</label><textarea name="probonoseo_faq[' . esc_attr($i) . '][answer]" rows="2">' . esc_textarea($faq['answer']) . '</textarea></div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<div class="probonoseo-schema-save-row">';
        echo '<button type="button" class="button" onclick="probonoseoAddFaq()">+ FAQを追加</button>';
        echo '<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="' . esc_attr($post->ID) . '" data-action="probonoseo_save_faq" data-nonce="' . esc_attr(wp_create_nonce('probonoseo_save_faq')) . '">保存</button>';
        echo '<span class="probonoseo-schema-save-msg"></span>';
        echo '</div>';
        echo '</div>';
        ?>
        <script>
        function probonoseoAddFaq() {
            var list = document.getElementById('probonoseo-faq-list');
            var count = list.children.length;
            var item = document.createElement('div');
            item.className = 'probonoseo-schema-faq-item';
            item.innerHTML = '<div class="probonoseo-schema-faq-header"><span class="probonoseo-schema-faq-num">' + (count + 1) + '</span><button type="button" class="probonoseo-schema-faq-remove" onclick="probonoseoRemoveFaq(this)">×</button></div><div class="probonoseo-schema-grid"><div class="probonoseo-schema-row"><label>質問</label><input type="text" name="probonoseo_faq[' + count + '][question]" value=""></div><div class="probonoseo-schema-row"><label>回答</label><textarea name="probonoseo_faq[' + count + '][answer]" rows="2"></textarea></div></div>';
            list.appendChild(item);
        }
        function probonoseoRemoveFaq(btn) {
            btn.closest('.probonoseo-schema-faq-item').remove();
        }
        </script>
        <?php
    }

    public function ajax_save_faq() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_faq')) {
            wp_send_json_error(array('message' => '認証エラー'));
            return;
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => '権限がありません'));
            return;
        }
        $faqs = array();
        if (isset($_POST['probonoseo_faq']) && is_array($_POST['probonoseo_faq'])) {
            $raw_faqs = wp_unslash($_POST['probonoseo_faq']);
            foreach ($raw_faqs as $faq) {
                $question = isset($faq['question']) ? sanitize_text_field($faq['question']) : '';
                $answer = isset($faq['answer']) ? sanitize_textarea_field($faq['answer']) : '';
                if (!empty($question) || !empty($answer)) {
                    $faqs[] = array('question' => $question, 'answer' => $answer);
                }
            }
        }
        update_post_meta($post_id, '_probonoseo_faqs', $faqs);
        wp_send_json_success(array('message' => '保存しました'));
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
        if (isset($_POST['probonoseo_faq']) && is_array($_POST['probonoseo_faq'])) {
            $faqs = array();
            $raw_faqs = wp_unslash($_POST['probonoseo_faq']);
            foreach ($raw_faqs as $faq) {
                $question = isset($faq['question']) ? sanitize_text_field($faq['question']) : '';
                $answer = isset($faq['answer']) ? sanitize_textarea_field($faq['answer']) : '';
                if (!empty($question) || !empty($answer)) {
                    $faqs[] = array('question' => $question, 'answer' => $answer);
                }
            }
            update_post_meta($post_id, '_probonoseo_faqs', $faqs);
        }
    }
}

ProbonoSEO_SERP_FAQ::get_instance();