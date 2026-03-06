<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Sitelinks {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_sitelinks_schema'), 25);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_sitelinks', '0') === '1';
    }

    public function output_sitelinks_schema() {
        if (!is_front_page() || !$this->is_enabled()) {
            return;
        }
        $schema = $this->build_schema();
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema() {
        $menus = wp_get_nav_menus();
        $menu_items = array();
        if (!empty($menus)) {
            $menu = $menus[0];
            $items = wp_get_nav_menu_items($menu->term_id);
            if (!empty($items)) {
                foreach ($items as $item) {
                    if ($item->menu_item_parent == 0) {
                        $menu_items[] = array(
                            '@type' => 'SiteNavigationElement',
                            'name' => $item->title,
                            'url' => $item->url
                        );
                    }
                }
            }
        }
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => array(
                    '@type' => 'EntryPoint',
                    'urlTemplate' => home_url('/?s={search_term_string}')
                ),
                'query-input' => 'required name=search_term_string'
            )
        );
        if (!empty($menu_items)) {
            $schema['hasPart'] = $menu_items;
        }
        return $schema;
    }
}

ProbonoSEO_SERP_Sitelinks::get_instance();