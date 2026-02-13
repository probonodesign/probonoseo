<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">競合分析 <span class="probonoseo-pro-label">Pro</span></h2>
	<p class="probonoseo-section-description">競合サイトを登録し、SEO要素を比較分析します。最大10サイトまで登録可能です。</p>

<?php if ($probonoseo_license_active) : ?>
	<div class="probonoseo-competitor-grid">
		<div class="probonoseo-competitor-col-left">

			<div class="probonoseo-competitor-box">
				<h4 class="probonoseo-competitor-box-title">競合サイト登録</h4>
				<div class="probonoseo-competitor-list" id="probonoseo-competitor-list">
					<?php
					$probonoseo_competitors = get_option('probonoseo_competitors', array());
					if (empty($probonoseo_competitors)) {
						$probonoseo_competitors = array_fill(0, 3, '');
					}
					foreach ($probonoseo_competitors as $probonoseo_index => $probonoseo_url) :
					?>
						<div class="probonoseo-competitor-row" data-index="<?php echo esc_attr($probonoseo_index); ?>">
							<span class="probonoseo-competitor-num"><?php echo esc_html($probonoseo_index + 1); ?></span>
							<input type="url" name="probonoseo_competitors[]" value="<?php echo esc_url($probonoseo_url); ?>" placeholder="https://example.com/page" class="probonoseo-competitor-input">
							<button type="button" class="probonoseo-competitor-del">×</button>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="probonoseo-competitor-btns">
					<button type="button" id="probonoseo-add-competitor" class="button button-small">+ 追加</button>
					<button type="button" id="probonoseo-save-competitors" class="button button-primary button-small">保存</button>
				</div>
				<div id="probonoseo-competitor-message" class="probonoseo-competitor-msg" style="display:none;"></div>
			</div>

			<div class="probonoseo-competitor-box">
				<h4 class="probonoseo-competitor-box-title">自サイトURL（比較対象）</h4>
				<input type="url" id="probonoseo-target-url" value="<?php echo esc_url(get_option('probonoseo_target_url', '')); ?>" placeholder="https://yoursite.com/page" class="probonoseo-competitor-input-full">
			</div>

			<div class="probonoseo-competitor-box">
				<h4 class="probonoseo-competitor-box-title">分析項目</h4>
				<div class="probonoseo-competitor-checks">
					<label><input type="checkbox" name="analysis_title" value="1" checked> タイトル</label>
					<label><input type="checkbox" name="analysis_meta" value="1" checked> メタD</label>
					<label><input type="checkbox" name="analysis_heading" value="1" checked> 見出し</label>
					<label><input type="checkbox" name="analysis_wordcount" value="1" checked> 文字数</label>
					<label><input type="checkbox" name="analysis_images" value="1" checked> 画像数</label>
					<label><input type="checkbox" name="analysis_internal" value="1" checked> 内部リンク</label>
					<label><input type="checkbox" name="analysis_external" value="1" checked> 外部リンク</label>
					<label><input type="checkbox" name="analysis_schema" value="1" checked> schema</label>
					<label><input type="checkbox" name="analysis_keywords" value="1" checked> キーワード</label>
				</div>
				<div class="probonoseo-competitor-run">
					<button type="button" id="probonoseo-run-analysis" class="button button-primary">競合分析を実行</button>
				</div>
			</div>

		</div>

		<div class="probonoseo-competitor-col-right">
			<div class="probonoseo-competitor-box probonoseo-competitor-box-sticky">
				<h4 class="probonoseo-competitor-box-title">分析結果</h4>
				<div id="probonoseo-analysis-results" class="probonoseo-competitor-results">
					<div class="probonoseo-competitor-results-empty">
						<span class="dashicons dashicons-chart-area"></span>
						<p>「競合分析を実行」をクリック</p>
					</div>
				</div>
				<div class="probonoseo-competitor-report" id="probonoseo-report-wrap" style="display:none;">
					<button type="button" id="probonoseo-generate-report" class="button">PDFレポートを生成</button>
				</div>
			</div>
		</div>
	</div>

	<h3 class="probonoseo-subsection-title">分析機能の設定</h3>
<?php endif; ?>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">競合分析機能</h3>
					<p class="probonoseo-card-description">競合分析機能全体の有効/無効</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_enabled', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タイトル分析</h3>
					<p class="probonoseo-card-description">タイトルタグの文字数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_title', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">メタD分析</h3>
					<p class="probonoseo-card-description">メタディスクリプションの文字数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_meta', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">見出し構成分析</h3>
					<p class="probonoseo-card-description">H1〜H6の見出し構成を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_heading', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">文字数分析</h3>
					<p class="probonoseo-card-description">本文の文字数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_wordcount', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像数分析</h3>
					<p class="probonoseo-card-description">ページ内の画像数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_images', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">内部リンク分析</h3>
					<p class="probonoseo-card-description">内部リンクの数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_internal', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">外部リンク分析</h3>
					<p class="probonoseo-card-description">外部リンクの数を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_external', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">schema分析</h3>
					<p class="probonoseo-card-description">構造化データの種類を分析</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_schema', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">キーワード密度分析</h3>
					<p class="probonoseo-card-description">頻出キーワードを抽出</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_keywords', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">総合スコア算出</h3>
					<p class="probonoseo-card-description">各項目を総合してスコアを算出</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_score', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">PDFレポート生成</h3>
					<p class="probonoseo-card-description">分析結果をPDFレポートで出力</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_competitor_report', '有効化', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>

