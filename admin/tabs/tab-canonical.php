<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">canonical</h2>
	<p class="probonoseo-section-description">重複コンテンツを防ぐためのcanonical URLの設定を行います。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">canonical 自動生成</h3>
					<p class="probonoseo-card-description">重複URLをまとめ、ページ評価の分散を防ぎます。検索エンジンに正式なURLを伝え、最適な評価を維持します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_canonical', 'canonical', true, false, 'canonicalを無効にすると重複ページ評価が分散します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">自動canonical生成</h3>
					<p class="probonoseo-card-description">すべてのページに対して適切なcanonical URLを自動生成します。手動設定不要で重複コンテンツ問題を防止します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_canonical_auto', '自動生成', true, false, '自動生成を無効にすると手動設定が必要になります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">URL最適化（スラッシュ整理）</h3>
					<p class="probonoseo-card-description">URL末尾のスラッシュを統一し、重複URLを防止します。サイト全体でURL形式を統一します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_canonical_slash', 'スラッシュ整理', true, false, 'スラッシュ整理を無効にするとURL重複が発生します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">重複URL統合</h3>
					<p class="probonoseo-card-description">同一コンテンツを指す複数のURLを1つに統合します。検索エンジンの評価を一箇所に集約します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_canonical_merge', 'URL統合', true, false, 'URL統合を無効にすると評価が分散します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">パラメータ除外</h3>
					<p class="probonoseo-card-description">URLパラメータ（?utm_source など）を除外したcanonical URLを生成します。トラッキングパラメータによる重複を防止します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_canonical_params', 'パラメータ除外', true, false, 'パラメータ除外を無効にすると不要な重複が発生します'); ?>
				</div>
			</div>
		</div>

	</div>
</div>