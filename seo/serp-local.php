<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Local {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_local_schema'), 25);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $probonoseo_license = ProbonoSEO_License::get_instance();
        if (!$probonoseo_license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_local', '0') === '1';
    }

    public function output_local_schema() {
        if (!is_front_page() || !$this->is_enabled()) {
            return;
        }
        $probonoseo_local_name = get_option('probonoseo_serp_local_name', '');
        if (empty($probonoseo_local_name)) {
            return;
        }
        $probonoseo_schema = $this->build_schema();
        echo '<script type="application/ld+json">' . wp_json_encode($probonoseo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema() {
        $probonoseo_local_name = get_option('probonoseo_serp_local_name', '');
        $probonoseo_local_type = get_option('probonoseo_serp_local_type', 'LocalBusiness');
        $probonoseo_local_address = get_option('probonoseo_serp_local_address', '');
        $probonoseo_local_phone = get_option('probonoseo_serp_local_phone', '');
        $probonoseo_local_hours = get_option('probonoseo_serp_local_hours', '');
        $probonoseo_schema = array(
            '@context' => 'https://schema.org',
            '@type' => $probonoseo_local_type,
            'name' => $probonoseo_local_name,
            'url' => home_url('/')
        );
        $probonoseo_custom_logo_id = get_theme_mod('custom_logo');
        if ($probonoseo_custom_logo_id) {
            $probonoseo_logo_url = wp_get_attachment_image_url($probonoseo_custom_logo_id, 'full');
            if ($probonoseo_logo_url) {
                $probonoseo_schema['image'] = $probonoseo_logo_url;
            }
        }
        if (!empty($probonoseo_local_address)) {
            $probonoseo_schema['address'] = array(
                '@type' => 'PostalAddress',
                'streetAddress' => $probonoseo_local_address,
                'addressCountry' => 'JP'
            );
        }
        if (!empty($probonoseo_local_phone)) {
            $probonoseo_schema['telephone'] = $probonoseo_local_phone;
        }
        if (!empty($probonoseo_local_hours)) {
            $probonoseo_schema['openingHours'] = $probonoseo_local_hours;
        }
        return $probonoseo_schema;
    }
}

ProbonoSEO_SERP_Local::get_instance();