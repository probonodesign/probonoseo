<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">タイトル最適化</h2>
	<p class="probonoseo-section-description">検索結果に表示されるタイトルタグの最適化設定です。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タイトル最適化</h3>
					<p class="probonoseo-card-description">検索で読みやすくクリックされやすいタイトルを自動で整えます。記事タイトルを検索向けに調整し、CTR（クリック率）の向上を狙います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_title', 'タイトル最適化', true, false, 'タイトル最適化を無効にすると検索結果での訴求力が低下します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">区切り文字最適化</h3>
					<p class="probonoseo-card-description">タイトル内の区切り文字（- / | / ：）を最適化し、検索結果での視認性を向上させます。日本語タイトルに最適な区切り文字を自動選択します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_separator', '区切り文字最適化', true, false, '区切り文字の最適化を無効にすると視認性が低下する可能性があります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイト名付与ルール</h3>
					<p class="probonoseo-card-description">タイトル末尾へのサイト名付与を最適化します。ページ種別（投稿 / 固定ページ / アーカイブ）に応じて適切にサイト名を付与します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_sitename', 'サイト名付与', true, false, 'サイト名付与を無効にするとブランド認知が低下します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">H1整合チェック</h3>
					<p class="probonoseo-card-description">タイトルタグとH1タグの整合性をチェックします。両者が大きく異なる場合に警告を表示し、SEO上の問題を未然に防ぎます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_h1_check', 'H1整合チェック', true, false, 'H1整合チェックを無効にすると不整合に気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カテゴリ名付与調整</h3>
					<p class="probonoseo-card-description">アーカイブページのタイトルにカテゴリ名を付与する際の形式を最適化します。冗長な表現を避け、検索結果での視認性を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_category', 'カテゴリ名付与', true, false, 'カテゴリ名付与を無効にすると構造が伝わりにくくなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">重複タイトル自動回避</h3>
					<p class="probonoseo-card-description">同一タイトルの重複を検出し、自動的に番号や識別子を付与して重複を回避します。SEO上のペナルティリスクを軽減します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_duplicate', '重複回避', true, false, '重複回避を無効にするとSEO評価が分散します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">数字・記号の整理</h3>
					<p class="probonoseo-card-description">タイトル内の数字や記号を日本語環境に最適化します。全角・半角の統一、不要な記号の除去などを自動で行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_title_symbols', '数字・記号整理', true, false, '記号整理を無効にすると表示が不統一になります'); ?>
				</div>
			</div>
		</div>

	</div>
</div>