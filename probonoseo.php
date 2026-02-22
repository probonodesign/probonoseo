<?php
/*
Plugin Name: ProbonoSEO Pro
Plugin URI: https://seo.prbn.org
Description: 日本語サイト向けに最適化された Made in Japan の SEO プラグイン（Pro版）
Version: 1.5.2
Author: ProbonoDesign
Author URI: https://prbn.org
License: GPLv2 or later
Text Domain: probonoseo-pro
*/

if (!defined('ABSPATH')) {
	exit;
}

define('PROBONOSEO_PATH', plugin_dir_path(__FILE__));
define('PROBONOSEO_URL', plugin_dir_url(__FILE__));
define('PROBONOSEO_VERSION', '1.5.2');

function probonoseo_admin_assets($hook) {
	if ($hook !== 'toplevel_page_probonoseo') {
		return;
	}
	wp_enqueue_style(
		'probonoseo-admin-style',
		PROBONOSEO_URL . 'admin/admin-style.css',
		array(),
		PROBONOSEO_VERSION
	);
	wp_enqueue_style(
		'probonoseo-switch',
		PROBONOSEO_URL . 'admin/switch.css',
		array('probonoseo-admin-style'),
		PROBONOSEO_VERSION
	);
	wp_enqueue_style(
		'probonoseo-tabs',
		PROBONOSEO_URL . 'admin/tabs.css',
		array('probonoseo-admin-style'),
		PROBONOSEO_VERSION
	);
	wp_enqueue_script(
		'probonoseo-admin-switch',
		PROBONOSEO_URL . 'admin/admin-switch.js',
		array(),
		PROBONOSEO_VERSION,
		true
	);
}
add_action('admin_enqueue_scripts', 'probonoseo_admin_assets');

function probonoseo_gutenberg_sidebar_assets() {
	if (!class_exists('ProbonoSEO_License') || !class_exists('ProbonoSEO_OpenAI_API')) {
		return;
	}
	
	$license = ProbonoSEO_License::get_instance();
	if (!$license->is_pro_active()) {
		return;
	}
	
	$openai = ProbonoSEO_OpenAI_API::get_instance();
	if (!$openai->is_api_key_set()) {
		return;
	}
	
	wp_enqueue_script(
		'probonoseo-gutenberg-sidebar',
		PROBONOSEO_URL . 'admin/gutenberg-sidebar.js',
		array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'jquery'),
		PROBONOSEO_VERSION,
		true
	);
	
	wp_localize_script('probonoseo-gutenberg-sidebar', 'probonoseoGutenberg', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('probonoseo_ai_nonce')
	));
}
add_action('enqueue_block_editor_assets', 'probonoseo_gutenberg_sidebar_assets');

function probonoseo_register_menu() {
	add_menu_page(
		'ProbonoSEO',
		'ProbonoSEO',
		'manage_options',
		'probonoseo',
		'probonoseo_render_admin_page',
		'dashicons-chart-area',
		56
	);
}
add_action('admin_menu', 'probonoseo_register_menu');

function probonoseo_render_admin_page() {
	require_once PROBONOSEO_PATH . 'admin/admin-page.php';
}

require_once PROBONOSEO_PATH . 'admin/pro-lock.php';
require_once PROBONOSEO_PATH . 'admin/license.php';

if (file_exists(PROBONOSEO_PATH . 'pro/license.php')) {
	require_once PROBONOSEO_PATH . 'pro/license.php';
}

if (file_exists(PROBONOSEO_PATH . 'pro/openai-api.php')) {
	require_once PROBONOSEO_PATH . 'pro/openai-api.php';
}

require_once PROBONOSEO_PATH . 'seo/title.php';
require_once PROBONOSEO_PATH . 'seo/meta.php';
require_once PROBONOSEO_PATH . 'seo/meta-defaults.php';
require_once PROBONOSEO_PATH . 'seo/canonical.php';

require_once PROBONOSEO_PATH . 'seo/ogp.php';
require_once PROBONOSEO_PATH . 'seo/ogp-meta.php';
require_once PROBONOSEO_PATH . 'seo/open-graph.php';
require_once PROBONOSEO_PATH . 'seo/twitter-card.php';

