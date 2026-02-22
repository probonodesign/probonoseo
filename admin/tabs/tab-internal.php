<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">内部リンク最適化</h2>
	<p class="probonoseo-section-description">サイト内のリンク構造と表示速度を最適化します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">前後記事リンク強化</h3>
					<p class="probonoseo-card-description">投稿の前後記事へのリンクを最適化します。関連性の高い記事への導線を強化し、サイト内回遊率を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_prev_next', '前後記事リンク', true, false, '前後記事リンクを無効にすると回遊率が低下します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カテゴリ内リンク最適化</h3>
					<p class="probonoseo-card-description">同一カテゴリ内の記事へのリンクを自動生成します。関連記事の発見性を高め、SEO評価を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_category', 'カテゴリ内リンク', true, false, 'カテゴリ内リンクを無効にすると関連性が伝わりにくくなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">子ページ判定</h3>
					<p class="probonoseo-card-description">親ページから子ページへの適切なリンク構造を自動生成します。サイト階層を検索エンジンに正確に伝えます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_child_pages', '子ページ判定', true, false, '子ページ判定を無効にすると階層構造が伝わりにくくなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">関連記事自動抽出</h3>
					<p class="probonoseo-card-description">記事内容から関連性の高い記事を自動抽出します。タグ・カテゴリ・キーワードの一致度で判定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_related', '関連記事抽出', true, false, '関連記事抽出を無効にすると関連記事が表示されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タグ判断ロジック</h3>
					<p class="probonoseo-card-description">記事のタグ情報を元に関連記事を判定します。共通タグが多い記事を優先的に表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_tag_logic', 'タグ判断', true, false, 'タグ判断を無効にすると関連性判定の精度が低下します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">内部リンクのnofollow制御</h3>
					<p class="probonoseo-card-description">内部リンクに不要なnofollow属性が付与されていないかチェックします。SEO評価の流れを最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_nofollow', 'nofollow制御', true, false, 'nofollow制御を無効にするとリンク評価が適切に伝わりません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カテゴリ名整形</h3>
					<p class="probonoseo-card-description">カテゴリ名を日本語環境に最適化します。長すぎるカテゴリ名を省略し、表示を整えます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_internal_category_format', 'カテゴリ名整形', true, false, 'カテゴリ名整形を無効にすると表示が崩れる可能性があります'); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">表示速度最適化</h2>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">遅延読み込み（画像）</h3>
					<p class="probonoseo-card-description">画像の遅延読み込み（Lazy Load）を有効化します。ページ表示速度を大幅に改善し、Core Web Vitalsのスコアを向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_lazy_images', '画像遅延読み込み', true, false, '画像遅延読み込みを無効にするとページ表示が遅くなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">遅延読み込み（iframe）</h3>
					<p class="probonoseo-card-description">埋め込み動画やマップなどのiframeを遅延読み込みします。YouTubeやGoogleマップの表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_lazy_iframes', 'iframe遅延読み込み', true, false, 'iframe遅延読み込みを無効にすると埋め込みコンテンツが重くなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">CSS縮小</h3>
					<p class="probonoseo-card-description">CSSファイルを圧縮し、ファイルサイズを削減します。ページ読み込み速度を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_minify_css', 'CSS縮小', true, false, 'CSS縮小を無効にするとファイルサイズが増加します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">JS縮小（安全範囲）</h3>
					<p class="probonoseo-card-description">JavaScriptファイルを安全に圧縮します。サイトの動作を維持しながらファイルサイズを削減します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_minify_js', 'JS縮小', true, false, 'JS縮小を無効にするとファイルサイズが増加します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">WP標準スクリプト安全化</h3>
					<p class="probonoseo-card-description">WordPress標準のjQuery、emoji、embed.jsなどを最適化します。不要なスクリプトを削減し、速度を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_optimize_wp_scripts', 'WPスクリプト最適化', true, false, 'WPスクリプト最適化を無効にすると不要なスクリプトが残ります'); ?>
				</div>
			</div>
		</div>

	</div>
</div>