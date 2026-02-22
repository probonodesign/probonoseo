<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_is_pro_active = $probonoseo_license->is_pro_active();

$probonoseo_manage_notice = '';
$probonoseo_manage_notice_type = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES cannot be sanitized directly
	if (isset($_POST['probonoseo_import_settings']) && isset($_FILES['probonoseo_import_file'])) {
		check_admin_referer('probonoseo_manage_action', 'probonoseo_manage_nonce');
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES cannot be sanitized directly
		$probonoseo_manage_notice = probonoseo_info_import_settings($_FILES['probonoseo_import_file']);
		$probonoseo_manage_notice_type = 'success';
	}
	
	if (isset($_POST['probonoseo_reset_free_settings'])) {
		check_admin_referer('probonoseo_manage_action', 'probonoseo_manage_nonce');
		probonoseo_info_reset_free_settings();
		$probonoseo_manage_notice = '無料版の設定をリセットしました。';
		$probonoseo_manage_notice_type = 'success';
	}
}

function probonoseo_info_get_free_switch_item_map() {
	return array(
		'probonoseo_basic_title' => 1,
		'probonoseo_title_separator' => 1,
		'probonoseo_title_sitename' => 1,
		'probonoseo_title_h1_check' => 1,
		'probonoseo_title_category' => 1,
		'probonoseo_title_duplicate' => 1,
		'probonoseo_title_symbols' => 1,
		'probonoseo_basic_metadesc' => 1,
		'probonoseo_meta_extraction' => 1,
		'probonoseo_meta_keywords' => 1,
		'probonoseo_meta_summary' => 1,
		'probonoseo_meta_forbidden' => 1,
		'probonoseo_meta_length' => 1,
		'probonoseo_meta_duplicate' => 1,
		'probonoseo_basic_canonical' => 1,
		'probonoseo_canonical_auto' => 1,
		'probonoseo_canonical_slash' => 1,
		'probonoseo_canonical_merge' => 1,
		'probonoseo_canonical_params' => 1,
		'probonoseo_basic_ogp' => 1,
		'probonoseo_ogp_title' => 1,
		'probonoseo_ogp_desc' => 1,
		'probonoseo_ogp_image_auto' => 1,
		'probonoseo_ogp_image_fixed' => 1,
		'probonoseo_ogp_facebook' => 1,
		'probonoseo_ogp_line' => 1,
		'probonoseo_ogp_thumbnail' => 1,
		'probonoseo_ogp_size_detect' => 1,
		'probonoseo_ogp_alt' => 1,
		'probonoseo_ogp_japanese_url' => 1,
		'probonoseo_basic_twitter' => 1,
		'probonoseo_basic_schema' => 8,
		'probonoseo_basic_breadcrumb' => 8,
		'probonoseo_internal_prev_next' => 1,
		'probonoseo_internal_category' => 1,
		'probonoseo_internal_child_pages' => 1,
		'probonoseo_internal_related' => 1,
		'probonoseo_internal_tag_logic' => 1,
		'probonoseo_internal_nofollow' => 1,
		'probonoseo_internal_category_format' => 1,
		'probonoseo_speed_lazy_images' => 1,
		'probonoseo_speed_lazy_iframes' => 1,
		'probonoseo_speed_minify_css' => 1,
		'probonoseo_speed_minify_js' => 1,
		'probonoseo_speed_optimize_wp_scripts' => 1,
		'probonoseo_article_heading_check' => 1,
		'probonoseo_article_alt_check' => 1,
		'probonoseo_article_image_count' => 1,
		'probonoseo_article_word_count' => 1,
		'probonoseo_article_category_match' => 1,
		'probonoseo_article_tag_duplicate' => 1,
		'probonoseo_diagnosis_title_duplicate' => 1,
		'probonoseo_diagnosis_meta_duplicate' => 1,
		'probonoseo_diagnosis_speed' => 1,
		'probonoseo_meta_cleanup' => 1,
		'probonoseo_gsc_verify' => 1,
	);
}

