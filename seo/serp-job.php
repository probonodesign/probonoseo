<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Job {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_job_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_job_metabox'));
        add_action('save_post', array($this, 'save_job_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_probonoseo_save_job', array($this, 'ajax_save_job'));
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
        return get_option('probonoseo_serp_job', '0') === '1';
    }

    public function output_job_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $probonoseo_post_id = get_the_ID();
        $probonoseo_job = get_post_meta($probonoseo_post_id, '_probonoseo_job', true);
        if (empty($probonoseo_job) || empty($probonoseo_job['title'])) {
            return;
        }
        $probonoseo_schema = $this->build_schema($probonoseo_job, $probonoseo_post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($job, $post_id) {
        $probonoseo_job_type = get_option('probonoseo_serp_job_type', 'FULL_TIME');
        $probonoseo_job_remote = get_option('probonoseo_serp_job_remote', '0');
        $probonoseo_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job['title'],
            'description' => !empty($job['description']) ? $job['description'] : get_the_excerpt($post_id),
            'datePosted' => get_the_date('c', $post_id),
            'employmentType' => !empty($job['employment_type']) ? $job['employment_type'] : $probonoseo_job_type
        );
        if (!empty($job['valid_through'])) {
            $probonoseo_schema['validThrough'] = $job['valid_through'];
        }
        if (!empty($job['company_name'])) {
            $probonoseo_schema['hiringOrganization'] = array(
                '@type' => 'Organization',
                'name' => $job['company_name'],
                'sameAs' => !empty($job['company_url']) ? $job['company_url'] : get_home_url()
            );
            if (!empty($job['company_logo'])) {
                $probonoseo_schema['hiringOrganization']['logo'] = $job['company_logo'];
            }
        }
        $probonoseo_remote = !empty($job['remote']) ? $job['remote'] : $probonoseo_job_remote;
        if ($probonoseo_remote === '2') {
            $probonoseo_schema['jobLocationType'] = 'TELECOMMUTE';
        } elseif (!empty($job['location'])) {
            $probonoseo_schema['jobLocation'] = array(
                '@type' => 'Place',
                'address' => array('@type' => 'PostalAddress', 'streetAddress' => $job['location'], 'addressCountry' => 'JP')
            );
        }
        if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
            $probonoseo_schema['baseSalary'] = array(
                '@type' => 'MonetaryAmount',
                'currency' => 'JPY',
                'value' => array('@type' => 'QuantitativeValue', 'unitText' => !empty($job['salary_unit']) ? $job['salary_unit'] : 'MONTH')
            );
            if (!empty($job['salary_min'])) {
                $probonoseo_schema['baseSalary']['value']['minValue'] = intval($job['salary_min']);
            }
            if (!empty($job['salary_max'])) {
                $probonoseo_schema['baseSalary']['value']['maxValue'] = intval($job['salary_max']);
            }
        }
        return $probonoseo_schema;
    }

    public function add_job_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        foreach (array('post', 'page') as $probonoseo_post_type) {
            add_meta_box('probonoseo_job_metabox', 'JobPosting schema（求人情報）', array($this, 'render_job_metabox'), $probonoseo_post_type, 'normal', 'default');
        }
    }

    public function render_job_metabox($post) {
        wp_nonce_field('probonoseo_job_save', 'probonoseo_job_nonce');
        $probonoseo_job = get_post_meta($post->ID, '_probonoseo_job', true);
        if (!is_array($probonoseo_job)) {
            $probonoseo_job = array('title' => '', 'description' => '', 'company_name' => '', 'company_url' => '', 'company_logo' => '', 'location' => '', 'remote' => '', 'employment_type' => '', 'salary_min' => '', 'salary_max' => '', 'salary_unit' => '', 'valid_through' => '');
        }
        echo '<div class="probonoseo-schema-container">';
        echo '<div class="probonoseo-schema-grid">';
        echo '<div class="probonoseo-schema-row"><label>職種名</label><input type="text" name="probonoseo_job[title]" value="' . esc_attr($probonoseo_job['title']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>雇用形態</label><select name="probonoseo_job[employment_type]"><option value="">デフォルト</option><option value="FULL_TIME"' . selected($probonoseo_job['employment_type'], 'FULL_TIME', false) . '>正社員</option><option value="PART_TIME"' . selected($probonoseo_job['employment_type'], 'PART_TIME', false) . '>パート</option><option value="CONTRACT"' . selected($probonoseo_job['employment_type'], 'CONTRACT', false) . '>契約社員</option><option value="TEMPORARY"' . selected($probonoseo_job['employment_type'], 'TEMPORARY', false) . '>派遣</option><option value="INTERN"' . selected($probonoseo_job['employment_type'], 'INTERN', false) . '>インターン</option></select></div>';
        echo '<div class="probonoseo-schema-row"><label>会社名</label><input type="text" name="probonoseo_job[company_name]" value="' . esc_attr($probonoseo_job['company_name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>会社URL</label><input type="url" name="probonoseo_job[company_url]" value="' . esc_attr($probonoseo_job['company_url']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>会社ロゴURL</label><input type="url" name="probonoseo_job[company_logo]" value="' . esc_attr($probonoseo_job['company_logo']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>勤務地</label><input type="text" name="probonoseo_job[location]" value="' . esc_attr($probonoseo_job['location']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>リモート</label><select name="probonoseo_job[remote]"><option value="">デフォルト</option><option value="0"' . selected($probonoseo_job['remote'], '0', false) . '>オフィス</option><option value="1"' . selected($probonoseo_job['remote'], '1', false) . '>リモート可</option><option value="2"' . selected($probonoseo_job['remote'], '2', false) . '>フルリモート</option></select></div>';
        echo '<div class="probonoseo-schema-row"><label>給与下限</label><input type="number" name="probonoseo_job[salary_min]" value="' . esc_attr($probonoseo_job['salary_min']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>給与上限</label><input type="number" name="probonoseo_job[salary_max]" value="' . esc_attr($probonoseo_job['salary_max']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>給与単位</label><select name="probonoseo_job[salary_unit]"><option value="MONTH"' . selected($probonoseo_job['salary_unit'], 'MONTH', false) . '>月給</option><option value="YEAR"' . selected($probonoseo_job['salary_unit'], 'YEAR', false) . '>年収</option><option value="HOUR"' . selected($probonoseo_job['salary_unit'], 'HOUR', false) . '>時給</option></select></div>';
        echo '<div class="probonoseo-schema-row"><label>募集終了日</label><input type="date" name="probonoseo_job[valid_through]" value="' . esc_attr($probonoseo_job['valid_through']) . '"></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-grid probonoseo-schema-grid-full">';
        echo '<div class="probonoseo-schema-row"><label>仕事内容</label><textarea name="probonoseo_job[description]" rows="3">' . esc_textarea($probonoseo_job['description']) . '</textarea></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-save-row">';
        echo '<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="' . esc_attr($post->ID) . '" data-action="probonoseo_save_job" data-nonce="' . esc_attr(wp_create_nonce('probonoseo_save_job')) . '">保存</button>';
        echo '<span class="probonoseo-schema-save-msg"></span>';
        echo '</div>';
        echo '</div>';
    }

    public function ajax_save_job() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_job')) {
            wp_send_json_error(array('message' => '認証エラー'));
            return;
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => '権限がありません'));
            return;
        }
        if (isset($_POST['probonoseo_job']) && is_array($_POST['probonoseo_job'])) {
            $probonoseo_fields = array('title', 'description', 'company_name', 'company_url', 'company_logo', 'location', 'remote', 'employment_type', 'salary_min', 'salary_max', 'salary_unit', 'valid_through');
            $probonoseo_job = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_job'][$probonoseo_field])) {
                    $probonoseo_job[$probonoseo_field] = '';
                    continue;
                }
                if (in_array($probonoseo_field, array('company_url', 'company_logo'), true)) {
                    $probonoseo_job[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_job[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                } else {
                    $probonoseo_job[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_job', $probonoseo_job);
        }
        wp_send_json_success(array('message' => '保存しました'));
    }

    public function save_job_data($post_id) {
        if (!isset($_POST['probonoseo_job_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_job_nonce'])), 'probonoseo_job_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_job']) && is_array($_POST['probonoseo_job'])) {
            $probonoseo_fields = array('title', 'description', 'company_name', 'company_url', 'company_logo', 'location', 'remote', 'employment_type', 'salary_min', 'salary_max', 'salary_unit', 'valid_through');
            $probonoseo_job = array();
            foreach ($probonoseo_fields as $probonoseo_field) {
                if (!isset($_POST['probonoseo_job'][$probonoseo_field])) {
                    $probonoseo_job[$probonoseo_field] = '';
                    continue;
                }
                if (in_array($probonoseo_field, array('company_url', 'company_logo'), true)) {
                    $probonoseo_job[$probonoseo_field] = esc_url_raw(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                } elseif ($probonoseo_field === 'description') {
                    $probonoseo_job[$probonoseo_field] = sanitize_textarea_field(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                } else {
                    $probonoseo_job[$probonoseo_field] = sanitize_text_field(wp_unslash($_POST['probonoseo_job'][$probonoseo_field]));
                }
            }
            update_post_meta($post_id, '_probonoseo_job', $probonoseo_job);
        }
    }
}

ProbonoSEO_SERP_Job::get_instance();