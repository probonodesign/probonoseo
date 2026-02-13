(function($) {
    'use strict';
    
    var ProbonoSEOMetabox = {
        
        init: function() {
            this.bindEvents();
            this.initCharCounters();
            this.initMediaUploader();
            
            setTimeout(function() {
                ProbonoSEOMetabox.runAnalysis();
            }, 1000);
        },
        
        bindEvents: function() {
            $('.probonoseo-seo-tab').on('click', this.handleTabClick);
            $('.probonoseo-analyze-button').on('click', this.runAnalysis);
            $('.probonoseo-get-link-suggestions').on('click', this.getLinkSuggestions);
            
            $('#probonoseo_custom_title').on('input', this.updateTitlePreview);
            $('#probonoseo_custom_description').on('input', this.updateDescPreview);
            $('#probonoseo_og_title, #probonoseo_og_description, #probonoseo_og_image').on('input', this.updateSocialPreview);
            
            var debounceTimer;
            $('#probonoseo_focus_keyword, #probonoseo_custom_title, #probonoseo_custom_description').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    ProbonoSEOMetabox.runAnalysis();
                }, 1000);
            });
        },
        
        handleTabClick: function() {
            var tab = $(this).data('tab');
            
            $('.probonoseo-seo-tab').removeClass('active');
            $(this).addClass('active');
            
            $('.probonoseo-seo-panel').removeClass('active');
            $('.probonoseo-seo-panel[data-panel="' + tab + '"]').addClass('active');
        },
        
        initCharCounters: function() {
            var titleInput = $('#probonoseo_custom_title');
            var descInput = $('#probonoseo_custom_description');
            
            titleInput.on('input', function() {
                var length = $(this).val().length;
                var counter = $(this).siblings('.probonoseo-char-counter');
                counter.find('.probonoseo-char-count').text(length);
                
                var status = counter.find('.probonoseo-char-status');
                if (length >= 30 && length <= 60) {
                    status.text('最適').removeClass('warning error').addClass('optimal');
                } else if (length > 60) {
                    status.text('長すぎ').removeClass('optimal warning').addClass('error');
                } else if (length > 0) {
                    status.text('短め').removeClass('optimal error').addClass('warning');
                } else {
                    status.text('');
                }
            }).trigger('input');
            
            descInput.on('input', function() {
                var length = $(this).val().length;
                var counter = $(this).siblings('.probonoseo-char-counter');
                counter.find('.probonoseo-char-count').text(length);
                
                var status = counter.find('.probonoseo-char-status');
                if (length >= 70 && length <= 120) {
                    status.text('最適').removeClass('warning error').addClass('optimal');
                } else if (length > 120) {
                    status.text('長すぎ').removeClass('optimal warning').addClass('error');
                } else if (length > 0) {
                    status.text('短め').removeClass('optimal error').addClass('warning');
                } else {
                    status.text('');
                }
            }).trigger('input');
        },
        
        initMediaUploader: function() {
            var mediaUploader;
            
            $('.probonoseo-select-image').on('click', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'OGP画像を選択',
                    button: { text: '選択' },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#probonoseo_og_image').val(attachment.url).trigger('input');
                    ProbonoSEOMetabox.updateSocialPreview();
                });
                
                mediaUploader.open();
            });
        },
        
        getContent: function() {
            var content = '';
            
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                content = wp.data.select('core/editor').getEditedPostContent();
            } else if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                content = tinyMCE.get('content').getContent();
            } else {
                content = $('#content').val() || '';
            }
            
            return content;
        },
        
        getTitle: function() {
            var customTitle = $('#probonoseo_custom_title').val();
            if (customTitle) return customTitle;
            
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                return wp.data.select('core/editor').getEditedPostAttribute('title') || '';
            }
            
            return $('#title').val() || '';
        },
        
        runAnalysis: function() {
            var content = ProbonoSEOMetabox.getContent();
            var title = ProbonoSEOMetabox.getTitle();
            var description = $('#probonoseo_custom_description').val();
            var focusKeyword = $('#probonoseo_focus_keyword').val();
            
            $.ajax({
                url: probonoseoSEO.ajaxurl,
                type: 'POST',
                data: {
                    action: 'probonoseo_calculate_seo_score',
                    nonce: probonoseoSEO.nonce,
                    post_id: probonoseoSEO.post_id,
                    content: content,
                    title: title,
                    description: description,
                    focus_keyword: focusKeyword
                },
                success: function(response) {
                    if (response.success) {
                        ProbonoSEOMetabox.updateScores(response.data.scores);
                        ProbonoSEOMetabox.updateAnalysis(response.data.analysis);
                        ProbonoSEOMetabox.updateChecklist(response.data.checklist);
                        ProbonoSEOMetabox.updateSuggestions(response.data.suggestions);
                        ProbonoSEOMetabox.checkPublishWarning(response.data.scores, response.data.suggestions);
                        
                        var now = new Date();
                        $('.probonoseo-last-analyzed').text('最終分析: ' + now.toLocaleTimeString('ja-JP'));
                    }
                }
            });
        },
        
        updateScores: function(scores) {
            var totalScore = scores.total;
            var scoreNumber = $('.probonoseo-score-number');
            var scoreProgress = $('.probonoseo-score-progress');
            
            scoreNumber.text(totalScore);
            
            var circumference = 283;
            var offset = circumference - (totalScore / 100) * circumference;
            scoreProgress.css('stroke-dashoffset', offset);
            
            scoreProgress.removeClass('score-low score-medium score-high');
            if (totalScore < 50) {
                scoreProgress.addClass('score-low');
            } else if (totalScore < 80) {
                scoreProgress.addClass('score-medium');
            } else {
                scoreProgress.addClass('score-high');
            }
            
            var scoreItems = {
                'title': scores.title,
                'meta': scores.meta,
                'content': scores.content,
                'keyword': scores.keyword,
                'links': scores.links
            };
            
            $.each(scoreItems, function(key, value) {
                var fill = $('.probonoseo-score-item-fill[data-score="' + key + '"]');
                var valueEl = $('.probonoseo-score-item-value[data-value="' + key + '"]');
                
                fill.css('width', value + '%');
                valueEl.text(value);
                
                fill.removeClass('score-low score-medium score-high');
                if (value < 50) {
                    fill.addClass('score-low');
                } else if (value < 80) {
                    fill.addClass('score-medium');
                } else {
                    fill.addClass('score-high');
                }
            });
        },
        
        updateAnalysis: function(analysis) {
            $('[data-metric="word_count"]').text(analysis.word_count.toLocaleString());
            $('[data-metric="read_time"]').text(analysis.read_time);
            $('[data-metric="keyword_density"]').text(analysis.keyword_density);
            $('[data-metric="heading_count"]').text(analysis.heading_count);
            $('[data-metric="internal_links"]').text(analysis.internal_links);
            $('[data-metric="external_links"]').text(analysis.external_links);
            $('[data-metric="image_count"]').text(analysis.image_count);
            $('[data-metric="images_with_alt"]').text(analysis.images_with_alt);
        },
        
        updateChecklist: function(checklist) {
            $.each(checklist, function(group, items) {
                var container = $('.probonoseo-checklist-items[data-group="' + group + '"]');
                container.empty();
                
                $.each(items, function(index, item) {
                    var icon = item.status === 'pass' ? '✓' : (item.status === 'warning' ? '!' : '✕');
                    var html = '<div class="probonoseo-checklist-item">' +
                        '<span class="probonoseo-checklist-icon ' + item.status + '">' + icon + '</span>' +
                        '<span class="probonoseo-checklist-text">' + item.message + '</span>' +
                        '</div>';
                    container.append(html);
                });
            });
        },
        
        updateSuggestions: function(suggestions) {
            var container = $('.probonoseo-suggestions-list');
            container.empty();
            
            $.each(suggestions, function(index, item) {
                var icon = item.type === 'success' ? '✓' : (item.type === 'warning' ? '⚠️' : '✕');
                var html = '<div class="probonoseo-suggestion-item ' + item.type + '">' +
                    '<span class="probonoseo-suggestion-icon">' + icon + '</span>' +
                    '<span class="probonoseo-suggestion-text">' + item.message + '</span>' +
                    '</div>';
                container.append(html);
            });
        },
        
        checkPublishWarning: function(scores, suggestions) {
            var warningBox = $('.probonoseo-publish-warning');
            var warningMessage = $('.probonoseo-warning-message');
            
            var criticalIssues = suggestions.filter(function(item) {
                return item.type === 'error';
            });
            
            if (scores.total < 50 || criticalIssues.length > 0) {
                var messages = [];
                if (scores.total < 50) {
                    messages.push('SEOスコアが低いです（' + scores.total + '点）');
                }
                $.each(criticalIssues, function(i, issue) {
                    messages.push(issue.message);
                });
                
                warningMessage.html(messages.join('<br>'));
                warningBox.show();
            } else {
                warningBox.hide();
            }
        },
        
        updateTitlePreview: function() {
            var title = $(this).val() || ProbonoSEOMetabox.getTitle();
            $('.probonoseo-serp-title').text(title);
        },
        
        updateDescPreview: function() {
            var desc = $(this).val() || '説明文がここに表示されます...';
            $('.probonoseo-serp-description').text(desc);
        },
        
        updateSocialPreview: function() {
            var ogTitle = $('#probonoseo_og_title').val() || $('#probonoseo_custom_title').val() || ProbonoSEOMetabox.getTitle();
            var ogDesc = $('#probonoseo_og_description').val() || $('#probonoseo_custom_description').val() || '説明文';
            var ogImage = $('#probonoseo_og_image').val();
            
            $('.probonoseo-facebook-title, .probonoseo-twitter-title').text(ogTitle);
            $('.probonoseo-facebook-desc, .probonoseo-twitter-desc').text(ogDesc);
            
            if (ogImage) {
                $('.probonoseo-facebook-image, .probonoseo-twitter-image').html('<img src="' + ogImage + '" alt="">');
            } else {
                $('.probonoseo-facebook-image, .probonoseo-twitter-image').html('<div class="probonoseo-no-image">画像未設定</div>');
            }
        },
        
        getLinkSuggestions: function() {
            var focusKeyword = $('#probonoseo_focus_keyword').val();
            var content = ProbonoSEOMetabox.getContent();
            
            if (!focusKeyword) {
                alert('フォーカスキーワードを設定してください');
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true).text('検索中...');
            
            $.ajax({
                url: probonoseoSEO.ajaxurl,
                type: 'POST',
                data: {
                    action: 'probonoseo_get_internal_link_suggestions',
                    nonce: probonoseoSEO.nonce,
                    post_id: probonoseoSEO.post_id,
                    focus_keyword: focusKeyword,
                    content: content
                },
                success: function(response) {
                    button.prop('disabled', false).text('関連記事を検索');
                    
                    if (response.success) {
                        var container = $('.probonoseo-link-suggestions');
                        container.empty();
                        
                        if (response.data.suggestions.length === 0) {
                            container.html('<p>関連記事が見つかりませんでした</p>');
                            return;
                        }
                        
                        $.each(response.data.suggestions, function(index, item) {
                            var html = '<div class="probonoseo-link-suggestion-item">' +
                                '<div>' +
                                '<div class="probonoseo-link-suggestion-title">' + item.title + '</div>' +
                                '<div class="probonoseo-link-suggestion-url">' + item.url + '</div>' +
                                '</div>' +
                                '<button type="button" class="button probonoseo-link-suggestion-copy" data-url="' + item.url + '" data-title="' + item.title + '">URLコピー</button>' +
                                '</div>';
                            container.append(html);
                        });
                        
                        container.find('.probonoseo-link-suggestion-copy').on('click', function() {
                            var url = $(this).data('url');
                            navigator.clipboard.writeText(url).then(function() {
                                alert('URLをコピーしました');
                            });
                        });
                    }
                },
                error: function() {
                    button.prop('disabled', false).text('関連記事を検索');
                    alert('エラーが発生しました');
                }
            });
        }
    };
    
    $(document).ready(function() {
        if ($('.probonoseo-seo-metabox').length) {
            ProbonoSEOMetabox.init();
        }
    });
    
})(jQuery);