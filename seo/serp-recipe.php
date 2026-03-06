<?php
if (!defined('ABSPATH')) {
    exit;
}

class ProbonoSEO_SERP_Recipe {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_recipe_schema'), 25);
        add_action('add_meta_boxes', array($this, 'add_recipe_metabox'));
        add_action('save_post', array($this, 'save_recipe_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_probonoseo_save_recipe', array($this, 'ajax_save_recipe'));
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
        return get_option('probonoseo_serp_recipe', '0') === '1';
    }

    public function output_recipe_schema() {
        if (!is_singular() || !$this->is_enabled()) {
            return;
        }
        $post_id = get_the_ID();
        $recipe = get_post_meta($post_id, '_probonoseo_recipe', true);
        if (empty($recipe) || empty($recipe['name'])) {
            return;
        }
        $schema = $this->build_schema($recipe, $post_id);
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private function build_schema($recipe, $post_id) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => $recipe['name'],
            'author' => array('@type' => 'Person', 'name' => !empty($recipe['author']) ? $recipe['author'] : get_the_author()),
            'datePublished' => get_the_date('c', $post_id)
        );
        if (!empty($recipe['description'])) {
            $schema['description'] = $recipe['description'];
        }
        if (!empty($recipe['image'])) {
            $schema['image'] = $recipe['image'];
        } elseif (has_post_thumbnail($post_id)) {
            $schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
        }
        if (!empty($recipe['prep_time'])) {
            $schema['prepTime'] = 'PT' . intval($recipe['prep_time']) . 'M';
        }
        if (!empty($recipe['cook_time'])) {
            $schema['cookTime'] = 'PT' . intval($recipe['cook_time']) . 'M';
        }
        if (!empty($recipe['yield'])) {
            $schema['recipeYield'] = $recipe['yield'];
        }
        if (!empty($recipe['ingredients'])) {
            $schema['recipeIngredient'] = array_filter(array_map('trim', explode("\n", $recipe['ingredients'])));
        }
        if (!empty($recipe['instructions'])) {
            $steps = array();
            foreach (array_filter(array_map('trim', explode("\n", $recipe['instructions']))) as $probonoseo_i => $instruction) {
                $steps[] = array('@type' => 'HowToStep', 'position' => $probonoseo_i + 1, 'text' => $instruction);
            }
            $schema['recipeInstructions'] = $steps;
        }
        if (get_option('probonoseo_serp_recipe_nutrition', '1') === '1' && !empty($recipe['calories'])) {
            $schema['nutrition'] = array('@type' => 'NutritionInformation', 'calories' => $recipe['calories'] . ' calories');
        }
        return $schema;
    }

    public function add_recipe_metabox() {
        if (!$this->is_enabled()) {
            return;
        }
        foreach (array('post', 'page') as $post_type) {
            add_meta_box('probonoseo_recipe_metabox', 'Recipe schema（レシピ・料理）', array($this, 'render_recipe_metabox'), $post_type, 'normal', 'default');
        }
    }

    public function render_recipe_metabox($post) {
        wp_nonce_field('probonoseo_recipe_save', 'probonoseo_recipe_nonce');
        $recipe = get_post_meta($post->ID, '_probonoseo_recipe', true);
        if (!is_array($recipe)) {
            $recipe = array('name' => '', 'description' => '', 'image' => '', 'author' => '', 'prep_time' => '', 'cook_time' => '', 'yield' => '', 'ingredients' => '', 'instructions' => '', 'calories' => '');
        }
        echo '<div class="probonoseo-schema-container">';
        echo '<div class="probonoseo-schema-grid">';
        echo '<div class="probonoseo-schema-row"><label>レシピ名</label><input type="text" name="probonoseo_recipe[name]" value="' . esc_attr($recipe['name']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>著者</label><input type="text" name="probonoseo_recipe[author]" value="' . esc_attr($recipe['author']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>準備時間（分）</label><input type="number" name="probonoseo_recipe[prep_time]" value="' . esc_attr($recipe['prep_time']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>調理時間（分）</label><input type="number" name="probonoseo_recipe[cook_time]" value="' . esc_attr($recipe['cook_time']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>分量</label><input type="text" name="probonoseo_recipe[yield]" value="' . esc_attr($recipe['yield']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>カロリー</label><input type="number" name="probonoseo_recipe[calories]" value="' . esc_attr($recipe['calories']) . '"></div>';
        echo '<div class="probonoseo-schema-row"><label>画像URL</label><input type="url" name="probonoseo_recipe[image]" value="' . esc_attr($recipe['image']) . '"></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-grid probonoseo-schema-grid-full">';
        echo '<div class="probonoseo-schema-row"><label>説明</label><textarea name="probonoseo_recipe[description]" rows="2">' . esc_textarea($recipe['description']) . '</textarea></div>';
        echo '<div class="probonoseo-schema-row"><label>材料（1行に1つ）</label><textarea name="probonoseo_recipe[ingredients]" rows="5">' . esc_textarea($recipe['ingredients']) . '</textarea></div>';
        echo '<div class="probonoseo-schema-row"><label>作り方（1行に1ステップ）</label><textarea name="probonoseo_recipe[instructions]" rows="5">' . esc_textarea($recipe['instructions']) . '</textarea></div>';
        echo '</div>';
        echo '<div class="probonoseo-schema-save-row">';
        echo '<button type="button" class="button button-primary probonoseo-schema-save-btn" data-post-id="' . esc_attr($post->ID) . '" data-action="probonoseo_save_recipe" data-nonce="' . esc_attr(wp_create_nonce('probonoseo_save_recipe')) . '">保存</button>';
        echo '<span class="probonoseo-schema-save-msg"></span>';
        echo '</div>';
        echo '</div>';
    }

    public function ajax_save_recipe() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_save_recipe')) {
            wp_send_json_error(array('message' => '認証エラー'));
            return;
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => '権限がありません'));
            return;
        }
        if (isset($_POST['probonoseo_recipe']) && is_array($_POST['probonoseo_recipe'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_recipe']);
            $fields = array('name', 'description', 'image', 'author', 'prep_time', 'cook_time', 'yield', 'ingredients', 'instructions', 'calories');
            $recipe = array();
            foreach ($fields as $field) {
                if (!isset($probonoseo_raw[$field])) {
                    $recipe[$field] = '';
                    continue;
                }
                if ($field === 'image') {
                    $recipe[$field] = esc_url_raw($probonoseo_raw[$field]);
                } elseif (in_array($field, array('description', 'ingredients', 'instructions'), true)) {
                    $recipe[$field] = sanitize_textarea_field($probonoseo_raw[$field]);
                } else {
                    $recipe[$field] = sanitize_text_field($probonoseo_raw[$field]);
                }
            }
            update_post_meta($post_id, '_probonoseo_recipe', $recipe);
        }
        wp_send_json_success(array('message' => '保存しました'));
    }

    public function save_recipe_data($post_id) {
        if (!isset($_POST['probonoseo_recipe_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_recipe_nonce'])), 'probonoseo_recipe_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['probonoseo_recipe']) && is_array($_POST['probonoseo_recipe'])) {
            $probonoseo_raw = wp_unslash($_POST['probonoseo_recipe']);
            $fields = array('name', 'description', 'image', 'author', 'prep_time', 'cook_time', 'yield', 'ingredients', 'instructions', 'calories');
            $recipe = array();
            foreach ($fields as $field) {
                if (!isset($probonoseo_raw[$field])) {
                    $recipe[$field] = '';
                    continue;
                }
                if ($field === 'image') {
                    $recipe[$field] = esc_url_raw($probonoseo_raw[$field]);
                } elseif (in_array($field, array('description', 'ingredients', 'instructions'), true)) {
                    $recipe[$field] = sanitize_textarea_field($probonoseo_raw[$field]);
                } else {
                    $recipe[$field] = sanitize_text_field($probonoseo_raw[$field]);
                }
            }
            update_post_meta($post_id, '_probonoseo_recipe', $recipe);
        }
    }
}

ProbonoSEO_SERP_Recipe::get_instance();