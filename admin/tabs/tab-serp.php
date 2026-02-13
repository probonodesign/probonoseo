<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">リッチスニペット対応（Pro版）</h2>
	<p class="probonoseo-section-description">FAQ、レシピ、イベントなどのリッチリザルト対応を行います。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">FAQ schema</h3>
					<p class="probonoseo-card-description">よくある質問（FAQ）を構造化データとして出力し、検索結果にFAQリッチリザルトを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_faq', 'FAQ schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">HowTo schema</h3>
					<p class="probonoseo-card-description">手順・ステップ形式のコンテンツを構造化データとして出力し、検索結果にHowToリッチリザルトを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_howto', 'HowTo schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Review schema</h3>
					<p class="probonoseo-card-description">レビュー・評価を構造化データとして出力し、検索結果に星評価を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_review', 'Review schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Recipe schema</h3>
					<p class="probonoseo-card-description">レシピ情報を構造化データとして出力し、検索結果にレシピカードを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_recipe', 'Recipe schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Event schema</h3>
					<p class="probonoseo-card-description">イベント情報を構造化データとして出力し、検索結果にイベントリストを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_event', 'Event schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Product schema</h3>
					<p class="probonoseo-card-description">商品情報を構造化データとして出力し、検索結果に商品リッチリザルトを表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_product', 'Product schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Video schema</h3>
					<p class="probonoseo-card-description">動画情報を構造化データとして出力し、検索結果に動画リッチリザルトを表示します。YouTube/Vimeo自動検出対応。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_video', 'Video schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">JobPosting schema</h3>
					<p class="probonoseo-card-description">求人情報を構造化データとして出力し、Googleしごと検索に対応します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_job', 'JobPosting schema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">検索結果最適化（Pro版）</h2>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトリンク最適化</h3>
					<p class="probonoseo-card-description">Googleサイトリンク表示を最適化するための構造化データを出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitelinks', 'サイトリンク最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索ボックス表示</h3>
					<p class="probonoseo-card-description">検索結果内にサイト内検索ボックスを表示するための構造化データを出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_searchbox', '検索ボックス表示', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ナレッジパネル最適化</h3>
					<p class="probonoseo-card-description">Organization schemaを強化し、ナレッジパネル表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_knowledge', 'ナレッジパネル最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">カルーセル表示最適化</h3>
					<p class="probonoseo-card-description">ItemList schemaを出力し、検索結果でのカルーセル表示を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_carousel', 'カルーセル表示最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">強調スニペット最適化</h3>
					<p class="probonoseo-card-description">コンテンツのマークアップを最適化し、強調スニペット表示を狙います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_featured', '強調スニペット最適化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">ローカル＆サイトマップ（Pro版）</h2>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネスschema</h3>
					<p class="probonoseo-card-description">店舗・事業所の情報を構造化データとして出力し、ローカル検索結果を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local', 'ローカルビジネスschema', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトマップ高度版</h3>
					<p class="probonoseo-card-description">XML/HTML/画像/動画サイトマップを自動生成します。優先度・更新頻度を自動算出。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap', 'サイトマップ高度版', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">SERP詳細設定（Pro版）</h2>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">FAQ 自動抽出</h3>
					<p class="probonoseo-card-description">H2/H3見出しからFAQを自動抽出してschemaを生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_faq_auto', 'FAQ自動抽出', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">HowTo 所要時間表示</h3>
					<p class="probonoseo-card-description">HowTo schemaで所要時間を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_howto_time', 'HowTo所要時間', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">HowTo 費用表示</h3>
					<p class="probonoseo-card-description">HowTo schemaで費用を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_howto_cost', 'HowTo費用', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Recipe 栄養情報表示</h3>
					<p class="probonoseo-card-description">Recipe schemaで栄養情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_recipe_nutrition', 'Recipe栄養情報', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Recipe 動画埋め込み</h3>
					<p class="probonoseo-card-description">Recipe schemaで動画埋め込みに対応します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_recipe_video', 'Recipe動画', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Event チケット情報</h3>
					<p class="probonoseo-card-description">Event schemaでチケット情報を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_event_offers', 'Eventチケット', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Video YouTube自動検出</h3>
					<p class="probonoseo-card-description">YouTube動画を自動検出してVideo schemaを出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_video_youtube', 'YouTube検出', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Video Vimeo自動検出</h3>
					<p class="probonoseo-card-description">Vimeo動画を自動検出してVideo schemaを出力します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_video_vimeo', 'Vimeo検出', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">XMLサイトマップ生成</h3>
					<p class="probonoseo-card-description">XMLサイトマップの自動生成を有効にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_xml', 'XMLサイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">HTMLサイトマップ生成</h3>
					<p class="probonoseo-card-description">HTMLサイトマップの自動生成を有効にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_html', 'HTMLサイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像サイトマップ生成</h3>
					<p class="probonoseo-card-description">画像サイトマップの自動生成を有効にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_image', '画像サイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">動画サイトマップ生成</h3>
					<p class="probonoseo-card-description">動画サイトマップの自動生成を有効にします。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_video', '動画サイトマップ', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">詳細オプション設定（Pro版）</h2>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">FAQ デフォルト表示数設定</h3>
					<p class="probonoseo-card-description">FAQ schemaのデフォルト表示数を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_faq_limit_enabled', 'FAQ表示数設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_faq_limit_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_faq_limit = get_option('probonoseo_serp_faq_limit', '10'); ?>
				<select name="probonoseo_serp_faq_limit">
					<option value="5" <?php selected($probonoseo_faq_limit, '5'); ?>>5件</option>
					<option value="10" <?php selected($probonoseo_faq_limit, '10'); ?>>10件</option>
					<option value="20" <?php selected($probonoseo_faq_limit, '20'); ?>>20件</option>
					<option value="0" <?php selected($probonoseo_faq_limit, '0'); ?>>無制限</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Review 評価スケール設定</h3>
					<p class="probonoseo-card-description">Review schemaのデフォルト評価スケールを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_review_scale_enabled', '評価スケール設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_review_scale_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_review_scale = get_option('probonoseo_serp_review_scale', '5'); ?>
				<select name="probonoseo_serp_review_scale">
					<option value="5" <?php selected($probonoseo_review_scale, '5'); ?>>5段階評価</option>
					<option value="10" <?php selected($probonoseo_review_scale, '10'); ?>>10段階評価</option>
					<option value="100" <?php selected($probonoseo_review_scale, '100'); ?>>100点満点</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Review レビュー対象設定</h3>
					<p class="probonoseo-card-description">Review schemaのデフォルトレビュー対象を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_review_type_enabled', 'レビュー対象設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_review_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_review_type = get_option('probonoseo_serp_review_type', 'Product'); ?>
				<select name="probonoseo_serp_review_type">
					<option value="Product" <?php selected($probonoseo_review_type, 'Product'); ?>>商品</option>
					<option value="LocalBusiness" <?php selected($probonoseo_review_type, 'LocalBusiness'); ?>>店舗</option>
					<option value="Book" <?php selected($probonoseo_review_type, 'Book'); ?>>書籍</option>
					<option value="Movie" <?php selected($probonoseo_review_type, 'Movie'); ?>>映画</option>
					<option value="Restaurant" <?php selected($probonoseo_review_type, 'Restaurant'); ?>>レストラン</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Event 開催形式設定</h3>
					<p class="probonoseo-card-description">Event schemaのデフォルト開催形式を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_event_mode_enabled', '開催形式設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_event_mode_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_event_mode = get_option('probonoseo_serp_event_mode', 'OfflineEventAttendanceMode'); ?>
				<select name="probonoseo_serp_event_mode">
					<option value="OfflineEventAttendanceMode" <?php selected($probonoseo_event_mode, 'OfflineEventAttendanceMode'); ?>>オフライン</option>
					<option value="OnlineEventAttendanceMode" <?php selected($probonoseo_event_mode, 'OnlineEventAttendanceMode'); ?>>オンライン</option>
					<option value="MixedEventAttendanceMode" <?php selected($probonoseo_event_mode, 'MixedEventAttendanceMode'); ?>>ハイブリッド</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Product 通貨設定</h3>
					<p class="probonoseo-card-description">Product schemaのデフォルト通貨を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_product_currency_enabled', '通貨設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_product_currency_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_product_currency = get_option('probonoseo_serp_product_currency', 'JPY'); ?>
				<select name="probonoseo_serp_product_currency">
					<option value="JPY" <?php selected($probonoseo_product_currency, 'JPY'); ?>>日本円（JPY）</option>
					<option value="USD" <?php selected($probonoseo_product_currency, 'USD'); ?>>米ドル（USD）</option>
					<option value="EUR" <?php selected($probonoseo_product_currency, 'EUR'); ?>>ユーロ（EUR）</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Product 在庫状況設定</h3>
					<p class="probonoseo-card-description">Product schemaのデフォルト在庫状況を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_product_availability_enabled', '在庫状況設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_product_availability_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_product_availability = get_option('probonoseo_serp_product_availability', 'InStock'); ?>
				<select name="probonoseo_serp_product_availability">
					<option value="InStock" <?php selected($probonoseo_product_availability, 'InStock'); ?>>在庫あり</option>
					<option value="OutOfStock" <?php selected($probonoseo_product_availability, 'OutOfStock'); ?>>在庫切れ</option>
					<option value="PreOrder" <?php selected($probonoseo_product_availability, 'PreOrder'); ?>>予約受付中</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Job 雇用形態設定</h3>
					<p class="probonoseo-card-description">JobPosting schemaのデフォルト雇用形態を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_job_type_enabled', '雇用形態設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_job_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_job_type = get_option('probonoseo_serp_job_type', 'FULL_TIME'); ?>
				<select name="probonoseo_serp_job_type">
					<option value="FULL_TIME" <?php selected($probonoseo_job_type, 'FULL_TIME'); ?>>正社員</option>
					<option value="PART_TIME" <?php selected($probonoseo_job_type, 'PART_TIME'); ?>>パート・アルバイト</option>
					<option value="CONTRACT" <?php selected($probonoseo_job_type, 'CONTRACT'); ?>>契約社員</option>
					<option value="TEMPORARY" <?php selected($probonoseo_job_type, 'TEMPORARY'); ?>>派遣社員</option>
					<option value="INTERN" <?php selected($probonoseo_job_type, 'INTERN'); ?>>インターン</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Job リモートワーク設定</h3>
					<p class="probonoseo-card-description">JobPosting schemaのデフォルトリモートワーク設定を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_job_remote_enabled', 'リモート設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_job_remote_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_job_remote = get_option('probonoseo_serp_job_remote', '0'); ?>
				<select name="probonoseo_serp_job_remote">
					<option value="0" <?php selected($probonoseo_job_remote, '0'); ?>>オフィス勤務</option>
					<option value="1" <?php selected($probonoseo_job_remote, '1'); ?>>リモート可</option>
					<option value="2" <?php selected($probonoseo_job_remote, '2'); ?>>フルリモート</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネス名設定</h3>
					<p class="probonoseo-card-description">ローカルビジネスschemaで使用するビジネス名を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local_name_enabled', 'ビジネス名設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_local_name_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_serp_local_name" value="<?php echo esc_attr(get_option('probonoseo_serp_local_name', '')); ?>" placeholder="例: 株式会社〇〇">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネスタイプ設定</h3>
					<p class="probonoseo-card-description">ローカルビジネスschemaのビジネスタイプを設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local_type_enabled', 'タイプ設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_local_type_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_local_type = get_option('probonoseo_serp_local_type', 'LocalBusiness'); ?>
				<select name="probonoseo_serp_local_type">
					<option value="LocalBusiness" <?php selected($probonoseo_local_type, 'LocalBusiness'); ?>>一般</option>
					<option value="Restaurant" <?php selected($probonoseo_local_type, 'Restaurant'); ?>>レストラン</option>
					<option value="Store" <?php selected($probonoseo_local_type, 'Store'); ?>>店舗</option>
					<option value="MedicalBusiness" <?php selected($probonoseo_local_type, 'MedicalBusiness'); ?>>医療機関</option>
					<option value="LegalService" <?php selected($probonoseo_local_type, 'LegalService'); ?>>法律事務所</option>
					<option value="FinancialService" <?php selected($probonoseo_local_type, 'FinancialService'); ?>>金融サービス</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネス住所設定</h3>
					<p class="probonoseo-card-description">ローカルビジネスschemaで使用する住所を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local_address_enabled', '住所設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_local_address_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_serp_local_address" value="<?php echo esc_attr(get_option('probonoseo_serp_local_address', '')); ?>" placeholder="例: 東京都渋谷区〇〇1-2-3">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネス電話番号設定</h3>
					<p class="probonoseo-card-description">ローカルビジネスschemaで使用する電話番号を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local_phone_enabled', '電話番号設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_local_phone_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_serp_local_phone" value="<?php echo esc_attr(get_option('probonoseo_serp_local_phone', '')); ?>" placeholder="例: 03-1234-5678">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ローカルビジネス営業時間設定</h3>
					<p class="probonoseo-card-description">ローカルビジネスschemaで使用する営業時間を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_local_hours_enabled', '営業時間設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_local_hours_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<input type="text" name="probonoseo_serp_local_hours" value="<?php echo esc_attr(get_option('probonoseo_serp_local_hours', '')); ?>" placeholder="例: 月-金 9:00-18:00">
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトマップ 更新頻度設定</h3>
					<p class="probonoseo-card-description">サイトマップのデフォルト更新頻度を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_freq_enabled', '更新頻度設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_sitemap_freq_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_sitemap_freq = get_option('probonoseo_serp_sitemap_freq', 'weekly'); ?>
				<select name="probonoseo_serp_sitemap_freq">
					<option value="always" <?php selected($probonoseo_sitemap_freq, 'always'); ?>>常時</option>
					<option value="hourly" <?php selected($probonoseo_sitemap_freq, 'hourly'); ?>>毎時</option>
					<option value="daily" <?php selected($probonoseo_sitemap_freq, 'daily'); ?>>毎日</option>
					<option value="weekly" <?php selected($probonoseo_sitemap_freq, 'weekly'); ?>>毎週</option>
					<option value="monthly" <?php selected($probonoseo_sitemap_freq, 'monthly'); ?>>毎月</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトマップ 最大URL数設定</h3>
					<p class="probonoseo-card-description">サイトマップに含める最大URL数を設定します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_serp_sitemap_limit_enabled', '最大URL数設定', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
			<?php if ($probonoseo_license_active && get_option('probonoseo_serp_sitemap_limit_enabled', '0') === '1') : ?>
			<div class="probonoseo-card-detail">
				<?php $probonoseo_sitemap_limit = get_option('probonoseo_serp_sitemap_limit', '50000'); ?>
				<select name="probonoseo_serp_sitemap_limit">
					<option value="1000" <?php selected($probonoseo_sitemap_limit, '1000'); ?>>1,000</option>
					<option value="5000" <?php selected($probonoseo_sitemap_limit, '5000'); ?>>5,000</option>
					<option value="10000" <?php selected($probonoseo_sitemap_limit, '10000'); ?>>10,000</option>
					<option value="50000" <?php selected($probonoseo_sitemap_limit, '50000'); ?>>50,000</option>
				</select>
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>