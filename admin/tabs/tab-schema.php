<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">schema / パンくず</h2>
	<p class="probonoseo-section-description">Googleリッチリザルト対応の構造化データを出力します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">schema.org（WebSite / WebPage）</h3>
					<p class="probonoseo-card-description">検索エンジンに対し、ページの意味情報を伝えます。リッチリザルト対応のための重要な構造化データを自動出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_schema', 'schema.org', true, false, 'schema.orgを無効にすると検索エンジンに意味情報が伝わりにくくなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">パンくず（BreadcrumbList）</h3>
					<p class="probonoseo-card-description">サイト階層を分かりやすく示し、ユーザーと検索エンジンの両方に貢献します。内部リンクの整理にも大きく役立ちます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_breadcrumb', 'パンくず', true, false, 'パンくずを無効にすると内部構造の把握が難しくなります'); ?>
				</div>
			</div>
		</div>

	</div>
</div>