require_once PROBONOSEO_PATH . 'seo/schema.php';
require_once PROBONOSEO_PATH . 'seo/breadcrumb.php';
require_once PROBONOSEO_PATH . 'seo/breadcrumb-core.php';
require_once PROBONOSEO_PATH . 'seo/breadcrumb-data.php';
require_once PROBONOSEO_PATH . 'seo/breadcrumb-schema.php';
require_once PROBONOSEO_PATH . 'seo/breadcrumb-schema-cleaner.php';

require_once PROBONOSEO_PATH . 'seo/internal-links.php';
require_once PROBONOSEO_PATH . 'seo/speed.php';
require_once PROBONOSEO_PATH . 'seo/article-seo.php';
require_once PROBONOSEO_PATH . 'seo/diagnosis.php';

require_once PROBONOSEO_PATH . 'seo/prepublish-safety.php';
require_once PROBONOSEO_PATH . 'seo/seo-core.php';
require_once PROBONOSEO_PATH . 'seo/seo-utils.php';

require_once PROBONOSEO_PATH . 'seo/ai-title.php';
require_once PROBONOSEO_PATH . 'seo/ai-metadesc.php';
require_once PROBONOSEO_PATH . 'seo/ai-heading.php';
require_once PROBONOSEO_PATH . 'seo/ai-outline.php';
require_once PROBONOSEO_PATH . 'seo/ai-body.php';
require_once PROBONOSEO_PATH . 'seo/ai-summary.php';
require_once PROBONOSEO_PATH . 'seo/ai-faq.php';
require_once PROBONOSEO_PATH . 'admin/metabox-ai.php';
require_once PROBONOSEO_PATH . 'seo/ai-keywords.php';
require_once PROBONOSEO_PATH . 'seo/ai-rewrite.php';
require_once PROBONOSEO_PATH . 'seo/ai-readability.php';
require_once PROBONOSEO_PATH . 'seo/ai-sentiment.php';
require_once PROBONOSEO_PATH . 'seo/ai-duplicate.php';
require_once PROBONOSEO_PATH . 'seo/ai-target.php';
require_once PROBONOSEO_PATH . 'seo/ai-intent.php';
require_once PROBONOSEO_PATH . 'seo/ai-gap.php';
require_once PROBONOSEO_PATH . 'seo/ai-caption.php';
require_once PROBONOSEO_PATH . 'seo/ai-internal-link.php';
require_once PROBONOSEO_PATH . 'seo/ai-external-link.php';
require_once PROBONOSEO_PATH . 'seo/ai-update.php';
require_once PROBONOSEO_PATH . 'seo/ai-performance.php';

require_once PROBONOSEO_PATH . 'seo/competitor-core.php';
require_once PROBONOSEO_PATH . 'seo/competitor-analyzer.php';
require_once PROBONOSEO_PATH . 'seo/competitor-score.php';
require_once PROBONOSEO_PATH . 'seo/competitor-report.php';

require_once PROBONOSEO_PATH . 'seo/pro-cpt.php';
require_once PROBONOSEO_PATH . 'seo/pro-taxonomy.php';
require_once PROBONOSEO_PATH . 'seo/pro-author.php';
require_once PROBONOSEO_PATH . 'seo/pro-date.php';
require_once PROBONOSEO_PATH . 'seo/pro-404.php';
require_once PROBONOSEO_PATH . 'seo/pro-search.php';
require_once PROBONOSEO_PATH . 'seo/pro-attachment.php';
require_once PROBONOSEO_PATH . 'seo/pro-amp.php';
require_once PROBONOSEO_PATH . 'seo/pro-pwa.php';
require_once PROBONOSEO_PATH . 'seo/pro-multisite.php';
require_once PROBONOSEO_PATH . 'seo/pro-rest-api.php';
require_once PROBONOSEO_PATH . 'seo/pro-cli.php';
require_once PROBONOSEO_PATH . 'seo/pro-gsc.php';