function probonoseo_info_get_free_default_off_keys() {
	return array(
		'probonoseo_ogp_image_fixed',
		'probonoseo_basic_ogp',
		'probonoseo_basic_twitter',
	);
}

function probonoseo_info_get_pro_switch_item_map() {
	return array(
		'probonoseo_pro_title_ai' => 1,
		'probonoseo_pro_heading_ai' => 1,
		'probonoseo_pro_outline_ai' => 1,
		'probonoseo_pro_body_ai' => 1,
		'probonoseo_pro_summary_ai' => 1,
		'probonoseo_pro_faq_ai' => 1,
		'probonoseo_pro_metadesc_ai' => 1,
		'probonoseo_pro_keywords_ai' => 1,
		'probonoseo_pro_rewrite_ai' => 1,
		'probonoseo_pro_readability_ai' => 1,
		'probonoseo_pro_sentiment_ai' => 1,
		'probonoseo_pro_duplicate_ai' => 1,
		'probonoseo_pro_target_ai' => 1,
		'probonoseo_pro_intent_ai' => 1,
		'probonoseo_pro_gap_ai' => 1,
		'probonoseo_pro_caption_ai' => 1,
		'probonoseo_pro_internal_link_ai' => 1,
		'probonoseo_pro_external_link_ai' => 1,
		'probonoseo_pro_update_ai' => 1,
		'probonoseo_pro_performance_ai' => 1,
		'probonoseo_pro_morphological_analysis' => 1,
		'probonoseo_competitor_enabled' => 1,
		'probonoseo_competitor_title' => 1,
		'probonoseo_competitor_meta' => 1,
		'probonoseo_competitor_heading' => 1,
		'probonoseo_competitor_wordcount' => 1,
		'probonoseo_competitor_images' => 1,
		'probonoseo_competitor_internal' => 1,
		'probonoseo_competitor_external' => 1,
		'probonoseo_competitor_schema' => 1,
		'probonoseo_competitor_keywords' => 1,
		'probonoseo_competitor_score' => 1,
		'probonoseo_competitor_report' => 1,
		'probonoseo_post_seo_metabox' => 1,
		'probonoseo_post_seo_score' => 1,
		'probonoseo_post_seo_title_preview' => 1,
		'probonoseo_post_seo_meta_preview' => 1,
		'probonoseo_post_seo_serp_preview' => 1,
		'probonoseo_post_seo_social_preview' => 1,
		'probonoseo_post_seo_focus_keyword' => 1,
		'probonoseo_post_seo_keyword_density' => 1,
		'probonoseo_post_seo_internal_links' => 1,
		'probonoseo_post_seo_external_links' => 1,
		'probonoseo_post_seo_image_alt' => 1,
		'probonoseo_post_seo_heading_structure' => 1,
		'probonoseo_post_seo_word_count' => 1,
		'probonoseo_post_seo_read_time' => 1,
		'probonoseo_post_seo_content_score' => 1,
		'probonoseo_post_seo_suggestions' => 1,
		'probonoseo_post_seo_checklist' => 1,
		'probonoseo_post_seo_publish_warning' => 1,
		'probonoseo_post_seo_type_post' => 1,
		'probonoseo_post_seo_type_page' => 1,
		'probonoseo_post_seo_type_custom' => 1,
		'probonoseo_pro_cpt' => 1,
		'probonoseo_pro_taxonomy' => 1,
		'probonoseo_pro_author' => 1,
		'probonoseo_pro_date' => 1,
		'probonoseo_pro_404' => 1,
		'probonoseo_pro_search' => 1,
		'probonoseo_pro_attachment' => 1,
		'probonoseo_robots_txt' => 1,
		'probonoseo_pro_amp' => 1,
		'probonoseo_pro_pwa' => 1,
		'probonoseo_pro_multisite' => 1,
		'probonoseo_pro_rest_api' => 1,
		'probonoseo_pro_cli' => 1,
		'probonoseo_pro_gsc' => 1,
		'probonoseo_pro_breadcrumb_customize' => 1,
		'probonoseo_pro_breadcrumb_exclude' => 1,
		'probonoseo_pro_schema_validator' => 1,
		'probonoseo_pro_rich_results_test' => 1,
		'probonoseo_pro_sitemap_exclude' => 1,
		'probonoseo_pro_sitemap_html' => 1,
		'probonoseo_pro_sitemap_image' => 1,
		'probonoseo_pro_sitemap_video' => 1,
		'probonoseo_pro_sitemap_news' => 1,
		'probonoseo_pro_sitemap_hreflang' => 1,
		'probonoseo_pro_404_title_enabled' => 1,
		'probonoseo_pro_404_desc_enabled' => 1,
		'probonoseo_pro_search_title_enabled' => 1,
		'probonoseo_pro_search_robots_enabled' => 1,
		'probonoseo_pro_date_title_enabled' => 1,
		'probonoseo_pro_date_robots_enabled' => 1,
		'probonoseo_pro_attachment_redirect_enabled' => 1,
		'probonoseo_pro_attachment_robots_enabled' => 1,
		'probonoseo_pro_pwa_app_name_enabled' => 1,
		'probonoseo_pro_pwa_short_name_enabled' => 1,
		'probonoseo_pro_pwa_theme_color_enabled' => 1,
		'probonoseo_pro_pwa_bg_color_enabled' => 1,
		'probonoseo_pro_pwa_icon_enabled' => 1,
		'probonoseo_gsc_client_id_enabled' => 1,
		'probonoseo_gsc_client_secret_enabled' => 1,
		'probonoseo_serp_faq' => 1,
		'probonoseo_serp_howto' => 1,
		'probonoseo_serp_review' => 1,
		'probonoseo_serp_recipe' => 1,
		'probonoseo_serp_event' => 1,
		'probonoseo_serp_product' => 1,
		'probonoseo_serp_video' => 1,
		'probonoseo_serp_job' => 1,
		'probonoseo_serp_sitelinks' => 1,
		'probonoseo_serp_searchbox' => 1,
		'probonoseo_serp_knowledge' => 1,
		'probonoseo_serp_carousel' => 1,
		'probonoseo_serp_featured' => 1,
		'probonoseo_serp_local' => 1,
		'probonoseo_serp_sitemap' => 1,
		'probonoseo_serp_faq_auto' => 1,
		'probonoseo_serp_howto_time' => 1,
		'probonoseo_serp_howto_cost' => 1,
		'probonoseo_serp_recipe_nutrition' => 1,
		'probonoseo_serp_recipe_video' => 1,
		'probonoseo_serp_event_offers' => 1,
		'probonoseo_serp_video_youtube' => 1,
		'probonoseo_serp_video_vimeo' => 1,
		'probonoseo_serp_sitemap_xml' => 1,
		'probonoseo_serp_sitemap_html' => 1,
		'probonoseo_serp_sitemap_image' => 1,
		'probonoseo_serp_sitemap_video' => 1,
		'probonoseo_serp_faq_limit_enabled' => 1,
		'probonoseo_serp_review_scale_enabled' => 1,
		'probonoseo_serp_review_type_enabled' => 1,
		'probonoseo_serp_event_mode_enabled' => 1,
		'probonoseo_serp_product_currency_enabled' => 1,
		'probonoseo_serp_product_availability_enabled' => 1,
		'probonoseo_serp_job_type_enabled' => 1,
		'probonoseo_serp_job_remote_enabled' => 1,
		'probonoseo_serp_local_name_enabled' => 1,
		'probonoseo_serp_local_type_enabled' => 1,
		'probonoseo_serp_local_address_enabled' => 1,
		'probonoseo_serp_local_phone_enabled' => 1,
		'probonoseo_serp_local_hours_enabled' => 1,
		'probonoseo_serp_sitemap_freq_enabled' => 1,
		'probonoseo_serp_sitemap_limit_enabled' => 1,
		'probonoseo_schema_software' => 1,
		'probonoseo_schema_course' => 1,
		'probonoseo_schema_book' => 1,
		'probonoseo_schema_movie' => 1,
		'probonoseo_schema_music' => 1,
		'probonoseo_schema_podcast' => 1,
		'probonoseo_schema_organization' => 1,
		'probonoseo_schema_person' => 1,
		'probonoseo_schema_article' => 1,
		'probonoseo_schema_news' => 1,
		'probonoseo_schema_blog' => 1,
		'probonoseo_schema_announcement' => 1,
		'probonoseo_schema_image' => 1,
		'probonoseo_schema_dataset' => 1,
		'probonoseo_schema_rating' => 1,
		'probonoseo_schema_claim' => 1,
		'probonoseo_schema_speakable' => 1,
		'probonoseo_schema_auto_select' => 1,
		'probonoseo_schema_software_category_enabled' => 1,
		'probonoseo_schema_software_os_enabled' => 1,
		'probonoseo_schema_software_price_type_enabled' => 1,
		'probonoseo_schema_software_currency_enabled' => 1,
		'probonoseo_schema_course_provider_enabled' => 1,
		'probonoseo_schema_course_mode_enabled' => 1,
		'probonoseo_schema_book_format_enabled' => 1,
		'probonoseo_schema_book_language_enabled' => 1,
		'probonoseo_schema_music_type_enabled' => 1,
		'probonoseo_schema_podcast_category_enabled' => 1,
		'probonoseo_schema_org_type_enabled' => 1,
		'probonoseo_schema_org_founded_enabled' => 1,
		'probonoseo_schema_org_address_enabled' => 1,
		'probonoseo_schema_person_job_enabled' => 1,
		'probonoseo_schema_person_affiliation_enabled' => 1,
		'probonoseo_schema_article_type_enabled' => 1,
		'probonoseo_schema_article_author_auto_enabled' => 1,
		'probonoseo_schema_article_publisher_enabled' => 1,
		'probonoseo_schema_article_logo_enabled' => 1,
		'probonoseo_schema_announcement_category_enabled' => 1,
		'probonoseo_schema_announcement_expires_enabled' => 1,
		'probonoseo_schema_image_copyright_enabled' => 1,
		'probonoseo_schema_dataset_creator_enabled' => 1,
		'probonoseo_schema_dataset_format_enabled' => 1,
		'probonoseo_schema_rating_scale_enabled' => 1,
		'probonoseo_schema_rating_item_type_enabled' => 1,
		'probonoseo_schema_claim_org_enabled' => 1,
		'probonoseo_schema_claim_rating_enabled' => 1,
		'probonoseo_schema_speakable_target_enabled' => 1,
		'probonoseo_schema_speakable_selector_enabled' => 1,
		'probonoseo_schema_auto_priority_enabled' => 1,
		'probonoseo_schema_auto_fallback_enabled' => 1,
		'probonoseo_speed_pro_webp' => 1,
		'probonoseo_speed_pro_avif' => 1,
		'probonoseo_speed_pro_compress' => 1,
		'probonoseo_speed_pro_responsive' => 1,
		'probonoseo_speed_pro_css_inline' => 1,
		'probonoseo_speed_pro_js_inline' => 1,
		'probonoseo_speed_pro_fonts' => 1,
		'probonoseo_speed_pro_dns' => 1,
		'probonoseo_speed_pro_hints' => 1,
		'probonoseo_speed_pro_db' => 1,
		'probonoseo_speed_pro_object_cache' => 1,
		'probonoseo_speed_pro_page_cache' => 1,
		'probonoseo_speed_pro_compress_quality_enabled' => 1,
		'probonoseo_speed_pro_responsive_sizes_enabled' => 1,
		'probonoseo_speed_pro_dns_domains_enabled' => 1,
		'probonoseo_speed_pro_preconnect_domains_enabled' => 1,
		'probonoseo_speed_pro_cache_expiry_enabled' => 1,
		'probonoseo_speed_pro_cache_exclude_enabled' => 1,
		'probonoseo_diagnosis_pro_index' => 1,
		'probonoseo_diagnosis_pro_crawl' => 1,
		'probonoseo_diagnosis_pro_mobile' => 1,
		'probonoseo_diagnosis_pro_vitals' => 1,
		'probonoseo_diagnosis_pro_security' => 1,
		'probonoseo_diagnosis_pro_ssl' => 1,
		'probonoseo_diagnosis_pro_sitemap' => 1,
		'probonoseo_diagnosis_pro_robots' => 1,
		'probonoseo_diagnosis_pro_htaccess' => 1,
		'probonoseo_diagnosis_pro_performance' => 1,
		'probonoseo_diagnosis_pro_total' => 1,
		'probonoseo_diagnosis_pro_pdf' => 1,
		'probonoseo_notify_email_enabled' => 1,
		'probonoseo_notify_slack_enabled' => 1,
		'probonoseo_debug_mode' => 1,
	);
}

