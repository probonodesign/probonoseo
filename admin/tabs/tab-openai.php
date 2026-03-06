<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_is_pro_active = $probonoseo_license->is_pro_active();

$probonoseo_is_api_key_set = false;
$probonoseo_usage_stats = array('count' => 0, 'limit' => 10);
$probonoseo_model = 'gpt-4o';
$probonoseo_max_tokens = 1000;
$probonoseo_temperature = 0.7;

if ($probonoseo_is_pro_active && class_exists('ProbonoSEO_OpenAI_API')) {
	$probonoseo_openai = ProbonoSEO_OpenAI_API::get_instance();
	$probonoseo_is_api_key_set = $probonoseo_openai->is_api_key_set();
	$probonoseo_usage_stats = $probonoseo_openai->get_usage_stats();
	$probonoseo_model = get_option(ProbonoSEO_OpenAI_API::OPTION_MODEL, ProbonoSEO_OpenAI_API::DEFAULT_MODEL);
	$probonoseo_max_tokens = get_option(ProbonoSEO_OpenAI_API::OPTION_MAX_TOKENS, ProbonoSEO_OpenAI_API::DEFAULT_MAX_TOKENS);
	$probonoseo_temperature = get_option(ProbonoSEO_OpenAI_API::OPTION_TEMPERATURE, ProbonoSEO_OpenAI_API::DEFAULT_TEMPERATURE);
}
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">OpenAI API設定（Pro版）</h2>
	<p class="probonoseo-section-description">AI機能で使用するOpenAI APIの設定を行います。APIキーは暗号化して安全に保存されます。</p>
</div>

