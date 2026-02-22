<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Podcast {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_podcast_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_podcast_metabox'));
        add_action('save_post', array($this, 'save_podcast_data'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $probonoseo_license = ProbonoSEO_License::get_instance();
        if (!$probonoseo_license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_podcast', '0') === '1';
    }

    public function output_podcast_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $probonoseo_post_id = get_the_ID();
        $probonoseo_podcast = get_post_meta($probonoseo_post_id, '_probonoseo_podcast', true);
        if (empty($probonoseo_podcast) || empty($probonoseo_podcast['name'])) {
            return;
        }
        $probonoseo_schema = $this->build_schema($probonoseo_podcast, $probonoseo_post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($podcast, $post_id) {
        $probonoseo_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'PodcastSeries',
            'name' => $podcast['name'],
            'description' => !empty($podcast['description']) ? $podcast['description'] : get_the_excerpt($post_id),
            'url' => get_permalink($post_id)
        );
        if (!empty($podcast['image'])) {
            $probonoseo_schema['image'] = $podcast['image'];
        } elseif (has_post_thumbnail($post_id)) {
            $probonoseo_schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
        }
        if (!empty($podcast['author'])) {
            $probonoseo_schema['author'] = array('@type' => 'Person', 'name' => $podcast['author']);
        }
        if (!empty($podcast['publisher'])) {
            $probonoseo_schema['publisher'] = array('@type' => 'Organization', 'name' => $podcast['publisher']);
        }
        if (!empty($podcast['feed_url'])) {
            $probonoseo_schema['webFeed'] = $podcast['feed_url'];
        }
        if (!empty($podcast['language'])) {
            $probonoseo_schema['inLanguage'] = $podcast['language'];
        }
        if (!empty($podcast['genre'])) {
            $probonoseo_schema['genre'] = $podcast['genre'];
        }
        if (!empty($podcast['episode_count'])) {
            $probonoseo_schema['numberOfEpisodes'] = intval($podcast['episode_count']);
        }
        return $probonoseo_schema;
    }

    public function add_podcast_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        $probonoseo_post_types = array('post', 'page');
        foreach ($probonoseo_post_types as $probonoseo_post_type) {
            add_meta_box('probonoseo_podcast_metabox', 'Podcast schema（ProbonoSEO）', array($this, 'render_podcast_metabox'), $probonoseo_post_type, 'normal', 'default');
        }
    }

    public function render_podcast_metabox($post) {
        wp_nonce_field('probonoseo_podcast_save', 'probonoseo_podcast_nonce');
        $probonoseo_podcast = get_post_meta($post->ID, '_probonoseo_podcast', true);
        if (!is_array($probonoseo_podcast)) {
            $probonoseo_podcast = array('name' => '', 'description' => '', 'image' => '', 'author' => '', 'publisher' => '', 'feed_url' => '', 'language' => '', 'genre' => '', 'episode_count' => '');
        }
        echo '<div class="probonoseo-podcast-container">';
        echo '<div class="probonoseo-schema-row"><label>ポッドキャスト名</label><input type="text" name="probonoseo_podcast[name]" value="' . esc_attr($probonoseo_podcast['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_podcast[description]" rows="2">' . esc_textarea($probonoseo_podcast['description']) . '</textarea></div>';
        echo '<div class="probonoseo-schema-row"><label>著者</label><input type="text" name="probonoseo_podcast[author]" value="' . esc_attr($probonoseo_podcast['author']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>配信元</label><input type="text" name="probonoseo_podcast[publisher]" value="' . esc_attr($probonoseo_podcast['publisher']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>フィードURL</label><input type="url" name="probonoseo_podcast[feed_url]" value="' . esc_attr($probonoseo_podcast['feed_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>言語</label><input type="text" name="probonoseo_podcast[language]" value="' . esc_attr($probonoseo_podcast['language']) . '" placeholder="ja"></div>';
        echo '<div class="probonoseo-schema-row"><label>ジャンル</label><input type="text" name="probonoseo_podcast[genre]" value="' . esc_attr($probonoseo_podcast['genre']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>エピソード数</label><input type="number" name="probonoseo_podcast[episode_count]" value="' . esc_attr($probonoseo_podcast['episode_count']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>画像URL</label><input type="url" name="probonoseo_podcast[image]" value="' . esc_attr($probonoseo_podcast['image']) . '"></div>';
        echo '</div>';
    }

    public function save_podcast_data($post_id) {
        if (!isset($_POST['probonoseo_podcast_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_podcast_nonce'])), 'probonoseo_podcast_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_podcast']) && is_array($_POST['probonoseo_podcast'])) {
            $probonoseo_fields = array('name', 'description', 'image', 'author', 'publisher', 'feed_url', 'language', 'genre', 'episode_count');
            $probonoseo_podcast = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_podcast'][$probonoseo_field])) {
                    $probonoseo_podcast[$probonoseo_field] = '';
                    continue;
                }
                if ($probonoseo_field === 'image' || $probonoseo_field === 'feed_url') {
                    $probonoseo_podcast[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_podcast'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_podcast[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_podcast'][$probonoseo_field]));
                } else {
                    $probonoseo_podcast[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_podcast'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_podcast', $probonoseo_podcast);
        }
    }
}

ProbonoSEO_SERP_Podcast::get_instance();