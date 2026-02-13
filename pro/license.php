<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_License {
    
    const LICENSE_API_URL = 'https://seo.prbn.org/api';
    const LICENSE_API_KEY = 'becd9672a65a0f5eac173d2898b35eca6df3484252df814ad10a3aa4a556bff3';
    
    const OPTION_LICENSE_KEY = 'probonoseo_license_key';
    const OPTION_LICENSE_STATUS = 'probonoseo_license_status';
    const OPTION_LICENSE_EMAIL = 'probonoseo_license_email';
    const OPTION_LICENSE_ACTIVATED_DATE = 'probonoseo_license_activated_date';
    const OPTION_LICENSE_SITE_URL = 'probonoseo_license_site_url';
    const OPTION_LAST_CHECK = 'probonoseo_license_last_check';
    const OPTION_DEV_MODE = 'probonoseo_dev_mode';
    const OPTION_PLAN_TYPE = 'probonoseo_license_plan_type';
    const OPTION_MAX_SITES = 'probonoseo_license_max_sites';
    const OPTION_GRACE_PERIOD_START = 'probonoseo_grace_period_start';
    
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';
    const STATUS_INVALID = 'invalid';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_GRACE = 'grace';
    
    const GRACE_PERIOD_DAYS = 7;
    const CHECK_INTERVAL = 86400;
    
    const DEV_LICENSE_KEY = 'DEV0-0000-0000-TEST';
    
    private static $allowed_dev_domains = array(
        'probono-design.local',
        'localhost',
        '127.0.0.1',
    );
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        if (is_admin()) {
            add_action('admin_init', array($this, 'check_license_status'));
            add_action('admin_init', array($this, 'validate_dev_mode_domain'));
            add_action('admin_notices', array($this, 'show_license_notices'));
            add_action('wp_ajax_probonoseo_activate_license', array($this, 'ajax_activate_license'));
            add_action('wp_ajax_probonoseo_deactivate_license', array($this, 'ajax_deactivate_license'));
            add_action('wp_ajax_probonoseo_check_license', array($this, 'ajax_check_license'));
            add_action('wp_ajax_probonoseo_toggle_dev_mode', array($this, 'ajax_toggle_dev_mode'));
        }
    }
    
    private function api_request($endpoint, $data) {
        $url = self::LICENSE_API_URL . '/' . $endpoint . '.php';
        
        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => self::LICENSE_API_KEY
            ),
            'body' => json_encode($data)
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => 'connection_failed',
                'message' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (!$result) {
            return array(
                'success' => false,
                'error' => 'invalid_response',
                'message' => 'サーバーからの応答が不正です。'
            );
        }
        
        $result['http_code'] = $code;
        return $result;
    }
    
    private function get_current_domain() {
        $site_url = get_site_url();
        $domain = wp_parse_url($site_url, PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $domain);
        return strtolower($domain);
    }
    
    public function is_dev_domain_allowed() {
        $domain = $this->get_current_domain();
        
        if (empty($domain)) {
            return false;
        }
        
        if (in_array($domain, self::$allowed_dev_domains, true)) {
            return true;
        }
        
        if (substr($domain, -6) === '.local') {
            return true;
        }
        
        if (substr($domain, -5) === '.test') {
            return true;
        }
        
        return false;
    }
    
    public function validate_dev_mode_domain() {
        if ($this->is_dev_mode() && !$this->is_dev_domain_allowed()) {
            update_option(self::OPTION_DEV_MODE, 0);
            delete_option(self::OPTION_LICENSE_KEY);
            delete_option(self::OPTION_LICENSE_STATUS);
            delete_option(self::OPTION_LICENSE_EMAIL);
            delete_option(self::OPTION_LICENSE_ACTIVATED_DATE);
            delete_option(self::OPTION_LICENSE_SITE_URL);
            delete_option(self::OPTION_PLAN_TYPE);
            delete_option(self::OPTION_MAX_SITES);
        }
    }
    
    public function is_dev_mode() {
        return get_option(self::OPTION_DEV_MODE, 0) == 1;
    }
    
    public function should_show_dev_mode_ui() {
        return $this->is_dev_domain_allowed();
    }
    
    public function toggle_dev_mode() {
        if (!$this->is_dev_domain_allowed()) {
            return array(
                'success' => false,
                'message' => '開発モードはこのドメインでは使用できません。'
            );
        }
        
        $current = get_option(self::OPTION_DEV_MODE, 0);
        $new_value = $current == 1 ? 0 : 1;
        
        if ($new_value == 1) {
            update_option(self::OPTION_DEV_MODE, 1);
            update_option(self::OPTION_LICENSE_KEY, $this->encrypt_license_key(self::DEV_LICENSE_KEY));
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_ACTIVE);
            update_option(self::OPTION_LICENSE_EMAIL, 'dev@localhost');
            update_option(self::OPTION_LICENSE_ACTIVATED_DATE, current_time('mysql'));
            update_option(self::OPTION_LICENSE_SITE_URL, get_site_url());
            update_option(self::OPTION_PLAN_TYPE, 'unlimited');
            update_option(self::OPTION_MAX_SITES, 999);
            
            return array(
                'success' => true,
                'message' => '開発モードが有効になりました。仮ライセンスでPro版機能を使用できます。'
            );
        } else {
            update_option(self::OPTION_DEV_MODE, 0);
            delete_option(self::OPTION_LICENSE_KEY);
            delete_option(self::OPTION_LICENSE_STATUS);
            delete_option(self::OPTION_LICENSE_EMAIL);
            delete_option(self::OPTION_LICENSE_ACTIVATED_DATE);
            delete_option(self::OPTION_LICENSE_SITE_URL);
            delete_option(self::OPTION_PLAN_TYPE);
            delete_option(self::OPTION_MAX_SITES);
            
            return array(
                'success' => true,
                'message' => '開発モードが無効になりました。'
            );
        }
    }
    
    public function activate_license($license_key) {
        if (empty($license_key)) {
            return array(
                'success' => false,
                'message' => 'ライセンスキーを入力してください。'
            );
        }
        
        if (!$this->validate_license_format($license_key)) {
            return array(
                'success' => false,
                'message' => 'ライセンスキーの形式が正しくありません。'
            );
        }
        
        $domain = $this->get_current_domain();
        
        $result = $this->api_request('license-activate', array(
            'license_key' => $license_key,
            'domain' => $domain
        ));
        
        if (!$result['success']) {
            if (isset($result['error']) && $result['error'] === 'connection_failed') {
                return array(
                    'success' => false,
                    'message' => 'ライセンスサーバーに接続できませんでした。インターネット接続を確認してください。'
                );
            }
            
            $message = isset($result['error']) ? $result['error'] : 'ライセンスの認証に失敗しました。';
            
            if ($message === 'Maximum number of sites reached') {
                $max = isset($result['max_sites']) ? $result['max_sites'] : 5;
                $current = isset($result['current_sites']) ? $result['current_sites'] : $max;
                $message = "登録可能なサイト数の上限（{$max}サイト）に達しています。現在{$current}サイト登録済みです。";
            }
            
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_INVALID);
            
            return array(
                'success' => false,
                'message' => $message
            );
        }
        
        $email = get_option('admin_email');
        
        update_option(self::OPTION_LICENSE_KEY, $this->encrypt_license_key($license_key));
        update_option(self::OPTION_LICENSE_STATUS, self::STATUS_ACTIVE);
        update_option(self::OPTION_LICENSE_EMAIL, sanitize_email($email));
        update_option(self::OPTION_LICENSE_ACTIVATED_DATE, current_time('mysql'));
        update_option(self::OPTION_LICENSE_SITE_URL, get_site_url());
        update_option(self::OPTION_LAST_CHECK, time());
        update_option(self::OPTION_DEV_MODE, 0);
        delete_option(self::OPTION_GRACE_PERIOD_START);
        
        if (isset($result['plan_type'])) {
            update_option(self::OPTION_PLAN_TYPE, sanitize_text_field($result['plan_type']));
        }
        if (isset($result['max_sites'])) {
            update_option(self::OPTION_MAX_SITES, intval($result['max_sites']));
        }
        
        return array(
            'success' => true,
            'message' => 'ライセンスが正常に認証されました。Pro版機能が有効になりました。'
        );
    }
    
    public function deactivate_license() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            return array(
                'success' => false,
                'message' => 'アクティブなライセンスが見つかりません。'
            );
        }
        
        $domain = $this->get_current_domain();
        
        $result = $this->api_request('license-deactivate', array(
            'license_key' => $license_key,
            'domain' => $domain
        ));
        
        delete_option(self::OPTION_LICENSE_KEY);
        delete_option(self::OPTION_LICENSE_STATUS);
        delete_option(self::OPTION_LICENSE_EMAIL);
        delete_option(self::OPTION_LICENSE_ACTIVATED_DATE);
        delete_option(self::OPTION_LICENSE_SITE_URL);
        delete_option(self::OPTION_LAST_CHECK);
        delete_option(self::OPTION_PLAN_TYPE);
        delete_option(self::OPTION_MAX_SITES);
        delete_option(self::OPTION_GRACE_PERIOD_START);
        update_option(self::OPTION_DEV_MODE, 0);
        
        if (!$result['success'] && isset($result['error']) && $result['error'] === 'connection_failed') {
            return array(
                'success' => true,
                'message' => 'ライセンスをローカルで解除しました。サーバーへの通知に失敗しましたが、問題ありません。'
            );
        }
        
        return array(
            'success' => true,
            'message' => 'ライセンスが正常に解除されました。'
        );
    }
    
    public function check_license_status() {
        if ($this->is_dev_mode()) {
            return;
        }
        
        $last_check = get_option(self::OPTION_LAST_CHECK, 0);
        $current_time = time();
        
        if (($current_time - $last_check) < self::CHECK_INTERVAL) {
            return;
        }
        
        $license_key = $this->get_license_key();
        $domain = $this->get_current_domain();
        
        if (empty($license_key)) {
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_INACTIVE);
            return;
        }
        
        $result = $this->api_request('license-verify', array(
            'license_key' => $license_key,
            'domain' => $domain
        ));
        
        if (!$result['success'] || (isset($result['error']) && $result['error'] === 'connection_failed')) {
            $this->handle_connection_failure();
            return;
        }
        
        delete_option(self::OPTION_GRACE_PERIOD_START);
        
        if (isset($result['valid']) && $result['valid'] === true) {
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_ACTIVE);
            
            if (isset($result['plan_type'])) {
                update_option(self::OPTION_PLAN_TYPE, sanitize_text_field($result['plan_type']));
            }
            if (isset($result['max_sites'])) {
                update_option(self::OPTION_MAX_SITES, intval($result['max_sites']));
            }
        } else {
            $error = isset($result['error']) ? $result['error'] : '';
            
            if (strpos($error, 'expired') !== false) {
                update_option(self::OPTION_LICENSE_STATUS, self::STATUS_EXPIRED);
            } elseif (strpos($error, 'not active') !== false) {
                update_option(self::OPTION_LICENSE_STATUS, self::STATUS_SUSPENDED);
            } else {
                update_option(self::OPTION_LICENSE_STATUS, self::STATUS_INVALID);
            }
        }
        
        update_option(self::OPTION_LAST_CHECK, $current_time);
    }
    
    private function handle_connection_failure() {
        $grace_start = get_option(self::OPTION_GRACE_PERIOD_START, 0);
        
        if ($grace_start === 0) {
            update_option(self::OPTION_GRACE_PERIOD_START, time());
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_GRACE);
            return;
        }
        
        $grace_days = (time() - $grace_start) / 86400;
        
        if ($grace_days > self::GRACE_PERIOD_DAYS) {
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_INVALID);
        } else {
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_GRACE);
        }
    }
    
    public function is_pro_active() {
        if ($this->is_dev_mode()) {
            return $this->is_dev_domain_allowed();
        }
        
        $status = get_option(self::OPTION_LICENSE_STATUS, self::STATUS_INACTIVE);
        return ($status === self::STATUS_ACTIVE || $status === self::STATUS_GRACE);
    }
    
    public function get_license_status() {
        return get_option(self::OPTION_LICENSE_STATUS, self::STATUS_INACTIVE);
    }
    
    public function get_license_key() {
        $encrypted_key = get_option(self::OPTION_LICENSE_KEY, '');
        
        if (empty($encrypted_key)) {
            return '';
        }
        
        return $this->decrypt_license_key($encrypted_key);
    }
    
    public function get_masked_license_key() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            return '';
        }
        
        if ($license_key === self::DEV_LICENSE_KEY) {
            return 'DEV0-****-****-TEST（開発モード）';
        }
        
        $parts = explode('-', $license_key);
        
        if (count($parts) !== 4) {
            return '****-****-****-****';
        }
        
        return $parts[0] . '-****-****-' . $parts[3];
    }
    
    public function get_license_info() {
        return array(
            'status' => $this->get_license_status(),
            'email' => get_option(self::OPTION_LICENSE_EMAIL, ''),
            'activated_date' => get_option(self::OPTION_LICENSE_ACTIVATED_DATE, ''),
            'site_url' => get_option(self::OPTION_LICENSE_SITE_URL, ''),
            'masked_key' => $this->get_masked_license_key(),
            'is_dev_mode' => $this->is_dev_mode(),
            'is_dev_domain_allowed' => $this->is_dev_domain_allowed(),
            'plan_type' => get_option(self::OPTION_PLAN_TYPE, ''),
            'max_sites' => get_option(self::OPTION_MAX_SITES, 0),
            'grace_period_start' => get_option(self::OPTION_GRACE_PERIOD_START, 0)
        );
    }
    
    private function validate_license_format($license_key) {
        return preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key);
    }
    
    private function encrypt_license_key($license_key) {
        if (!defined('AUTH_KEY') || empty(AUTH_KEY)) {
            return base64_encode($license_key);
        }
        
        $key = substr(hash('sha256', AUTH_KEY), 0, 32);
        $iv = substr(hash('sha256', AUTH_SALT), 0, 16);
        
        $encrypted = openssl_encrypt($license_key, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($encrypted);
    }
    
    private function decrypt_license_key($encrypted_key) {
        if (!defined('AUTH_KEY') || empty(AUTH_KEY)) {
            return base64_decode($encrypted_key);
        }
        
        $key = substr(hash('sha256', AUTH_KEY), 0, 32);
        $iv = substr(hash('sha256', AUTH_SALT), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($encrypted_key), 'AES-256-CBC', $key, 0, $iv);
        
        return $decrypted;
    }
    
    public function show_license_notices() {
        $screen = get_current_screen();
        
        if (!$screen || strpos($screen->id, 'probonoseo') === false) {
            return;
        }
        
        if ($this->is_dev_mode() && $this->is_dev_domain_allowed()) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>ProbonoSEO 開発モード</strong>: 仮ライセンスでPro版機能を使用中です。（開発環境専用）</p>';
            echo '</div>';
            return;
        }
        
        $status = $this->get_license_status();
        
        if ($status === self::STATUS_INACTIVE) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>ProbonoSEO Pro版</strong>: ライセンスキーを入力してPro版機能を有効化してください。</p>';
            echo '</div>';
        } elseif ($status === self::STATUS_INVALID) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>ProbonoSEO Pro版</strong>: ライセンスキーが無効です。正しいキーを入力してください。</p>';
            echo '</div>';
        } elseif ($status === self::STATUS_EXPIRED) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>ProbonoSEO Pro版</strong>: ライセンスの有効期限が切れています。</p>';
            echo '</div>';
        } elseif ($status === self::STATUS_SUSPENDED) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>ProbonoSEO Pro版</strong>: ライセンスが停止されています。サポートにお問い合わせください。</p>';
            echo '</div>';
        } elseif ($status === self::STATUS_GRACE) {
            $grace_start = get_option(self::OPTION_GRACE_PERIOD_START, time());
            $days_left = self::GRACE_PERIOD_DAYS - floor((time() - $grace_start) / 86400);
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>ProbonoSEO Pro版</strong>: ライセンスサーバーに接続できません。オフライン猶予期間中です（残り' . esc_html($days_left) . '日）。</p>';
            echo '</div>';
        }
    }
    
    public function ajax_activate_license() {
        check_ajax_referer('probonoseo_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        $license_key = isset($_POST['license_key']) ? sanitize_text_field(wp_unslash($_POST['license_key'])) : '';
        
        $result = $this->activate_license($license_key);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_deactivate_license() {
        check_ajax_referer('probonoseo_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        $result = $this->deactivate_license();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_check_license() {
        check_ajax_referer('probonoseo_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        $license_key = $this->get_license_key();
        $domain = $this->get_current_domain();
        
        if (empty($license_key)) {
            wp_send_json_error(array('message' => 'ライセンスキーが登録されていません。'));
            return;
        }
        
        $result = $this->api_request('license-verify', array(
            'license_key' => $license_key,
            'domain' => $domain
        ));
        
        if (!$result['success'] || (isset($result['error']) && $result['error'] === 'connection_failed')) {
            wp_send_json_error(array('message' => 'ライセンスサーバーに接続できませんでした。'));
            return;
        }
        
        if (isset($result['valid']) && $result['valid'] === true) {
            update_option(self::OPTION_LICENSE_STATUS, self::STATUS_ACTIVE);
            update_option(self::OPTION_LAST_CHECK, time());
            delete_option(self::OPTION_GRACE_PERIOD_START);
            
            wp_send_json_success(array(
                'message' => 'ライセンスは有効です。',
                'status' => self::STATUS_ACTIVE
            ));
        } else {
            $message = isset($result['error']) ? $result['error'] : 'ライセンスの確認に失敗しました。';
            wp_send_json_error(array('message' => $message));
        }
    }
    
    public function ajax_toggle_dev_mode() {
        check_ajax_referer('probonoseo_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        if (!$this->is_dev_domain_allowed()) {
            wp_send_json_error(array('message' => '開発モードはこのドメインでは使用できません。'));
            return;
        }
        
        $result = $this->toggle_dev_mode();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}

ProbonoSEO_License::get_instance();