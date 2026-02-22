<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_Sitemap_Advanced {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_shortcode('probonoseo_html_sitemap', array($this, 'render_html_sitemap'));
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_sitemap', '0') === '1';
    }

    public function register_rewrite_rules() {
        if (!$this->is_enabled()) {
            return;
        }
        add_rewrite_rule('^probonoseo-sitemap\.xml$', 'index.php?probonoseo_sitemap=index', 'top');
        add_rewrite_rule('^probonoseo-sitemap-posts\.xml$', 'index.php?probonoseo_sitemap=posts', 'top');
        add_rewrite_rule('^probonoseo-sitemap-pages\.xml$', 'index.php?probonoseo_sitemap=pages', 'top');
        add_rewrite_rule('^probonoseo-sitemap-images\.xml$', 'index.php?probonoseo_sitemap=images', 'top');
    }

    public function add_query_vars($vars) {
        $vars[] = 'probonoseo_sitemap';
        return $vars;
    }

    public function handle_sitemap_request() {
        $sitemap_type = get_query_var('probonoseo_sitemap');
        if (empty($sitemap_type) || !$this->is_enabled()) {
            return;
        }
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex, follow');
        switch ($sitemap_type) {
            case 'index':
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output
                echo $this->generate_sitemap_index();
                break;
            case 'posts':
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output
                echo $this->generate_posts_sitemap();
                break;
            case 'pages':
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output
                echo $this->generate_pages_sitemap();
                break;
            case 'images':
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output
                echo $this->generate_images_sitemap();
                break;
        }
        exit;
    }

    private function generate_sitemap_index() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '<sitemap><loc>' . home_url('/probonoseo-sitemap-posts.xml') . '</loc><lastmod>' . gmdate('c') . '</lastmod></sitemap>' . "\n";
        $xml .= '<sitemap><loc>' . home_url('/probonoseo-sitemap-pages.xml') . '</loc><lastmod>' . gmdate('c') . '</lastmod></sitemap>' . "\n";
        if (get_option('probonoseo_serp_sitemap_image', '1') === '1') {
            $xml .= '<sitemap><loc>' . home_url('/probonoseo-sitemap-images.xml') . '</loc><lastmod>' . gmdate('c') . '</lastmod></sitemap>' . "\n";
        }
        $xml .= '</sitemapindex>';
        return $xml;
    }

    private function generate_posts_sitemap() {
        $limit = intval(get_option('probonoseo_serp_sitemap_limit', '50000'));
        $freq = get_option('probonoseo_serp_sitemap_freq', 'weekly');
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($posts as $post) {
            $priority = $this->calculate_priority($post);
            $xml .= '<url>';
            $xml .= '<loc>' . get_permalink($post->ID) . '</loc>';
            $xml .= '<lastmod>' . get_the_modified_date('c', $post->ID) . '</lastmod>';
            $xml .= '<changefreq>' . $freq . '</changefreq>';
            $xml .= '<priority>' . $priority . '</priority>';
            $xml .= '</url>' . "\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    private function generate_pages_sitemap() {
        $limit = intval(get_option('probonoseo_serp_sitemap_limit', '50000'));
        $freq = get_option('probonoseo_serp_sitemap_freq', 'weekly');
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '<url><loc>' . home_url('/') . '</loc><lastmod>' . gmdate('c') . '</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";
        foreach ($pages as $page) {
            $priority = $this->calculate_priority($page);
            $xml .= '<url>';
            $xml .= '<loc>' . get_permalink($page->ID) . '</loc>';
            $xml .= '<lastmod>' . get_the_modified_date('c', $page->ID) . '</lastmod>';
            $xml .= '<changefreq>' . $freq . '</changefreq>';
            $xml .= '<priority>' . $priority . '</priority>';
            $xml .= '</url>' . "\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    private function generate_images_sitemap() {
        $limit = intval(get_option('probonoseo_serp_sitemap_limit', '50000'));
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        foreach ($attachments as $attachment) {
            $parent_id = $attachment->post_parent;
            if ($parent_id && get_post_status($parent_id) === 'publish') {
                $xml .= '<url>';
                $xml .= '<loc>' . get_permalink($parent_id) . '</loc>';
                $xml .= '<image:image>';
                $xml .= '<image:loc>' . wp_get_attachment_url($attachment->ID) . '</image:loc>';
                if (!empty($attachment->post_title)) {
                    $xml .= '<image:title>' . esc_html($attachment->post_title) . '</image:title>';
                }
                $xml .= '</image:image>';
                $xml .= '</url>' . "\n";
            }
        }
        $xml .= '</urlset>';
        return $xml;
    }

    private function calculate_priority($post) {
        $days_since_modified = (time() - strtotime($post->post_modified)) / 86400;
        if ($days_since_modified < 7) {
            return '0.8';
        } elseif ($days_since_modified < 30) {
            return '0.6';
        } elseif ($days_since_modified < 90) {
            return '0.4';
        }
        return '0.3';
    }

    public function render_html_sitemap($atts) {
        if (!$this->is_enabled() || get_option('probonoseo_serp_sitemap_html', '1') !== '1') {
            return '';
        }
        $atts = shortcode_atts(array(
            'show_pages' => 'yes',
            'show_posts' => 'yes',
            'show_categories' => 'yes'
        ), $atts);
        $output = '<div class="probonoseo-html-sitemap">';
        if ($atts['show_pages'] === 'yes') {
            $output .= '<div class="probonoseo-sitemap-section">';
            $output .= '<h3>固定ページ</h3>';
            $output .= '<ul>';
            $pages = get_pages(array('sort_column' => 'menu_order'));
            foreach ($pages as $page) {
                $output .= '<li><a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a></li>';
            }
            $output .= '</ul></div>';
        }
        if ($atts['show_categories'] === 'yes') {
            $output .= '<div class="probonoseo-sitemap-section">';
            $output .= '<h3>カテゴリー</h3>';
            $output .= '<ul>';
            $categories = get_categories(array('hide_empty' => true));
            foreach ($categories as $category) {
                $output .= '<li><a href="' . get_category_link($category->term_id) . '">' . esc_html($category->name) . '</a> (' . $category->count . ')</li>';
            }
            $output .= '</ul></div>';
        }
        if ($atts['show_posts'] === 'yes') {
            $output .= '<div class="probonoseo-sitemap-section">';
            $output .= '<h3>投稿記事</h3>';
            $output .= '<ul>';
            $posts = get_posts(array('posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC'));
            foreach ($posts as $post) {
                $output .= '<li><a href="' . get_permalink($post->ID) . '">' . esc_html($post->post_title) . '</a> <span class="probonoseo-sitemap-date">(' . get_the_date('Y/m/d', $post->ID) . ')</span></li>';
            }
            $output .= '</ul></div>';
        }
        $output .= '</div>';
        return $output;
    }
}

ProbonoSEO_Sitemap_Advanced::get_instance();