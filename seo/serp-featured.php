<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Featured {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('the_content', array($this, 'optimize_content_for_featured'), 99);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_featured', '0') === '1';
    }

    public function optimize_content_for_featured($content) {
        if (!is_singular() || !$this->is_enabled()) {
            return $content;
        }
        $content = $this->add_summary_after_headings($content);
        $content = $this->optimize_lists($content);
        $content = $this->optimize_tables($content);
        return $content;
    }

    private function add_summary_after_headings($content) {
        $pattern = '/(<h2[^>]*>.*?<\/h2>)\s*(<p>)/is';
        $replacement = '$1<div class="probonoseo-featured-summary">$2';
        $content = preg_replace($pattern, $replacement, $content, 3);
        return $content;
    }

    private function optimize_lists($content) {
        $pattern = '/<ul([^>]*)>/i';
        $replacement = '<ul$1 itemscope itemtype="https://schema.org/ItemList">';
        $content = preg_replace($pattern, $replacement, $content);
        $pattern = '/<ol([^>]*)>/i';
        $replacement = '<ol$1 itemscope itemtype="https://schema.org/ItemList">';
        $content = preg_replace($pattern, $replacement, $content);
        return $content;
    }

    private function optimize_tables($content) {
        $pattern = '/<table([^>]*)>/i';
        $replacement = '<table$1 itemscope itemtype="https://schema.org/Table">';
        $content = preg_replace($pattern, $replacement, $content);
        return $content;
    }
}

ProbonoSEO_SERP_Featured::get_instance();