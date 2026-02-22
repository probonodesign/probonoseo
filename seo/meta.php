<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Meta {
    private static $instance = null;
    private $forbidden_words = array(
        'クリック', 'こちら', '詳細はこちら', '続きを読む', 
        '無料', '今すぐ', '限定', '特典', 'キャンペーン'
    );
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        if (get_option('probonoseo_basic_metadesc', '1') === '1') {
            add_action('wp_head', array($this, 'output_meta_description'), 1);
        }
    }
    
    public function output_meta_description() {
        if (is_admin()) {
            return;
        }
        
        $probonoseo_description = $this->get_meta_description();
        
        if (!empty($probonoseo_description)) {
            echo '<meta name="description" content="' . esc_attr($probonoseo_description) . '">' . "\n";
        }
    }
    
    private function get_meta_description() {
        global $post;
        
        $probonoseo_description = '';
        
        if (is_front_page()) {
            $probonoseo_description = $this->get_frontpage_description();
        } elseif (is_singular()) {
            $probonoseo_description = $this->get_singular_description($post);
        } elseif (is_category()) {
            $probonoseo_description = $this->get_category_description();
        } elseif (is_tag()) {
            $probonoseo_description = $this->get_tag_description();
        } elseif (is_author()) {
            $probonoseo_description = $this->get_author_description();
        } elseif (is_archive()) {
            $probonoseo_description = $this->get_archive_description();
        } elseif (is_search()) {
            $probonoseo_description = $this->get_search_description();
        }
        
        if (get_option('probonoseo_meta_forbidden', '1') === '1') {
            $probonoseo_description = $this->remove_forbidden_words($probonoseo_description);
        }
        
        if (get_option('probonoseo_meta_length', '1') === '1') {
            $probonoseo_description = $this->optimize_length($probonoseo_description);
        }
        
        if (get_option('probonoseo_meta_duplicate', '1') === '1') {
            $probonoseo_description = $this->check_duplicate($probonoseo_description);
        }
        
        return $probonoseo_description;
    }
    
    private function get_frontpage_description() {
        $probonoseo_description = get_bloginfo('description');
        
        if (empty($probonoseo_description)) {
            $probonoseo_description = get_bloginfo('name') . 'のホームページです。';
        }
        
        return $probonoseo_description;
    }
    
    private function get_singular_description($post) {
        if (!$post) {
            return '';
        }
        
        $probonoseo_custom_description = get_post_meta($post->ID, '_probonoseo_meta_description', true);
        if (!empty($probonoseo_custom_description)) {
            return $probonoseo_custom_description;
        }
        
        $probonoseo_excerpt = $post->post_excerpt;
        
        if (empty($probonoseo_excerpt)) {
            $probonoseo_excerpt = $this->extract_from_content($post->post_content);
        }
        
        if (get_option('probonoseo_meta_keywords', '1') === '1') {
            $probonoseo_excerpt = $this->prioritize_important_words($probonoseo_excerpt, $post);
        }
        
        if (get_option('probonoseo_meta_summary', '1') === '1') {
            $probonoseo_excerpt = $this->create_japanese_summary($probonoseo_excerpt);
        }
        
        return $probonoseo_excerpt;
    }
    
    private function get_category_description() {
        $probonoseo_category = get_queried_object();
        
        $probonoseo_description = $probonoseo_category->description;
        
        if (empty($probonoseo_description)) {
            $probonoseo_description = $probonoseo_category->name . 'に関する記事一覧です。';
        }
        
        return $probonoseo_description;
    }
    
    private function get_tag_description() {
        $probonoseo_tag = get_queried_object();
        
        $probonoseo_description = $probonoseo_tag->description;
        
        if (empty($probonoseo_description)) {
            $probonoseo_description = $probonoseo_tag->name . 'タグの記事一覧です。';
        }
        
        return $probonoseo_description;
    }
    
    private function get_author_description() {
        $probonoseo_author = get_queried_object();
        
        $probonoseo_description = get_the_author_meta('description', $probonoseo_author->ID);
        
        if (empty($probonoseo_description)) {
            $probonoseo_description = $probonoseo_author->display_name . 'の記事一覧ページです。';
        }
        
        return $probonoseo_description;
    }
    
    private function get_archive_description() {
        $probonoseo_description = '';
        
        if (is_post_type_archive()) {
            $probonoseo_post_type = get_query_var('post_type');
            if (is_array($probonoseo_post_type)) {
                $probonoseo_post_type = reset($probonoseo_post_type);
            }
            $probonoseo_post_type_obj = get_post_type_object($probonoseo_post_type);
            $probonoseo_description = $probonoseo_post_type_obj->labels->name . 'の一覧ページです。';
        } elseif (is_year()) {
            $probonoseo_description = get_the_time('Y年') . 'に投稿された記事の一覧です。';
        } elseif (is_month()) {
            $probonoseo_description = get_the_time('Y年n月') . 'に投稿された記事の一覧です。';
        } elseif (is_day()) {
            $probonoseo_description = get_the_time('Y年n月j日') . 'に投稿された記事の一覧です。';
        } else {
            $probonoseo_description = 'アーカイブページです。';
        }
        
        return $probonoseo_description;
    }
    
    private function get_search_description() {
        $probonoseo_search_query = get_search_query();
        $probonoseo_description = '"' . $probonoseo_search_query . '"の検索結果ページです。';
        
        return $probonoseo_description;
    }
    
    private function extract_from_content($content) {
        $probonoseo_content = wp_strip_all_tags($content);
        $probonoseo_content = strip_shortcodes($probonoseo_content);
        
        $probonoseo_content = preg_replace('/\[.*?\]/', '', $probonoseo_content);
        
        $probonoseo_content = preg_replace('/\s+/', ' ', $probonoseo_content);
        $probonoseo_content = trim($probonoseo_content);
        
        $probonoseo_sentences = preg_split('/[。！?]/u', $probonoseo_content);
        
        $probonoseo_extracted = '';
        $probonoseo_current_length = 0;
        
        foreach ($probonoseo_sentences as $probonoseo_sentence) {
            $probonoseo_sentence = trim($probonoseo_sentence);
            
            if (empty($probonoseo_sentence)) {
                continue;
            }
            
            $probonoseo_sentence_length = mb_strlen($probonoseo_sentence);
            
            if ($probonoseo_current_length + $probonoseo_sentence_length > 150) {
                break;
            }
            
            $probonoseo_extracted .= $probonoseo_sentence . '。';
            $probonoseo_current_length += $probonoseo_sentence_length;
        }
        
        return $probonoseo_extracted;
    }
    
    private function prioritize_important_words($text, $post) {
        $probonoseo_title = get_the_title($post);
        
        $probonoseo_title_words = $this->extract_keywords($probonoseo_title);
        
        $probonoseo_sentences = preg_split('/[。！?]/u', $text);
        
        $probonoseo_scored_sentences = array();
        
        foreach ($probonoseo_sentences as $probonoseo_sentence) {
            $probonoseo_sentence = trim($probonoseo_sentence);
            
            if (empty($probonoseo_sentence)) {
                continue;
            }
            
            $probonoseo_score = 0;
            
            foreach ($probonoseo_title_words as $probonoseo_word) {
                if (mb_strpos($probonoseo_sentence, $probonoseo_word) !== false) {
                    $probonoseo_score += mb_strlen($probonoseo_word);
                }
            }
            
            $probonoseo_scored_sentences[] = array(
                'sentence' => $probonoseo_sentence,
                'score' => $probonoseo_score
            );
        }
        
        usort($probonoseo_scored_sentences, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        $probonoseo_result = '';
        $probonoseo_current_length = 0;
        
        foreach ($probonoseo_scored_sentences as $probonoseo_item) {
            $probonoseo_sentence_length = mb_strlen($probonoseo_item['sentence']);
            
            if ($probonoseo_current_length + $probonoseo_sentence_length > 150) {
                break;
            }
            
            $probonoseo_result .= $probonoseo_item['sentence'] . '。';
            $probonoseo_current_length += $probonoseo_sentence_length;
        }
        
        return $probonoseo_result;
    }
    
    private function extract_keywords($text) {
        $probonoseo_text = wp_strip_all_tags($text);
        
        $probonoseo_words = preg_split('/[\s、。！?]/u', $probonoseo_text);
        
        $probonoseo_keywords = array();
        
        foreach ($probonoseo_words as $probonoseo_word) {
            $probonoseo_word = trim($probonoseo_word);
            
            if (mb_strlen($probonoseo_word) >= 2) {
                $probonoseo_keywords[] = $probonoseo_word;
            }
        }
        
        return $probonoseo_keywords;
    }
    
    private function create_japanese_summary($text) {
        $probonoseo_sentences = preg_split('/[。！?]/u', $text);
        
        $probonoseo_summary = '';
        $probonoseo_sentence_count = 0;
        $probonoseo_current_length = 0;
        
        foreach ($probonoseo_sentences as $probonoseo_sentence) {
            $probonoseo_sentence = trim($probonoseo_sentence);
            
            if (empty($probonoseo_sentence)) {
                continue;
            }
            
            $probonoseo_sentence_length = mb_strlen($probonoseo_sentence);
            
            if ($probonoseo_sentence_count >= 2 || $probonoseo_current_length + $probonoseo_sentence_length > 120) {
                break;
            }
            
            $probonoseo_summary .= $probonoseo_sentence . '。';
            $probonoseo_sentence_count++;
            $probonoseo_current_length += $probonoseo_sentence_length;
        }
        
        if (empty($probonoseo_summary)) {
            $probonoseo_summary = mb_substr($text, 0, 100) . '…';
        }
        
        return $probonoseo_summary;
    }
    
    private function remove_forbidden_words($text) {
        foreach ($this->forbidden_words as $probonoseo_word) {
            $text = str_replace($probonoseo_word, '', $text);
        }
        
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    private function optimize_length($text) {
        $probonoseo_length = mb_strlen($text);
        
        if ($probonoseo_length < 70) {
            return $text;
        }
        
        if ($probonoseo_length > 120) {
            $text = mb_substr($text, 0, 117) . '…';
        }
        
        return $text;
    }
    
    private function check_duplicate($description) {
        global $wpdb;
        
        if (!is_singular()) {
            return $description;
        }
        
        global $post;
        
        $probonoseo_cache_key = 'probonoseo_meta_dup_' . md5($description) . '_' . $post->ID;
        $probonoseo_duplicate = wp_cache_get($probonoseo_cache_key, 'probonoseo');
        
        if ($probonoseo_duplicate === false) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $probonoseo_duplicate = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_probonoseo_meta_description' AND meta_value = %s AND post_id != %d LIMIT 1",
                $description,
                $post->ID
            ));
            wp_cache_set($probonoseo_cache_key, $probonoseo_duplicate ? $probonoseo_duplicate : 'none', 'probonoseo', 3600);
        }
        
        if ($probonoseo_duplicate && $probonoseo_duplicate !== 'none') {
            $description .= ' - ' . get_the_title($post);
            $description = $this->optimize_length($description);
        }
        
        return $description;
    }
    
    public static function get_description_length($description) {
        return mb_strlen($description);
    }
    
    public static function is_description_optimized($description) {
        $probonoseo_length = self::get_description_length($description);
        return $probonoseo_length >= 70 && $probonoseo_length <= 120;
    }
}

function probonoseo_init_meta() {
    ProbonoSEO_Meta::get_instance();
}
add_action('init', 'probonoseo_init_meta');