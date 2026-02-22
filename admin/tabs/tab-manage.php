<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_is_pro_active = $probonoseo_license->is_pro_active();

$probonoseo_manage_notice = '';
$probonoseo_manage_notice_type = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['probonoseo_reset_settings'])) {
		check_admin_referer('probonoseo_manage_action', 'probonoseo_manage_nonce');
		probonoseo_manage_reset_all_settings();
		$probonoseo_manage_notice = 'すべての設定をリセットしました。';
		$probonoseo_manage_notice_type = 'success';
	}
	
	if (isset($_POST['probonoseo_reset_pro_settings'])) {
		check_admin_referer('probonoseo_manage_action', 'probonoseo_manage_nonce');
		probonoseo_manage_reset_pro_settings();
		$probonoseo_manage_notice = 'Pro版の設定をリセットしました。';
		$probonoseo_manage_notice_type = 'success';
	}
}

function probonoseo_manage_get_free_option_keys() {
	return array(
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
}

function probonoseo_manage_get_pro_option_keys() {
	return array(
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
}

function probonoseo_manage_reset_all_settings() {
	$probonoseo_free_keys = probonoseo_manage_get_free_option_keys();
	$probonoseo_pro_keys = probonoseo_manage_get_pro_option_keys();
	$probonoseo_all_keys = array_merge($probonoseo_free_keys, $probonoseo_pro_keys);
	foreach ($probonoseo_all_keys as $probonoseo_key) {
		delete_option($probonoseo_key);
	}
}

function probonoseo_manage_reset_pro_settings() {
	$probonoseo_keys = probonoseo_manage_get_pro_option_keys();
	foreach ($probonoseo_keys as $probonoseo_key) {
		delete_option($probonoseo_key);
	}
}

$probonoseo_manage_advanced = null;
if (class_exists('ProbonoSEO_Manage_Advanced')) {
	$probonoseo_manage_advanced = ProbonoSEO_Manage_Advanced::get_instance();
}
$probonoseo_statistics = $probonoseo_manage_advanced ? $probonoseo_manage_advanced->get_statistics() : array();
$probonoseo_backups = $probonoseo_manage_advanced ? $probonoseo_manage_advanced->get_backups() : array();
?>

<?php if (!empty($probonoseo_manage_notice)) : ?>
<div class="notice notice-<?php echo esc_attr($probonoseo_manage_notice_type); ?> is-dismissible probonoseo-notice">
	<p><?php echo esc_html($probonoseo_manage_notice); ?></p>
</div>
<?php endif; ?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">全体管理 <span class="probonoseo-pro-label">Pro</span></h2>
	<p class="probonoseo-section-description">通知設定、バックアップ、詳細リセットなどの高度な管理機能を提供します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">メール通知</h3>
					<p class="probonoseo-card-description">SEO診断結果やエラーをメールで通知します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_notify_email_enabled', 'メール通知', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Slack通知</h3>
					<p class="probonoseo-card-description">SEO診断結果やエラーをSlackに通知します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_notify_slack_enabled', 'Slack通知', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	<?php if ($probonoseo_is_pro_active) : ?>

		<div class="probonoseo-card pro-feature">
			<h3 class="probonoseo-card-title">自動バックアップ</h3>
			<p class="probonoseo-card-description">設定の自動バックアップを管理します。最大10件まで保存されます。</p>
			<div class="probonoseo-card-action">
				<button type="button" id="probonoseo-create-backup" class="button button-primary">
					<span class="dashicons dashicons-backup probonoseo-btn-icon"></span>
					今すぐバックアップ
				</button>
			</div>
			<?php if (!empty($probonoseo_backups)) : ?>
			<div class="probonoseo-backup-list">
				<h4 class="probonoseo-backup-list-title">バックアップ一覧</h4>
				<table class="widefat probonoseo-backup-table">
					<thead>
						<tr>
							<th>日時</th>
							<th>バージョン</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach (array_slice($probonoseo_backups, 0, 5) as $probonoseo_backup) : ?>
						<tr>
							<td><?php echo esc_html($probonoseo_backup['date']); ?></td>
							<td><?php echo esc_html($probonoseo_backup['version']); ?></td>
							<td>
								<button type="button" class="button button-small probonoseo-restore-backup" data-id="<?php echo esc_attr($probonoseo_backup['id']); ?>">復元</button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		</div>

		<?php if (!empty($probonoseo_statistics)) : ?>
		<div class="probonoseo-card pro-feature">
			<h3 class="probonoseo-card-title">統計情報</h3>
			<div class="probonoseo-info-list">
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">公開記事数</span>
					<span class="probonoseo-info-value"><?php echo esc_html($probonoseo_statistics['total_posts']); ?></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">SEO最適化済み記事</span>
					<span class="probonoseo-info-value"><?php echo esc_html($probonoseo_statistics['optimized_posts']); ?></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">AI使用回数</span>
					<span class="probonoseo-info-value"><?php echo esc_html($probonoseo_statistics['ai_usage_count']); ?></span>
				</div>
				<div class="probonoseo-info-row">
					<span class="probonoseo-info-label">診断実行回数</span>
					<span class="probonoseo-info-value"><?php echo esc_html($probonoseo_statistics['diagnosis_count']); ?></span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">デバッグモード</h3>
					<p class="probonoseo-card-description">開発者向けデバッグ情報を出力します。本番環境ではOFFにしてください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_debug_mode', 'デバッグモード', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature probonoseo-card-danger">
			<h3 class="probonoseo-card-title probonoseo-card-title-danger">設定のリセット</h3>
			<p class="probonoseo-card-description">設定を初期状態に戻します。この操作は取り消せません。</p>
			<div class="probonoseo-card-action probonoseo-reset-buttons">
				<button type="button" id="probonoseo-reset-pro" class="button">Pro版のみリセット</button>
				<button type="button" id="probonoseo-reset-all" class="button probonoseo-button-danger">すべてリセット</button>
			</div>
		</div>

	<?php else : ?>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">デバッグモード</h3>
					<p class="probonoseo-card-description">開発者向けデバッグ情報を出力します。本番環境ではOFFにしてください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_debug_mode', 'デバッグモード', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	<?php endif; ?>

	</div>

</div>

<?php if ($probonoseo_is_pro_active) : ?>
<script>
jQuery(document).ready(function($) {
	var advancedNonce = '<?php echo esc_attr(wp_create_nonce('probonoseo_manage_advanced')); ?>';
	
	$('#probonoseo-create-backup').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text('作成中...');
		$.post(ajaxurl, {
			action: 'probonoseo_backup_settings',
			nonce: advancedNonce
		}, function(response) {
			if (response.success) {
				alert('バックアップを作成しました: ' + response.data.date);
				location.reload();
			} else {
				alert('エラー: ' + response.data.message);
			}
		}).always(function() {
			$btn.prop('disabled', false).html('<span class="dashicons dashicons-backup probonoseo-btn-icon"></span>今すぐバックアップ');
		});
	});
	
	$('.probonoseo-restore-backup').on('click', function() {
		if (!confirm('この設定を復元しますか？現在の設定は上書きされます。')) {
			return;
		}
		var $btn = $(this);
		var backupId = $btn.data('id');
		$btn.prop('disabled', true).text('復元中...');
		$.post(ajaxurl, {
			action: 'probonoseo_restore_settings',
			nonce: advancedNonce,
			backup_id: backupId
		}, function(response) {
			if (response.success) {
				alert('設定を復元しました');
				location.reload();
			} else {
				alert('エラー: ' + response.data.message);
			}
		}).always(function() {
			$btn.prop('disabled', false).text('復元');
		});
	});

	$('#probonoseo-reset-pro').on('click', function() {
		if (confirm('Pro版の設定をすべてリセットします。この操作は取り消せません。続行しますか？')) {
			var form = $('<form method="POST">' +
				'<input type="hidden" name="probonoseo_reset_pro_settings" value="1">' +
				'<?php wp_nonce_field("probonoseo_manage_action", "probonoseo_manage_nonce"); ?>' +
				'</form>');
			$('body').append(form);
			form.submit();
		}
	});

	$('#probonoseo-reset-all').on('click', function() {
		if (confirm('すべての設定をリセットします。この操作は取り消せません。続行しますか？')) {
			var form = $('<form method="POST">' +
				'<input type="hidden" name="probonoseo_reset_settings" value="1">' +
				'<?php wp_nonce_field("probonoseo_manage_action", "probonoseo_manage_nonce"); ?>' +
				'</form>');
			$('body').append(form);
			form.submit();
		}
	});
});
</script>
<?php endif; ?>