function probonoseo_info_get_free_option_keys() {
	return array_keys(probonoseo_info_get_free_switch_item_map());
}

function probonoseo_info_get_pro_option_keys() {
	return array_keys(probonoseo_info_get_pro_switch_item_map());
}

function probonoseo_info_get_all_option_keys() {
	return array_merge(probonoseo_info_get_free_option_keys(), probonoseo_info_get_pro_option_keys());
}

function probonoseo_info_count_enabled_items($probonoseo_switch_map, $probonoseo_default_on_keys = array()) {
	$probonoseo_count = 0;
	foreach ($probonoseo_switch_map as $probonoseo_key => $probonoseo_item_count) {
		$probonoseo_value = get_option($probonoseo_key, null);
		if ($probonoseo_value === '1') {
			$probonoseo_count += $probonoseo_item_count;
		} elseif ($probonoseo_value === null && in_array($probonoseo_key, $probonoseo_default_on_keys, true)) {
			$probonoseo_count += $probonoseo_item_count;
		}
	}
	return $probonoseo_count;
}

function probonoseo_info_get_total_items($probonoseo_switch_map) {
	return array_sum($probonoseo_switch_map);
}

function probonoseo_info_import_settings($probonoseo_file) {
	if ($probonoseo_file['error'] !== UPLOAD_ERR_OK) {
		return 'ファイルのアップロードに失敗しました。';
	}
	$probonoseo_content = file_get_contents($probonoseo_file['tmp_name']);
	$probonoseo_settings = json_decode($probonoseo_content, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		return 'JSONファイルの形式が正しくありません。';
	}
	$probonoseo_keys = probonoseo_info_get_all_option_keys();
	$probonoseo_imported = 0;
	foreach ($probonoseo_keys as $probonoseo_key) {
		if (isset($probonoseo_settings[$probonoseo_key])) {
			update_option($probonoseo_key, $probonoseo_settings[$probonoseo_key]);
			$probonoseo_imported++;
		}
	}
	return $probonoseo_imported . '件の設定をインポートしました。';
}

