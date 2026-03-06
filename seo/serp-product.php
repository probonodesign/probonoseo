<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Product {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_product_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_product_metabox'));
        add_action('save_post', array($this, 'save_product_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_probonoseo_save_product', array($this, 'ajax_save_product'));
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        wp_enqueue_style('probonoseo-serp-metabox', plugin_dir_url(dirname(__FILE__)) . 'admin/serp-metabox.css', array(), PROBONOSEO_VERSION);
        wp_enqueue_script('probonoseo-serp-metabox', plugin_dir_url(dirname(__FILE__)) . 'admin/serp-metabox.js', array('jquery'), PROBONOSEO_VERSION, true);
    }

    private function is_enabled() {
        if (!class_exists('ProbonoSEO_License')) {
            return false;
        }
        $license = ProbonoSEO_License::get_instance();
        if (!$license->is_pro_active()) {
            return false;
        }
        return get_option('probonoseo_serp_product', '0') === '1';
    }

    public function output_product_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $post_id = get_the_ID();
        $product = get_post_meta($post_id, '_probonoseo_product', true);
        if (empty($product) || empty($product['name'])) {
            return;
        }
        $schema = $this->build_schema($product, $post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($product, $post_id) {
        $currency = get_option('probonoseo_serp_product_currency', 'JPY');
        $availability = get_option('probonoseo_serp_product_availability', 'InStock');
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'],
            'description' => !empty($product['description']) ? $product['description'] : get_the_excerpt($post_id)
        );
        if (!empty($product['image'])) {
            $schema['image'] = $product['image'];
        } elseif (has_post_thumbnail($post_id)) {
            $schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
        }
        if (!empty($product['brand'])) {
            $schema['brand'] = array('@type' => 'Brand', 'name' => $product['brand']);
        }
        if (!empty($product['sku'])) {
            $schema['sku'] = $product['sku'];
        }
        if (!empty($product['price'])) {
            $schema['offers'] = array(
                '@type' => 'Offer',
                'price' => $product['price'],
                'priceCurrency' => $currency,
                'availability' => 'https://schema.org/' . (!empty($product['availability']) ? $product['availability'] : $availability),
                'url' => get_permalink($post_id)
            );
        }
        if (!empty($product['rating'])) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => floatval($product['rating']),
                'reviewCount' => !empty($product['review_count']) ? intval($product['review_count']) : 1,
                'bestRating' => 5,
                'worstRating' => 1
            );
        }
        return $schema;
    }

    public function add_product_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        foreach (array('post', 'page') as $post_type) {
            add_meta_box('probonoseo_product_metabox', 'Product schema（商品・製品情報）', array($this, 'render_product_metabox'), $post_type, 'normal', 'default');
        }
    }

    public function render_product_metabox($post) {
        wp_nonce_field('probonoseo_product_save', 'probonoseo_product_nonce');
        $product = get_post_meta($post->ID, '_probonoseo_product', true);
        if (!is_array($product)) {
            $product = array('name' => '', 'description' => '', 'image' => '', 'brand' => '', 'sku' => '', 'price' => '', 'availability' => '', 'rating' => '', 'review_count' => '');
        }
        echo '<div class="probonoseo-schema-container">';
        echo '<div class="probonoseo-schema-grid">';
        echo '<div class="probonoseo-schema-row"><label>商品名</label><input type="text" name="probonoseo_product[name]" value="' . esc_attr($product['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>ブランド</label><input type="text" name="probonoseo_product[brand]" value="' . esc_attr($product['brand']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>SKU</label><input type="text" name="probonoseo_product[sku]" value="' . esc_attr($product['sku']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>価格</label><input type="number" name="probonoseo_product[price]" value="' . esc_attr($product['price']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>在庫状況</label><select name="probonoseo_product[availability]"><option value="">デフォルト</option><option value="InStock"' . selected($product['availability'], 'InStock', false) . '>在庫あり</option><option value="OutOfStock"' . selected($product['availability'], 'OutOfStock', false) . '>在庫切れ</option><option value="PreOrder"' . selected($product['availability'], 'PreOrder', false) . '>予約受付中</option></select></div>';
        echo '<div class="probonoseo-schema-row"><label>評価（1-5）</label><input type="number" name="probonoseo_product[rating]" value="' . esc_attr($product['rating']) . '" min="1" max="5" step="0.1"></div>';
        echo '<div class="probonoseo-schema-row"><label>レビュー数</label><input type="number" name="probonoseo_product[review_count]" value="' . esc_attr($product['review_count']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>画像URL</label><input type="url" name="probonoseo_product[image]" value="' . esc_attr($product['image']) . '"></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-grid probonoseo-schema-grid-full">';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_product[description]" rows="3">' . esc_textarea($product['description']) . '</textarea></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-save-row">';
        echo '<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="' . esc_attr($post->ID) . '" data-action="probonoseo_save_product" data-nonce="' . esc_attr(wp_create_nonce('probonoseo_save_product')) . '">保存</button>';
        echo '<span class="probonoseo-schema-save-msg"></span>';
        echo '</div>';
        echo '</div>';
    }

    public function ajax_save_product() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_product')) {
            wp_send_json_error(array('message' => '認証エラー'));
            return;
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => '権限がありません'));
            return;
        }
        if (isset($_POST['probonoseo_product']) && is_array($_POST['probonoseo_product'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_product']);
            $fields = array('name', 'description', 'image', 'brand', 'sku', 'price', 'availability', 'rating', 'review_count');
            $product = array();
            foreach ($fields as $field) {
                if (!isset($probonoseo_raw[$field])) {
                    $product[$field] = '';
                    continue;
                }
                if ($field === 'image') {
                    $product[$field] = esc_url_raw($probonoseo_raw[$field]);
                } elseif ($field === 'description') {
                    $product[$field] = sanitize_textarea_field($probonoseo_raw[$field]);
                } else {
                    $product[$field] = sanitize_text_field($probonoseo_raw[$field]);
                }
            }
            update_post_meta($post_id, '_probonoseo_product', $product);
        }
        wp_send_json_success(array('message' => '保存しました'));
    }

    public function save_product_data($post_id) {
        if (!isset($_POST['probonoseo_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_product_nonce'])), 'probonoseo_product_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_product']) && is_array($_POST['probonoseo_product'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_product']);
            $fields = array('name', 'description', 'image', 'brand', 'sku', 'price', 'availability', 'rating', 'review_count');
            $product = array();
            foreach ($fields as $field) {
                if (!isset($probonoseo_raw[$field])) {
                    $product[$field] = '';
                    continue;
                }
                if ($field === 'image') {
                    $product[$field] = esc_url_raw($probonoseo_raw[$field]);
                } elseif ($field === 'description') {
                    $product[$field] = sanitize_textarea_field($probonoseo_raw[$field]);
                } else {
                    $product[$field] = sanitize_text_field($probonoseo_raw[$field]);
                }
            }
            update_post_meta($post_id, '_probonoseo_product', $product);
        }
    }
}

ProbonoSEO_SERP_Product::get_instance();