<div class="probonoseo-openai-wrap">
	<div class="probonoseo-openai-grid">
		<div class="probonoseo-openai-left">
			<div class="probonoseo-card pro-feature">
				<h3 class="probonoseo-card-title">
					<span class="dashicons dashicons-admin-generic"></span>
					OpenAI API設定
				</h3>
				
				<?php if ($probonoseo_is_pro_active) : ?>
					<?php if ($probonoseo_is_api_key_set) : ?>
						<div class="probonoseo-api-status probonoseo-api-active">
							<span class="dashicons dashicons-yes-alt"></span>
							<strong>APIキー設定済み</strong>
							<p>OpenAI APIが利用可能です。</p>
						</div>
					<?php else : ?>
						<div class="probonoseo-api-status probonoseo-api-inactive">
							<span class="dashicons dashicons-warning"></span>
							<strong>APIキー未設定</strong>
							<p>AI機能を利用するにはOpenAI APIキーが必要です。</p>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="probonoseo-api-status probonoseo-api-inactive">
						<span class="dashicons dashicons-lock"></span>
						<strong>Pro版限定機能</strong>
						<p>OpenAI API設定を利用するにはPro版ライセンスが必要です。</p>
					</div>
				<?php endif; ?>
				
				<div class="probonoseo-form-group">
					<label for="probonoseo-openai-api-key">OpenAI APIキー<?php if ($probonoseo_is_api_key_set) : ?> <span style="color: #4caf50; font-weight: normal;">（設定済み）</span><?php endif; ?><?php if (!$probonoseo_is_pro_active) : ?> <span style="color: #667eea;">🔒 Pro版</span><?php endif; ?></label>
					<input type="password" id="probonoseo-openai-api-key" class="regular-text" placeholder="<?php echo $probonoseo_is_api_key_set ? '新しいキーを入力すると上書きされます' : 'sk-...'; ?>" value="" style="width: 100%; max-width: 400px;" <?php echo !$probonoseo_is_pro_active ? 'disabled' : ''; ?>>
					<p class="description">
						OpenAIの<a href="https://platform.openai.com/api-keys" target="_blank">APIキー管理ページ</a>で取得できます。
					</p>
				</div>
				
				<div class="probonoseo-form-group">
					<label for="probonoseo-openai-model">使用モデル<?php if (!$probonoseo_is_pro_active) : ?> <span style="color: #667eea;">🔒 Pro版</span><?php endif; ?></label>
					<select id="probonoseo-openai-model" style="width: 100%; max-width: 400px;" <?php echo !$probonoseo_is_pro_active ? 'disabled' : ''; ?>>
						<option value="gpt-4o" <?php selected($probonoseo_model, 'gpt-4o'); ?>>GPT-4o（推奨・高品質・高速）</option>
						<option value="gpt-4o-mini" <?php selected($probonoseo_model, 'gpt-4o-mini'); ?>>GPT-4o mini（高速・低コスト）</option>
						<option value="gpt-4-turbo" <?php selected($probonoseo_model, 'gpt-4-turbo'); ?>>GPT-4 Turbo（高品質）</option>
						<option value="gpt-3.5-turbo" <?php selected($probonoseo_model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo（最安・高速）</option>
					</select>
					<p class="description">
						AI機能で使用するGPTモデルを選択します。GPT-4oが推奨です。
					</p>
				</div>
				
				<div class="probonoseo-form-group">
					<label for="probonoseo-openai-max-tokens">最大トークン数<?php if (!$probonoseo_is_pro_active) : ?> <span style="color: #667eea;">🔒 Pro版</span><?php endif; ?></label>
					<input type="number" id="probonoseo-openai-max-tokens" class="small-text" value="<?php echo esc_attr($probonoseo_max_tokens); ?>" min="100" max="4000" step="100" <?php echo !$probonoseo_is_pro_active ? 'disabled' : ''; ?>>
					<p class="description">
						1回のAPI呼び出しで生成する最大トークン数（100-4000）。デフォルト: 1000
					</p>
				</div>
				
				<div class="probonoseo-form-group">
					<label for="probonoseo-openai-temperature">Temperature（創造性）<?php if (!$probonoseo_is_pro_active) : ?> <span style="color: #667eea;">🔒 Pro版</span><?php endif; ?></label>
					<input type="number" id="probonoseo-openai-temperature" class="small-text" value="<?php echo esc_attr($probonoseo_temperature); ?>" min="0" max="2" step="0.1" <?php echo !$probonoseo_is_pro_active ? 'disabled' : ''; ?>>
					<p class="description">
						生成テキストの創造性（0.0-2.0）。0.7推奨。高いほど創造的、低いほど正確。
					</p>
				</div>
				
				<?php if ($probonoseo_is_pro_active) : ?>
				<div class="probonoseo-api-actions">
					<button type="button" class="button button-primary button-large" id="probonoseo-save-openai-settings">
						<span class="dashicons dashicons-saved"></span>
						設定を保存
					</button>
					<button type="button" class="button button-secondary" id="probonoseo-test-openai-api" <?php echo !$probonoseo_is_api_key_set ? 'disabled' : ''; ?>>
						<span class="dashicons dashicons-yes"></span>
						接続テスト
					</button>
				</div>
				<?php endif; ?>
			</div>
		</div>
		
		<div class="probonoseo-openai-right">
			<div class="probonoseo-card pro-feature probonoseo-card-sticky">
				<h3 class="probonoseo-card-title">
					<span class="dashicons dashicons-chart-bar"></span>
					API使用状況
				</h3>
				
				<div class="probonoseo-usage-stats">
					<div class="probonoseo-usage-stat">
						<span class="probonoseo-usage-label">今分のリクエスト数:</span>
						<span class="probonoseo-usage-value"><?php echo esc_html($probonoseo_usage_stats['count']); ?> / <?php echo esc_html($probonoseo_usage_stats['limit']); ?></span>
					</div>
					<div class="probonoseo-usage-progress">
						<div class="probonoseo-usage-bar" style="width: <?php echo esc_attr(min(100, ($probonoseo_usage_stats['count'] / $probonoseo_usage_stats['limit']) * 100)); ?>%"></div>
					</div>
					<p class="description">レート制限: 10リクエスト/分</p>
				</div>
				
				<hr>
				
				<h4>AI機能について</h4>
				<p>ProbonoSEOのAI機能はOpenAI APIを使用します。以下の機能が利用できます:</p>
				
				<ul class="probonoseo-feature-list">
					<li>AIタイトル提案（3パターン生成）</li>
					<li>AI見出し提案（構成最適化）</li>
					<li>AI目次提案（記事全体構成）</li>
					<li>AI本文生成補助（段落単位）</li>
					<li>AI要約生成（記事まとめ）</li>
					<li>AI FAQ生成（Q&A自動作成）</li>
					<li>AIメタディスクリプション生成</li>
					<li>AI関連キーワード抽出</li>
					<li>AIリライト提案（文章改善）</li>
					<li>AI読みやすさチェック</li>
					<li>その他10項目</li>
				</ul>
				
				<hr>
				
				<h4>APIキーの取得方法</h4>
				<ol class="probonoseo-steps">
					<li><a href="https://platform.openai.com/signup" target="_blank">OpenAIアカウント</a>を作成</li>
					<li><a href="https://platform.openai.com/api-keys" target="_blank">APIキー管理ページ</a>にアクセス</li>
					<li>「Create new secret key」をクリック</li>
					<li>生成されたAPIキーをコピー</li>
					<li>上記の入力欄に貼り付けて保存</li>
				</ol>
				
				<hr>
				
				<h4>料金について</h4>
				<p>OpenAI APIは従量課金制です。目安:</p>
				<ul class="probonoseo-pricing-list">
					<li><strong>GPT-4o:</strong> $2.50/1Mトークン（入力）</li>
					<li><strong>GPT-4o mini:</strong> $0.15/1Mトークン（入力）</li>
					<li><strong>GPT-3.5 Turbo:</strong> $0.50/1Mトークン（入力）</li>
					<li><strong>月間想定コスト:</strong> 約$1-10（使用量による）</li>
				</ul>
				
				<div class="probonoseo-api-warning">
					<span class="dashicons dashicons-info"></span>
					<p>APIキーは暗号化して保存されます。WordPress AUTH_KEYを使用した安全な暗号化方式です。</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if ($probonoseo_is_pro_active) : ?>
<script>
jQuery(document).ready(function($) {
	$('#probonoseo-save-openai-settings').on('click', function() {
		var button = $(this);
		var apiKey = $('#probonoseo-openai-api-key').val().trim();
		var model = $('#probonoseo-openai-model').val();
		var maxTokens = $('#probonoseo-openai-max-tokens').val();
		var temperature = $('#probonoseo-openai-temperature').val();
		
		button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 保存中...');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'probonoseo_save_openai_settings',
				nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_openai_nonce')); ?>',
				api_key: apiKey,
				model: model,
				max_tokens: maxTokens,
				temperature: temperature
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message);
					button.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> 設定を保存');
				}
			},
			error: function() {
				alert('通信エラーが発生しました。');
				button.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> 設定を保存');
			}
		});
	});
	
	$('#probonoseo-test-openai-api').on('click', function() {
		var button = $(this);
		button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> テスト中...');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'probonoseo_test_openai_api',
				nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_openai_nonce')); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert('✓ ' + response.data.message);
				} else {
					alert('✗ ' + response.data.message);
				}
				button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> 接続テスト');
			},
			error: function() {
				alert('通信エラーが発生しました。');
				button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> 接続テスト');
			}
		});
	});
});
</script>
<?php endif; ?>