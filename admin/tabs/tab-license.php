<?php
if (!defined('ABSPATH')) exit;

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_info = $probonoseo_license->get_license_info();
$probonoseo_is_active = $probonoseo_license->is_pro_active();
$probonoseo_is_dev_mode = $probonoseo_license_info['is_dev_mode'];
$probonoseo_status = $probonoseo_license_info['status'];
$probonoseo_show_dev_mode_ui = $probonoseo_license->should_show_dev_mode_ui();
?>

<div class="probonoseo-license-wrap">
    <div class="probonoseo-license-grid">
        <div class="probonoseo-license-left">
            <div class="probonoseo-card">
                <h2 class="probonoseo-card-title">
                    <span class="dashicons dashicons-admin-network"></span>
                    Pro版ライセンス認証
                </h2>
                
                <?php if ($probonoseo_is_active): ?>
                    <?php if ($probonoseo_is_dev_mode): ?>
                        <div class="probonoseo-license-status probonoseo-license-dev">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <strong>開発モード（仮ライセンス）</strong>
                            <p>テスト用の仮ライセンスでPro版機能を使用中です。</p>
                        </div>
                    <?php else: ?>
                        <div class="probonoseo-license-status probonoseo-license-active">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>ライセンス認証済み</strong>
                            <p>Pro版機能がすべて利用可能です。</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="probonoseo-license-info">
                        <div class="probonoseo-license-info-row">
                            <span class="probonoseo-license-info-label">ライセンスキー:</span>
                            <span class="probonoseo-license-info-value"><?php echo esc_html($probonoseo_license_info['masked_key']); ?></span>
                        </div>
                        <div class="probonoseo-license-info-row">
                            <span class="probonoseo-license-info-label">認証日:</span>
                            <span class="probonoseo-license-info-value">
                                <?php 
                                if (!empty($probonoseo_license_info['activated_date'])) {
                                    echo esc_html(date_i18n('Y年n月j日 H:i', strtotime($probonoseo_license_info['activated_date'])));
                                }
                                ?>
                            </span>
                        </div>
                        <div class="probonoseo-license-info-row">
                            <span class="probonoseo-license-info-label">登録サイト:</span>
                            <span class="probonoseo-license-info-value"><?php echo esc_html($probonoseo_license_info['site_url']); ?></span>
                        </div>
                    </div>
                    
                    <div class="probonoseo-license-actions">
                        <?php if (!$probonoseo_is_dev_mode): ?>
                            <button type="button" class="button button-secondary" id="probonoseo-check-license">
                                <span class="dashicons dashicons-update"></span>
                                ライセンス状態を確認
                            </button>
                        <?php endif; ?>
                        <button type="button" class="button button-link-delete" id="probonoseo-deactivate-license">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php echo $probonoseo_is_dev_mode ? '開発モードを解除' : 'ライセンスを解除'; ?>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="probonoseo-license-status probonoseo-license-inactive">
                        <span class="dashicons dashicons-info"></span>
                        <strong>ライセンス未認証</strong>
                        <p>Pro版機能を利用するにはライセンスキーを入力してください。</p>
                    </div>
                    
                    <div class="probonoseo-license-form">
                        <div class="probonoseo-form-group">
                            <label for="probonoseo-license-key">ライセンスキー</label>
                            <input 
                                type="text" 
                                id="probonoseo-license-key" 
                                class="regular-text" 
                                placeholder="XXXX-XXXX-XXXX-XXXX"
                                maxlength="19"
                            >
                            <p class="description">購入時に送信されたライセンスキーを入力してください。</p>
                        </div>
                        
                        <div class="probonoseo-license-actions">
                            <button type="button" class="button button-primary button-large" id="probonoseo-activate-license">
                                <span class="dashicons dashicons-yes"></span>
                                ライセンスを認証
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($probonoseo_show_dev_mode_ui && !$probonoseo_is_active): ?>
            <div class="probonoseo-dev-mode-card">
                <h3 class="probonoseo-dev-mode-title">
                    <span class="dashicons dashicons-admin-tools"></span>
                    開発者向け：開発モード
                </h3>
                <p class="probonoseo-dev-mode-warning">このオプションはローカル開発環境でのみ表示されます。</p>
                <ul class="probonoseo-dev-mode-features">
                    <li>ライセンスキーなしでPro版機能をテスト可能</li>
                    <li>本番環境では使用不可</li>
                    <li>許可ドメイン：localhost、.local、.test</li>
                </ul>
                <button type="button" class="button probonoseo-dev-mode-button" id="probonoseo-toggle-dev-mode">
                    <span class="dashicons dashicons-admin-tools"></span>
                    開発モードを有効化
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="probonoseo-license-right">
            <div class="probonoseo-card probonoseo-card-sticky">
                <h3 class="probonoseo-card-title">
                    <span class="dashicons dashicons-star-filled"></span>
                    Pro版機能一覧
                </h3>
                
                <div class="probonoseo-total-features">
                    <div class="probonoseo-feature-count">
                        <span class="probonoseo-count-label">Basic版</span>
                        <span class="probonoseo-count-number">70項目</span>
                    </div>
                    <div class="probonoseo-feature-plus">+</div>
                    <div class="probonoseo-feature-count">
                        <span class="probonoseo-count-label">Pro版</span>
                        <span class="probonoseo-count-number probonoseo-count-pro">218項目</span>
                    </div>
                    <div class="probonoseo-feature-equals">=</div>
                    <div class="probonoseo-feature-count probonoseo-feature-total">
                        <span class="probonoseo-count-label">合計</span>
                        <span class="probonoseo-count-number probonoseo-count-total">288項目</span>
                    </div>
                </div>
                
                <p class="probonoseo-feature-description">Pro版ライセンスで全288項目が利用可能になります。</p>
                
                <div class="probonoseo-pro-features">
                    <div class="probonoseo-pro-feature-category">
                        <h4>AI日本語SEO補助（21項目）</h4>
                        <ul>
                            <li>タイトル提案AI</li>
                            <li>見出し改善AI</li>
                            <li>記事構成案生成AI</li>
                            <li>説明文改善AI</li>
                            <li>要点サマリー生成</li>
                            <li>FAQ自動生成</li>
                            <li>高品質メタディスクリプション生成</li>
                            <li>その他14項目</li>
                        </ul>
                    </div>
                    
                    <div class="probonoseo-pro-feature-category">
                        <h4>競合分析（12項目）</h4>
                        <ul>
                            <li>競合分析機能</li>
                            <li>競合タイトル分析</li>
                            <li>競合メタディスクリプション分析</li>
                            <li>競合見出し構造分析</li>
                            <li>その他8項目</li>
                        </ul>
                    </div>
                    
                    <div class="probonoseo-pro-feature-category">
                        <h4>投稿SEO（21項目）</h4>
                        <ul>
                            <li>SEOメタボックス</li>
                            <li>SEOスコア表示</li>
                            <li>タイトルプレビュー</li>
                            <li>検索結果プレビュー</li>
                            <li>その他17項目</li>
                        </ul>
                    </div>
                    
                    <div class="probonoseo-pro-feature-category">
                        <h4>Pro専用強化（25項目）</h4>
                        <ul>
                            <li>カスタム投稿タイプ対応</li>
                            <li>カスタムタクソノミー対応</li>
                            <li>著者アーカイブSEO</li>
                            <li>robots.txt最適化</li>
                            <li>PWA対応</li>
                            <li>その他20項目</li>
                        </ul>
                    </div>
                    
                    <div class="probonoseo-pro-feature-category">
                        <h4>その他Pro専用機能</h4>
                        <ul>
                            <li>リッチスニペット対応（32項目）</li>
                            <li>schema高度版（48項目）</li>
                            <li>速度改善Pro（18項目）</li>
                            <li>サイト診断Pro（12項目）</li>
                            <li>全体管理（6項目）</li>
                            <li>OpenAI API設定（2項目）</li>
                        </ul>
                    </div>
                </div>
                
                <div class="probonoseo-pro-purchase">
                    <h4>Pro版を購入</h4>
                    
                    <div class="probonoseo-pricing-plans">
                        <div class="probonoseo-plan">
                            <div class="probonoseo-plan-name">5サイトプラン</div>
                            <div class="probonoseo-plan-price">¥9,800</div>
                            <div class="probonoseo-plan-sites">最大5サイトまで</div>
                        </div>
                        <div class="probonoseo-plan probonoseo-plan-popular">
                            <div class="probonoseo-plan-badge">おすすめ</div>
                            <div class="probonoseo-plan-name">無制限プラン</div>
                            <div class="probonoseo-plan-price">¥14,800</div>
                            <div class="probonoseo-plan-sites">サイト数無制限</div>
                        </div>
                    </div>
                    
                    <p class="probonoseo-plan-note">買い切り・永久ライセンス</p>
                    
                    <a href="https://seo.prbn.org/pro/" target="_blank" class="button button-primary button-hero">
                        Pro版を購入する
                    </a>
                    
                    <p class="probonoseo-purchase-note">
                        年間費用なし・アップデート永久無料<br>
                        1ライセンスで複数サイト運営可能
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#probonoseo-activate-license').on('click', function() {
        var button = $(this);
        var licenseKey = $('#probonoseo-license-key').val().trim().toUpperCase();
        
        if (!licenseKey) {
            alert('ライセンスキーを入力してください。');
            return;
        }
        
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 認証中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'probonoseo_activate_license',
                nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_license_nonce')); ?>',
                license_key: licenseKey
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> ライセンスを認証');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
                button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> ライセンスを認証');
            }
        });
    });
    
    $('#probonoseo-deactivate-license').on('click', function() {
        if (!confirm('本当にライセンスを解除しますか？Pro版機能が使用できなくなります。')) {
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 解除中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'probonoseo_deactivate_license',
                nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_license_nonce')); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    button.prop('disabled', false).html('<span class="dashicons dashicons-dismiss"></span> ライセンスを解除');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
                button.prop('disabled', false).html('<span class="dashicons dashicons-dismiss"></span> ライセンスを解除');
            }
        });
    });
    
    $('#probonoseo-check-license').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 確認中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'probonoseo_check_license',
                nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_license_nonce')); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
                button.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> ライセンス状態を確認');
            },
            error: function() {
                alert('通信エラーが発生しました。');
                button.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> ライセンス状態を確認');
            }
        });
    });
    
    $('#probonoseo-toggle-dev-mode').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 処理中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'probonoseo_toggle_dev_mode',
                nonce: '<?php echo esc_attr(wp_create_nonce('probonoseo_license_nonce')); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> 開発モードを有効化');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
                button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> 開発モードを有効化');
            }
        });
    });
    
    $('#probonoseo-license-key').on('input', function() {
        var value = $(this).val().replace(/[^A-Z0-9]/g, '');
        var formatted = value.match(/.{1,4}/g);
        if (formatted) {
            $(this).val(formatted.join('-'));
        }
    });
});
</script>