(function($) {
	'use strict';

	function initMetaboxToggle() {
		var $container = $('.edit-post-meta-boxes-main');
		if (!$container.length) {
			return false;
		}
		var $toggleBtn = $container.find('> div > div > button[aria-expanded]');
		if (!$toggleBtn.length) {
			return false;
		}
		if ($container.data('probonoseo-init')) {
			return true;
		}
		$container.data('probonoseo-init', true);

		$container.css({
			'transition': 'height 0.3s ease',
			'overflow': 'hidden',
			'height': '36px'
		});

		$toggleBtn.find('svg').hide();
		$toggleBtn.css({
			'color': '#4a90e2',
			'font-weight': '700',
			'cursor': 'default'
		});

		var $label = $('<span>').text('AI\u88dc\u52a9\u3092\u958b\u304f').css({
			'display': 'inline-block',
			'margin-left': '10px',
			'padding': '2px 12px',
			'width': '120px',
			'text-align': 'center',
			'font-size': '13px',
			'font-weight': '600',
			'color': '#fff',
			'background': '#4a90e2',
			'border-radius': '3px',
			'cursor': 'pointer',
			'vertical-align': 'middle',
			'line-height': '22px'
		});
		$toggleBtn.append($label);

		$toggleBtn.off('click.probonoseo').on('click.probonoseo', function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
		});

		$label.on('click.probonoseo', function(e) {
			e.stopImmediatePropagation();
			var isOpen = $container.data('probonoseo-open');
			if (isOpen) {
				$container.css('height', '36px');
				$container.removeClass('probonoseo-panel-open');
				$container.data('probonoseo-open', false);
				$label.text('AI\u88dc\u52a9\u3092\u958b\u304f');
				$toggleBtn.attr('aria-expanded', 'false');
			} else {
				$container.css('height', 'calc(100vh - 60px)');
				$container.addClass('probonoseo-panel-open');
				$container.data('probonoseo-open', true);
				$label.text('AI\u88dc\u52a9\u3092\u9589\u3058\u308b');
				$toggleBtn.attr('aria-expanded', 'true');
			}
		});

		return true;
	}

	$(document).ready(function() {

		if (!initMetaboxToggle()) {
			var observer = new MutationObserver(function(mutations, obs) {
				if (initMetaboxToggle()) {
					obs.disconnect();
				}
			});
			observer.observe(document.body, { childList: true, subtree: true });
		}

		$('#probonoseo-generate-title').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-title-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u30bf\u30a4\u30c8\u30eb\u3092\u751f\u6210\u4e2d...</p></div>');
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
							html += '<div class="probonoseo-ai-result-meta" ' + lengthClass + '>' + item.length + '\u6587\u5b57</div>';
							if (item.description) {
								html += '<div class="probonoseo-ai-result-desc">' + escapeHtml(item.description) + '</div>';
							}
							html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-title" data-title="' + escapeAttr(item.title) + '" data-post-id="' + postId + '">\u3053\u306e\u30bf\u30a4\u30c8\u30eb\u3092\u9069\u7528</button>';
							html += '</div>';
						});
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u30bf\u30a4\u30c8\u30eb\u306e\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-apply-title', function() {
			var $btn = $(this);
			var title = $btn.data('title');
			var postId = $btn.data('post-id');
			if (!confirm('\u30bf\u30a4\u30c8\u30eb\u3092\u300c' + title + '\u300d\u306b\u5909\u66f4\u3057\u307e\u3059\u304b\uff1f')) {
				return;
			}
			$btn.prop('disabled', true).text('\u9069\u7528\u4e2d...');
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
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('\u9069\u7528\u6e08\u307f').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u9069\u7528\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						alert(message);
						$btn.prop('disabled', false).text('\u3053\u306e\u30bf\u30a4\u30c8\u30eb\u3092\u9069\u7528');
					}
				},
				error: function() {
					alert('\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002');
					$btn.prop('disabled', false).text('\u3053\u306e\u30bf\u30a4\u30c8\u30eb\u3092\u9069\u7528');
				}
			});
		});

		$('#probonoseo-generate-metadesc').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-metadesc-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u30e1\u30bf\u30c7\u30a3\u30b9\u30af\u30ea\u30d7\u30b7\u30e7\u30f3\u3092\u751f\u6210\u4e2d...</p></div>');
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
						html += '<div class="probonoseo-ai-result-meta">' + item.length + '\u6587\u5b57</div>';
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-metadesc" data-metadesc="' + escapeAttr(item.text) + '" data-post-id="' + postId + '">\u3053\u306e\u30e1\u30bf\u30c7\u30a3\u30b9\u30af\u30ea\u30d7\u30b7\u30e7\u30f3\u3092\u9069\u7528</button>';
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-apply-metadesc', function() {
			var $btn = $(this);
			var metadesc = $btn.data('metadesc');
			var postId = $btn.data('post-id');
			$btn.prop('disabled', true).text('\u9069\u7528\u4e2d...');
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
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('\u9069\u7528\u6e08\u307f').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u9069\u7528\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						alert(message);
						$btn.prop('disabled', false).text('\u3053\u306e\u30e1\u30bf\u30c7\u30a3\u30b9\u30af\u30ea\u30d7\u30b7\u30e7\u30f3\u3092\u9069\u7528');
					}
				},
				error: function() {
					alert('\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002');
					$btn.prop('disabled', false).text('\u3053\u306e\u30e1\u30bf\u30c7\u30a3\u30b9\u30af\u30ea\u30d7\u30b7\u30e7\u30f3\u3092\u9069\u7528');
				}
			});
		});

		$('#probonoseo-generate-heading').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $results = $('#probonoseo-heading-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u898b\u51fa\u3057\u63d0\u6848\u3092\u751f\u6210\u4e2d...</p></div>');
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
								html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-heading" data-original="' + escapeAttr(item.original_html) + '" data-new-text="' + escapeAttr(item.suggested) + '" data-level="' + item.level + '" data-post-id="' + postId + '">\u3053\u306e\u898b\u51fa\u3057\u3092\u9069\u7528</button>';
							} else {
								html += '<div class="probonoseo-ai-heading-current">' + escapeHtml(item.current) + '</div>';
								html += '<div class="probonoseo-ai-heading-reason">\u5909\u66f4\u4e0d\u8981</div>';
							}
							html += '</div>';
						});
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-apply-heading', function() {
			var $btn = $(this);
			var original = $btn.data('original');
			var newText = $btn.data('new-text');
			var level = $btn.data('level');
			var postId = $btn.data('post-id');
			$btn.prop('disabled', true).text('\u9069\u7528\u4e2d...');
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
						$btn.closest('.probonoseo-ai-heading-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('\u9069\u7528\u6e08\u307f').prop('disabled', true);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u9069\u7528\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						alert(message);
						$btn.prop('disabled', false).text('\u3053\u306e\u898b\u51fa\u3057\u3092\u9069\u7528');
					}
				},
				error: function() {
					alert('\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002');
					$btn.prop('disabled', false).text('\u3053\u306e\u898b\u51fa\u3057\u3092\u9069\u7528');
				}
			});
		});

		$('#probonoseo-generate-outline').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var keyword = $('#probonoseo-outline-keyword').val();
			var $results = $('#probonoseo-outline-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u8a18\u4e8b\u69cb\u6210\u3092\u751f\u6210\u4e2d...</p></div>');
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
									html += '<div class="probonoseo-ai-outline-h3">\u2514 ' + escapeHtml(h3.text) + '</div>';
								});
							}
							html += '</div>';
						});
						html += '<div class="probonoseo-ai-actions">';
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-outline" data-outline=\'' + JSON.stringify(response.data.outline).replace(/'/g, "&#39;") + '\' data-post-id="' + postId + '">\u8a18\u4e8b\u306b\u633f\u5165</button>';
						html += '</div>';
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-apply-outline', function() {
			var $btn = $(this);
			var outline = $btn.data('outline');
			var postId = $btn.data('post-id');
			if (!confirm('\u8a18\u4e8b\u306e\u5148\u982d\u306b\u69cb\u6210\u6848\u3092\u633f\u5165\u3057\u307e\u3059\u304b\uff1f')) {
				return;
			}
			$btn.prop('disabled', true).text('\u633f\u5165\u4e2d...');
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
						$btn.closest('.probonoseo-ai-result-item').addClass('probonoseo-ai-success').find('.probonoseo-ai-apply-btn').text('\u633f\u5165\u6e08\u307f').prop('disabled', true);
						alert('\u8a18\u4e8b\u69cb\u6210\u3092\u633f\u5165\u3057\u307e\u3057\u305f\u3002\u30da\u30fc\u30b8\u3092\u66f4\u65b0\u3057\u3066\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044\u3002');
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u633f\u5165\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						alert(message);
						$btn.prop('disabled', false).text('\u8a18\u4e8b\u306b\u633f\u5165');
					}
				},
				error: function() {
					alert('\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002');
					$btn.prop('disabled', false).text('\u8a18\u4e8b\u306b\u633f\u5165');
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
				alert('\u898b\u51fa\u3057\u3092\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002');
				return;
			}
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u672c\u6587\u3092\u751f\u6210\u4e2d...</p></div>');
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
						html += '<div class="probonoseo-ai-result-meta">' + item.word_count + '\u6587\u5b57</div>';
						html += '<div class="probonoseo-ai-actions">';
						html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(item.text) + '">\u30b3\u30d4\u30fc</button>';
						html += '</div>';
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-copy-body', function() {
			var text = $(this).data('text');
			if (navigator.clipboard) {
				navigator.clipboard.writeText(text).then(function() {
					alert('\u30af\u30ea\u30c3\u30d7\u30dc\u30fc\u30c9\u306b\u30b3\u30d4\u30fc\u3057\u307e\u3057\u305f\u3002');
				});
			} else {
				var textarea = document.createElement('textarea');
				textarea.value = text;
				document.body.appendChild(textarea);
				textarea.select();
				document.execCommand('copy');
				document.body.removeChild(textarea);
				alert('\u30af\u30ea\u30c3\u30d7\u30dc\u30fc\u30c9\u306b\u30b3\u30d4\u30fc\u3057\u307e\u3057\u305f\u3002');
			}
		});

		$('#probonoseo-generate-summary').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var style = $('#probonoseo-summary-style').val();
			var $results = $('#probonoseo-summary-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>\u8981\u7d04\u3092\u751f\u6210\u4e2d...</p></div>');
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
							html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(summary.text) + '">\u30b3\u30d4\u30fc</button>';
							html += '</div>';
						} else {
							html += '<ul class="probonoseo-ai-summary-list">';
							$.each(summary.items, function(i, item) {
								html += '<li>' + escapeHtml(item) + '</li>';
							});
							html += '</ul>';
							html += '<div class="probonoseo-ai-actions">';
							html += '<button type="button" class="button probonoseo-copy-body" data-text="' + escapeAttr(summary.items.join('\n')) + '">\u30b3\u30d4\u30fc</button>';
							html += '</div>';
						}
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$('#probonoseo-generate-faq').on('click', function() {
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var count = $('#probonoseo-faq-count').val();
			var $results = $('#probonoseo-faq-results');
			$btn.prop('disabled', true);
			$results.html('<div class="probonoseo-ai-loading"><span class="spinner is-active"></span><p>FAQ\u3092\u751f\u6210\u4e2d...</p></div>');
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
						html += '<button type="button" class="button probonoseo-ai-apply-btn probonoseo-apply-faq" data-faqs=\'' + JSON.stringify(response.data.faqs).replace(/'/g, "&#39;") + '\' data-post-id="' + postId + '">\u8a18\u4e8b\u306b\u633f\u5165</button>';
						html += '</div>';
						$results.html(html);
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u751f\u6210\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						$results.html('<div class="probonoseo-ai-error">' + escapeHtml(message) + '</div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$results.html('<div class="probonoseo-ai-error">\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002</div>');
				}
			});
		});

		$(document).on('click', '.probonoseo-apply-faq', function() {
			var $btn = $(this);
			var faqs = $btn.data('faqs');
			var postId = $btn.data('post-id');
			if (!confirm('\u8a18\u4e8b\u306e\u672b\u5c3e\u306bFAQ\u3092\u633f\u5165\u3057\u307e\u3059\u304b\uff1f')) {
				return;
			}
			$btn.prop('disabled', true).text('\u633f\u5165\u4e2d...');
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
						$btn.text('\u633f\u5165\u6e08\u307f').prop('disabled', true);
						alert('FAQ\u3092\u633f\u5165\u3057\u307e\u3057\u305f\u3002\u30da\u30fc\u30b8\u3092\u66f4\u65b0\u3057\u3066\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044\u3002');
					} else {
						var message = response.data && response.data.message ? response.data.message : '\u633f\u5165\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002';
						alert(message);
						$btn.prop('disabled', false).text('\u8a18\u4e8b\u306b\u633f\u5165');
					}
				},
				error: function() {
					alert('\u901a\u4fe1\u30a8\u30e9\u30fc\u304c\u767a\u751f\u3057\u307e\u3057\u305f\u3002');
					$btn.prop('disabled', false).text('\u8a18\u4e8b\u306b\u633f\u5165');
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