<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Knowledge {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_knowledge_schema'), 25);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_knowledge', '0') === '1';
    }

    public function output_knowledge_schema() {
        if (!is_front_page() || !$this->is_enabled()) {
            return;
        }
        $local_name = get_option('probonoseo_serp_local_name', '');
        $local_type = get_option('probonoseo_serp_local_type', 'Organization');
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => !empty($local_type) ? $local_type : 'Organization',
            'name' => !empty($local_name) ? $local_name : get_bloginfo('name'),
            'url' => home_url('/'),
            'logo' => $this->get_site_logo()
        );
        $same_as = $this->get_social_links();
        if (!empty($same_as)) {
            $schema['sameAs'] = $same_as;
        }
        $local_phone = get_option('probonoseo_serp_local_phone', '');
        if (!empty($local_phone)) {
            $schema['telephone'] = $local_phone;
        }
        $local_address = get_option('probonoseo_serp_local_address', '');
        if (!empty($local_address)) {
            $schema['address'] = array(
                '@type' => 'PostalAddress',
                'streetAddress' => $local_address,
                'addressCountry' => 'JP'
            );
        }
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function get_site_logo() {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                return $logo_url;
            }
        }
        return home_url('/favicon.ico');
    }

    private function get_social_links() {
        $links = array();
        $social_options = array(
            'probonoseo_ogp_facebook_url',
            'probonoseo_twitter_url',
            'probonoseo_instagram_url',
            'probonoseo_youtube_url',
            'probonoseo_linkedin_url'
        );
        foreach ($social_options as $option) {
            $url = get_option($option, '');
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        return $links;
    }
}

ProbonoSEO_SERP_Knowledge::get_instance();