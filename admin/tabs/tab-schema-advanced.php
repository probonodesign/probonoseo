<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();

function probonoseo_get_schema_text_value($probonoseo_key) {
	$probonoseo_value = get_option($probonoseo_key, '');
	if ($probonoseo_value === '0' || $probonoseo_value === 0) {
		return '';
	}
	return $probonoseo_value;
}
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">schema高度版（Pro版）</h2>
	<p class="probonoseo-section-description">ソフトウェア、書籍、音楽など専門的な構造化データを出力します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SoftwareApplication schema</h3>
					<p class="probonoseo-card-description">アプリ・ソフトウェア情報を構造化データとして出力し、検索結果にアプリ情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_software', 'アプリ情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Course schema</h3>
					<p class="probonoseo-card-description">コース・講座情報を構造化データとして出力し、検索結果にコース情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_course', 'コース・講座', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Book schema</h3>
					<p class="probonoseo-card-description">書籍情報を構造化データとして出力し、検索結果に書籍情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_book', '書籍情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Movie schema</h3>
					<p class="probonoseo-card-description">映画情報を構造化データとして出力し、検索結果に映画情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_movie', '映画情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">MusicAlbum schema</h3>
					<p class="probonoseo-card-description">音楽アルバム情報を構造化データとして出力し、検索結果に音楽情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_music', '音楽情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Podcast schema</h3>
					<p class="probonoseo-card-description">ポッドキャスト情報を構造化データとして出力し、検索結果にポッドキャスト情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_podcast', 'ポッドキャスト', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Organization schema（拡張版）</h3>
					<p class="probonoseo-card-description">組織・会社情報を詳細に構造化データとして出力し、ナレッジパネル表示を促進します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_organization', '組織情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Person schema（拡張版）</h3>
					<p class="probonoseo-card-description">人物情報を詳細に構造化データとして出力し、著者情報やプロフィールを強化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_person', '人物情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Article schema（詳細版）</h3>
					<p class="probonoseo-card-description">記事情報を詳細に構造化データとして出力し、検索結果での表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_article', '記事詳細', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">NewsArticle schema</h3>
					<p class="probonoseo-card-description">ニュース記事を構造化データとして出力し、Google Newsやトップニュースへの表示を促進します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_news', 'ニュース記事', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">BlogPosting schema</h3>
					<p class="probonoseo-card-description">ブログ記事を構造化データとして出力し、検索結果での表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_blog', 'ブログ記事', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SpecialAnnouncement schema</h3>
					<p class="probonoseo-card-description">特別告知（緊急情報など）を構造化データとして出力し、重要情報を目立たせます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_announcement', '特別告知', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ImageObject schema（詳細版）</h3>
					<p class="probonoseo-card-description">画像情報を詳細に構造化データとして出力し、画像検索での表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_image', '画像詳細', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Dataset schema</h3>
					<p class="probonoseo-card-description">データセット情報を構造化データとして出力し、Google Dataset Searchでの表示を可能にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_dataset', 'データセット', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">AggregateRating schema</h3>
					<p class="probonoseo-card-description">集計評価を構造化データとして出力し、検索結果に星評価を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_rating', '集計評価', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ClaimReview schema</h3>
					<p class="probonoseo-card-description">ファクトチェック情報を構造化データとして出力し、検索結果にファクトチェックラベルを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_claim', 'ファクトチェック', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Speakable schema</h3>
					<p class="probonoseo-card-description">音声読み上げ対象コンテンツを指定し、Google Assistantでの読み上げを可能にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_speakable', '音声読み上げ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">schema自動選択</h3>
					<p class="probonoseo-card-description">投稿タイプ・カテゴリ・コンテンツ内容から最適なschemaを自動判定して出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_auto_select', '自動選択', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Software デフォルトカテゴリ設定</h3>
					<p class="probonoseo-card-description">SoftwareApplication schemaのデフォルトカテゴリを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_software_category_enabled', 'Softwareカテゴリ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_software_category_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_cat = probonoseo_get_schema_text_value('probonoseo_schema_software_category'); ?>
				<select name="probonoseo_schema_software_category">
					<option value="WebApplication" <?php selected($probonoseo_cat === '' || $probonoseo_cat === 'WebApplication', true); ?>>Webアプリ</option>
					<option value="MobileApplication" <?php selected($probonoseo_cat, 'MobileApplication'); ?>>モバイルアプリ</option>
					<option value="DesktopApplication" <?php selected($probonoseo_cat, 'DesktopApplication'); ?>>デスクトップアプリ</option>
					<option value="GameApplication" <?php selected($probonoseo_cat, 'GameApplication'); ?>>ゲーム</option>
					<option value="BusinessApplication" <?php selected($probonoseo_cat, 'BusinessApplication'); ?>>ビジネス</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Software デフォルトOS設定</h3>
					<p class="probonoseo-card-description">SoftwareApplication schemaのデフォルトOSを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_software_os_enabled', 'SoftwareOS', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_software_os_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_os = probonoseo_get_schema_text_value('probonoseo_schema_software_os'); ?>
				<select name="probonoseo_schema_software_os">
					<option value="Windows" <?php selected($probonoseo_os === '' || $probonoseo_os === 'Windows', true); ?>>Windows</option>
					<option value="macOS" <?php selected($probonoseo_os, 'macOS'); ?>>macOS</option>
					<option value="iOS" <?php selected($probonoseo_os, 'iOS'); ?>>iOS</option>
					<option value="Android" <?php selected($probonoseo_os, 'Android'); ?>>Android</option>
					<option value="Linux" <?php selected($probonoseo_os, 'Linux'); ?>>Linux</option>
					<option value="Web" <?php selected($probonoseo_os, 'Web'); ?>>Web</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Software 価格タイプ設定</h3>
					<p class="probonoseo-card-description">SoftwareApplication schemaのデフォルト価格タイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_software_price_type_enabled', 'Software価格', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_software_price_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_pt = probonoseo_get_schema_text_value('probonoseo_schema_software_price_type'); ?>
				<select name="probonoseo_schema_software_price_type">
					<option value="Free" <?php selected($probonoseo_pt === '' || $probonoseo_pt === 'Free', true); ?>>無料</option>
					<option value="Paid" <?php selected($probonoseo_pt, 'Paid'); ?>>有料</option>
					<option value="Freemium" <?php selected($probonoseo_pt, 'Freemium'); ?>>フリーミアム</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">デフォルト通貨設定</h3>
					<p class="probonoseo-card-description">価格表示で使用するデフォルト通貨を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_software_currency_enabled', 'デフォルト通貨', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_software_currency_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_cur = probonoseo_get_schema_text_value('probonoseo_schema_software_currency'); ?>
				<select name="probonoseo_schema_software_currency">
					<option value="JPY" <?php selected($probonoseo_cur === '' || $probonoseo_cur === 'JPY', true); ?>>日本円（JPY）</option>
					<option value="USD" <?php selected($probonoseo_cur, 'USD'); ?>>米ドル（USD）</option>
					<option value="EUR" <?php selected($probonoseo_cur, 'EUR'); ?>>ユーロ（EUR）</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Course デフォルト提供者名設定</h3>
					<p class="probonoseo-card-description">Course schemaのデフォルト提供者名を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_course_provider_enabled', 'Course提供者', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_course_provider_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_course_provider" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_course_provider')); ?>" placeholder="例: <?php echo esc_attr(get_bloginfo('name')); ?>">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Course デフォルト受講形式設定</h3>
					<p class="probonoseo-card-description">Course schemaのデフォルト受講形式を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_course_mode_enabled', 'Course形式', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_course_mode_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_mode = probonoseo_get_schema_text_value('probonoseo_schema_course_mode'); ?>
				<select name="probonoseo_schema_course_mode">
					<option value="Online" <?php selected($probonoseo_mode === '' || $probonoseo_mode === 'Online', true); ?>>オンライン</option>
					<option value="Onsite" <?php selected($probonoseo_mode, 'Onsite'); ?>>対面</option>
					<option value="Blended" <?php selected($probonoseo_mode, 'Blended'); ?>>ブレンド</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Book デフォルト形式設定</h3>
					<p class="probonoseo-card-description">Book schemaのデフォルト書籍形式を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_book_format_enabled', 'Book形式', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_book_format_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_fmt = probonoseo_get_schema_text_value('probonoseo_schema_book_format'); ?>
				<select name="probonoseo_schema_book_format">
					<option value="Paperback" <?php selected($probonoseo_fmt === '' || $probonoseo_fmt === 'Paperback', true); ?>>ペーパーバック</option>
					<option value="Hardcover" <?php selected($probonoseo_fmt, 'Hardcover'); ?>>ハードカバー</option>
					<option value="EBook" <?php selected($probonoseo_fmt, 'EBook'); ?>>電子書籍</option>
					<option value="AudioBook" <?php selected($probonoseo_fmt, 'AudioBook'); ?>>オーディオブック</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">デフォルト言語設定</h3>
					<p class="probonoseo-card-description">各種schemaのデフォルト言語を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_book_language_enabled', 'デフォルト言語', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_book_language_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_lang = probonoseo_get_schema_text_value('probonoseo_schema_book_language'); ?>
				<select name="probonoseo_schema_book_language">
					<option value="ja" <?php selected($probonoseo_lang === '' || $probonoseo_lang === 'ja', true); ?>>日本語</option>
					<option value="en" <?php selected($probonoseo_lang, 'en'); ?>>英語</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">MusicAlbum デフォルトタイプ設定</h3>
					<p class="probonoseo-card-description">MusicAlbum schemaのデフォルトタイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_music_type_enabled', 'Musicタイプ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_music_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_type = probonoseo_get_schema_text_value('probonoseo_schema_music_type'); ?>
				<select name="probonoseo_schema_music_type">
					<option value="MusicAlbum" <?php selected($probonoseo_type === '' || $probonoseo_type === 'MusicAlbum', true); ?>>アルバム</option>
					<option value="SingleRelease" <?php selected($probonoseo_type, 'SingleRelease'); ?>>シングル</option>
					<option value="EPRelease" <?php selected($probonoseo_type, 'EPRelease'); ?>>EP</option>
					<option value="CompilationAlbum" <?php selected($probonoseo_type, 'CompilationAlbum'); ?>>コンピレーション</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Podcast デフォルトカテゴリ設定</h3>
					<p class="probonoseo-card-description">Podcast schemaのデフォルトカテゴリを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_podcast_category_enabled', 'Podcastカテゴリ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_podcast_category_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_pcat = probonoseo_get_schema_text_value('probonoseo_schema_podcast_category'); ?>
				<select name="probonoseo_schema_podcast_category">
					<option value="Technology" <?php selected($probonoseo_pcat === '' || $probonoseo_pcat === 'Technology', true); ?>>テクノロジー</option>
					<option value="Business" <?php selected($probonoseo_pcat, 'Business'); ?>>ビジネス</option>
					<option value="Education" <?php selected($probonoseo_pcat, 'Education'); ?>>教育</option>
					<option value="Entertainment" <?php selected($probonoseo_pcat, 'Entertainment'); ?>>エンターテイメント</option>
					<option value="News" <?php selected($probonoseo_pcat, 'News'); ?>>ニュース</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Organization デフォルトタイプ設定</h3>
					<p class="probonoseo-card-description">Organization schemaのデフォルト組織タイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_org_type_enabled', 'Orgタイプ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_org_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_otype = probonoseo_get_schema_text_value('probonoseo_schema_org_type'); ?>
				<select name="probonoseo_schema_org_type">
					<option value="Organization" <?php selected($probonoseo_otype === '' || $probonoseo_otype === 'Organization', true); ?>>一般組織</option>
					<option value="Corporation" <?php selected($probonoseo_otype, 'Corporation'); ?>>株式会社</option>
					<option value="LocalBusiness" <?php selected($probonoseo_otype, 'LocalBusiness'); ?>>ローカルビジネス</option>
					<option value="EducationalOrganization" <?php selected($probonoseo_otype, 'EducationalOrganization'); ?>>教育機関</option>
					<option value="NGO" <?php selected($probonoseo_otype, 'NGO'); ?>>NGO</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Organization 設立年設定</h3>
					<p class="probonoseo-card-description">Organization schemaのデフォルト設立年を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_org_founded_enabled', 'Org設立年', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_org_founded_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_org_founded" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_org_founded')); ?>" placeholder="例: 2020">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Organization 所在地設定</h3>
					<p class="probonoseo-card-description">Organization schemaのデフォルト所在地を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_org_address_enabled', 'Org所在地', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_org_address_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_org_address" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_org_address')); ?>" placeholder="例: 東京都渋谷区〇〇1-2-3">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Person デフォルト職業設定</h3>
					<p class="probonoseo-card-description">Person schemaのデフォルト職業を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_person_job_enabled', 'Person職業', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_person_job_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_person_job" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_person_job')); ?>" placeholder="例: ライター、エンジニア">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Person デフォルト所属設定</h3>
					<p class="probonoseo-card-description">Person schemaのデフォルト所属を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_person_affiliation_enabled', 'Person所属', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_person_affiliation_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_person_affiliation" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_person_affiliation')); ?>" placeholder="例: 株式会社〇〇">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Article デフォルトタイプ設定</h3>
					<p class="probonoseo-card-description">Article schemaのデフォルト記事タイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_article_type_enabled', 'Articleタイプ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_article_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_atype = probonoseo_get_schema_text_value('probonoseo_schema_article_type'); ?>
				<select name="probonoseo_schema_article_type">
					<option value="Article" <?php selected($probonoseo_atype === '' || $probonoseo_atype === 'Article', true); ?>>一般記事</option>
					<option value="NewsArticle" <?php selected($probonoseo_atype, 'NewsArticle'); ?>>ニュース記事</option>
					<option value="BlogPosting" <?php selected($probonoseo_atype, 'BlogPosting'); ?>>ブログ記事</option>
					<option value="TechArticle" <?php selected($probonoseo_atype, 'TechArticle'); ?>>技術記事</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Article 著者名自動取得設定</h3>
					<p class="probonoseo-card-description">投稿者名を著者名として自動取得するかを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_article_author_auto_enabled', 'Article著者自動', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_article_author_auto_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_auto = probonoseo_get_schema_text_value('probonoseo_schema_article_author_auto'); ?>
				<select name="probonoseo_schema_article_author_auto">
					<option value="1" <?php selected($probonoseo_auto === '' || $probonoseo_auto === '1', true); ?>>有効（投稿者名を使用）</option>
					<option value="0" <?php selected($probonoseo_auto, '0'); ?>>無効（手動入力）</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Article 発行者名設定</h3>
					<p class="probonoseo-card-description">Article schemaの発行者名を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_article_publisher_enabled', 'Article発行者', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_article_publisher_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_article_publisher" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_article_publisher')); ?>" placeholder="例: <?php echo esc_attr(get_bloginfo('name')); ?>">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Article 発行者ロゴURL設定</h3>
					<p class="probonoseo-card-description">Article schemaの発行者ロゴURLを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_article_logo_enabled', 'Articleロゴ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_article_logo_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="url" name="probonoseo_schema_article_logo" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_article_logo')); ?>" placeholder="https://example.com/logo.png">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Announcement デフォルトカテゴリ設定</h3>
					<p class="probonoseo-card-description">SpecialAnnouncement schemaのデフォルトカテゴリを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_announcement_category_enabled', 'Announcementカテゴリ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_announcement_category_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_ancat = probonoseo_get_schema_text_value('probonoseo_schema_announcement_category'); ?>
				<select name="probonoseo_schema_announcement_category">
					<option value="DiseasePrevention" <?php selected($probonoseo_ancat === '' || $probonoseo_ancat === 'DiseasePrevention', true); ?>>感染症予防</option>
					<option value="GovernmentBenefits" <?php selected($probonoseo_ancat, 'GovernmentBenefits'); ?>>政府支援</option>
					<option value="HealthcareService" <?php selected($probonoseo_ancat, 'HealthcareService'); ?>>医療サービス</option>
					<option value="EventSchedule" <?php selected($probonoseo_ancat, 'EventSchedule'); ?>>イベント予定</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Announcement デフォルト有効日数設定</h3>
					<p class="probonoseo-card-description">SpecialAnnouncement schemaの有効期限を投稿日から何日後にするかを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_announcement_expires_enabled', 'Announcement有効日数', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_announcement_expires_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="number" name="probonoseo_schema_announcement_expires" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_announcement_expires')); ?>" min="1" max="365" placeholder="30">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Image デフォルト著作権者設定</h3>
					<p class="probonoseo-card-description">ImageObject schemaのデフォルト著作権者を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_image_copyright_enabled', 'Image著作権者', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_image_copyright_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_image_copyright" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_image_copyright')); ?>" placeholder="例: <?php echo esc_attr(get_bloginfo('name')); ?>">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Dataset デフォルト作成者設定</h3>
					<p class="probonoseo-card-description">Dataset schemaのデフォルト作成者を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_dataset_creator_enabled', 'Dataset作成者', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_dataset_creator_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_dataset_creator" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_dataset_creator')); ?>" placeholder="例: <?php echo esc_attr(get_bloginfo('name')); ?>">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Dataset デフォルトファイル形式設定</h3>
					<p class="probonoseo-card-description">Dataset schemaのデフォルトファイル形式を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_dataset_format_enabled', 'Datasetファイル形式', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_dataset_format_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_dfmt = probonoseo_get_schema_text_value('probonoseo_schema_dataset_format'); ?>
				<select name="probonoseo_schema_dataset_format">
					<option value="CSV" <?php selected($probonoseo_dfmt === '' || $probonoseo_dfmt === 'CSV', true); ?>>CSV</option>
					<option value="JSON" <?php selected($probonoseo_dfmt, 'JSON'); ?>>JSON</option>
					<option value="XML" <?php selected($probonoseo_dfmt, 'XML'); ?>>XML</option>
					<option value="Excel" <?php selected($probonoseo_dfmt, 'Excel'); ?>>Excel</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Rating デフォルト評価スケール設定</h3>
					<p class="probonoseo-card-description">AggregateRating schemaのデフォルト評価スケールを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_rating_scale_enabled', 'Rating評価スケール', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_rating_scale_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_scale = probonoseo_get_schema_text_value('probonoseo_schema_rating_scale'); ?>
				<select name="probonoseo_schema_rating_scale">
					<option value="5" <?php selected($probonoseo_scale === '' || $probonoseo_scale === '5', true); ?>>5段階</option>
					<option value="10" <?php selected($probonoseo_scale, '10'); ?>>10段階</option>
					<option value="100" <?php selected($probonoseo_scale, '100'); ?>>100点満点</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Rating デフォルト評価対象タイプ設定</h3>
					<p class="probonoseo-card-description">AggregateRating schemaのデフォルト評価対象タイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_rating_item_type_enabled', 'Rating対象タイプ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_rating_item_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_rtype = probonoseo_get_schema_text_value('probonoseo_schema_rating_item_type'); ?>
				<select name="probonoseo_schema_rating_item_type">
					<option value="Product" <?php selected($probonoseo_rtype === '' || $probonoseo_rtype === 'Product', true); ?>>商品</option>
					<option value="Service" <?php selected($probonoseo_rtype, 'Service'); ?>>サービス</option>
					<option value="Organization" <?php selected($probonoseo_rtype, 'Organization'); ?>>組織</option>
					<option value="LocalBusiness" <?php selected($probonoseo_rtype, 'LocalBusiness'); ?>>ローカルビジネス</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ClaimReview デフォルト検証組織名設定</h3>
					<p class="probonoseo-card-description">ClaimReview schemaのデフォルト検証組織名を設定します。空欄の場合はサイト名が使用されます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_claim_org_enabled', 'ClaimReview組織名', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_claim_org_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_claim_org" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_claim_org')); ?>" placeholder="例: <?php echo esc_attr(get_bloginfo('name')); ?>">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ClaimReview デフォルト検証結果設定</h3>
					<p class="probonoseo-card-description">ClaimReview schemaのデフォルト検証結果を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_claim_rating_enabled', 'ClaimReview検証結果', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_claim_rating_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_crating = probonoseo_get_schema_text_value('probonoseo_schema_claim_rating'); ?>
				<select name="probonoseo_schema_claim_rating">
					<option value="True" <?php selected($probonoseo_crating === '' || $probonoseo_crating === 'True', true); ?>>真実</option>
					<option value="Mostly True" <?php selected($probonoseo_crating, 'Mostly True'); ?>>ほぼ真実</option>
					<option value="Half True" <?php selected($probonoseo_crating, 'Half True'); ?>>半分真実</option>
					<option value="Mostly False" <?php selected($probonoseo_crating, 'Mostly False'); ?>>ほぼ虚偽</option>
					<option value="False" <?php selected($probonoseo_crating, 'False'); ?>>虚偽</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Speakable デフォルト読み上げ対象設定</h3>
					<p class="probonoseo-card-description">Speakable schemaのデフォルト読み上げ対象を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_speakable_target_enabled', 'Speakable対象', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_speakable_target_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_starget = probonoseo_get_schema_text_value('probonoseo_schema_speakable_target'); ?>
				<select name="probonoseo_schema_speakable_target">
					<option value="headline" <?php selected($probonoseo_starget === '' || $probonoseo_starget === 'headline', true); ?>>タイトルのみ</option>
					<option value="summary" <?php selected($probonoseo_starget, 'summary'); ?>>要約のみ</option>
					<option value="both" <?php selected($probonoseo_starget, 'both'); ?>>タイトル＋要約</option>
					<option value="custom" <?php selected($probonoseo_starget, 'custom'); ?>>カスタム指定</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Speakable カスタムCSSセレクタ設定</h3>
					<p class="probonoseo-card-description">カスタム読み上げ対象のCSSセレクタを設定します。カンマ区切りで複数指定可能です。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_speakable_selector_enabled', 'Speakableセレクタ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_speakable_selector_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_schema_speakable_selector" value="<?php echo esc_attr(probonoseo_get_schema_text_value('probonoseo_schema_speakable_selector')); ?>" placeholder="例: .speakable-content, .intro">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">自動選択 判定優先度設定</h3>
					<p class="probonoseo-card-description">schema自動選択の判定優先度を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_auto_priority_enabled', '自動選択優先度', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_auto_priority_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_priority = probonoseo_get_schema_text_value('probonoseo_schema_auto_priority'); ?>
				<select name="probonoseo_schema_auto_priority">
					<option value="content" <?php selected($probonoseo_priority === '' || $probonoseo_priority === 'content', true); ?>>コンテンツ内容で判定</option>
					<option value="posttype" <?php selected($probonoseo_priority, 'posttype'); ?>>投稿タイプで判定</option>
					<option value="category" <?php selected($probonoseo_priority, 'category'); ?>>カテゴリで判定</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">自動選択 フォールバックタイプ設定</h3>
					<p class="probonoseo-card-description">判定できなかった場合のデフォルトschemaタイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_schema_auto_fallback_enabled', '自動選択フォールバック', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_schema_auto_fallback_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_fallback = probonoseo_get_schema_text_value('probonoseo_schema_auto_fallback'); ?>
				<select name="probonoseo_schema_auto_fallback">
					<option value="Article" <?php selected($probonoseo_fallback === '' || $probonoseo_fallback === 'Article', true); ?>>Article</option>
					<option value="BlogPosting" <?php selected($probonoseo_fallback, 'BlogPosting'); ?>>BlogPosting</option>
					<option value="WebPage" <?php selected($probonoseo_fallback, 'WebPage'); ?>>WebPage</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>