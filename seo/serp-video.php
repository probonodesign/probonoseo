<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Video {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_video_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_video_metabox'));
        add_action('save_post', array($this, 'save_video_data'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $probonoseo_license = ProbonoSEO_License::get_instance();
        if (!$probonoseo_license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_video', '0') === '1';
    }

    public function output_video_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $probonoseo_post_id = get_the_ID();
        $probonoseo_video = get_post_meta($probonoseo_post_id, '_probonoseo_video', true);
        if (empty($probonoseo_video) || empty($probonoseo_video['name'])) {
            if (get_option('probonoseo_serp_video_youtube', '1') === '1') {
                $probonoseo_video = $this->auto_detect_video($probonoseo_post_id);
            }
        }
        if (empty($probonoseo_video) || empty($probonoseo_video['content_url'])) {
            return;
        }
        $probonoseo_schema = $this->build_schema($probonoseo_video, $probonoseo_post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function auto_detect_video($post_id) {
        $probonoseo_content = get_post_field('post_content', $post_id);
        $probonoseo_video = array();
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $probonoseo_content, $probonoseo_matches)) {
            $probonoseo_video_id = $probonoseo_matches[1];
            $probonoseo_video = array(
                'name' => get_the_title($post_id),
                'description' => get_the_excerpt($post_id),
                'content_url' => 'https://www.youtube.com/watch?v=' . $probonoseo_video_id,
                'embed_url' => 'https://www.youtube.com/embed/' . $probonoseo_video_id,
                'thumbnail_url' => 'https://img.youtube.com/vi/' . $probonoseo_video_id . '/maxresdefault.jpg'
            );
        }
        return $probonoseo_video;
    }

    private function build_schema($video, $post_id) {
        $probonoseo_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $video['name'],
            'description' => !empty($video['description']) ? $video['description'] : get_the_excerpt($post_id),
            'uploadDate' => get_the_date('c', $post_id)
        );
        if (!empty($video['thumbnail_url'])) {
            $probonoseo_schema['thumbnailUrl'] = $video['thumbnail_url'];
        }
        if (!empty($video['content_url'])) {
            $probonoseo_schema['contentUrl'] = $video['content_url'];
        }
        if (!empty($video['embed_url'])) {
            $probonoseo_schema['embedUrl'] = $video['embed_url'];
        }
        if (!empty($video['duration'])) {
            $probonoseo_schema['duration'] = 'PT' . $video['duration'] . 'S';
        }
        return $probonoseo_schema;
    }

    public function add_video_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        $probonoseo_post_types = array('post', 'page');
        foreach ($probonoseo_post_types as $probonoseo_post_type) {
            add_meta_box('probonoseo_video_metabox', 'Video schema（ProbonoSEO）', array($this, 'render_video_metabox'), $probonoseo_post_type, 'normal', 'default');
        }
    }

    public function render_video_metabox($post) {
        wp_nonce_field('probonoseo_video_save', 'probonoseo_video_nonce');
        $probonoseo_video = get_post_meta($post->ID, '_probonoseo_video', true);
        if (!is_array($probonoseo_video)) {
            $probonoseo_video = array('name' => '', 'description' => '', 'content_url' => '', 'embed_url' => '', 'thumbnail_url' => '', 'duration' => '');
        }
        echo '<div class="probonoseo-video-container">';
        echo '<div class="probonoseo-schema-row"><label>動画タイトル</label><input type="text" name="probonoseo_video[name]" value="' . esc_attr($probonoseo_video['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_video[description]" rows="2">' . esc_textarea($probonoseo_video['description']) . '</textarea></div>';
        echo '<div class="probonoseo-schema-row"><label>動画URL</label><input type="url" name="probonoseo_video[content_url]" value="' . esc_attr($probonoseo_video['content_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>埋め込みURL</label><input type="url" name="probonoseo_video[embed_url]" value="' . esc_attr($probonoseo_video['embed_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>サムネイルURL</label><input type="url" name="probonoseo_video[thumbnail_url]" value="' . esc_attr($probonoseo_video['thumbnail_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>再生時間（秒）</label><input type="number" name="probonoseo_video[duration]" value="' . esc_attr($probonoseo_video['duration']) . '"></div>';
        echo '</div>';
    }

    public function save_video_data($post_id) {
        if (!isset($_POST['probonoseo_video_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_video_nonce'])), 'probonoseo_video_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_video']) && is_array($_POST['probonoseo_video'])) {
            $probonoseo_fields = array('name', 'description', 'content_url', 'embed_url', 'thumbnail_url', 'duration');
            $probonoseo_video = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_video'][$probonoseo_field])) {
                    $probonoseo_video[$probonoseo_field] = '';
                    continue;
                }
                if (in_array($probonoseo_field, array('content_url', 'embed_url', 'thumbnail_url'), true)) {
                    $probonoseo_video[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_video'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_video[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_video'][$probonoseo_field]));
                } else {
                    $probonoseo_video[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_video'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_video', $probonoseo_video);
        }
    }
}

ProbonoSEO_SERP_Video::get_instance();