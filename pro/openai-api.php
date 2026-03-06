<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_OpenAI_API {
    
    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    const API_VISION_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    const API_EMBEDDING_ENDPOINT = 'https://api.openai.com/v1/embeddings';
    
    const OPTION_API_KEY = 'probonoseo_openai_api_key';
    const OPTION_MODEL = 'probonoseo_openai_model';
    const OPTION_MAX_TOKENS = 'probonoseo_openai_max_tokens';
    const OPTION_TEMPERATURE = 'probonoseo_openai_temperature';
    const OPTION_USAGE_COUNT = 'probonoseo_openai_usage_count';
    const OPTION_LAST_RESET = 'probonoseo_openai_last_reset';
    
    const DEFAULT_MODEL = 'gpt-4o';
    const DEFAULT_MAX_TOKENS = 1000;
    const DEFAULT_TEMPERATURE = 0.7;
    const RATE_LIMIT = 60;
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        if (is_admin()) {
            add_action('wp_ajax_probonoseo_test_openai_api', array($this, 'ajax_test_api'));
            add_action('wp_ajax_probonoseo_save_openai_settings', array($this, 'ajax_save_settings'));
        }
    }
    
    public function save_api_key($api_key) {
        if (empty($api_key)) {
            delete_option(self::OPTION_API_KEY);
            return array(
                'success' => true,
                'message' => 'APIキーを削除しました。'
            );
        }
        
        if (!$this->validate_api_key_format($api_key)) {
            return array(
                'success' => false,
                'message' => 'APIキーの形式が正しくありません。sk-で始まる必要があります。'
            );
        }
        
        $encrypted_key = $this->encrypt_api_key($api_key);
        update_option(self::OPTION_API_KEY, $encrypted_key);
        
        return array(
            'success' => true,
            'message' => 'APIキーを保存しました。'
        );
    }
    
    public function get_api_key() {
        $encrypted_key = get_option(self::OPTION_API_KEY, '');
        
        if (empty($encrypted_key)) {
            return '';
        }
        
        return $this->decrypt_api_key($encrypted_key);
    }
    
    public function get_masked_api_key() {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return '';
        }
        
        if (strlen($api_key) < 20) {
            return 'sk-****';
        }
        
        return substr($api_key, 0, 7) . '****' . substr($api_key, -4);
    }
    
    public function is_api_key_set() {
        return !empty($this->get_api_key());
    }
    
    public function test_connection() {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'APIキーが設定されていません。'
            );
        }
        
        $response = wp_remote_post(self::API_ENDPOINT, array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => 'Hello'
                    )
                ),
                'max_tokens' => 10
            ))
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'OpenAI APIへの接続に失敗しました: ' . $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200) {
            return array(
                'success' => true,
                'message' => 'OpenAI APIへの接続に成功しました。'
            );
        } elseif ($status_code === 401) {
            return array(
                'success' => false,
                'message' => 'APIキーが無効です。正しいキーを入力してください。'
            );
        } elseif ($status_code === 429) {
            return array(
                'success' => false,
                'message' => 'OpenAIのレート制限に達しました。しばらく待ってから再試行してください。'
            );
        } else {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : '不明なエラー';
            return array(
                'success' => false,
                'message' => 'APIエラー: ' . $error_message
            );
        }
    }
    
    public function send_request($messages, $options = array()) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'APIキーが設定されていません。'
            );
        }
        
        $model = isset($options['model']) ? $options['model'] : get_option(self::OPTION_MODEL, self::DEFAULT_MODEL);
        $max_tokens = isset($options['max_tokens']) ? $options['max_tokens'] : get_option(self::OPTION_MAX_TOKENS, self::DEFAULT_MAX_TOKENS);
        $temperature = isset($options['temperature']) ? $options['temperature'] : get_option(self::OPTION_TEMPERATURE, self::DEFAULT_TEMPERATURE);
        
        $request_body = array(
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => intval($max_tokens),
            'temperature' => floatval($temperature)
        );
        
        $response = wp_remote_post(self::API_ENDPOINT, array(
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_body)
        ));
        
        $this->increment_usage_count();
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API通信エラー: ' . $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'content' => $data['choices'][0]['message']['content'],
                'usage' => isset($data['usage']) ? $data['usage'] : null
            );
        } elseif ($status_code === 401) {
            return array(
                'success' => false,
                'message' => 'APIキーが無効です。'
            );
        } elseif ($status_code === 429) {
            return array(
                'success' => false,
                'message' => 'OpenAI APIのレート制限です。プランの上限を確認するか、しばらく待ってから再試行してください。'
            );
        } else {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : '不明なエラー';
            return array(
                'success' => false,
                'message' => 'APIエラー: ' . $error_message
            );
        }
    }
    
    public function send_vision_request($messages, $options = array()) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'APIキーが設定されていません。'
            );
        }
        
        $max_tokens = isset($options['max_tokens']) ? $options['max_tokens'] : 300;
        
        $request_body = array(
            'model' => 'gpt-4o',
            'messages' => $messages,
            'max_tokens' => intval($max_tokens)
        );
        
        $response = wp_remote_post(self::API_VISION_ENDPOINT, array(
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_body)
        ));
        
        $this->increment_usage_count();
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API通信エラー: ' . $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'content' => $data['choices'][0]['message']['content'],
                'usage' => isset($data['usage']) ? $data['usage'] : null
            );
        } else {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : '不明なエラー';
            return array(
                'success' => false,
                'message' => 'APIエラー: ' . $error_message
            );
        }
    }
    
    public function get_embedding($text) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'APIキーが設定されていません。'
            );
        }
        
        $response = wp_remote_post(self::API_EMBEDDING_ENDPOINT, array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'text-embedding-3-small',
                'input' => $text
            ))
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API通信エラー: ' . $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 && isset($data['data'][0]['embedding'])) {
            return array(
                'success' => true,
                'embedding' => $data['data'][0]['embedding']
            );
        } else {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : '不明なエラー';
            return array(
                'success' => false,
                'message' => 'APIエラー: ' . $error_message
            );
        }
    }
    
    private function increment_usage_count() {
        $current_time = time();
        $last_reset = get_option(self::OPTION_LAST_RESET, 0);
        
        if (($current_time - $last_reset) >= 60) {
            update_option(self::OPTION_USAGE_COUNT, 1);
            update_option(self::OPTION_LAST_RESET, $current_time);
        } else {
            $usage_count = get_option(self::OPTION_USAGE_COUNT, 0);
            update_option(self::OPTION_USAGE_COUNT, $usage_count + 1);
        }
    }
    
    public function get_usage_stats() {
        $current_time = time();
        $last_reset = get_option(self::OPTION_LAST_RESET, 0);
        $usage_count = get_option(self::OPTION_USAGE_COUNT, 0);
        
        if (($current_time - $last_reset) >= 60) {
            $usage_count = 0;
        }
        
        return array(
            'count' => $usage_count,
            'limit' => self::RATE_LIMIT,
            'last_reset' => $last_reset
        );
    }
    
    private function validate_api_key_format($api_key) {
        if (strpos($api_key, 'sk-') === 0 && strlen($api_key) >= 20) {
            return true;
        }
        return false;
    }
    
    private function encrypt_api_key($api_key) {
        if (!defined('AUTH_KEY') || empty(AUTH_KEY)) {
            return base64_encode($api_key);
        }
        
        $key = substr(hash('sha256', AUTH_KEY), 0, 32);
        $iv = substr(hash('sha256', AUTH_SALT), 0, 16);
        
        $encrypted = openssl_encrypt($api_key, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($encrypted);
    }
    
    private function decrypt_api_key($encrypted_key) {
        if (!defined('AUTH_KEY') || empty(AUTH_KEY)) {
            return base64_decode($encrypted_key);
        }
        
        $key = substr(hash('sha256', AUTH_KEY), 0, 32);
        $iv = substr(hash('sha256', AUTH_SALT), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($encrypted_key), 'AES-256-CBC', $key, 0, $iv);
        
        return $decrypted;
    }
    
    public function ajax_test_api() {
        check_ajax_referer('probonoseo_openai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        $result = $this->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('probonoseo_openai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
            return;
        }
        
        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : self::DEFAULT_MODEL;
        $max_tokens = isset($_POST['max_tokens']) ? intval($_POST['max_tokens']) : self::DEFAULT_MAX_TOKENS;
        $temperature = isset($_POST['temperature']) ? floatval($_POST['temperature']) : self::DEFAULT_TEMPERATURE;
        
        if (!empty($api_key)) {
            $result = $this->save_api_key($api_key);
            if (!$result['success']) {
                wp_send_json_error($result);
                return;
            }
        }
        
        update_option(self::OPTION_MODEL, $model);
        update_option(self::OPTION_MAX_TOKENS, max(100, min(4000, $max_tokens)));
        update_option(self::OPTION_TEMPERATURE, max(0.0, min(2.0, $temperature)));
        
        wp_send_json_success(array('message' => '設定を保存しました。'));
    }
}

ProbonoSEO_OpenAI_API::get_instance();