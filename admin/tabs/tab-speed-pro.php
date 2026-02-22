<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">速度改善Pro（Pro版）</h2>
	<p class="probonoseo-section-description">画像最適化やキャッシュなど高度な速度改善を行います。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像WebP自動変換</h3>
					<p class="probonoseo-card-description">アップロードされた画像を自動的にWebP形式に変換します。ファイルサイズを大幅に削減し、ページ読み込み速度を向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_webp', '画像WebP自動変換', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像AVIF対応</h3>
					<p class="probonoseo-card-description">次世代フォーマットAVIFへの変換に対応します。WebPよりもさらに高い圧縮率を実現し、対応ブラウザで自動配信します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_avif', '画像AVIF対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像圧縮（ロスレス/非可逆）</h3>
					<p class="probonoseo-card-description">画像のアップロード時に自動圧縮を行います。品質を維持しながらファイルサイズを最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_compress', '画像圧縮', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">レスポンシブ画像自動生成</h3>
					<p class="probonoseo-card-description">様々な画面サイズに対応したレスポンシブ画像を自動生成します。srcset属性を最適化し、デバイスに応じた画像を配信します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_responsive', 'レスポンシブ画像', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">CSSインライン化（重要部分）</h3>
					<p class="probonoseo-card-description">ファーストビューに必要なクリティカルCSSをインライン化します。レンダリングブロックを削減し、初期表示を高速化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_css_inline', 'CSSインライン化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">JSインライン化（重要部分）</h3>
					<p class="probonoseo-card-description">初期表示に必要な小さなJavaScriptをインライン化します。HTTPリクエスト数を削減し、パフォーマンスを向上させます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_js_inline', 'JSインライン化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">フォント最適化（サブセット化）</h3>
					<p class="probonoseo-card-description">Webフォントを使用文字のみにサブセット化します。日本語フォントのファイルサイズを大幅に削減します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_fonts', 'フォント最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">DNS Prefetch/Preconnect設定</h3>
					<p class="probonoseo-card-description">外部リソースへのDNS解決とコネクション確立を事前に行います。サードパーティリソースの読み込みを高速化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_dns', 'DNS Prefetch', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">リソースヒント最適化</h3>
					<p class="probonoseo-card-description">preload、prefetch、prerender等のリソースヒントを最適化します。重要なリソースの優先読み込みを制御します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_hints', 'リソースヒント', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">データベースクエリ最適化</h3>
					<p class="probonoseo-card-description">WordPressのデータベースクエリを最適化します。不要なクエリの削減とインデックス最適化でサーバー負荷を軽減します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_db', 'DBクエリ最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">オブジェクトキャッシュ実装</h3>
					<p class="probonoseo-card-description">データベースクエリ結果をメモリにキャッシュします。繰り返しのクエリを削減し、レスポンス時間を短縮します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_object_cache', 'オブジェクトキャッシュ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ページキャッシュ実装</h3>
					<p class="probonoseo-card-description">生成されたHTMLページをキャッシュします。PHP処理をスキップし、静的ファイルとして高速配信します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_page_cache', 'ページキャッシュ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像圧縮品質設定</h3>
					<p class="probonoseo-card-description">WebP/AVIF変換時の画像品質を設定します。数値が高いほど高品質ですが、ファイルサイズも大きくなります。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_compress_quality_enabled', '圧縮品質設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_compress_quality_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_compress_quality = get_option('probonoseo_speed_pro_compress_quality', '82'); ?>
				<select name="probonoseo_speed_pro_compress_quality">
					<option value="60" <?php selected($probonoseo_compress_quality, '60'); ?>>60（高圧縮）</option>
					<option value="70" <?php selected($probonoseo_compress_quality, '70'); ?>>70</option>
					<option value="82" <?php selected($probonoseo_compress_quality, '82'); ?>>82（推奨）</option>
					<option value="90" <?php selected($probonoseo_compress_quality, '90'); ?>>90（高品質）</option>
					<option value="100" <?php selected($probonoseo_compress_quality, '100'); ?>>100（ロスレス）</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">レスポンシブ画像サイズ設定</h3>
					<p class="probonoseo-card-description">自動生成するレスポンシブ画像の幅を設定します。カンマ区切りで複数指定できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_responsive_sizes_enabled', 'サイズ設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_responsive_sizes_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_speed_pro_responsive_sizes" value="<?php echo esc_attr(get_option('probonoseo_speed_pro_responsive_sizes', '320,640,768,1024,1280,1536')); ?>" placeholder="例: 320,640,768,1024,1280,1536">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">DNS Prefetchドメイン設定</h3>
					<p class="probonoseo-card-description">DNS Prefetchを行う外部ドメインを設定します。1行に1ドメインを記入してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_dns_domains_enabled', 'Prefetchドメイン', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_dns_domains_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<textarea name="probonoseo_speed_pro_dns_domains" rows="4" placeholder="例:&#10;fonts.googleapis.com&#10;www.google-analytics.com&#10;cdn.example.com"><?php echo esc_textarea(get_option('probonoseo_speed_pro_dns_domains', '')); ?></textarea>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Preconnectドメイン設定</h3>
					<p class="probonoseo-card-description">Preconnectを行う外部ドメインを設定します。重要なサードパーティリソースのドメインを指定してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_preconnect_domains_enabled', 'Preconnectドメイン', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_preconnect_domains_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<textarea name="probonoseo_speed_pro_preconnect_domains" rows="4" placeholder="例:&#10;fonts.gstatic.com&#10;www.googletagmanager.com"><?php echo esc_textarea(get_option('probonoseo_speed_pro_preconnect_domains', '')); ?></textarea>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ページキャッシュ有効期限設定</h3>
					<p class="probonoseo-card-description">ページキャッシュの有効期限を設定します。頻繁に更新するサイトは短めに設定してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_cache_expiry_enabled', 'キャッシュ期限', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_cache_expiry_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_cache_expiry = get_option('probonoseo_speed_pro_cache_expiry', '3600'); ?>
				<select name="probonoseo_speed_pro_cache_expiry">
					<option value="1800" <?php selected($probonoseo_cache_expiry, '1800'); ?>>30分</option>
					<option value="3600" <?php selected($probonoseo_cache_expiry, '3600'); ?>>1時間（推奨）</option>
					<option value="7200" <?php selected($probonoseo_cache_expiry, '7200'); ?>>2時間</option>
					<option value="21600" <?php selected($probonoseo_cache_expiry, '21600'); ?>>6時間</option>
					<option value="43200" <?php selected($probonoseo_cache_expiry, '43200'); ?>>12時間</option>
					<option value="86400" <?php selected($probonoseo_cache_expiry, '86400'); ?>>24時間</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">キャッシュ除外URL設定</h3>
					<p class="probonoseo-card-description">ページキャッシュから除外するURLパターンを設定します。1行に1パターンを記入してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_speed_pro_cache_exclude_enabled', 'キャッシュ除外', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_speed_pro_cache_exclude_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<textarea name="probonoseo_speed_pro_cache_exclude" rows="4" placeholder="例:&#10;/cart/&#10;/checkout/&#10;/my-account/"><?php echo esc_textarea(get_option('probonoseo_speed_pro_cache_exclude', '')); ?></textarea>
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>