require_once PROBONOSEO_PATH . 'seo/post-seo-checker.php';
require_once PROBONOSEO_PATH . 'seo/post-seo-score.php';
require_once PROBONOSEO_PATH . 'admin/metabox-seo.php';

require_once PROBONOSEO_PATH . 'seo/serp-faq.php';
require_once PROBONOSEO_PATH . 'seo/serp-howto.php';
require_once PROBONOSEO_PATH . 'seo/serp-review.php';
require_once PROBONOSEO_PATH . 'seo/serp-recipe.php';
require_once PROBONOSEO_PATH . 'seo/serp-event.php';
require_once PROBONOSEO_PATH . 'seo/serp-product.php';
require_once PROBONOSEO_PATH . 'seo/serp-video.php';
require_once PROBONOSEO_PATH . 'seo/serp-job.php';
require_once PROBONOSEO_PATH . 'seo/serp-sitelinks.php';
require_once PROBONOSEO_PATH . 'seo/serp-searchbox.php';
require_once PROBONOSEO_PATH . 'seo/serp-knowledge.php';
require_once PROBONOSEO_PATH . 'seo/serp-carousel.php';
require_once PROBONOSEO_PATH . 'seo/serp-featured.php';
require_once PROBONOSEO_PATH . 'seo/serp-local.php';
require_once PROBONOSEO_PATH . 'seo/sitemap-advanced.php';

require_once PROBONOSEO_PATH . 'seo/schema-software.php';
require_once PROBONOSEO_PATH . 'seo/schema-course.php';
require_once PROBONOSEO_PATH . 'seo/schema-book.php';
require_once PROBONOSEO_PATH . 'seo/schema-movie.php';
require_once PROBONOSEO_PATH . 'seo/schema-music.php';
require_once PROBONOSEO_PATH . 'seo/schema-podcast.php';
require_once PROBONOSEO_PATH . 'seo/schema-organization.php';
require_once PROBONOSEO_PATH . 'seo/schema-person.php';
require_once PROBONOSEO_PATH . 'seo/schema-article.php';
require_once PROBONOSEO_PATH . 'seo/schema-news.php';
require_once PROBONOSEO_PATH . 'seo/schema-blog.php';
require_once PROBONOSEO_PATH . 'seo/schema-announcement.php';
require_once PROBONOSEO_PATH . 'seo/schema-image.php';
require_once PROBONOSEO_PATH . 'seo/schema-dataset.php';
require_once PROBONOSEO_PATH . 'seo/schema-rating.php';
require_once PROBONOSEO_PATH . 'seo/schema-claim.php';
require_once PROBONOSEO_PATH . 'seo/schema-speakable.php';
require_once PROBONOSEO_PATH . 'seo/schema-auto-select.php';

require_once PROBONOSEO_PATH . 'seo/speed-webp.php';
require_once PROBONOSEO_PATH . 'seo/speed-avif.php';
require_once PROBONOSEO_PATH . 'seo/speed-compress.php';
require_once PROBONOSEO_PATH . 'seo/speed-responsive.php';
require_once PROBONOSEO_PATH . 'seo/speed-css-inline.php';
require_once PROBONOSEO_PATH . 'seo/speed-js-inline.php';
require_once PROBONOSEO_PATH . 'seo/speed-fonts.php';
require_once PROBONOSEO_PATH . 'seo/speed-dns.php';
require_once PROBONOSEO_PATH . 'seo/speed-hints.php';
require_once PROBONOSEO_PATH . 'seo/speed-db.php';
require_once PROBONOSEO_PATH . 'seo/speed-object-cache.php';
require_once PROBONOSEO_PATH . 'seo/speed-page-cache.php';

if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-index-status.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-index-status.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-crawl-errors.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-crawl-errors.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-mobile.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-mobile.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-web-vitals.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-web-vitals.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-security.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-security.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-ssl.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-ssl.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-sitemap.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-sitemap.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-robots.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-robots.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-htaccess.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-htaccess.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-performance.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-performance.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-total-score.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-total-score.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/diagnosis-pdf-report.php')) {
	require_once PROBONOSEO_PATH . 'seo/diagnosis-pdf-report.php';
}
if (file_exists(PROBONOSEO_PATH . 'seo/manage-advanced.php')) {
	require_once PROBONOSEO_PATH . 'seo/manage-advanced.php';
}

