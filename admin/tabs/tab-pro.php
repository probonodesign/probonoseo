<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();

function probonoseo_pro_get_text_value($probonoseo_key) {
	$probonoseo_value = get_option($probonoseo_key, '');
	if ($probonoseo_value === '0' || $probonoseo_value === 0) {
		return '';
	}
	return $probonoseo_value;
}
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">Pro専用強化（Pro版）</h2>
	<p class="probonoseo-section-description">カスタム投稿タイプや高度なSEO設定を行います。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カスタム投稿タイプ対応</h3>
					<p class="probonoseo-card-description">カスタム投稿タイプ（CPT）の個別SEO設定を有効にします。各CPTの編集画面でタイトル・メタD・robotsを個別設定できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_cpt', 'カスタム投稿タイプ対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カスタムタクソノミー対応</h3>
					<p class="probonoseo-card-description">カスタムタクソノミーの個別SEO設定を有効にします。各タクソノミーの編集画面でタイトル・メタD・robotsを個別設定できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_taxonomy', 'カスタムタクソノミー対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">著者アーカイブSEO</h3>
					<p class="probonoseo-card-description">著者アーカイブページのSEO設定を有効にします。ユーザープロフィール画面でタイトル・メタD・robotsを個別設定できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_author', '著者アーカイブSEO', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日付アーカイブSEO</h3>
					<p class="probonoseo-card-description">日付アーカイブページ（年・月・日）のSEO設定を有効にします。タイトルテンプレートとrobots設定をカスタマイズできます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_date', '日付アーカイブSEO', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">404ページSEO</h3>
					<p class="probonoseo-card-description">404エラーページのSEO設定を有効にします。カスタムタイトルとメタディスクリプションを設定できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_404', '404ページSEO', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索結果ページSEO</h3>
					<p class="probonoseo-card-description">サイト内検索結果ページのSEO設定を有効にします。タイトルテンプレートとrobots設定をカスタマイズできます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_search', '検索結果ページSEO', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">添付ファイルページSEO</h3>
					<p class="probonoseo-card-description">添付ファイル（メディア）ページのSEO設定を有効にします。親投稿へのリダイレクトやrobots設定をカスタマイズできます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_attachment', '添付ファイルページSEO', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">robots.txt最適化</h3>
					<p class="probonoseo-card-description">robots.txtを最適化し、クローラーのアクセスを効率化します。サイトマップURLの自動追加や詳細な制御が可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_robots_txt', 'robots.txt最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">AMP対応</h3>
					<p class="probonoseo-card-description">AMPページのSEO最適化を有効にします。AMPプラグインと連携し、canonical・メタD・schemaを自動出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_amp', 'AMP対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA対応</h3>
					<p class="probonoseo-card-description">PWA（プログレッシブウェブアプリ）機能を有効にします。マニフェスト・Service Workerを自動生成し、アプリライクな体験を提供します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa', 'PWA対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Multisiteサポート</h3>
					<p class="probonoseo-card-description">WordPressマルチサイトネットワークに対応します。ネットワーク全体の設定管理やライセンス一括管理が可能になります。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_multisite', 'Multisiteサポート', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">REST API拡張</h3>
					<p class="probonoseo-card-description">REST API経由でSEO情報の取得・更新を可能にします。ヘッドレスCMSや外部アプリケーションとの連携に便利です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_rest_api', 'REST API拡張', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">WP-CLI対応</h3>
					<p class="probonoseo-card-description">WP-CLIコマンドでSEO設定を管理できます。設定のエクスポート・インポート、診断実行などをコマンドラインで操作可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_cli', 'WP-CLI対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Google Search Consoleデータ連携</h3>
					<p class="probonoseo-card-description">Google Search Consoleと連携し、検索パフォーマンスデータを管理画面内で確認できます。クエリ・ページ別のデータを取得します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_gsc', 'GSCデータ連携', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">パンくずカスタマイズ</h3>
					<p class="probonoseo-card-description">パンくずリストの表示形式や区切り文字をカスタマイズできます。デザインに合わせた柔軟な設定が可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_breadcrumb_customize', 'パンくずカスタマイズ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">パンくず除外設定</h3>
					<p class="probonoseo-card-description">特定のページやカテゴリをパンくずリストから除外できます。不要な階層を非表示にできます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_breadcrumb_exclude', 'パンくず除外', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Schema検証ツール</h3>
					<p class="probonoseo-card-description">出力される構造化データの構文を検証します。エラーや警告を事前に検出できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_schema_validator', 'Schema検証', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">リッチリザルトテスト連携</h3>
					<p class="probonoseo-card-description">Googleリッチリザルトテストと連携し、構造化データの適合性を確認できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_rich_results_test', 'リッチリザルトテスト', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトマップ除外設定</h3>
					<p class="probonoseo-card-description">特定のページや投稿タイプをサイトマップから除外できます。noindex設定と連動した除外も可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_exclude', 'サイトマップ除外', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">HTMLサイトマップ生成</h3>
					<p class="probonoseo-card-description">訪問者向けのHTMLサイトマップページを自動生成します。ショートコードでの埋め込みにも対応。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_html', 'HTMLサイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像サイトマップ生成</h3>
					<p class="probonoseo-card-description">画像専用のサイトマップを生成し、画像検索での表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_image', '画像サイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">動画サイトマップ生成</h3>
					<p class="probonoseo-card-description">動画専用のサイトマップを生成し、動画検索での表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_video', '動画サイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ニュースサイトマップ生成</h3>
					<p class="probonoseo-card-description">Google News向けのニュースサイトマップを生成します。ニュースサイト運営に最適です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_news', 'ニュースサイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">hreflangサイトマップ対応</h3>
					<p class="probonoseo-card-description">多言語サイト向けのhreflang属性をサイトマップに追加します。国際SEOに対応。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sitemap_hreflang', 'hreflang対応', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">404ページ タイトル設定</h3>
					<p class="probonoseo-card-description">404エラーページに表示されるタイトルタグを設定します。空欄の場合はデフォルト値が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_404_title_enabled', '404タイトル設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_404_title_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_404_title" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_404_title')); ?>" placeholder="例: ページが見つかりません | サイト名">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">404ページ メタディスクリプション設定</h3>
					<p class="probonoseo-card-description">404エラーページのメタディスクリプションを設定します。SNSシェア時に使用されることがあります。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_404_desc_enabled', '404メタD設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_404_desc_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<textarea name="probonoseo_pro_404_desc" rows="2" placeholder="例: お探しのページは見つかりませんでした。URLをご確認ください。"><?php echo esc_textarea(probonoseo_pro_get_text_value('probonoseo_pro_404_desc')); ?></textarea>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索結果ページ タイトル設定</h3>
					<p class="probonoseo-card-description">サイト内検索結果ページのタイトルテンプレートを設定します。{query}は検索キーワードに置換されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_search_title_enabled', '検索タイトル設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_search_title_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_search_title" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_search_title')); ?>" placeholder="例: {query}の検索結果 | サイト名">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索結果ページ robots設定</h3>
					<p class="probonoseo-card-description">検索結果ページのインデックス設定を行います。重複コンテンツ防止のため、noindexが推奨されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_search_robots_enabled', '検索robots設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_search_robots_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_search_robots = probonoseo_pro_get_text_value('probonoseo_pro_search_robots'); ?>
				<select name="probonoseo_pro_search_robots">
					<option value="noindex, nofollow" <?php selected($probonoseo_search_robots === '' || $probonoseo_search_robots === 'noindex, nofollow', true); ?>>noindex, nofollow（推奨）</option>
					<option value="noindex" <?php selected($probonoseo_search_robots, 'noindex'); ?>>noindex</option>
					<option value="index" <?php selected($probonoseo_search_robots, 'index'); ?>>指定なし</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日付アーカイブ タイトル設定</h3>
					<p class="probonoseo-card-description">日付アーカイブページのタイトルテンプレートを設定します。{year} {month} {day} {site_name}が使用可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_date_title_enabled', '日付タイトル設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_date_title_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_date_title" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_date_title')); ?>" placeholder="例: {year}年{month}月の記事一覧 | {site_name}">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日付アーカイブ robots設定</h3>
					<p class="probonoseo-card-description">日付アーカイブページのインデックス設定を行います。SEO効果が薄いため、noindexが推奨されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_date_robots_enabled', '日付robots設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_date_robots_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_date_robots = probonoseo_pro_get_text_value('probonoseo_pro_date_robots'); ?>
				<select name="probonoseo_pro_date_robots">
					<option value="noindex" <?php selected($probonoseo_date_robots === '' || $probonoseo_date_robots === 'noindex', true); ?>>noindex（推奨）</option>
					<option value="noindex, nofollow" <?php selected($probonoseo_date_robots, 'noindex, nofollow'); ?>>noindex, nofollow</option>
					<option value="index" <?php selected($probonoseo_date_robots, 'index'); ?>>指定なし</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">添付ファイル リダイレクト設定</h3>
					<p class="probonoseo-card-description">添付ファイルページへのアクセス時に親投稿へリダイレクトするかを設定します。SEO的にはリダイレクトが推奨されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_attachment_redirect_enabled', '添付リダイレクト', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_attachment_redirect_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_attachment_redirect = probonoseo_pro_get_text_value('probonoseo_pro_attachment_redirect'); ?>
				<select name="probonoseo_pro_attachment_redirect">
					<option value="1" <?php selected($probonoseo_attachment_redirect === '' || $probonoseo_attachment_redirect === '1', true); ?>>親投稿にリダイレクト（推奨）</option>
					<option value="0" <?php selected($probonoseo_attachment_redirect, '0'); ?>>リダイレクトしない</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">添付ファイル robots設定</h3>
					<p class="probonoseo-card-description">添付ファイルページのインデックス設定を行います。リダイレクトしない場合に適用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_attachment_robots_enabled', '添付robots設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_attachment_robots_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_attachment_robots = probonoseo_pro_get_text_value('probonoseo_pro_attachment_robots'); ?>
				<select name="probonoseo_pro_attachment_robots">
					<option value="noindex" <?php selected($probonoseo_attachment_robots === '' || $probonoseo_attachment_robots === 'noindex', true); ?>>noindex（推奨）</option>
					<option value="noindex, nofollow" <?php selected($probonoseo_attachment_robots, 'noindex, nofollow'); ?>>noindex, nofollow</option>
					<option value="index" <?php selected($probonoseo_attachment_robots, 'index'); ?>>指定なし</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA アプリ名設定</h3>
					<p class="probonoseo-card-description">PWAとしてインストールされた際に表示されるアプリ名を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa_app_name_enabled', 'PWAアプリ名', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_pwa_app_name_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_pwa_app_name" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_pwa_app_name')); ?>" placeholder="例: マイサイト">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA 短縮名設定</h3>
					<p class="probonoseo-card-description">ホーム画面のアイコン下に表示される短縮名を設定します。12文字以内で設定してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa_short_name_enabled', 'PWA短縮名', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_pwa_short_name_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_pwa_short_name" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_pwa_short_name')); ?>" maxlength="12" placeholder="例: マイサイト">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA テーマカラー設定</h3>
					<p class="probonoseo-card-description">ブラウザのアドレスバーやタスクバーの色を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa_theme_color_enabled', 'PWAテーマカラー', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_pwa_theme_color_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_pwa_theme_color" id="pwa_theme_color" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_pwa_theme_color')); ?>" placeholder="例: #4a90e2">
				<div class="probonoseo-color-samples">
					<span class="probonoseo-color-sample" data-color="#4a90e2" title="#4a90e2"></span>
					<span class="probonoseo-color-sample" data-color="#22c55e" title="#22c55e"></span>
					<span class="probonoseo-color-sample" data-color="#ef4444" title="#ef4444"></span>
					<span class="probonoseo-color-sample" data-color="#f59e0b" title="#f59e0b"></span>
					<span class="probonoseo-color-sample" data-color="#8b5cf6" title="#8b5cf6"></span>
					<span class="probonoseo-color-sample" data-color="#ec4899" title="#ec4899"></span>
					<span class="probonoseo-color-sample" data-color="#06b6d4" title="#06b6d4"></span>
					<span class="probonoseo-color-sample" data-color="#000000" title="#000000"></span>
					<span class="probonoseo-color-sample" data-color="#ffffff" title="#ffffff"></span>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA 背景色設定</h3>
					<p class="probonoseo-card-description">アプリ起動時のスプラッシュスクリーンの背景色を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa_bg_color_enabled', 'PWA背景色', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_pwa_bg_color_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_pro_pwa_bg_color" id="pwa_bg_color" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_pwa_bg_color')); ?>" placeholder="例: #ffffff">
				<div class="probonoseo-color-samples">
					<span class="probonoseo-color-sample" data-color="#ffffff" title="#ffffff"></span>
					<span class="probonoseo-color-sample" data-color="#f8fafc" title="#f8fafc"></span>
					<span class="probonoseo-color-sample" data-color="#f1f5f9" title="#f1f5f9"></span>
					<span class="probonoseo-color-sample" data-color="#e2e8f0" title="#e2e8f0"></span>
					<span class="probonoseo-color-sample" data-color="#1e293b" title="#1e293b"></span>
					<span class="probonoseo-color-sample" data-color="#0f172a" title="#0f172a"></span>
					<span class="probonoseo-color-sample" data-color="#000000" title="#000000"></span>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PWA アイコンURL設定</h3>
					<p class="probonoseo-card-description">ホーム画面に表示されるアイコン画像のURLを設定します。192x192ピクセル以上のPNG画像を推奨します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_pwa_icon_enabled', 'PWAアイコン', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_pro_pwa_icon_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="url" name="probonoseo_pro_pwa_icon" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_pro_pwa_icon')); ?>" placeholder="例: https://example.com/icon-192.png">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Search Console クライアントID設定</h3>
					<p class="probonoseo-card-description">Google Cloud ConsoleのOAuth 2.0クライアントIDを入力します。Search Console APIの認証に使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_gsc_client_id_enabled', 'GSCクライアントID', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_gsc_client_id_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_gsc_client_id" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_gsc_client_id')); ?>" placeholder="例: 123456789-xxxxx.apps.googleusercontent.com">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Search Console クライアントシークレット設定</h3>
					<p class="probonoseo-card-description">Google Cloud ConsoleのOAuth 2.0クライアントシークレットを入力します。安全に保管してください。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_gsc_client_secret_enabled', 'GSCシークレット', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_gsc_client_secret_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="password" name="probonoseo_gsc_client_secret" value="<?php echo esc_attr(probonoseo_pro_get_text_value('probonoseo_gsc_client_secret')); ?>" placeholder="クライアントシークレットを入力">
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('.probonoseo-color-sample').on('click', function() {
		var probonoseo_color = $(this).data('color');
		var $probonoseo_input = $(this).closest('.probonoseo-card-detail').find('input[type="text"]');
		$probonoseo_input.val(probonoseo_color);
	});
});
</script>