function probonoseo_info_reset_free_settings() {
	$probonoseo_keys = probonoseo_info_get_free_option_keys();
	foreach ($probonoseo_keys as $probonoseo_key) {
		delete_option($probonoseo_key);
	}
}

$probonoseo_free_switch_map = probonoseo_info_get_free_switch_item_map();
$probonoseo_pro_switch_map = probonoseo_info_get_pro_switch_item_map();
$probonoseo_free_default_off_keys = probonoseo_info_get_free_default_off_keys();
$probonoseo_free_default_on_keys = array_diff(probonoseo_info_get_free_option_keys(), $probonoseo_free_default_off_keys);

$probonoseo_free_total = probonoseo_info_get_total_items($probonoseo_free_switch_map);
$probonoseo_pro_total = probonoseo_info_get_total_items($probonoseo_pro_switch_map);

$probonoseo_free_enabled = probonoseo_info_count_enabled_items($probonoseo_free_switch_map, $probonoseo_free_default_on_keys);

if ($probonoseo_is_pro_active) {
	$probonoseo_pro_enabled = probonoseo_info_count_enabled_items($probonoseo_pro_switch_map, array());
	$probonoseo_pro_available = $probonoseo_pro_total;
} else {
	$probonoseo_pro_enabled = 0;
	$probonoseo_pro_available = 0;
}

