(function($) {
	'use strict';
	
	$(document).ready(function() {
		
		$('#probonoseo-generate-title').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-title-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>タイトルを生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_title',
					nonce: probonoseoAI.nonce,
					post_id: postId
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.titles) {
						var html = '';
						
						$.each(response.data.titles, function(index, item) {
							var lengthClass = '';
							if (item.length > 60) {
								lengthClass = 'style="color: #e65100;"';
							} else if (item.length >= 30 && item.length <= 60) {
								lengthClass = 'style="color: #2e7d32;"';
							}
							
							html += '<div class="probonoseo-ai-result-item">';
							html += '<div class="probonoseo-ai-result-title">' + escapeHtml(item.title) + '</div>';
							html += '<div class="probonoseo-ai-result-meta" ' + lengthClass + '>' + item.length + '文字</div>';
							if (item.description) {
								html += '<div class="probonoseo-ai-result-desc">' + escapeHtml(item.description) + '</div>';
							}
							html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-title" data-title="' + escapeAttr(item.title) + '" data-post-id="' + postId + '">このタイトルを適用</button>';
							html += '</div>';
						});
						
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : 'タイトルの生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-apply-title', function() {
			var $btn = $(this);
			var title = $btn.data('title');
			var postId = $btn.data('post-id');
			
			if (!confirm('タイトルを「' + title + '」に変更しますか？')) {
				return;
			}
			
			$btn.prop('disabled', true).text('適用中...');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_title',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					title: title
				},
				success: function(response) {
					if (response.success) {
						$('#title').val(title);
						
						if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
							wp.data.dispatch('core/editor').editPost({ title: title });
						}
						
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('適用済み').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '適用に失敗しました。';
						alert(message);
						$btn.prop('disabled', false).text('このタイトルを適用');
					}
				},
				error: function() {
					alert('通信エラーが発生しました。');
					$btn.prop('disabled', false).text('このタイトルを適用');
				}
			});
		});
		
		$('#probonoseo-generate-metadesc').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-metadesc-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>メタディスクリプションを生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_metadesc',
					nonce: probonoseoAI.nonce,
					post_id: postId
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.metadesc) {
						var item = response.data.metadesc;
						var html = '<div class="probonoseo-ai-result-item">';
						html += '<div class="probonoseo-ai-result-title">' + escapeHtml(item.text) + '</div>';
						html += '<div class="probonoseo-ai-result-meta">' + item.length + '文字</div>';
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-metadesc" data-metadesc="' + escapeAttr(item.text) + '" data-post-id="' + postId + '">このメタDを適用</button>';
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-apply-metadesc', function() {
			var $btn = $(this);
			var metadesc = $btn.data('metadesc');
			var postId = $btn.data('post-id');
			
			$btn.prop('disabled', true).text('適用中...');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_metadesc',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					metadesc: metadesc
				},
				success: function(response) {
					if (response.success) {
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('適用済み').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '適用に失敗しました。';
						alert(message);
						$btn.prop('disabled', false).text('このメタDを適用');
					}
				},
				error: function() {
					alert('通信エラーが発生しました。');
					$btn.prop('disabled', false).text('このメタDを適用');
				}
			});
		});
		
		$('#probonoseo-generate-heading').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-heading-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>見出し提案を生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_heading',
					nonce: probonoseoAI.nonce,
					post_id: postId
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.suggestions) {
						var html = '';
						
						$.each(response.data.suggestions, function(index, item) {
							var unchangedClass = item.is_changed ? '' : ' probonoseo-ai-heading-unchanged';
							html += '<div class="probonoseo-ai-heading-item' + unchangedClass + '">';
							html += '<span class="probonoseo-ai-heading-level' + (item.level === 3 ? ' h3' : '') + '">H' + item.level + '</span>';
							
							if (item.is_changed) {
								html += '<div class="probonoseo-ai-heading-current">' + escapeHtml(item.current) + '</div>';
								html += '<div class="probonoseo-ai-heading-suggested">' + escapeHtml(item.suggested) + '</div>';
								if (item.reason) {
									html += '<div class="probonoseo-ai-heading-reason">' + escapeHtml(item.reason) + '</div>';
								}
								html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-heading" data-original="' + escapeAttr(item.original_html) + '" data-new-text="' + escapeAttr(item.suggested) + '" data-level="' + item.level + '" data-post-id="' + postId + '">この見出しを適用</button>';
							} else {
								html += '<div class="probonoseo-ai-heading-current">' + escapeHtml(item.current) + '</div>';
								html += '<div class="probonoseo-ai-heading-reason">変更不要</div>';
							}
							
							html += '</div>';
						});
						
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-apply-heading', function() {
			var $btn = $(this);
			var original = $btn.data('original');
			var newText = $btn.data('new-text');
			var level = $btn.data('level');
			var postId = $btn.data('post-id');
			
			$btn.prop('disabled', true).text('適用中...');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_heading',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					original_html: original,
					new_text: newText,
					level: level
				},
				success: function(response) {
					if (response.success) {
						$btn.closest('.probonoseo-ai-heading-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('適用済み').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '適用に失敗しました。';
						alert(message);
						$btn.prop('disabled', false).text('この見出しを適用');
					}
				},
				error: function() {
					alert('通信エラーが発生しました。');
					$btn.prop('disabled', false).text('この見出しを適用');
				}
			});
		});
		
		$('#probonoseo-generate-outline').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var keyword = $('#probonoseo-outline-keyword').val();
			var $results = $('#probonoseo-outline-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>記事構成を生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_outline',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					keyword: keyword
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.outline) {
						var html = '<div class="probonoseo-ai-result-item">';
						
						$.each(response.data.outline, function(index, h2) {
							html += '<div class="probonoseo-ai-outline-item">';
							html += '<div class="probonoseo-ai-outline-h2">' + escapeHtml(h2.text) + '</div>';
							if (h2.description) {
								html += '<div class="probonoseo-ai-outline-desc">' + escapeHtml(h2.description) + '</div>';
							}
							
							if (h2.children && h2.children.length > 0) {
								$.each(h2.children, function(i, h3) {
									html += '<div class="probonoseo-ai-outline-h3">└ ' + escapeHtml(h3.text) + '</div>';
								});
							}
							html += '</div>';
						});
						
						html += '<div class="probonoseo-ai-actions">';
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-outline" data-outline=\'' + JSON.stringify(response.data.outline).replace(/'/g, "&#39;") + '\' data-post-id="' + postId + '">記事に挿入</button>';
						html += '</div>';
						html += '</div>';
						
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-apply-outline', function() {
			var $btn = $(this);
			var outline = $btn.data('outline');
			var postId = $btn.data('post-id');
			
			if (!confirm('記事の先頭に構成案を挿入しますか？')) {
				return;
			}
			
			$btn.prop('disabled', true).text('挿入中...');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_outline',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					outline: JSON.stringify(outline)
				},
				success: function(response) {
					if (response.success) {
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('挿入済み').prop('disabled', true);
						alert('記事構成を挿入しました。ページを更新して確認してください。');
					} else {
						var message = response.data && response.data.message ? response.data.message : '挿入に失敗しました。';
						alert(message);
						$btn.prop('disabled', false).text('記事に挿入');
					}
				},
				error: function() {
					alert('通信エラーが発生しました。');
					$btn.prop('disabled', false).text('記事に挿入');
				}
			});
		});
		
		$('#probonoseo-generate-body').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var heading = $('#probonoseo-body-heading').val();
			var context = $('#probonoseo-body-context').val();
			var $results = $('#probonoseo-body-results');
			
			if (!heading) {
				alert('見出しを入力してください。');
				return;
			}
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>本文を生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_body',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					heading: heading,
					context: context
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.body) {
						var item = response.data.body;
						var html = '<div class="probonoseo-ai-result-item">';
						html += '<div class="probonoseo-ai-body-preview">' + escapeHtml(item.text).replace(/\n/g, '<br>') + '</div>';
						html += '<div class="probonoseo-ai-result-meta">' + item.word_count + '文字</div>';
						html += '<div class="probonoseo-ai-actions">';
						html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(item.text) + '">コピー</button>';
						html += '</div>';
						html += '</div>';
						
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-copy-body', function() {
			var text = $(this).data('text');
			
			if (navigator.clipboard) {
				navigator.clipboard.writeText(text).then(function() {
					alert('クリップボードにコピーしました。');
				});
			} else {
				var textarea = document.createElement('textarea');
				textarea.value = text;
				document.body.appendChild(textarea);
				textarea.select();
				document.execCommand('copy');
				document.body.removeChild(textarea);
				alert('クリップボードにコピーしました。');
			}
		});
		
		$('#probonoseo-generate-summary').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var style = $('#probonoseo-summary-style').val();
			var $results = $('#probonoseo-summary-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>要約を生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_summary',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					style: style
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.summary) {
						var summary = response.data.summary;
						var html = '<div class="probonoseo-ai-summary-item">';
						
						if (summary.type === 'paragraph') {
							html += '<div class="probonoseo-ai-body-preview">' + escapeHtml(summary.text) + '</div>';
							html += '<div class="probonoseo-ai-actions">';
							html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(summary.text) + '">コピー</button>';
							html += '</div>';
						} else {
							html += '<ul class="probonoseo-ai-summary-list">';
							$.each(summary.items, function(i, item) {
								html += '<li>' + escapeHtml(item) + '</li>';
							});
							html += '</ul>';
							html += '<div class="probonoseo-ai-actions">';
							html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(summary.items.join('\n')) + '">コピー</button>';
							html += '</div>';
						}
						
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$('#probonoseo-generate-faq').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var count = $('#probonoseo-faq-count').val();
			var $results = $('#probonoseo-faq-results');
			
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>FAQを生成中...</p></div>');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_faq',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					count: count
				},
				success: function(response) {
					$btn.prop('disabled', false);
					
					if (response.success && response.data.faqs) {
						var html = '';
						
						$.each(response.data.faqs, function(index, faq) {
							html += '<div class="probonoseo-ai-faq-item">';
							html += '<div class="probonoseo-ai-faq-question">Q. ' + escapeHtml(faq.question) + '</div>';
							html += '<div class="probonoseo-ai-faq-answer">A. ' + escapeHtml(faq.answer) + '</div>';
							html += '</div>';
						});
						
						html += '<div class="probonoseo-ai-actions">';
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-faq" data-faqs=\'' + JSON.stringify(response.data.faqs).replace(/'/g, "&#39;") + '\' data-post-id="' + postId + '">記事に挿入</button>';
						html += '</div>';
						
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '生成に失敗しました。';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">通信エラーが発生しました。</div>');
				}
			});
		});
		
		$(document).on('click', '.probonoseo-apply-faq', function() {
			var $btn = $(this);
			var faqs = $btn.data('faqs');
			var postId = $btn.data('post-id');
			
			if (!confirm('記事の末尾にFAQを挿入しますか？')) {
				return;
			}
			
			$btn.prop('disabled', true).text('挿入中...');
			
			$.ajax({
				url: probonoseoAI.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_faq',
					nonce: probonoseoAI.nonce,
					post_id: postId,
					faqs: JSON.stringify(faqs),
					include_schema: 'true'
				},
				success: function(response) {
					if (response.success) {
						$btn.text('挿入済み').prop('disabled', true);
						alert('FAQを挿入しました。ページを更新して確認してください。');
					} else {
						var message = response.data && response.data.message ? response.data.message : '挿入に失敗しました。';
						alert(message);
						$btn.prop('disabled', false).text('記事に挿入');
					}
				},
				error: function() {
					alert('通信エラーが発生しました。');
					$btn.prop('disabled', false).text('記事に挿入');
				}
			});
		});
		
		function escapeHtml(text) {
			var div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}
		
		function escapeAttr(text) {
			return text.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
		}
		
	});
})(jQuery);