</div>

<?php if ($probonoseo_license_active) : ?>
<script>
jQuery(document).ready(function($) {
	var maxCompetitors = 10;

	$('#probonoseo-add-competitor').on('click', function() {
		var list = $('#probonoseo-competitor-list');
		var count = list.find('.probonoseo-competitor-row').length;
		if (count >= maxCompetitors) {
			showMsg('最大10件まで', 'error');
			return;
		}
		var html = '<div class="probonoseo-competitor-row" data-index="' + count + '">' +
			'<span class="probonoseo-competitor-num">' + (count + 1) + '</span>' +
			'<input type="url" name="probonoseo_competitors[]" value="" placeholder="https://example.com/page" class="probonoseo-competitor-input">' +
			'<button type="button" class="probonoseo-competitor-del">×</button></div>';
		list.append(html);
		updateNums();
	});

	$(document).on('click', '.probonoseo-competitor-del', function() {
		var list = $('#probonoseo-competitor-list');
		if (list.find('.probonoseo-competitor-row').length <= 1) {
			$(this).siblings('input').val('');
			return;
		}
		$(this).parent().remove();
		updateNums();
	});

	function updateNums() {
		$('#probonoseo-competitor-list .probonoseo-competitor-row').each(function(i) {
			$(this).attr('data-index', i).find('.probonoseo-competitor-num').text(i + 1);
		});
	}

	$('#probonoseo-save-competitors').on('click', function() {
		var urls = [];
		$('.probonoseo-competitor-input').each(function() {
			var v = $(this).val().trim();
			if (v) urls.push(v);
		});
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'probonoseo_save_competitors',
				nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_competitor_nonce')); ?>',
				competitors: urls,
				target_url: $('#probonoseo-target-url').val().trim()
			},
			success: function(r) {
				showMsg(r.success ? '保存しました' : (r.data.message || '失敗'), r.success ? 'success' : 'error');
			},
			error: function() { showMsg('通信エラー', 'error'); }
		});
	});

	$('#probonoseo-run-analysis').on('click', function() {
		var btn = $(this);
		var urls = [];
		$('.probonoseo-competitor-input').each(function() {
			var v = $(this).val().trim();
			if (v) urls.push(v);
		});
		if (urls.length === 0) {
			showMsg('競合サイトを登録してください', 'error');
			return;
		}
		var opts = {};
		$('.probonoseo-competitor-checks input:checked').each(function() {
			opts[$(this).attr('name')] = 1;
		});
		btn.prop('disabled', true).text('分析中...');
		$('#probonoseo-analysis-results').html('<div class="probonoseo-competitor-results-loading"><span class="dashicons dashicons-update spin"></span></div>');
		$('#probonoseo-report-wrap').hide();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'probonoseo_run_competitor_analysis',
				nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_competitor_nonce')); ?>',
				competitors: urls,
				target_url: $('#probonoseo-target-url').val().trim(),
				options: opts
			},
			success: function(r) {
				btn.prop('disabled', false).text('競合分析を実行');
				if (r.success) {
					$('#probonoseo-analysis-results').html(r.data.html);
					$('#probonoseo-report-wrap').show();
				} else {
					$('#probonoseo-analysis-results').html('<div class="probonoseo-competitor-results-error">' + (r.data.message || '失敗') + '</div>');
				}
			},
			error: function() {
				btn.prop('disabled', false).text('競合分析を実行');
				$('#probonoseo-analysis-results').html('<div class="probonoseo-competitor-results-error">通信エラー</div>');
			}
		});
	});

	$('#probonoseo-generate-report').on('click', function() {
		var btn = $(this);
		btn.prop('disabled', true).text('生成中...');
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'probonoseo_generate_competitor_report',
				nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_competitor_nonce')); ?>'
			},
			success: function(r) {
				btn.prop('disabled', false).text('PDFレポートを生成');
				if (r.success && r.data.url) {
					window.open(r.data.url, '_blank');
				} else {
					showMsg(r.data.message || '失敗', 'error');
				}
			},
			error: function() {
				btn.prop('disabled', false).text('PDFレポートを生成');
				showMsg('通信エラー', 'error');
			}
		});
	});

	function showMsg(t, type) {
		var m = $('#probonoseo-competitor-message');
		m.removeClass('success error').addClass(type).text(t).show();
		setTimeout(function() { m.fadeOut(); }, 3000);
	}
});
</script>
<?php endif; ?>