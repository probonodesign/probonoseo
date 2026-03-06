<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Event {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_event_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_event_metabox'));
        add_action('save_post', array($this, 'save_event_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_probonoseo_save_event', array($this, 'ajax_save_event'));
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
        $probonoseo_license = ProbonoSEO_License::get_instance();
        if (!$probonoseo_license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_event', '0') === '1';
    }

    public function output_event_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $probonoseo_post_id = get_the_ID();
        $probonoseo_event = get_post_meta($probonoseo_post_id, '_probonoseo_event', true);
        if (empty($probonoseo_event) || empty($probonoseo_event['name']) || empty($probonoseo_event['start_date'])) {
            return;
        }
        $probonoseo_schema = $this->build_schema($probonoseo_event, $probonoseo_post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($event, $post_id) {
        $probonoseo_event_mode = get_option('probonoseo_serp_event_mode', 'OfflineEventAttendanceMode');
        $probonoseo_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event['name'],
            'startDate' => $event['start_date'],
            'eventAttendanceMode' => 'https://schema.org/' . (!empty($event['attendance_mode']) ? $event['attendance_mode'] : $probonoseo_event_mode),
            'eventStatus' => 'https://schema.org/EventScheduled'
        );
        if (!empty($event['description'])) {
            $probonoseo_schema['description'] = $event['description'];
        }
        if (!empty($event['end_date'])) {
            $probonoseo_schema['endDate'] = $event['end_date'];
        }
        if (!empty($event['image'])) {
            $probonoseo_schema['image'] = $event['image'];
        } elseif (has_post_thumbnail($post_id)) {
            $probonoseo_schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
        }
        $probonoseo_attendance_mode = !empty($event['attendance_mode']) ? $event['attendance_mode'] : $probonoseo_event_mode;
        if ($probonoseo_attendance_mode === 'OnlineEventAttendanceMode' && !empty($event['online_url'])) {
            $probonoseo_schema['location'] = array('@type' => 'VirtualLocation', 'url' => $event['online_url']);
        } elseif (!empty($event['venue_name'])) {
            $probonoseo_schema['location'] = array(
                '@type' => 'Place',
                'name' => $event['venue_name'],
                'address' => array('@type' => 'PostalAddress', 'streetAddress' => !empty($event['venue_address']) ? $event['venue_address'] : '', 'addressCountry' => 'JP')
            );
        }
        if (!empty($event['organizer_name'])) {
            $probonoseo_schema['organizer'] = array('@type' => 'Organization', 'name' => $event['organizer_name'], 'url' => get_home_url());
        }
        if (get_option('probonoseo_serp_event_offers', '1') === '1' && !empty($event['price'])) {
            $probonoseo_schema['offers'] = array(
                '@type' => 'Offer',
                'price' => $event['price'],
                'priceCurrency' => 'JPY',
                'availability' => 'https://schema.org/InStock',
                'url' => get_permalink($post_id),
                'validFrom' => get_the_date('c', $post_id)
            );
        }
        return $probonoseo_schema;
    }

    public function add_event_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        foreach (array('post', 'page') as $probonoseo_post_type) {
            add_meta_box('probonoseo_event_metabox', 'Event schema（イベント情報）', array($this, 'render_event_metabox'), $probonoseo_post_type, 'normal', 'default');
        }
    }

    public function render_event_metabox($post) {
        wp_nonce_field('probonoseo_event_save', 'probonoseo_event_nonce');
        $probonoseo_event = get_post_meta($post->ID, '_probonoseo_event', true);
        if (!is_array($probonoseo_event)) {
            $probonoseo_event = array('name' => '', 'description' => '', 'start_date' => '', 'end_date' => '', 'image' => '', 'attendance_mode' => '', 'online_url' => '', 'venue_name' => '', 'venue_address' => '', 'organizer_name' => '', 'price' => '');
        }
        echo '<div class="probonoseo-schema-container">';
        echo '<div class="probonoseo-schema-grid">';
        echo '<div class="probonoseo-schema-row"><label>イベント名</label><input type="text" name="probonoseo_event[name]" value="' . esc_attr($probonoseo_event['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>開催形式</label><select name="probonoseo_event[attendance_mode]"><option value="">デフォルト</option><option value="OfflineEventAttendanceMode"' . selected($probonoseo_event['attendance_mode'], 'OfflineEventAttendanceMode', false) . '>オフライン</option><option value="OnlineEventAttendanceMode"' . selected($probonoseo_event['attendance_mode'], 'OnlineEventAttendanceMode', false) . '>オンライン</option><option value="MixedEventAttendanceMode"' . selected($probonoseo_event['attendance_mode'], 'MixedEventAttendanceMode', false) . '>ハイブリッド</option></select></div>';
        echo '<div class="probonoseo-schema-row"><label>開始日時</label><input type="datetime-local" name="probonoseo_event[start_date]" value="' . esc_attr($probonoseo_event['start_date']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>終了日時</label><input type="datetime-local" name="probonoseo_event[end_date]" value="' . esc_attr($probonoseo_event['end_date']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>会場名</label><input type="text" name="probonoseo_event[venue_name]" value="' . esc_attr($probonoseo_event['venue_name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>オンラインURL</label><input type="url" name="probonoseo_event[online_url]" value="' . esc_attr($probonoseo_event['online_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>会場住所</label><input type="text" name="probonoseo_event[venue_address]" value="' . esc_attr($probonoseo_event['venue_address']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>主催者名</label><input type="text" name="probonoseo_event[organizer_name]" value="' . esc_attr($probonoseo_event['organizer_name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>料金（円）</label><input type="number" name="probonoseo_event[price]" value="' . esc_attr($probonoseo_event['price']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>画像URL</label><input type="url" name="probonoseo_event[image]" value="' . esc_attr($probonoseo_event['image']) . '"></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-grid probonoseo-schema-grid-full">';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_event[description]" rows="2">' . esc_textarea($probonoseo_event['description']) . '</textarea></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-save-row">';
        echo '<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="' . esc_attr($post->ID) . '" data-action="probonoseo_save_event" data-nonce="' . esc_attr(wp_create_nonce('probonoseo_save_event')) . '">保存</button>';
        echo '<span class="probonoseo-schema-save-msg"></span>';
        echo '</div>';
        echo '</div>';
    }

    public function ajax_save_event() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_event')) {
            wp_send_json_error(array('message' => '認証エラー'));
            return;
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => '権限がありません'));
            return;
        }
        if (isset($_POST['probonoseo_event']) && is_array($_POST['probonoseo_event'])) {
            $probonoseo_fields = array('name', 'description', 'start_date', 'end_date', 'image', 'attendance_mode', 'online_url', 'venue_name', 'venue_address', 'organizer_name', 'price');
            $probonoseo_event = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_event'][$probonoseo_field])) {
                    $probonoseo_event[$probonoseo_field] = '';
                    continue;
                }
                if (in_array($probonoseo_field, array('image', 'online_url'), true)) {
                    $probonoseo_event[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_event[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                } else {
                    $probonoseo_event[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_event', $probonoseo_event);
        }
        wp_send_json_success(array('message' => '保存しました'));
    }

    public function save_event_data($post_id) {
        if (!isset($_POST['probonoseo_event_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_event_nonce'])), 'probonoseo_event_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_event']) && is_array($_POST['probonoseo_event'])) {
            $probonoseo_fields = array('name', 'description', 'start_date', 'end_date', 'image', 'attendance_mode', 'online_url', 'venue_name', 'venue_address', 'organizer_name', 'price');
            $probonoseo_event = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_event'][$probonoseo_field])) {
                    $probonoseo_event[$probonoseo_field] = '';
                    continue;
                }
                if (in_array($probonoseo_field, array('image', 'online_url'), true)) {
                    $probonoseo_event[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_event[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                } else {
                    $probonoseo_event[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_event'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_event', $probonoseo_event);
        }
    }
}

ProbonoSEO_SERP_Event::get_instance();