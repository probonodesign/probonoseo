<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Title {
    private static $instance = null;
    
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
        if (get_option('probonoseo_basic_title', '1') === '1') {
            add_filter('pre_get_document_title', array($this, 'optimize_title'), 10);
            add_filter('wp_title', array($this, 'optimize_wp_title'), 10, 3);
        }
    }
    
    public function optimize_title($title) {
        if (is_admin()) {
            return $title;
        }
        
        $probonoseo_optimized_title = $this->get_optimized_title();
        
        return $probonoseo_optimized_title ? $probonoseo_optimized_title : $title;
    }
    
    public function optimize_wp_title($title, $sep, $seplocation) {
        if (is_admin()) {
            return $title;
        }
        
        $probonoseo_optimized_title = $this->get_optimized_title();
        
        return $probonoseo_optimized_title ? $probonoseo_optimized_title : $title;
    }
    
    private function get_optimized_title() {
        global $post;
        
        $probonoseo_title = '';
        
        if (is_front_page()) {
            $probonoseo_title = $this->get_frontpage_title();
        } elseif (is_singular()) {
            $probonoseo_title = $this->get_singular_title($post);
        } elseif (is_category()) {
            $probonoseo_title = $this->get_category_title();
        } elseif (is_tag()) {
            $probonoseo_title = $this->get_tag_title();
        } elseif (is_author()) {
            $probonoseo_title = $this->get_author_title();
        } elseif (is_archive()) {
            $probonoseo_title = $this->get_archive_title();
        } elseif (is_search()) {
            $probonoseo_title = $this->get_search_title();
        } elseif (is_404()) {
            $probonoseo_title = $this->get_404_title();
        }
        
        if (get_option('probonoseo_title_separator', '1') === '1') {
            $probonoseo_title = $this->optimize_separator($probonoseo_title);
        }
        
        if (get_option('probonoseo_title_sitename', '1') === '1') {
            $probonoseo_title = $this->add_sitename($probonoseo_title);
        }
        
        if (get_option('probonoseo_title_symbols', '1') === '1') {
            $probonoseo_title = $this->cleanup_numbers_symbols($probonoseo_title);
        }
        
        if (get_option('probonoseo_title_duplicate', '1') === '1') {
            $probonoseo_title = $this->prevent_duplicate($probonoseo_title);
        }
        
        return $probonoseo_title;
    }
    
    private function get_frontpage_title() {
        $probonoseo_site_name = get_bloginfo('name');
        $probonoseo_tagline = get_bloginfo('description');
        
        if (!empty($probonoseo_tagline)) {
            return $probonoseo_site_name . ' - ' . $probonoseo_tagline;
        }
        
        return $probonoseo_site_name;
    }
    
    private function get_singular_title($post) {
        if (!$post) {
            return '';
        }
        
        $probonoseo_title = get_the_title($post);
        
        if (get_option('probonoseo_title_h1_check', '1') === '1') {
            $probonoseo_title = $this->check_h1_consistency($probonoseo_title, $post);
        }
        
        if (get_option('probonoseo_title_category', '1') === '1' && $post->post_type === 'post') {
            $probonoseo_title = $this->add_category_to_title($probonoseo_title, $post);
        }
        
        return $probonoseo_title;
    }
    
    private function get_category_title() {
        $probonoseo_category = get_queried_object();
        $probonoseo_title = single_cat_title('', false);
        
        if (get_option('probonoseo_title_category', '1') === '1') {
            $probonoseo_parent = get_category($probonoseo_category->parent);
            if ($probonoseo_parent && !is_wp_error($probonoseo_parent)) {
                $probonoseo_title = $probonoseo_parent->name . ' > ' . $probonoseo_title;
            }
        }
        
        if (get_query_var('paged') > 1) {
            $probonoseo_title .= ' - ページ' . get_query_var('paged');
        }
        
        return $probonoseo_title;
    }
    
    private function get_tag_title() {
        $probonoseo_title = single_tag_title('', false);
        
        if (get_query_var('paged') > 1) {
            $probonoseo_title .= ' - ページ' . get_query_var('paged');
        }
        
        return $probonoseo_title;
    }
    
    private function get_author_title() {
        $probonoseo_author = get_queried_object();
        $probonoseo_title = $probonoseo_author->display_name . 'の記事一覧';
        
        if (get_query_var('paged') > 1) {
            $probonoseo_title .= ' - ページ' . get_query_var('paged');
        }
        
        return $probonoseo_title;
    }
    
    private function get_archive_title() {
        $probonoseo_title = '';
        
        if (is_post_type_archive()) {
            $probonoseo_post_type = get_query_var('post_type');
            if (is_array($probonoseo_post_type)) {
                $probonoseo_post_type = reset($probonoseo_post_type);
            }
            $probonoseo_post_type_obj = get_post_type_object($probonoseo_post_type);
            $probonoseo_title = $probonoseo_post_type_obj->labels->name;
        } elseif (is_year()) {
            $probonoseo_title = get_the_time('Y年') . 'の記事';
        } elseif (is_month()) {
            $probonoseo_title = get_the_time('Y年n月') . 'の記事';
        } elseif (is_day()) {
            $probonoseo_title = get_the_time('Y年n月j日') . 'の記事';
        } else {
            $probonoseo_title = 'アーカイブ';
        }
        
        if (get_query_var('paged') > 1) {
            $probonoseo_title .= ' - ページ' . get_query_var('paged');
        }
        
        return $probonoseo_title;
    }
    
    private function get_search_title() {
        $probonoseo_search_query = get_search_query();
        $probonoseo_title = '"' . $probonoseo_search_query . '"の検索結果';
        
        if (get_query_var('paged') > 1) {
            $probonoseo_title .= ' - ページ' . get_query_var('paged');
        }
        
        return $probonoseo_title;
    }
    
    private function get_404_title() {
        return 'ページが見つかりません - 404エラー';
    }
    
    private function optimize_separator($title) {
        if (empty($title) || !is_string($title)) {
            return $title;
        }
        
        $probonoseo_separator = '-';
        
        $title = preg_replace('/[\-\|\/：]+/', ' ' . $probonoseo_separator . ' ', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);
        
        return $title;
    }
    
    private function add_sitename($title) {
        if (is_front_page()) {
            return $title;
        }
        
        if (empty($title) || !is_string($title)) {
            return $title;
        }
        
        $probonoseo_site_name = get_bloginfo('name');
        $probonoseo_separator = '-';
        
        if (strpos($title, $probonoseo_site_name) === false) {
            return $title . ' ' . $probonoseo_separator . ' ' . $probonoseo_site_name;
        }
        
        return $title;
    }
    
    private function check_h1_consistency($title, $post) {
        if (!$post || empty($post->post_content)) {
            return $title;
        }
        
        $probonoseo_content = $post->post_content;
        
        preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $probonoseo_content, $probonoseo_matches);
        
        if (!empty($probonoseo_matches[1])) {
            $probonoseo_h1_text = wp_strip_all_tags($probonoseo_matches[1]);
            $probonoseo_h1_text = trim($probonoseo_h1_text);
            
            if (!empty($probonoseo_h1_text) && mb_strlen($probonoseo_h1_text) > 3) {
                return $probonoseo_h1_text;
            }
        }
        
        return $title;
    }
    
    private function add_category_to_title($title, $post) {
        if (empty($title) || !is_string($title)) {
            return $title;
        }
        
        $probonoseo_categories = get_the_category($post->ID);
        
        if (!empty($probonoseo_categories)) {
            $probonoseo_category = $probonoseo_categories[0];
            
            if (strpos($title, $probonoseo_category->name) === false) {
                $probonoseo_separator = '-';
                return $probonoseo_category->name . ' ' . $probonoseo_separator . ' ' . $title;
            }
        }
        
        return $title;
    }
    
    private function cleanup_numbers_symbols($title) {
        if (empty($title) || !is_string($title)) {
            return '';
        }
        
        $title = preg_replace_callback('/[０-９]/u', function($matches) {
            return mb_convert_kana($matches[0], 'n');
        }, $title);
        
        if ($title === null || $title === false) {
            return '';
        }
        
        $title = (string)$title;
        
        $title = preg_replace('/[!！]/', '!', $title);
        if ($title === null) return '';
        
        $title = preg_replace('/[?？]/', '?', $title);
        if ($title === null) return '';
        
        $title = preg_replace('/[。、]/u', '', $title);
        if ($title === null) return '';
        
        $title = preg_replace('/\s+/', ' ', $title);
        if ($title === null) return '';
        
        $title = trim($title);
        
        return $title;
    }
    
    private function prevent_duplicate($title) {
        global $wpdb;
        
        if (!is_singular()) {
            return $title;
        }
        
        global $post;
        
        if (!$post) {
            return $title;
        }
        
        if (empty($title) || !is_string($title)) {
            return $title;
        }
        
        $probonoseo_cache_key = 'probonoseo_title_dup_' . md5($title) . '_' . $post->ID;
        $probonoseo_duplicate_count = wp_cache_get($probonoseo_cache_key, 'probonoseo');
        
        if ($probonoseo_duplicate_count === false) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$probonoseo_duplicate_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_title = %s AND post_status = 'publish' AND ID != %d",
                $title,
                $post->ID
            ));
            wp_cache_set($probonoseo_cache_key, $probonoseo_duplicate_count, 'probonoseo', 3600);
        }
        
        if ($probonoseo_duplicate_count > 0) {
            $title .= ' - ' . $post->ID;
        }
        
        return $title;
    }
    
    public static function get_title_length($title) {
        return mb_strlen($title);
    }
    
    public static function is_title_optimized($title) {
        $probonoseo_length = self::get_title_length($title);
        return $probonoseo_length >= 20 && $probonoseo_length <= 40;
    }
}

function probonoseo_init_title() {
    ProbonoSEO_Title::get_instance();
}
add_action('init', 'probonoseo_init_title');