add_action('wp_ajax_probonoseo_diagnosis', 'probonoseo_handle_diagnosis_ajax');

function probonoseo_handle_diagnosis_ajax() {
	check_ajax_referer('probonoseo_diagnosis_ajax', 'probonoseo_diagnosis_nonce');
	
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '権限がありません'));
		return;
	}
	
	$keys = array(
		'probonoseo_diagnosis_title_duplicate',
		'probonoseo_diagnosis_meta_duplicate',
		'probonoseo_diagnosis_speed'
	);
	
	foreach ($keys as $k) {
		if (isset($_POST[$k])) {
			update_option($k, $_POST[$k] === '1' ? '1' : '0');
		}
	}
	
	ProbonoSEO_Diagnosis::run_diagnosis();
	
	ob_start();
	ProbonoSEO_Diagnosis::display_results();
	$results_html = ob_get_clean();
	
	wp_send_json_success(array('html' => $results_html));
}

add_action('wp_ajax_probonoseo_diagnosis_pro_ajax', 'probonoseo_handle_diagnosis_pro_ajax');

function probonoseo_handle_diagnosis_pro_ajax() {
	check_ajax_referer('probonoseo_diagnosis_pro_ajax', 'probonoseo_diagnosis_pro_nonce');
	
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '権限がありません'));
		return;
	}
	
	$all_results = array();
	
	$diagnosis_classes = array(
		'index' => 'ProbonoSEO_Diagnosis_Index_Status',
		'crawl' => 'ProbonoSEO_Diagnosis_Crawl_Errors',
		'mobile' => 'ProbonoSEO_Diagnosis_Mobile',
		'vitals' => 'ProbonoSEO_Diagnosis_Web_Vitals',
		'security' => 'ProbonoSEO_Diagnosis_Security',
		'ssl' => 'ProbonoSEO_Diagnosis_SSL',
		'sitemap' => 'ProbonoSEO_Diagnosis_Sitemap',
		'robots' => 'ProbonoSEO_Diagnosis_Robots',
		'htaccess' => 'ProbonoSEO_Diagnosis_Htaccess',
		'performance' => 'ProbonoSEO_Diagnosis_Performance',
	);
	
	foreach ($diagnosis_classes as $key => $class) {
		if (class_exists($class)) {
			$instance = $class::get_instance();
			if ($instance->is_enabled()) {
				$all_results[$key] = $instance->run_diagnosis();
			}
		}
	}
	
	if (class_exists('ProbonoSEO_Diagnosis_Total_Score')) {
		$total = ProbonoSEO_Diagnosis_Total_Score::get_instance();
		if ($total->is_enabled()) {
			$all_results['total_score'] = $total->run_diagnosis($all_results);
		}
	}
	
	set_transient('probonoseo_diagnosis_pro_results', $all_results, 3600);
	
	update_option('probonoseo_diagnosis_count', get_option('probonoseo_diagnosis_count', 0) + 1);
	update_option('probonoseo_last_diagnosis_date', current_time('mysql'));
	
	$html = probonoseo_render_diagnosis_pro_results($all_results);
	
	wp_send_json_success(array('html' => $html));
}

function probonoseo_render_diagnosis_pro_results($all_results) {
	if (empty($all_results)) {
		return '<div class="probonoseo-diagnosis-pro-empty"><span class="dashicons dashicons-info"></span><p>有効な診断項目がありません。診断項目を有効化してください。</p></div>';
	}
	
	$html = '';
	
	if (isset($all_results['total_score']) && isset($all_results['total_score']['total_score'])) {
		$score = $all_results['total_score']['total_score'];
		$html .= '<div class="probonoseo-diagnosis-pro-score-box">';
		$html .= '<div class="probonoseo-diagnosis-pro-score-number">' . esc_html($score) . '</div>';
		$html .= '<div class="probonoseo-diagnosis-pro-score-label">総合SEOスコア / 100点</div>';
		$html .= '</div>';
	}
	
	foreach ($all_results as $key => $result) {
		if (!isset($result['title']) || !isset($result['items'])) {
			continue;
		}
		
		$icon = isset($result['icon']) ? $result['icon'] : 'dashicons-info';
		
		$html .= '<div class="probonoseo-diagnosis-pro-section">';
		$html .= '<h4 class="probonoseo-diagnosis-pro-section-title"><span class="dashicons ' . esc_attr($icon) . '"></span>' . esc_html($result['title']) . '</h4>';
		
		foreach ($result['items'] as $item) {
			$type = isset($item['type']) ? $item['type'] : 'info';
			$html .= '<div class="probonoseo-diagnosis-pro-item item-' . esc_attr($type) . '">' . esc_html($item['message']) . '</div>';
		}
		
		$html .= '</div>';
	}
	
	return $html;
}