$probonoseo_all_enabled = $probonoseo_free_enabled + $probonoseo_pro_enabled;
$probonoseo_all_available = $probonoseo_free_total + $probonoseo_pro_available;

$probonoseo_master_all_total = 288;
?>

<?php if (!empty($probonoseo_manage_notice)) : ?>
<div class="notice notice-<?php echo esc_attr($probonoseo_manage_notice_type); ?> is-dismissible probonoseo-notice">
	<p><?php echo esc_html($probonoseo_manage_notice); ?></p>
</div>
<?php endif; ?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">プラグイン情報</h2>
	<p class="probonoseo-section-description">ProbonoSEOの状態と設定管理を行います。</p>
</div>

<div class="probonoseo-info-grid">
	<div class="probonoseo-info-left">

		<div class="probonoseo-card">
			<h3 class="probonoseo-card-title">プラグイン情報</h3>
			<div class="probonoseo-info-list">
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">プラグインバージョン</span>
					<span class="probonoseo-info-value"><?php echo esc_html(PROBONOSEO_VERSION); ?></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">ライセンス状態</span>
					<span class="probonoseo-info-value"><?php echo $probonoseo_is_pro_active ? '<span class="probonoseo-status-pro">Pro版有効</span>' : '<span class="probonoseo-status-free">無料版</span>'; ?></span>
				</div>
				<div class="probonoseo-info-row probonoseo-info-row-border">
					<span class="probonoseo-info-label">無料版機能</span>
					<span class="probonoseo-info-value"><?php echo esc_html($probonoseo_free_enabled); ?> / <?php echo esc_html($probonoseo_free_total); ?> 有効</span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">Pro版機能（実装済み）</span>
					<span class="probonoseo-info-value">
						<?php if ($probonoseo_is_pro_active) : ?>
							<?php echo esc_html($probonoseo_pro_enabled); ?> / <?php echo esc_html($probonoseo_pro_total); ?> 有効
						<?php else : ?>
							<span class="probonoseo-status-free">- / <?php echo esc_html($probonoseo_pro_total); ?>（ライセンスなし）</span>
						<?php endif; ?>
					</span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">有効機能合計</span>
					<span class="probonoseo-info-value"><strong><?php echo esc_html($probonoseo_all_enabled); ?> / <?php echo esc_html($probonoseo_all_available); ?></strong></span>
				</div>
				<div class="probonoseo-info-row probonoseo-info-row-border">
					<span class="probonoseo-info-label">総機能数（マスタープラン）</span>
					<span class="probonoseo-info-value"><strong class="probonoseo-info-highlight"><?php echo esc_html($probonoseo_master_all_total); ?> 項目</strong></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">PHP バージョン</span>
					<span class="probonoseo-info-value"><?php echo esc_html(PHP_VERSION); ?></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">WordPress バージョン</span>
					<span class="probonoseo-info-value"><?php echo esc_html(get_bloginfo('version')); ?></span>
				</div>
			</div>
			
			<?php if (!$probonoseo_is_pro_active) : ?>
			<div class="probonoseo-upgrade-box">
				<p class="probonoseo-upgrade-text">
					<strong>💡 Pro版にアップグレード</strong><br>
					Pro版ライセンスを有効化すると、<?php echo esc_html($probonoseo_pro_total); ?>項目の追加機能が利用可能になります。
				</p>
				<a href="?page=probonoseo&tab=license" class="probonoseo-upgrade-link">ライセンス認証へ →</a>
			</div>
			<?php endif; ?>
		</div>

	</div>

	<div class="probonoseo-info-right">

		<div class="probonoseo-card">
			<h3 class="probonoseo-card-title">設定のエクスポート</h3>
			<p class="probonoseo-card-description">現在の設定をJSONファイルとしてダウンロードします。バックアップや他のサイトへの移行に使用できます。</p>
			<div class="probonoseo-card-action">
				<button type="button" id="probonoseo-export-settings" class="button button-primary" data-export-nonce="<?php echo esc_attr(wp_create_nonce('probonoseo_export_settings')); ?>">
					<span class="dashicons dashicons-download probonoseo-btn-icon"></span>
					設定をエクスポート
				</button>
			</div>
		</div>

		<div class="probonoseo-card">
			<h3 class="probonoseo-card-title">設定のインポート</h3>
			<p class="probonoseo-card-description">エクスポートしたJSONファイルから設定を復元します。現在の設定は上書きされます。</p>
			<div class="probonoseo-card-action">
				<input type="file" id="probonoseo-import-file" accept=".json" class="probonoseo-file-input">
				<button type="button" id="probonoseo-import-settings" class="button button-secondary" data-import-nonce="<?php echo esc_attr(wp_create_nonce('probonoseo_import_settings')); ?>">
					<span class="dashicons dashicons-upload probonoseo-btn-icon"></span>
					設定をインポート
				</button>
			</div>
		</div>

		<div class="probonoseo-card probonoseo-card-danger">
			<h3 class="probonoseo-card-title probonoseo-card-title-danger">設定のリセット</h3>
			<p class="probonoseo-card-description">設定を初期状態に戻します。この操作は取り消せません。</p>
			<div class="probonoseo-card-action">
				<button type="button" id="probonoseo-reset-free" class="button">無料版のみリセット</button>
			</div>
		</div>

	</div>
