jQuery(function($) {
    $(document).on('click', '.probonoseo-schema-save-btn', function() {
        var $btn = $(this);
        var $msg = $btn.siblings('.probonoseo-schema-save-msg');
        var postId = $btn.data('post-id');
        var action = $btn.data('action');
        var nonce = $btn.data('nonce');
        var $container = $btn.closest('.probonoseo-schema-container');
        var formData = $container.find('input, textarea, select').serialize();
        $btn.prop('disabled', true);
        $msg.hide().removeClass('error');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=' + action + '&post_id=' + postId + '&nonce=' + nonce,
            success: function(response) {
                $btn.prop('disabled', false);
                if (response.success) {
                    $msg.text('保存しました').show();
                } else {
                    $msg.text('保存に失敗しました').addClass('error').show();
                }
                setTimeout(function() { $msg.fadeOut(); }, 3000);
            },
            error: function() {
                $btn.prop('disabled', false);
                $msg.text('通信エラーが発生しました').addClass('error').show();
                setTimeout(function() { $msg.fadeOut(); }, 3000);
            }
        });
    });
});