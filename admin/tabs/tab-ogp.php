<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-section">
	<h2 class="probonoseo-section-title">OGP / Twitter</h2>
	<p class="probonoseo-section-description">SNSでシェアされた際の表示内容を最適化します。<br><strong>※「OGP 自動出力」と「Twitterカード 自動出力」はデフォルトで無効です。</strong>テーマや他のSEOプラグインと重複する可能性があるため、必要に応じて有効化してください。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">OGP 自動出力</h3>
					<p class="probonoseo-card-description">SNSシェア時のタイトル・説明文・画像を最適化して出力します。Facebook / X（Twitter）/ LINE などで統一した見た目を実現します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_ogp', 'OGP', false, false, 'テーマや他プラグインとの重複を避けるためデフォルト無効。必要に応じて有効化してください'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">OGP title 自動生成</h3>
					<p class="probonoseo-card-description">SNSシェア用のタイトルを自動生成します。通常のタイトルとは異なる、SNS向けに最適化されたタイトルを出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_title', 'OGP title', true, false, 'OGP titleを無効にするとタイトルが最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">OGP description 最適化</h3>
					<p class="probonoseo-card-description">SNSシェア用の説明文を最適化します。プラットフォームごとの文字数制限に合わせた説明文を自動生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_desc', 'OGP description', true, false, 'OGP descriptionを無効にすると説明文が最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">OG画像自動選択</h3>
					<p class="probonoseo-card-description">記事内の画像から最適なOG画像を自動選択します。アイキャッチ画像 → 本文最初の画像の順で選択します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_image_auto', 'OG画像自動選択', true, false, 'OG画像自動選択を無効にすると画像が表示されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">固定OGP画像設定</h3>
					<p class="probonoseo-card-description">サイト全体で使用する固定のOGP画像を設定できます。アイキャッチがない記事でも統一した画像を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_image_fixed', '固定OGP画像', false, false, 'デフォルト無効：画像未設定時の表示エラーを防ぐため。有効化後、固定画像をアップロードしてください'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Facebook互換最適化</h3>
					<p class="probonoseo-card-description">Facebook独自の仕様に対応した最適化を行います。Facebookシェア時の表示品質を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_facebook', 'Facebook最適化', true, false, 'Facebook最適化を無効にするとFacebook上での表示が最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">LINEプレビュー整形</h3>
					<p class="probonoseo-card-description">LINE上でのリンクプレビュー表示を最適化します。LINE特有の表示制限に対応した整形を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_line', 'LINEプレビュー', true, false, 'LINEプレビューを無効にするとLINE上での表示が最適化されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サムネイル取得最適化</h3>
					<p class="probonoseo-card-description">画像サムネイルの取得を最適化し、読み込み速度を向上させます。適切なサイズの画像を自動選択します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_thumbnail', 'サムネイル最適化', true, false, 'サムネイル最適化を無効にすると画像読み込みが遅くなります'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像の大きさ自動判定</h3>
					<p class="probonoseo-card-description">OG画像の推奨サイズ（1200x630px）を自動判定し、適切でない場合は警告を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_size_detect', '画像サイズ判定', true, false, '画像サイズ判定を無効にすると不適切なサイズに気づけません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">altテキスト優先</h3>
					<p class="probonoseo-card-description">OG画像の説明文として画像のaltテキストを優先的に使用します。アクセシビリティとSEOの両方を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_alt', 'altテキスト優先', true, false, 'altテキスト優先を無効にすると画像の説明が不足します'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日本語URL変換対応</h3>
					<p class="probonoseo-card-description">日本語を含むURLをエンコードして出力します。SNS上での正しいリンク表示を保証します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_ogp_japanese_url', '日本語URL対応', true, false, '日本語URL対応を無効にするとリンクが正しく表示されません'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Twitterカード 自動出力</h3>
					<p class="probonoseo-card-description">X（旧Twitter）上でカード形式のリンクプレビューを提供します。最適化されたOG情報でプラットフォームごとの表示崩れを防止します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_basic_twitter', 'Twitterカード', false, false, 'テーマや他プラグインとの重複を避けるためデフォルト無効。必要に応じて有効化してください'); ?>
				</div>
			</div>
		</div>

	</div>
</div>