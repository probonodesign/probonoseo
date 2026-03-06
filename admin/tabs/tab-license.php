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
        </div>
    </div>
</div>

<div id="probonoseo-deactivate-modal" class="probonoseo-modal" style="display:none;">
    <div class="probonoseo-modal-overlay"></div>
    <div class="probonoseo-modal-content">
        <h3 class="probonoseo-modal-title">
            <span class="dashicons dashicons-warning"></span>
            ライセンス解除の確認
        </h3>
        <p class="probonoseo-modal-message">本当にライセンスを解除しますか？<br>再認証には購入時のライセンスキーが必要です。</p>
        <div class="probonoseo-modal-input-group">
            <label for="probonoseo-deactivate-confirm">解除するには「解除」と入力してください：</label>
            <input type="text" id="probonoseo-deactivate-confirm" class="regular-text" placeholder="解除">
        </div>
        <div class="probonoseo-modal-actions">
            <button type="button" class="button button-secondary" id="probonoseo-deactivate-cancel">キャンセル</button>
            <button type="button" class="button button-link-delete" id="probonoseo-deactivate-confirm-btn" disabled>解除する</button>
        </div>
    </div>
</div>

<style>
.probonoseo-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.probonoseo-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
}
.probonoseo-modal-content {
    position: relative;
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}
.probonoseo-modal-title {
    margin: 0 0 15px;
    color: #d63638;
    display: flex;
    align-items: center;
    gap: 8px;
}
.probonoseo-modal-title .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}
.probonoseo-modal-message {
    margin: 0 0 20px;
    color: #50575e;
    line-height: 1.6;
}
.probonoseo-modal-input-group {
    margin-bottom: 20px;
}
.probonoseo-modal-input-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #1d2327;
}
.probonoseo-modal-input-group input {
    width: 100%;
}
.probonoseo-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.probonoseo-modal-actions .button-link-delete:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

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
        $('#probonoseo-deactivate-modal').fadeIn(200);
        $('#probonoseo-deactivate-confirm').val('').focus();
        $('#probonoseo-deactivate-confirm-btn').prop('disabled', true);
    });
    
    $('#probonoseo-deactivate-confirm').on('input', function() {
        var value = $(this).val().trim();
        $('#probonoseo-deactivate-confirm-btn').prop('disabled', value !== '解除');
    });
    
    $('#probonoseo-deactivate-cancel, .probonoseo-modal-overlay').on('click', function() {
        $('#probonoseo-deactivate-modal').fadeOut(200);
    });
    
    $('#probonoseo-deactivate-confirm-btn').on('click', function() {
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
                    $('#probonoseo-deactivate-modal').fadeOut(200);
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
                $('#probonoseo-deactivate-modal').fadeOut(200);
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