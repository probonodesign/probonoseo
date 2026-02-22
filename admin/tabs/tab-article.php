<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">記事SEO</h2>
	<p class="probonoseo-section-description">記事単位でのSEO品質をチェックし、改善点を提示します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">見出し階層チェック</h3>
					<p class="probonoseo-card-description">H1〜H6の見出しタグが正しい階層で使用されているかチェックします。SEO上重要な見出し構造を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_heading_check', '見出し階層チェック', true, false, '見出し階層チェックを無効にすると構造の問題に気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">alt不足チェック</h3>
					<p class="probonoseo-card-description">画像のalt属性が設定されているかチェックします。アクセシビリティとSEOの両方を改善します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_alt_check', 'alt不足チェック', true, false, 'alt不足チェックを無効にすると画像SEOが最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像数チェック</h3>
					<p class="probonoseo-card-description">記事内の画像数が適切かチェックします。画像が少なすぎる、または多すぎる場合に警告を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_image_count', '画像数チェック', true, false, '画像数チェックを無効にすると適切な画像数に気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">文字数チェック</h3>
					<p class="probonoseo-card-description">記事の文字数が適切かチェックします。SEOに最適な文字数（1000文字以上）を推奨します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_word_count', '文字数チェック', true, false, '文字数チェックを無効にすると適切な文字数に気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カテゴリ一致性</h3>
					<p class="probonoseo-card-description">記事内容とカテゴリの一致性をチェックします。適切なカテゴリ分類でSEO評価を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_category_match', 'カテゴリ一致性', true, false, 'カテゴリ一致性を無効にすると分類の問題に気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タグ重複防止</h3>
					<p class="probonoseo-card-description">同じタグが複数回付けられていないかチェックします。タグの整理でSEO効果を最大化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_article_tag_duplicate', 'タグ重複防止', true, false, 'タグ重複防止を無効にすると重複タグに気づけません'); ?>
				</div>
			</div>
		</div>

	</div>
</div>