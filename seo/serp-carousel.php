<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Carousel {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_carousel_schema'), 25);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_carousel', '0') === '1';
    }

    public function output_carousel_schema() {
        if (!is_home() && !is_archive() && !is_category()) {
            return;
        }
        if (!$this->is_enabled()) {
            return;
        }
        global $wp_query;
        if (empty($wp_query->posts)) {
            return;
        }
        $schema = $this->build_schema($wp_query->posts);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($posts) {
        $items = array();
        $position = 1;
        foreach ($posts as $post) {
            $item = array(
                '@type' => 'ListItem',
                'position' => $position,
                'url' => get_permalink($post->ID)
            );
            $items[] = $item;
            $position++;
        }
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items
        );
        return $schema;
    }
}

ProbonoSEO_SERP_Carousel::get_instance();