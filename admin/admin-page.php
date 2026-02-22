<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();

function probonoseo_get_value($key, $default_on) {
	$v = get_option($key, null);
	if ($v === null) {
		return $default_on ? '1' : '0';
	}
	return $v ? '1' : '0';
}

function probonoseo_render_switch($key, $label, $default_on, $locked, $off_msg) {
	$val = probonoseo_get_value($key, $default_on);
	$is_on = ($val === '1');

	$toggle_class = 'probonoseo-toggle';
	if ($is_on) {
		$toggle_class .= ' is-on';
	}
	if ($locked) {
		$toggle_class .= ' is-locked';
	}

	echo '<div class="probonoseo-toggle-container">';
	echo '<div class="' . esc_attr($toggle_class) . '" data-off-message="' . esc_attr($off_msg) . '">';
	echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';
	echo '<span class="probonoseo-toggle-label">' . esc_html($label) . '</span>';
	echo '<div class="probonoseo-toggle-switch">';
	echo '<div class="probonoseo-toggle-pill">';
	echo '<span class="probonoseo-toggle-state probonoseo-toggle-state-off">無効</span>';
	echo '<span class="probonoseo-toggle-state probonoseo-toggle-state-on">有効</span>';
	echo '<div class="probonoseo-toggle-knob"></div>';
	echo '</div>';
	echo '</div>';
	if ($locked) {
		echo '<span class="probonoseo-toggle-lock">🔒 Pro版</span>';
	}
	echo '<div class="probonoseo-toggle-tooltip"></div>';
	echo '</div>';
	echo '</div>';
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['probonoseo_save'])) {
	if (!isset($_POST['probonoseo_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['probonoseo_nonce'])), 'probonoseo_save_settings')) {
		echo '<div class="notice notice-error"><p>認証に失敗しました。</p></div>';
	} else {
		$probonoseo_keys = array(
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
			'probonoseo_pro_404_title_enabled',
			'probonoseo_pro_404_desc_enabled',
			'probonoseo_pro_search_title_enabled',
			'probonoseo_pro_search_robots_enabled',
			'probonoseo_pro_date_title_enabled',
			'probonoseo_pro_date_robots_enabled',
			'probonoseo_pro_attachment_redirect_enabled',
			'probonoseo_pro_attachment_robots_enabled',
			'probonoseo_pro_pwa_app_name_enabled',
			'probonoseo_pro_pwa_short_name_enabled',
			'probonoseo_pro_pwa_theme_color_enabled',
			'probonoseo_pro_pwa_bg_color_enabled',
			'probonoseo_pro_pwa_icon_enabled',
			'probonoseo_gsc_client_id_enabled',
			'probonoseo_gsc_client_secret_enabled',
			'probonoseo_serp_faq',
			'probonoseo_serp_howto',
			'probonoseo_serp_review',
			'probonoseo_serp_recipe',
			'probonoseo_serp_event',
			'probonoseo_serp_product',
			'probonoseo_serp_video',
			'probonoseo_serp_job',
			'probonoseo_serp_sitelinks',
			'probonoseo_serp_searchbox',
			'probonoseo_serp_knowledge',
			'probonoseo_serp_carousel',
			'probonoseo_serp_featured',
			'probonoseo_serp_local',
			'probonoseo_serp_sitemap',
			'probonoseo_serp_faq_auto',
			'probonoseo_serp_howto_time',
			'probonoseo_serp_howto_cost',
			'probonoseo_serp_recipe_nutrition',
			'probonoseo_serp_recipe_video',
			'probonoseo_serp_event_offers',
			'probonoseo_serp_video_youtube',
			'probonoseo_serp_video_vimeo',
			'probonoseo_serp_sitemap_xml',
			'probonoseo_serp_sitemap_html',
			'probonoseo_serp_sitemap_image',
			'probonoseo_serp_sitemap_video',
			'probonoseo_serp_faq_limit_enabled',
			'probonoseo_serp_review_scale_enabled',
			'probonoseo_serp_review_type_enabled',
			'probonoseo_serp_event_mode_enabled',
			'probonoseo_serp_product_currency_enabled',
			'probonoseo_serp_product_availability_enabled',
			'probonoseo_serp_job_type_enabled',
			'probonoseo_serp_job_remote_enabled',
			'probonoseo_serp_local_name_enabled',
			'probonoseo_serp_local_type_enabled',
			'probonoseo_serp_local_address_enabled',
			'probonoseo_serp_local_phone_enabled',
			'probonoseo_serp_local_hours_enabled',
			'probonoseo_serp_sitemap_freq_enabled',
			'probonoseo_serp_sitemap_limit_enabled',
			'probonoseo_schema_software',
			'probonoseo_schema_course',
			'probonoseo_schema_book',
			'probonoseo_schema_movie',
			'probonoseo_schema_music',
			'probonoseo_schema_podcast',
			'probonoseo_schema_organization',
			'probonoseo_schema_person',
			'probonoseo_schema_article',
			'probonoseo_schema_news',
			'probonoseo_schema_blog',
			'probonoseo_schema_announcement',
			'probonoseo_schema_image',
			'probonoseo_schema_dataset',
			'probonoseo_schema_rating',
			'probonoseo_schema_claim',
			'probonoseo_schema_speakable',
			'probonoseo_schema_auto_select',
			'probonoseo_schema_software_category_enabled',
			'probonoseo_schema_software_os_enabled',
			'probonoseo_schema_software_price_type_enabled',
			'probonoseo_schema_software_currency_enabled',
			'probonoseo_schema_course_provider_enabled',
			'probonoseo_schema_course_mode_enabled',
			'probonoseo_schema_book_format_enabled',
			'probonoseo_schema_book_language_enabled',
			'probonoseo_schema_music_type_enabled',
			'probonoseo_schema_podcast_category_enabled',
			'probonoseo_schema_org_type_enabled',
			'probonoseo_schema_org_founded_enabled',
			'probonoseo_schema_org_address_enabled',
			'probonoseo_schema_person_job_enabled',
			'probonoseo_schema_person_affiliation_enabled',
			'probonoseo_schema_article_type_enabled',
			'probonoseo_schema_article_author_auto_enabled',
			'probonoseo_schema_article_publisher_enabled',
			'probonoseo_schema_article_logo_enabled',
			'probonoseo_schema_announcement_category_enabled',
			'probonoseo_schema_announcement_expires_enabled',
			'probonoseo_schema_image_copyright_enabled',
			'probonoseo_schema_dataset_creator_enabled',
			'probonoseo_schema_dataset_format_enabled',
			'probonoseo_schema_rating_scale_enabled',
			'probonoseo_schema_rating_item_type_enabled',
			'probonoseo_schema_claim_org_enabled',
			'probonoseo_schema_claim_rating_enabled',
			'probonoseo_schema_speakable_target_enabled',
			'probonoseo_schema_speakable_selector_enabled',
			'probonoseo_schema_auto_priority_enabled',
			'probonoseo_schema_auto_fallback_enabled',
			'probonoseo_speed_pro_webp',
			'probonoseo_speed_pro_avif',
			'probonoseo_speed_pro_compress',
			'probonoseo_speed_pro_responsive',
			'probonoseo_speed_pro_css_inline',
			'probonoseo_speed_pro_js_inline',
			'probonoseo_speed_pro_fonts',
			'probonoseo_speed_pro_dns',
			'probonoseo_speed_pro_hints',
			'probonoseo_speed_pro_db',
			'probonoseo_speed_pro_object_cache',
			'probonoseo_speed_pro_page_cache',
			'probonoseo_speed_pro_compress_quality_enabled',
			'probonoseo_speed_pro_responsive_sizes_enabled',
			'probonoseo_speed_pro_dns_domains_enabled',
			'probonoseo_speed_pro_preconnect_domains_enabled',
			'probonoseo_speed_pro_cache_expiry_enabled',
			'probonoseo_speed_pro_cache_exclude_enabled',
			'probonoseo_diagnosis_pro_index',
			'probonoseo_diagnosis_pro_crawl',
			'probonoseo_diagnosis_pro_mobile',
			'probonoseo_diagnosis_pro_vitals',
			'probonoseo_diagnosis_pro_security',
			'probonoseo_diagnosis_pro_ssl',
			'probonoseo_diagnosis_pro_sitemap',
			'probonoseo_diagnosis_pro_robots',
			'probonoseo_diagnosis_pro_htaccess',
			'probonoseo_diagnosis_pro_performance',
			'probonoseo_diagnosis_pro_total',
			'probonoseo_diagnosis_pro_pdf',
			'probonoseo_notify_email_enabled',
			'probonoseo_notify_slack_enabled',
			'probonoseo_debug_mode',
		);

		$probonoseo_text_keys = array(
			'probonoseo_gsc_verify_code',
			'probonoseo_pro_404_title',
			'probonoseo_pro_404_desc',
			'probonoseo_pro_search_title',
			'probonoseo_pro_search_robots',
			'probonoseo_pro_date_title',
			'probonoseo_pro_date_robots',
			'probonoseo_pro_attachment_redirect',
			'probonoseo_pro_attachment_robots',
			'probonoseo_pro_pwa_app_name',
			'probonoseo_pro_pwa_short_name',
			'probonoseo_pro_pwa_theme_color',
			'probonoseo_pro_pwa_bg_color',
			'probonoseo_pro_pwa_icon',
			'probonoseo_gsc_client_id',
			'probonoseo_gsc_client_secret',
			'probonoseo_serp_faq_limit',
			'probonoseo_serp_review_scale',
			'probonoseo_serp_review_type',
			'probonoseo_serp_event_mode',
			'probonoseo_serp_product_currency',
			'probonoseo_serp_product_availability',
			'probonoseo_serp_job_type',
			'probonoseo_serp_job_remote',
			'probonoseo_serp_local_name',
			'probonoseo_serp_local_type',
			'probonoseo_serp_local_address',
			'probonoseo_serp_local_phone',
			'probonoseo_serp_local_hours',
			'probonoseo_serp_sitemap_freq',
			'probonoseo_serp_sitemap_limit',
			'probonoseo_schema_software_category',
			'probonoseo_schema_software_os',
			'probonoseo_schema_software_price_type',
			'probonoseo_schema_software_currency',
			'probonoseo_schema_course_provider',
			'probonoseo_schema_course_mode',
			'probonoseo_schema_book_format',
			'probonoseo_schema_book_language',
			'probonoseo_schema_music_type',
			'probonoseo_schema_podcast_category',
			'probonoseo_schema_org_type',
			'probonoseo_schema_org_founded',
			'probonoseo_schema_org_address',
			'probonoseo_schema_person_job',
			'probonoseo_schema_person_affiliation',
			'probonoseo_schema_article_type',
			'probonoseo_schema_article_author_auto',
			'probonoseo_schema_article_publisher',
			'probonoseo_schema_article_logo',
			'probonoseo_schema_announcement_category',
			'probonoseo_schema_announcement_expires',
			'probonoseo_schema_image_copyright',
			'probonoseo_schema_dataset_creator',
			'probonoseo_schema_dataset_format',
			'probonoseo_schema_rating_scale',
			'probonoseo_schema_rating_item_type',
			'probonoseo_schema_claim_org',
			'probonoseo_schema_claim_rating',
			'probonoseo_schema_speakable_target',
			'probonoseo_schema_speakable_selector',
			'probonoseo_schema_auto_priority',
			'probonoseo_schema_auto_fallback',
			'probonoseo_speed_pro_compress_quality',
			'probonoseo_speed_pro_responsive_sizes',
			'probonoseo_speed_pro_dns_domains',
			'probonoseo_speed_pro_preconnect_domains',
			'probonoseo_speed_pro_cache_expiry',
			'probonoseo_speed_pro_cache_exclude',
			'probonoseo_notify_email',
			'probonoseo_notify_slack_webhook',
		);

		foreach ($probonoseo_keys as $probonoseo_k) {
			if (isset($_POST[$probonoseo_k])) {
				update_option($probonoseo_k, $_POST[$probonoseo_k] === '1' ? '1' : '0');
			}
		}

		foreach ($probonoseo_text_keys as $probonoseo_k) {
			if (isset($_POST[$probonoseo_k])) {
				update_option($probonoseo_k, sanitize_text_field(wp_unslash($_POST[$probonoseo_k])));
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>設定を保存しました。</p></div>';
	}
}

$probonoseo_current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'title';

?>
<div class="probonoseo-wrap">

	<div class="probonoseo-header">
		<h1 class="probonoseo-title">ProbonoSEO</h1>
		<p class="probonoseo-subtitle">日本語サイト向けに最適化された日本製（Made in Japan）のSEOプラグインです</p>
	</div>

	<nav class="nav-tab-wrapper probonoseo-tabs-all">
		<a href="?page=probonoseo&tab=title" class="nav-tab <?php echo $probonoseo_current_tab === 'title' ? 'nav-tab-active' : ''; ?>">タイトル最適化</a>
		<a href="?page=probonoseo&tab=meta" class="nav-tab <?php echo $probonoseo_current_tab === 'meta' ? 'nav-tab-active' : ''; ?>">メタディスクリプション</a>
		<a href="?page=probonoseo&tab=canonical" class="nav-tab <?php echo $probonoseo_current_tab === 'canonical' ? 'nav-tab-active' : ''; ?>">canonical</a>
		<a href="?page=probonoseo&tab=ogp" class="nav-tab <?php echo $probonoseo_current_tab === 'ogp' ? 'nav-tab-active' : ''; ?>">OGP / Twitter</a>
		<a href="?page=probonoseo&tab=schema" class="nav-tab <?php echo $probonoseo_current_tab === 'schema' ? 'nav-tab-active' : ''; ?>">schema / パンくず</a>
		<a href="?page=probonoseo&tab=internal" class="nav-tab <?php echo $probonoseo_current_tab === 'internal' ? 'nav-tab-active' : ''; ?>">内部リンク / 速度</a>
		<a href="?page=probonoseo&tab=article" class="nav-tab <?php echo $probonoseo_current_tab === 'article' ? 'nav-tab-active' : ''; ?>">記事SEO</a>
		<a href="?page=probonoseo&tab=diagnosis" class="nav-tab <?php echo $probonoseo_current_tab === 'diagnosis' ? 'nav-tab-active' : ''; ?>">サイト診断</a>
		<a href="?page=probonoseo&tab=info" class="nav-tab <?php echo $probonoseo_current_tab === 'info' ? 'nav-tab-active' : ''; ?>">プラグイン情報</a>
		<a href="?page=probonoseo&tab=ai" class="nav-tab <?php echo $probonoseo_current_tab === 'ai' ? 'nav-tab-active' : ''; ?>">AI日本語SEO補助 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=analysis" class="nav-tab <?php echo $probonoseo_current_tab === 'analysis' ? 'nav-tab-active' : ''; ?>">競合分析 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=postseo" class="nav-tab <?php echo $probonoseo_current_tab === 'postseo' ? 'nav-tab-active' : ''; ?>">投稿SEO <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=pro" class="nav-tab <?php echo $probonoseo_current_tab === 'pro' ? 'nav-tab-active' : ''; ?>">Pro専用強化 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=speedpro" class="nav-tab <?php echo $probonoseo_current_tab === 'speedpro' ? 'nav-tab-active' : ''; ?>">速度改善Pro <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=diagnosispro" class="nav-tab <?php echo $probonoseo_current_tab === 'diagnosispro' ? 'nav-tab-active' : ''; ?>">サイト診断Pro <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=serp" class="nav-tab <?php echo $probonoseo_current_tab === 'serp' ? 'nav-tab-active' : ''; ?>">SERP強化 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=schemaadv" class="nav-tab <?php echo $probonoseo_current_tab === 'schemaadv' ? 'nav-tab-active' : ''; ?>">schema高度版 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=manage" class="nav-tab <?php echo $probonoseo_current_tab === 'manage' ? 'nav-tab-active' : ''; ?>">全体管理 <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=openai" class="nav-tab <?php echo $probonoseo_current_tab === 'openai' ? 'nav-tab-active' : ''; ?>">OpenAI API <span class="probonoseo-tab-badge">Pro</span></a>
		<a href="?page=probonoseo&tab=license" class="nav-tab <?php echo $probonoseo_current_tab === 'license' ? 'nav-tab-active' : ''; ?>">ライセンス <span class="probonoseo-tab-badge">Pro</span></a>
	</nav>

	<?php if ($probonoseo_current_tab === 'diagnosis' || $probonoseo_current_tab === 'license' || $probonoseo_current_tab === 'openai' || $probonoseo_current_tab === 'info') : ?>
		
		<?php 
		if ($probonoseo_current_tab === 'diagnosis') {
			require_once PROBONOSEO_PATH . 'admin/tabs/tab-diagnosis.php';
		} elseif ($probonoseo_current_tab === 'license') {
			require_once PROBONOSEO_PATH . 'admin/tabs/tab-license.php';
		} elseif ($probonoseo_current_tab === 'openai') {
			require_once PROBONOSEO_PATH . 'admin/tabs/tab-openai.php';
		} elseif ($probonoseo_current_tab === 'info') {
			require_once PROBONOSEO_PATH . 'admin/tabs/tab-info.php';
		}
		?>
		
	<?php else : ?>
		
		<form method="post" action="">
			<?php wp_nonce_field('probonoseo_save_settings', 'probonoseo_nonce'); ?>

			<div class="probonoseo-save-button-top">
				<?php submit_button('設定を保存', 'primary probonoseo-save-btn', 'probonoseo_save', false); ?>
			</div>

			<?php
			if ($probonoseo_current_tab === 'title') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-title.php';
			} elseif ($probonoseo_current_tab === 'meta') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-meta.php';
			} elseif ($probonoseo_current_tab === 'canonical') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-canonical.php';
			} elseif ($probonoseo_current_tab === 'ogp') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-ogp.php';
			} elseif ($probonoseo_current_tab === 'schema') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-schema.php';
			} elseif ($probonoseo_current_tab === 'internal') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-internal.php';
			} elseif ($probonoseo_current_tab === 'article') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-article.php';
			} elseif ($probonoseo_current_tab === 'ai') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-ai.php';
			} elseif ($probonoseo_current_tab === 'analysis') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-analysis.php';
			} elseif ($probonoseo_current_tab === 'postseo') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-post-seo.php';
			} elseif ($probonoseo_current_tab === 'pro') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-pro.php';
			} elseif ($probonoseo_current_tab === 'speedpro') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-speed-pro.php';
			} elseif ($probonoseo_current_tab === 'diagnosispro') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-diagnosis-pro.php';
			} elseif ($probonoseo_current_tab === 'serp') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-serp.php';
			} elseif ($probonoseo_current_tab === 'schemaadv') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-schema-advanced.php';
			} elseif ($probonoseo_current_tab === 'manage') {
				require_once PROBONOSEO_PATH . 'admin/tabs/tab-manage.php';
			}
			?>

			<div class="probonoseo-save-button-bottom">
				<?php submit_button('設定を保存', 'primary probonoseo-save-btn', 'probonoseo_save', false); ?>
			</div>

		</form>
		
	<?php endif; ?>
</div>