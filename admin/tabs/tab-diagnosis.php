<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="probonoseo-diagnosis-layout">
	
	<div class="probonoseo-diagnosis-left">
		<h2 class="probonoseo-section-title">診断項目の設定</h2>
		<p class="probonoseo-section-description">サイト全体のSEO状態を診断し、問題点を検出します。</p>
		<p style="color: #666; margin-bottom: 24px; font-size: 14px;">診断したい項目を選択してください。</p>

		<form method="post" action="" id="diagnosis-settings-form">
			<?php wp_nonce_field('probonoseo_save_settings', 'probonoseo_nonce'); ?>

			<div style="text-align: center; margin-bottom: 24px;">
				<?php submit_button('設定を保存', 'primary probonoseo-save-btn', 'probonoseo_save', false, array('style' => 'width: 100%;')); ?>
			</div>

			<div class="probonoseo-cards-wrap" style="display: block;">

				<div class="probonoseo-card">
					<div class="probonoseo-card-inner">
						<div class="probonoseo-card-left">
							<h3 class="probonoseo-card-title">タイトル重複警告</h3>
							<p class="probonoseo-card-description">サイト全体で同じタイトルの記事がないかチェックします。</p>
						</div>
						<div class="probonoseo-card-right">
							<?php probonoseo_render_switch('probonoseo_diagnosis_title_duplicate', 'タイトル重複警告', true, false, 'タイトル重複警告を無効にすると重複に気づけません'); ?>
						</div>
					</div>
				</div>

				<div class="probonoseo-card">
					<div class="probonoseo-card-inner">
						<div class="probonoseo-card-left">
							<h3 class="probonoseo-card-title">メタディスクリプション重複警告</h3>
							<p class="probonoseo-card-description">同じメタディスクリプションが複数の記事で使用されていないかチェックします。</p>
						</div>
						<div class="probonoseo-card-right">
							<?php probonoseo_render_switch('probonoseo_diagnosis_meta_duplicate', 'メタD重複警告', true, false, 'メタディスクリプション重複警告を無効にすると重複に気づけません'); ?>
						</div>
					</div>
				</div>

				<div class="probonoseo-card">
					<div class="probonoseo-card-inner">
						<div class="probonoseo-card-left">
							<h3 class="probonoseo-card-title">サイト高速化の簡易診断</h3>
							<p class="probonoseo-card-description">サイトの表示速度に影響する要因を簡易診断します。</p>
						</div>
						<div class="probonoseo-card-right">
							<?php probonoseo_render_switch('probonoseo_diagnosis_speed', '高速化診断', true, false, 'サイト高速化診断を無効にすると速度問題に気づけません'); ?>
						</div>
					</div>
				</div>

				<div class="probonoseo-card">
					<div class="probonoseo-card-inner">
						<div class="probonoseo-card-left">
							<h3 class="probonoseo-card-title">メタタグ出力制御</h3>
							<p class="probonoseo-card-description">WordPress標準の不要なメタタグ（generator、wlwmanifest、rsd_link等）を削除し、ソースコードを軽量化します。</p>
						</div>
						<div class="probonoseo-card-right">
							<?php probonoseo_render_switch('probonoseo_meta_cleanup', 'メタタグ制御', true, false, 'メタタグ出力制御を無効にすると不要なタグが出力されます'); ?>
						</div>
					</div>
				</div>

				<div class="probonoseo-card">
					<div class="probonoseo-card-inner">
						<div class="probonoseo-card-left">
							<h3 class="probonoseo-card-title">Google Search Console認証</h3>
							<p class="probonoseo-card-description">Google Search Consoleのサイト所有権を確認するためのメタタグを出力します。</p>
						</div>
						<div class="probonoseo-card-right">
							<?php probonoseo_render_switch('probonoseo_gsc_verify', 'GSC認証', true, false, 'GSC認証を無効にすると認証メタタグが出力されません'); ?>
						</div>
					</div>
				</div>

			</div>

			<div class="probonoseo-card" style="margin-top: 20px;">
				<div class="probonoseo-card-inner" style="display: block;">
					<h3 class="probonoseo-card-title">Google Search Console 認証コード</h3>
					<p class="probonoseo-card-description" style="margin-bottom: 12px;">Search Consoleで取得した認証コードを入力してください。メタタグ全体またはcontent属性の値のみを入力できます。</p>
					<input type="text" name="probonoseo_gsc_verify_code" value="<?php echo esc_attr(get_option('probonoseo_gsc_verify_code', '')); ?>" placeholder="例: google1234567890abcdef または &lt;meta name=&quot;google-site-verification&quot; content=&quot;...&quot;&gt;" style="width: 100%;">
				</div>
			</div>

			<div style="text-align: center; margin-top: 24px;">
				<?php submit_button('設定を保存', 'primary probonoseo-save-btn', 'probonoseo_save', false, array('style' => 'width: 100%;')); ?>
			</div>

		</form>
	</div>

	<div class="probonoseo-diagnosis-right">
		<h2 class="probonoseo-section-title">診断の実行</h2>
		<p style="color: #666; margin-bottom: 24px; font-size: 14px;">「診断を実行」ボタンをクリックすると診断が実行されます。</p>

		<div style="text-align: center; margin-bottom: 32px;">
			<button type="button" id="probonoseo-run-diagnosis" class="button button-primary probonoseo-diagnosis-button">
				🔍 診断を実行
			</button>
		</div>

		<div class="probonoseo-diagnosis-results">
			<h3>診断結果</h3>
			<div id="probonoseo-diagnosis-output">
				<?php
				$probonoseo_results = get_option('probonoseo_diagnosis_results', array());
				if (!empty($probonoseo_results)) {
					ProbonoSEO_Diagnosis::display_results();
				} else {
					echo '<p style="color: #666; text-align: center;">「診断を実行」ボタンをクリックすると診断が実行されます。</p>';
				}
				?>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#probonoseo-run-diagnosis').on('click', function() {
		var $button = $(this);
		var $output = $('#probonoseo-diagnosis-output');
		
		$button.prop('disabled', true).text('診断中...');
		$output.html('<p style="color: #666; text-align: center; padding: 40px 0;">診断を実行中です...</p>');
		
		$.post(ajaxurl, {
			action: 'probonoseo_diagnosis',
			probonoseo_diagnosis_nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_diagnosis_ajax')); ?>',
			probonoseo_diagnosis_title_duplicate: $('input[name="probonoseo_diagnosis_title_duplicate"]').val(),
			probonoseo_diagnosis_meta_duplicate: $('input[name="probonoseo_diagnosis_meta_duplicate"]').val(),
			probonoseo_diagnosis_speed: $('input[name="probonoseo_diagnosis_speed"]').val()
		}, function(response) {
			if (response && response.success && response.data && response.data.html) {
				var successMsg = '<div style="margin-bottom: 15px; padding: 10px; background: #d5f4e6; border-left: 4px solid #00a32a; text-align: center;">';
				successMsg += '<p style="margin: 0; color: #00a32a; font-weight: bold;">✓ 診断を実行しました</p>';
				successMsg += '</div>';
				$output.html(successMsg + response.data.html);
			} else {
				$output.html('<p style="color: #d63638; text-align: center;">診断に失敗しました。</p>');
			}
		}).fail(function() {
			$output.html('<p style="color: #d63638; text-align: center;">診断に失敗しました。もう一度お試しください。</p>');
		}).always(function() {
			$button.prop('disabled', false).html('🔍 診断を実行');
		});
	});
});
</script>