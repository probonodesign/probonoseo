<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Pro_REST_API {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_pro_rest_api', '1') === '1';
	}

	public function register_routes() {
		if (!$this->is_enabled()) {
			return;
		}

		register_rest_route('probonoseo/v1', '/seo/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_post_seo'),
			'permission_callback' => array($this, 'check_permission'),
			'args' => array(
				'id' => array(
					'validate_callback' => function($param) {
						return is_numeric($param);
					}
				)
			)
		));

		register_rest_route('probonoseo/v1', '/seo/(?P<id>\d+)', array(
			'methods' => 'POST',
			'callback' => array($this, 'update_post_seo'),
			'permission_callback' => array($this, 'check_permission'),
			'args' => array(
				'id' => array(
					'validate_callback' => function($param) {
						return is_numeric($param);
					}
				)
			)
		));

		register_rest_route('probonoseo/v1', '/settings', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_settings'),
			'permission_callback' => array($this, 'check_admin_permission')
		));

		register_rest_route('probonoseo/v1', '/diagnosis', array(
			'methods' => 'GET',
			'callback' => array($this, 'run_diagnosis'),
			'permission_callback' => array($this, 'check_admin_permission')
		));
	}

	public function check_permission() {
		return current_user_can('edit_posts');
	}

	public function check_admin_permission() {
		return current_user_can('manage_options');
	}

	public function get_post_seo($request) {
		$post_id = $request['id'];
		$post = get_post($post_id);

		if (!$post) {
			return new WP_Error('not_found', '投稿が見つかりません', array('status' => 404));
		}

		$seo_data = array(
			'post_id' => $post_id,
			'title' => get_the_title($post_id),
			'seo_title' => get_post_meta($post_id, '_probonoseo_seo_title', true),
			'meta_description' => get_post_meta($post_id, '_probonoseo_meta_description', true),
			'focus_keyword' => get_post_meta($post_id, '_probonoseo_focus_keyword', true),
			'canonical' => get_post_meta($post_id, '_probonoseo_canonical', true),
			'robots' => get_post_meta($post_id, '_probonoseo_robots', true),
			'og_title' => get_post_meta($post_id, '_probonoseo_og_title', true),
			'og_description' => get_post_meta($post_id, '_probonoseo_og_description', true),
			'og_image' => get_post_meta($post_id, '_probonoseo_og_image', true)
		);

		return rest_ensure_response($seo_data);
	}

	public function update_post_seo($request) {
		$post_id = $request['id'];
		$post = get_post($post_id);

		if (!$post) {
			return new WP_Error('not_found', '投稿が見つかりません', array('status' => 404));
		}

		if (!current_user_can('edit_post', $post_id)) {
			return new WP_Error('forbidden', '権限がありません', array('status' => 403));
		}

		$params = $request->get_json_params();
		$updated = array();

		$fields = array(
			'seo_title' => '_probonoseo_seo_title',
			'meta_description' => '_probonoseo_meta_description',
			'focus_keyword' => '_probonoseo_focus_keyword',
			'canonical' => '_probonoseo_canonical',
			'robots' => '_probonoseo_robots',
			'og_title' => '_probonoseo_og_title',
			'og_description' => '_probonoseo_og_description',
			'og_image' => '_probonoseo_og_image'
		);

		foreach ($fields as $param => $meta_key) {
			if (isset($params[$param])) {
				update_post_meta($post_id, $meta_key, sanitize_text_field($params[$param]));
				$updated[$param] = $params[$param];
			}
		}

		return rest_ensure_response(array(
			'success' => true,
			'updated' => $updated
		));
	}

	public function get_settings() {
		$settings = array(
			'basic_title' => get_option('probonoseo_basic_title', '1'),
			'basic_metadesc' => get_option('probonoseo_basic_metadesc', '1'),
			'basic_canonical' => get_option('probonoseo_basic_canonical', '1'),
			'basic_ogp' => get_option('probonoseo_basic_ogp', '1'),
			'basic_twitter' => get_option('probonoseo_basic_twitter', '1'),
			'basic_schema' => get_option('probonoseo_basic_schema', '1'),
			'basic_breadcrumb' => get_option('probonoseo_basic_breadcrumb', '1')
		);

		return rest_ensure_response($settings);
	}

	public function run_diagnosis() {
		if (class_exists('ProbonoSEO_Diagnosis')) {
			ProbonoSEO_Diagnosis::run_diagnosis();
			$results = ProbonoSEO_Diagnosis::get_results();

			return rest_ensure_response(array(
				'success' => true,
				'results' => $results
			));
		}

		return new WP_Error('not_available', '診断機能が利用できません', array('status' => 500));
	}
}

ProbonoSEO_Pro_REST_API::get_instance();