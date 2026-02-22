<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_is_locked = !$probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">投稿単位SEO設定 <span class="probonoseo-pro-label">Pro</span></h2>
	<p class="probonoseo-section-description">投稿編集画面に表示されるSEO分析・最適化機能の設定です。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SEOメタボックス表示</h3>
					<p class="probonoseo-card-description">投稿編集画面にSEO分析メタボックスを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_metabox', 'SEOメタボックス', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SEOスコア表示</h3>
					<p class="probonoseo-card-description">0〜100点のSEOスコアを円グラフで表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_score', 'SEOスコア', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タイトルプレビュー</h3>
					<p class="probonoseo-card-description">SEOタイトルの文字数と最適化状況をチェックします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_title_preview', 'タイトルプレビュー', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">メタディスクリプションプレビュー</h3>
					<p class="probonoseo-card-description">メタディスクリプションの文字数と最適化状況をチェックします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_meta_preview', 'メタDプレビュー', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索結果プレビュー（Google風）</h3>
					<p class="probonoseo-card-description">Google検索結果での表示をデスクトップ・モバイル両方でプレビューします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_serp_preview', 'SERPプレビュー', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SNSプレビュー（Facebook/Twitter）</h3>
					<p class="probonoseo-card-description">FacebookとX（Twitter）でシェアされた際の表示をプレビューします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_social_preview', 'SNSプレビュー', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">フォーカスキーワード設定</h3>
					<p class="probonoseo-card-description">記事ごとにメインキーワードを設定し、最適化チェックに使用します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_focus_keyword', 'フォーカスキーワード', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">キーワード密度チェック</h3>
					<p class="probonoseo-card-description">本文内のキーワード出現率を計算し、適切な範囲かチェックします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_keyword_density', 'キーワード密度', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">内部リンク提案</h3>
					<p class="probonoseo-card-description">キーワードに関連する記事を検索し、内部リンク候補を提案します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_internal_links', '内部リンク提案', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">外部リンクチェック</h3>
					<p class="probonoseo-card-description">記事内の外部リンク数をカウントし、表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_external_links', '外部リンクチェック', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像alt属性チェック</h3>
					<p class="probonoseo-card-description">画像のalt属性設定状況をチェックし、未設定の画像を警告します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_image_alt', '画像altチェック', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">見出し構造チェック</h3>
					<p class="probonoseo-card-description">H2/H3の階層構造が適切かチェックします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_heading_structure', '見出し構造', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">本文文字数チェック</h3>
					<p class="probonoseo-card-description">本文の文字数をカウントし、推奨文字数の目安を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_word_count', '文字数チェック', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">読了時間表示</h3>
					<p class="probonoseo-card-description">記事の推定読了時間を算出して表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_read_time', '読了時間', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">コンテンツスコア</h3>
					<p class="probonoseo-card-description">コンテンツの品質をスコア化して表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_content_score', 'コンテンツスコア', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">改善提案リスト</h3>
					<p class="probonoseo-card-description">具体的なSEO改善点をリスト形式で表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_suggestions', '改善提案', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SEOチェックリスト</h3>
					<p class="probonoseo-card-description">SEOチェック項目の一覧を表示し、合否を判定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_checklist', 'チェックリスト', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">公開前SEO警告</h3>
					<p class="probonoseo-card-description">SEOスコアが低い場合、公開前に警告を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_publish_warning', '公開前警告', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

	</div>

	<h3 class="probonoseo-section-title" style="margin-top: 40px; font-size: 18px;">対象投稿タイプ</h3>
	<p class="probonoseo-section-description">SEOメタボックスを表示する投稿タイプを選択します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">投稿（post）</h3>
					<p class="probonoseo-card-description">通常のブログ投稿にSEOメタボックスを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_type_post', '投稿', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">固定ページ（page）</h3>
					<p class="probonoseo-card-description">固定ページにSEOメタボックスを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_type_page', '固定ページ', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カスタム投稿タイプ</h3>
					<p class="probonoseo-card-description">公開設定のカスタム投稿タイプにもSEOメタボックスを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_post_seo_type_custom', 'カスタム投稿', false, $probonoseo_is_locked, 'Pro版が必要です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>