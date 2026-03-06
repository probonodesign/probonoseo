<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">メタディスクリプション</h2>
	<p class="probonoseo-section-description">検索結果に表示される説明文の自動生成と最適化を行います。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">メタディスクリプション自動生成</h3>
					<p class="probonoseo-card-description">本文から自然な説明文（メタディスクリプション）を自動生成します。検索結果での視認性とクリック率向上を目的としています。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_metadesc', 'メタディスクリプション', true, false, 'メタディスクリプションを無効にすると説明文が最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">本文抽出による自動生成</h3>
					<p class="probonoseo-card-description">記事本文の冒頭部分から自然な説明文を抽出します。要約欄が空欄の場合でも適切なメタディスクリプションを自動生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_extraction', '本文抽出', true, false, '本文抽出を無効にすると説明文が生成されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">重要語優先抽出</h3>
					<p class="probonoseo-card-description">本文からキーワードを含む重要な文章を優先的に抽出します。検索クエリとの関連性を高め、クリック率を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_keywords', '重要語優先', true, false, '重要語優先を無効にすると関連性が低下します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日本語簡易要約</h3>
					<p class="probonoseo-card-description">長文を自然な日本語で簡潔に要約します。文末処理や接続詞の最適化により、読みやすい説明文を生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_summary', '日本語要約', true, false, '日本語要約を無効にすると説明文が不自然になります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">禁止ワード除去</h3>
					<p class="probonoseo-card-description">メタディスクリプションから不適切な単語や記号を自動除去します。検索結果での表示品質を維持します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_forbidden', '禁止ワード除去', true, false, '禁止ワード除去を無効にすると不適切な文字が残ります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">文字数最適化（70〜120）</h3>
					<p class="probonoseo-card-description">メタディスクリプションの文字数を70〜120文字に自動調整します。検索結果で省略されずに全文表示される長さに最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_length', '文字数最適化', true, false, '文字数最適化を無効にすると表示が途切れる可能性があります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">重複メタDチェック</h3>
					<p class="probonoseo-card-description">同一のメタディスクリプションが複数ページで使用されていないかチェックします。重複がある場合は警告を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_meta_duplicate', '重複チェック', true, false, '重複チェックを無効にすると重複に気づけません'); ?>
				</div>
			</div>
		</div>

	</div>
</div>