</div>

<script>
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var exportBtn = document.getElementById('probonoseo-export-settings');
		if (exportBtn) {
			exportBtn.addEventListener('click', function() {
				var nonce = exportBtn.getAttribute('data-export-nonce');
				if (!nonce || typeof ajaxurl === 'undefined') {
					alert('エラーが発生しました。');
					return;
				}
				var originalHtml = exportBtn.innerHTML;
				exportBtn.disabled = true;
				exportBtn.textContent = 'エクスポート中...';
				var formData = new FormData();
				formData.append('action', 'probonoseo_export_settings');
				formData.append('nonce', nonce);
				fetch(ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				}).then(function(response) {
					return response.json();
				}).then(function(response) {
					if (!response || !response.success || !response.data || !response.data.settings) {
						alert('エクスポートに失敗しました。');
						return;
					}
					var jsonString = JSON.stringify(response.data.settings, null, 2);
					var blob = new Blob([jsonString], { type: 'application/json' });
					var url = URL.createObjectURL(blob);
					var a = document.createElement('a');
					a.href = url;
					a.download = response.data.filename || 'probonoseo-settings.json';
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					URL.revokeObjectURL(url);
				}).catch(function() {
					alert('エラーが発生しました。');
				}).finally(function() {
					exportBtn.disabled = false;
					exportBtn.innerHTML = originalHtml;
				});
			});
		}

		var resetFreeBtn = document.getElementById('probonoseo-reset-free');
		if (resetFreeBtn) {
			resetFreeBtn.addEventListener('click', function() {
				if (confirm('無料版の設定をすべてリセットします。この操作は取り消せません。続行しますか？')) {
					var form = document.createElement('form');
					form.method = 'POST';
					form.innerHTML = '<input type="hidden" name="probonoseo_reset_free_settings" value="1">' +
						'<?php wp_nonce_field("probonoseo_manage_action", "probonoseo_manage_nonce"); ?>';
					document.body.appendChild(form);
					form.submit();
				}
			});
		}
	});
})();
</script>