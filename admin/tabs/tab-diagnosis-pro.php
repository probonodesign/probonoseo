<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_is_pro_active = $probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">サイト診断Pro <span class="probonoseo-pro-label">Pro</span></h2>
	<p class="probonoseo-section-description">Google Search Console連携、Core Web Vitals、セキュリティ診断など、プロフェッショナルレベルのサイト診断機能を提供します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">インデックスステータス確認</h3>
					<p class="probonoseo-card-description">GSC APIからインデックス状況を取得し、問題ページをリスト表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_index', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">クロールエラー検出</h3>
					<p class="probonoseo-card-description">404/500エラーを検出し、修正提案を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_crawl', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">モバイルユーザビリティ診断</h3>
					<p class="probonoseo-card-description">viewport設定、タップ可能要素サイズ、フォントサイズを確認します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_mobile', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">Core Web Vitals診断</h3>
					<p class="probonoseo-card-description">LCP/FID/CLSを測定し、改善提案を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_vitals', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">パフォーマンス総合診断</h3>
					<p class="probonoseo-card-description">PageSpeed Insights APIでLighthouseスコアを取得します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_performance', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">セキュリティ診断</h3>
					<p class="probonoseo-card-description">HTTPS強制、セキュリティヘッダー、Mixed Contentを確認します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_security', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SSL証明書有効期限確認</h3>
					<p class="probonoseo-card-description">SSL証明書の有効期限を確認し、期限切れ前に警告します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_ssl', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">サイトマップ診断</h3>
					<p class="probonoseo-card-description">サイトマップのURL重複、404ページを検出します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_sitemap', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">robots.txt診断</h3>
					<p class="probonoseo-card-description">robots.txtの構文エラー、重要ページのブロックを確認します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_robots', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">.htaccess診断</h3>
					<p class="probonoseo-card-description">リダイレクト設定、セキュリティ設定を確認します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_htaccess', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">SEO総合スコア算出</h3>
					<p class="probonoseo-card-description">サイト全体のSEOスコアを0-100点で算出し、改善優先度を表示します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_total', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PDF診断レポート生成</h3>
					<p class="probonoseo-card-description">診断結果をPDFレポートとして出力し、ダウンロードできます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_diagnosis_pro_pdf', '有効化', false, !$probonoseo_is_pro_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>

<?php if ($probonoseo_is_pro_active) : ?>
	<h3 class="probonoseo-subsection-title">診断実行</h3>
	<p class="probonoseo-section-description">有効化した診断項目を一括で実行します。GSC連携が必要な項目は、Pro専用強化タブでGoogle Search Consoleを連携してください。</p>

	<div class="probonoseo-diagnosis-pro-action">
		<button type="button" id="probonoseo-run-diagnosis-pro" class="button button-primary button-hero">
			<span class="dashicons dashicons-search"></span>
			Pro診断を実行
		</button>
	</div>

	<div id="probonoseo-diagnosis-pro-results" class="probonoseo-diagnosis-pro-results">
		<div class="probonoseo-diagnosis-pro-empty">
			<span class="dashicons dashicons-clipboard"></span>
			<p>診断を実行すると、ここに結果が表示されます</p>
		</div>
	</div>

	<div class="probonoseo-diagnosis-pro-pdf-action">
		<button type="button" id="probonoseo-generate-pdf" class="button button-secondary" disabled>
			<span class="dashicons dashicons-pdf"></span>
			PDFレポートを生成
		</button>
		<p class="description">診断実行後にPDFレポートを生成できます。</p>
	</div>

<script>
jQuery(document).ready(function($) {
	$('#probonoseo-run-diagnosis-pro').on('click', function() {
		var $btn = $(this);
		var $results = $('#probonoseo-diagnosis-pro-results');
		
		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span>診断中...');
		$results.html('<div class="probonoseo-diagnosis-pro-loading"><span class="dashicons dashicons-update spin"></span><p>診断を実行しています...</p></div>');
		
		var formData = $('form').serialize();
		formData += '&action=probonoseo_diagnosis_pro_ajax';
		formData += '&probonoseo_diagnosis_pro_nonce=' + '<?php echo esc_attr(wp_create_nonce('probonoseo_diagnosis_pro_ajax')); ?>';
		
		$.post(ajaxurl, formData, function(response) {
			if (response.success) {
				$results.html(response.data.html);
				$('#probonoseo-generate-pdf').prop('disabled', false);
			} else {
				$results.html('<div class="probonoseo-diagnosis-pro-error"><p>診断に失敗しました: ' + (response.data.message || '不明なエラー') + '</p></div>');
			}
		}).fail(function() {
			$results.html('<div class="probonoseo-diagnosis-pro-error"><p>通信エラーが発生しました。</p></div>');
		}).always(function() {
			$btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span>Pro診断を実行');
		});
	});
	
	$('#probonoseo-generate-pdf').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span>生成中...');
		
		$.post(ajaxurl, {
			action: 'probonoseo_generate_pdf_report',
			probonoseo_pdf_nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_pdf_report')); ?>'
		}, function(response) {
			if (response.success && response.data.url) {
				window.location.href = response.data.url;
			} else {
				alert('PDFの生成に失敗しました: ' + (response.data.message || '不明なエラー'));
			}
		}).fail(function() {
			alert('通信エラーが発生しました。');
		}).always(function() {
			$btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span>PDFレポートを生成');
		});
	});
});
</script>
<?php endif; ?>

</div>