add_action('wp_ajax_probonoseo_export_settings', 'probonoseo_handle_export_settings_ajax');

function probonoseo_handle_export_settings_ajax() {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '権限がありません。'));
	}
	
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'probonoseo_export_settings')) {
		wp_send_json_error(array('message' => '不正なリクエストです。'));
	}
	
	$free_keys = array(
		'probonoseo_basic_title',
		'probonoseo_title_separator',
		'probonoseo_title_sitename',
		'probonoseo_title_h1_check',
		'probonoseo_title_category',
		'probonoseo_title_duplicate',
		'probonoseo_title_symbols',
		'probonoseo_basic_metadesc',
		'probonoseo_meta_extraction',
		'probonoseo_meta_keywords',
		'probonoseo_meta_summary',
		'probonoseo_meta_forbidden',
		'probonoseo_meta_length',
		'probonoseo_meta_duplicate',
		'probonoseo_basic_canonical',
		'probonoseo_canonical_auto',
		'probonoseo_canonical_slash',
		'probonoseo_canonical_merge',
		'probonoseo_canonical_params',
		'probonoseo_basic_ogp',
		'probonoseo_ogp_title',
		'probonoseo_ogp_desc',
		'probonoseo_ogp_image_auto',
		'probonoseo_ogp_image_fixed',
		'probonoseo_ogp_facebook',
		'probonoseo_ogp_line',
		'probonoseo_ogp_thumbnail',
		'probonoseo_ogp_size_detect',
		'probonoseo_ogp_alt',
		'probonoseo_ogp_japanese_url',
		'probonoseo_basic_twitter',
		'probonoseo_basic_schema',
		'probonoseo_basic_breadcrumb',
		'probonoseo_internal_prev_next',
		'probonoseo_internal_category',
		'probonoseo_internal_child_pages',
		'probonoseo_internal_related',
		'probonoseo_internal_tag_logic',
		'probonoseo_internal_nofollow',
		'probonoseo_internal_category_format',
		'probonoseo_speed_lazy_images',
		'probonoseo_speed_lazy_iframes',
		'probonoseo_speed_minify_css',
		'probonoseo_speed_minify_js',
		'probonoseo_speed_optimize_wp_scripts',
		'probonoseo_article_heading_check',
		'probonoseo_article_alt_check',
		'probonoseo_article_image_count',
		'probonoseo_article_word_count',
		'probonoseo_article_category_match',
		'probonoseo_article_tag_duplicate',
		'probonoseo_diagnosis_title_duplicate',
		'probonoseo_diagnosis_meta_duplicate',
		'probonoseo_diagnosis_speed',
		'probonoseo_meta_cleanup',
		'probonoseo_gsc_verify',
	);
	
	$pro_keys = array(
		'probonoseo_pro_title_ai',
		'probonoseo_pro_heading_ai',
		'probonoseo_pro_outline_ai',
		'probonoseo_pro_body_ai',
		'probonoseo_pro_summary_ai',
		'probonoseo_pro_faq_ai',
		'probonoseo_pro_metadesc_ai',
		'probonoseo_pro_keywords_ai',
		'probonoseo_pro_rewrite_ai',
		'probonoseo_pro_readability_ai',
		'probonoseo_pro_sentiment_ai',
		'probonoseo_pro_duplicate_ai',
		'probonoseo_pro_target_ai',
		'probonoseo_pro_intent_ai',
		'probonoseo_pro_gap_ai',
		'probonoseo_pro_caption_ai',
		'probonoseo_pro_internal_link_ai',
		'probonoseo_pro_external_link_ai',
		'probonoseo_pro_update_ai',
		'probonoseo_pro_performance_ai',
		'probonoseo_pro_morphological_analysis',
		'probonoseo_competitor_enabled',
		'probonoseo_competitor_title',
		'probonoseo_competitor_meta',
		'probonoseo_competitor_heading',
		'probonoseo_competitor_wordcount',
		'probonoseo_competitor_images',
		'probonoseo_competitor_internal',
		'probonoseo_competitor_external',
		'probonoseo_competitor_schema',
		'probonoseo_competitor_keywords',
		'probonoseo_competitor_score',
		'probonoseo_competitor_report',
		'probonoseo_post_seo_metabox',
		'probonoseo_post_seo_score',
		'probonoseo_post_seo_title_preview',
		'probonoseo_post_seo_meta_preview',
		'probonoseo_post_seo_serp_preview',
		'probonoseo_post_seo_social_preview',
		'probonoseo_post_seo_focus_keyword',
		'probonoseo_post_seo_keyword_density',
		'probonoseo_post_seo_internal_links',
		'probonoseo_post_seo_external_links',
		'probonoseo_post_seo_image_alt',
		'probonoseo_post_seo_heading_structure',
		'probonoseo_post_seo_word_count',
		'probonoseo_post_seo_read_time',
		'probonoseo_post_seo_content_score',
		'probonoseo_post_seo_suggestions',
		'probonoseo_post_seo_checklist',
		'probonoseo_post_seo_publish_warning',
		'probonoseo_post_seo_type_post',
		'probonoseo_post_seo_type_page',
		'probonoseo_post_seo_type_custom',
		'probonoseo_pro_cpt',
		'probonoseo_pro_taxonomy',
		'probonoseo_pro_author',
		'probonoseo_pro_date',
		'probonoseo_pro_404',
		'probonoseo_pro_search',
		'probonoseo_pro_attachment',
		'probonoseo_robots_txt',
		'probonoseo_pro_amp',
		'probonoseo_pro_pwa',
		'probonoseo_pro_multisite',
		'probonoseo_pro_rest_api',
		'probonoseo_pro_cli',
		'probonoseo_pro_gsc',
		'probonoseo_pro_breadcrumb_customize',
		'probonoseo_pro_breadcrumb_exclude',
		'probonoseo_pro_schema_validator',
		'probonoseo_pro_rich_results_test',
		'probonoseo_pro_sitemap_exclude',
		'probonoseo_pro_sitemap_html',
		'probonoseo_pro_sitemap_image',
		'probonoseo_pro_sitemap_video',
		'probonoseo_pro_sitemap_news',
		'probonoseo_pro_sitemap_hreflang',
		'probonoseo_notify_email_enabled',
		'probonoseo_notify_slack_enabled',
		'probonoseo_debug_mode',
	);
	
	$all_keys = array_merge($free_keys, $pro_keys);
	$settings = array();
	
	foreach ($all_keys as $key) {
		$settings[$key] = get_option($key, '');
	}
	
	$settings['_export_date'] = current_time('mysql');
	$settings['_plugin_version'] = PROBONOSEO_VERSION;
	
	$filename = 'probonoseo-settings-' . date_i18n('Y-m-d-His') . '.json';
	
	wp_send_json_success(array(
		'filename' => $filename,
		'settings' => $settings,
	));
}

register_activation_hook(__FILE__, 'probonoseo_activation');

function probonoseo_activation() {
	delete_option('probonoseo_basic_title');
	delete_option('probonoseo_basic_metadesc');
	delete_option('probonoseo_basic_canonical');
	delete_option('probonoseo_basic_ogp');
	delete_option('probonoseo_basic_twitter');
	delete_option('probonoseo_basic_schema');
	delete_option('probonoseo_basic_breadcrumb');
	delete_option('probonoseo_settings_reset_v1');
}