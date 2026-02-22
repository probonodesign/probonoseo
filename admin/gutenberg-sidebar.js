(function(wp) {
	'use strict';
	
	if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components || !wp.data) {
		return;
	}
	
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var Button = wp.components.Button;
	var Spinner = wp.components.Spinner;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var useSelect = wp.data.useSelect;
	
	var enabledFeatures = probonoseoGutenberg.enabledFeatures || {};
	
	function createSimpleAIComponent(config) {
		return function() {
			var _loadingState = useState(false);
			var isLoading = _loadingState[0];
			var setIsLoading = _loadingState[1];
			
			var _resultState = useState(null);
			var result = _resultState[0];
			var setResult = _resultState[1];
			
			var _errorState = useState(null);
			var error = _errorState[0];
			var setError = _errorState[1];
			
			var postId = useSelect(function(select) {
				return select('core/editor').getCurrentPostId();
			});
			
			var generate = function() {
				setIsLoading(true);
				setError(null);
				setResult(null);
				
				jQuery.ajax({
					url: probonoseoGutenberg.ajaxurl,
					type: 'POST',
					data: {
						action: config.action,
						nonce: probonoseoGutenberg.nonce,
						post_id: postId
					},
					success: function(response) {
						setIsLoading(false);
						if (response.success) {
							setResult(response.data);
						} else {
							setError(response.data && response.data.message ? response.data.message : config.errorMessage);
						}
					},
					error: function() {
						setIsLoading(false);
						setError('通信エラーが発生しました');
					}
				});
			};
			
			var children = [];
			
			children.push(createElement('h4', { style: { marginTop: 0 } }, config.title));
			children.push(createElement('p', { className: 'description' }, config.description));
			
			children.push(createElement(Button, {
				isPrimary: true,
				onClick: generate,
				disabled: isLoading,
				style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
			}, isLoading ? createElement(Spinner, null) : config.buttonText));
			
			if (error) {
				children.push(createElement('div', {
					style: {
						background: '#ffebee',
						border: '1px solid #ef5350',
						borderRadius: '4px',
						padding: '10px',
						color: '#c62828',
						fontSize: '12px',
						marginBottom: '12px'
					}
				}, error));
			}
			
			if (result && config.renderResult) {
				children.push(config.renderResult(result));
			}
			
			return createElement('div', { style: { marginBottom: '20px' } }, children);
		};
	}
	
	var AITitleGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultsState = useState(null);
		var results = _resultsState[0];
		var setResults = _resultsState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateTitle = function() {
			setIsLoading(true);
			setError(null);
			setResults(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_title',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.titles) {
						setResults(response.data.titles);
					} else {
						setError(response.data && response.data.message ? response.data.message : 'タイトル生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var applyTitle = function(title) {
			wp.data.dispatch('core/editor').editPost({ title: title });
			setResults(null);
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AIタイトル提案'));
		children.push(createElement('p', { className: 'description' }, '記事内容から最適なタイトルを3パターン提案します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateTitle,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : 'タイトル提案を生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (results && results.length > 0) {
			results.forEach(function(item, index) {
				var lengthColor = '#666';
				if (item.length > 60) {
					lengthColor = '#e65100';
				} else if (item.length >= 30 && item.length <= 60) {
					lengthColor = '#2e7d32';
				}
				
				children.push(createElement('div', {
					key: index,
					style: {
						background: '#f9f9f9',
						border: '1px solid #e0e0e0',
						borderRadius: '4px',
						padding: '10px',
						marginBottom: '8px'
					}
				}, [
					createElement('div', {
						style: { fontWeight: 600, fontSize: '13px', marginBottom: '4px', wordBreak: 'break-all' }
					}, item.title),
					createElement('div', {
						style: { fontSize: '11px', color: lengthColor, marginBottom: '6px' }
					}, item.length + '文字'),
					item.description ? createElement('div', {
						style: { fontSize: '11px', color: '#888', marginBottom: '8px' }
					}, item.description) : null,
					createElement(Button, {
						isSecondary: true,
						isSmall: true,
						onClick: function() { applyTitle(item.title); }
					}, 'このタイトルを適用')
				]));
			});
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIMetaDescGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateMetaDesc = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_metadesc',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.metadesc) {
						setResult(response.data.metadesc);
					} else {
						setError(response.data && response.data.message ? response.data.message : 'メタD生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var applyMetaDesc = function(text) {
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_metadesc',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					metadesc: text
				},
				success: function(response) {
					if (response.success) {
						setResult(null);
						alert('メタディスクリプションを保存しました');
					}
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AIメタディスクリプション'));
		children.push(createElement('p', { className: 'description' }, '記事内容から最適なメタディスクリプションを生成します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateMetaDesc,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : 'メタD生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (result) {
			children.push(createElement('div', {
				style: {
					background: '#f9f9f9',
					border: '1px solid #e0e0e0',
					borderRadius: '4px',
					padding: '10px',
					marginBottom: '8px'
				}
			}, [
				createElement('div', {
					style: { fontWeight: 600, fontSize: '13px', marginBottom: '4px', wordBreak: 'break-all' }
				}, result.text),
				createElement('div', {
					style: { fontSize: '11px', color: '#666', marginBottom: '8px' }
				}, result.length + '文字'),
				createElement(Button, {
					isSecondary: true,
					isSmall: true,
					onClick: function() { applyMetaDesc(result.text); }
				}, 'このメタDを適用')
			]));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIHeadingGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultsState = useState(null);
		var results = _resultsState[0];
		var setResults = _resultsState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateHeading = function() {
			setIsLoading(true);
			setError(null);
			setResults(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_heading',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.suggestions) {
						setResults(response.data.suggestions);
					} else {
						setError(response.data && response.data.message ? response.data.message : '見出し提案に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var applyHeading = function(item) {
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_heading',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					original_html: item.original_html,
					new_text: item.suggested,
					level: item.level
				},
				success: function(response) {
					if (response.success) {
						alert('見出しを更新しました。ページを更新して確認してください。');
					} else {
						alert(response.data && response.data.message ? response.data.message : '適用に失敗しました');
					}
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI見出し提案'));
		children.push(createElement('p', { className: 'description' }, '記事内のH2/H3見出しを改善提案します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateHeading,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '見出し提案を生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (results && results.length > 0) {
			results.forEach(function(item, index) {
				if (!item.is_changed) return;
				
				children.push(createElement('div', {
					key: index,
					style: {
						background: '#f9f9f9',
						border: '1px solid #e0e0e0',
						borderRadius: '4px',
						padding: '10px',
						marginBottom: '8px'
					}
				}, [
					createElement('span', {
						style: {
							display: 'inline-block',
							background: item.level === 3 ? '#7b1fa2' : '#1976d2',
							color: '#fff',
							fontSize: '10px',
							fontWeight: 600,
							padding: '2px 6px',
							borderRadius: '3px',
							marginRight: '6px'
						}
					}, 'H' + item.level),
					createElement('div', {
						style: { fontSize: '12px', color: '#666', margin: '6px 0', textDecoration: 'line-through' }
					}, item.current),
					createElement('div', {
						style: { fontWeight: 600, fontSize: '13px', margin: '6px 0' }
					}, item.suggested),
					item.reason ? createElement('div', {
						style: { fontSize: '11px', color: '#888', marginBottom: '8px' }
					}, item.reason) : null,
					createElement(Button, {
						isSecondary: true,
						isSmall: true,
						onClick: function() { applyHeading(item); }
					}, 'この見出しを適用')
				]));
			});
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIOutlineGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _keywordState = useState('');
		var keyword = _keywordState[0];
		var setKeyword = _keywordState[1];
		
		var _resultsState = useState(null);
		var results = _resultsState[0];
		var setResults = _resultsState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateOutline = function() {
			setIsLoading(true);
			setError(null);
			setResults(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_outline',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					keyword: keyword
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.outline) {
						setResults(response.data.outline);
					} else {
						setError(response.data && response.data.message ? response.data.message : '構成案生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var applyOutline = function() {
			if (!confirm('記事の先頭に構成案を挿入しますか？')) return;
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_outline',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					outline: JSON.stringify(results)
				},
				success: function(response) {
					if (response.success) {
						setResults(null);
						alert('記事構成を挿入しました。ページを更新して確認してください。');
					} else {
						alert(response.data && response.data.message ? response.data.message : '挿入に失敗しました');
					}
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI記事構成案'));
		children.push(createElement('p', { className: 'description' }, 'キーワードから記事構成（目次）を生成します。'));
		
		children.push(createElement(TextControl, {
			label: 'キーワード（任意）',
			value: keyword,
			onChange: setKeyword,
			placeholder: '例：WordPress SEO'
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateOutline,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '構成案を生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (results && results.length > 0) {
			var outlineItems = [];
			results.forEach(function(h2, index) {
				outlineItems.push(createElement('div', {
					key: 'h2-' + index,
					style: { fontWeight: 600, fontSize: '13px', marginBottom: '4px' }
				}, h2.text));
				
				if (h2.children && h2.children.length > 0) {
					h2.children.forEach(function(h3, h3Index) {
						outlineItems.push(createElement('div', {
							key: 'h3-' + index + '-' + h3Index,
							style: { fontSize: '12px', color: '#666', paddingLeft: '12px', marginBottom: '4px' }
						}, '└ ' + h3.text));
					});
				}
			});
			
			children.push(createElement('div', {
				style: {
					background: '#f9f9f9',
					border: '1px solid #e0e0e0',
					borderRadius: '4px',
					padding: '10px',
					marginBottom: '8px'
				}
			}, outlineItems));
			
			children.push(createElement(Button, {
				isSecondary: true,
				onClick: applyOutline,
				style: { width: '100%', justifyContent: 'center' }
			}, '記事に挿入'));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIBodyGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _headingState = useState('');
		var heading = _headingState[0];
		var setHeading = _headingState[1];
		
		var _contextState = useState('');
		var context = _contextState[0];
		var setContext = _contextState[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateBody = function() {
			if (!heading) {
				alert('見出しを入力してください。');
				return;
			}
			
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_body',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					heading: heading,
					context: context
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.body) {
						setResult(response.data.body);
					} else {
						setError(response.data && response.data.message ? response.data.message : '本文生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var copyText = function() {
			if (navigator.clipboard) {
				navigator.clipboard.writeText(result.text).then(function() {
					alert('クリップボードにコピーしました。');
				});
			}
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI本文生成補助'));
		children.push(createElement('p', { className: 'description' }, '見出しに対する本文を生成します。'));
		
		children.push(createElement(TextControl, {
			label: '見出し',
			value: heading,
			onChange: setHeading,
			placeholder: '例：WordPressのSEO設定方法'
		}));
		
		children.push(createElement(TextareaControl, {
			label: '追加指示（任意）',
			value: context,
			onChange: setContext,
			placeholder: '例：初心者向けに説明',
			rows: 2
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateBody,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '本文を生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (result) {
			children.push(createElement('div', {
				style: {
					background: '#f9f9f9',
					border: '1px solid #e0e0e0',
					borderRadius: '4px',
					padding: '10px',
					marginBottom: '8px',
					maxHeight: '150px',
					overflowY: 'auto',
					fontSize: '12px',
					lineHeight: '1.6'
				}
			}, result.text));
			
			children.push(createElement('div', {
				style: { fontSize: '11px', color: '#666', marginBottom: '8px' }
			}, result.word_count + '文字'));
			
			children.push(createElement(Button, {
				isSecondary: true,
				onClick: copyText,
				style: { width: '100%', justifyContent: 'center' }
			}, 'コピー'));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AISummaryGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _styleState = useState('points');
		var style = _styleState[0];
		var setStyle = _styleState[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateSummary = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_summary',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					style: style
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.summary) {
						setResult(response.data.summary);
					} else {
						setError(response.data && response.data.message ? response.data.message : '要約生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var copyText = function() {
			var text = result.type === 'paragraph' ? result.text : result.items.join('\n');
			if (navigator.clipboard) {
				navigator.clipboard.writeText(text).then(function() {
					alert('クリップボードにコピーしました。');
				});
			}
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI要約生成'));
		children.push(createElement('p', { className: 'description' }, '記事の要点をまとめます。'));
		
		children.push(createElement(SelectControl, {
			label: '要約形式',
			value: style,
			onChange: setStyle,
			options: [
				{ label: '箇条書き（ポイント）', value: 'points' },
				{ label: '1段落の文章', value: 'paragraph' },
				{ label: 'この記事でわかること', value: 'learn' }
			]
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateSummary,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '要約を生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (result) {
			var summaryContent;
			if (result.type === 'paragraph') {
				summaryContent = createElement('div', {
					style: { fontSize: '12px', lineHeight: '1.6' }
				}, result.text);
			} else {
				summaryContent = createElement('ul', {
					style: { margin: 0, paddingLeft: '20px' }
				}, result.items.map(function(item, i) {
					return createElement('li', { key: i, style: { fontSize: '12px', marginBottom: '4px' } }, item);
				}));
			}
			
			children.push(createElement('div', {
				style: {
					background: '#f9f9f9',
					border: '1px solid #e0e0e0',
					borderRadius: '4px',
					padding: '10px',
					marginBottom: '8px'
				}
			}, summaryContent));
			
			children.push(createElement(Button, {
				isSecondary: true,
				onClick: copyText,
				style: { width: '100%', justifyContent: 'center' }
			}, 'コピー'));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIFAQGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _countState = useState('5');
		var count = _countState[0];
		var setCount = _countState[1];
		
		var _resultsState = useState(null);
		var results = _resultsState[0];
		var setResults = _resultsState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generateFAQ = function() {
			setIsLoading(true);
			setError(null);
			setResults(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_faq',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					count: count
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.faqs) {
						setResults(response.data.faqs);
					} else {
						setError(response.data && response.data.message ? response.data.message : 'FAQ生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var applyFAQ = function() {
			if (!confirm('記事の末尾にFAQを挿入しますか？')) return;
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_apply_ai_faq',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					faqs: JSON.stringify(results),
					include_schema: 'true'
				},
				success: function(response) {
					if (response.success) {
						setResults(null);
						alert('FAQを挿入しました。ページを更新して確認してください。');
					} else {
						alert(response.data && response.data.message ? response.data.message : '挿入に失敗しました');
					}
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI FAQ生成'));
		children.push(createElement('p', { className: 'description' }, '記事内容からQ&Aを自動生成します。'));
		
		children.push(createElement(SelectControl, {
			label: '生成数',
			value: count,
			onChange: setCount,
			options: [
				{ label: '3個', value: '3' },
				{ label: '5個', value: '5' },
				{ label: '7個', value: '7' },
				{ label: '10個', value: '10' }
			]
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generateFAQ,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : 'FAQ生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: {
					background: '#ffebee',
					border: '1px solid #ef5350',
					borderRadius: '4px',
					padding: '10px',
					color: '#c62828',
					fontSize: '12px',
					marginBottom: '12px'
				}
			}, error));
		}
		
		if (results && results.length > 0) {
			results.forEach(function(faq, index) {
				children.push(createElement('div', {
					key: index,
					style: {
						background: '#f9f9f9',
						border: '1px solid #e0e0e0',
						borderRadius: '4px',
						padding: '10px',
						marginBottom: '8px'
					}
				}, [
					createElement('div', {
						style: { fontWeight: 600, fontSize: '12px', color: '#1565c0', marginBottom: '6px' }
					}, 'Q. ' + faq.question),
					createElement('div', {
						style: { fontSize: '12px', lineHeight: '1.5' }
					}, 'A. ' + faq.answer)
				]));
			});
			
			children.push(createElement(Button, {
				isSecondary: true,
				onClick: applyFAQ,
				style: { width: '100%', justifyContent: 'center' }
			}, '記事に挿入'));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIKeywordsGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generate = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_keywords',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.keywords) {
						setResult(response.data.keywords);
					} else {
						setError(response.data && response.data.message ? response.data.message : 'キーワード抽出に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI関連キーワード抽出'));
		children.push(createElement('p', { className: 'description' }, '記事からキーワードを自動抽出します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generate,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : 'キーワード抽出'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			var sections = [
				{ key: 'main', label: 'メインキーワード', color: '#1565c0' },
				{ key: 'sub', label: 'サブキーワード', color: '#2e7d32' },
				{ key: 'related', label: '関連キーワード', color: '#ed6c02' },
				{ key: 'longtail', label: 'ロングテール', color: '#7b1fa2' }
			];
			
			sections.forEach(function(section) {
				if (result[section.key] && result[section.key].length > 0) {
					children.push(createElement('div', {
						key: section.key,
						style: { marginBottom: '12px' }
					}, [
						createElement('div', {
							style: { fontWeight: 600, fontSize: '12px', color: section.color, marginBottom: '6px' }
						}, section.label),
						createElement('div', {
							style: { display: 'flex', flexWrap: 'wrap', gap: '4px' }
						}, result[section.key].map(function(kw, i) {
							return createElement('span', {
								key: i,
								style: {
									background: '#f0f0f0',
									padding: '2px 8px',
									borderRadius: '12px',
									fontSize: '11px'
								}
							}, kw.keyword);
						}))
					]));
				}
			});
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIRewriteGenerator = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _textState = useState('');
		var text = _textState[0];
		var setText = _textState[1];
		
		var _styleState = useState('improve');
		var style = _styleState[0];
		var setStyle = _styleState[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var generate = function() {
			if (!text) {
				alert('リライト対象のテキストを入力してください。');
				return;
			}
			
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_generate_ai_rewrite',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					text: text,
					style: style
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.rewrite) {
						setResult(response.data.rewrite);
					} else {
						setError(response.data && response.data.message ? response.data.message : 'リライト生成に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var copyText = function() {
			if (navigator.clipboard && result) {
				navigator.clipboard.writeText(result.text).then(function() {
					alert('クリップボードにコピーしました。');
				});
			}
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AIリライト提案'));
		children.push(createElement('p', { className: 'description' }, '文章を改善・リライトします。'));
		
		children.push(createElement(TextareaControl, {
			label: 'リライト対象テキスト',
			value: text,
			onChange: setText,
			placeholder: 'リライトしたい文章を入力',
			rows: 3
		}));
		
		children.push(createElement(SelectControl, {
			label: 'リライトスタイル',
			value: style,
			onChange: setStyle,
			options: [
				{ label: '全般的な改善', value: 'improve' },
				{ label: 'シンプルに', value: 'simple' },
				{ label: 'プロフェッショナルに', value: 'professional' },
				{ label: 'フレンドリーに', value: 'friendly' },
				{ label: '簡潔に', value: 'concise' },
				{ label: '詳細に', value: 'detailed' },
				{ label: 'SEO最適化', value: 'seo' }
			]
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: generate,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : 'リライト生成'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			children.push(createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', marginBottom: '8px', fontSize: '12px', lineHeight: '1.6' }
			}, result.text));
			
			if (result.improvements && result.improvements.length > 0) {
				children.push(createElement('div', { style: { fontSize: '11px', color: '#666', marginBottom: '8px' } }, [
					createElement('strong', null, '改善ポイント:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						result.improvements.map(function(imp, i) {
							return createElement('li', { key: i }, imp);
						})
					)
				]));
			}
			
			children.push(createElement(Button, {
				isSecondary: true,
				onClick: copyText,
				style: { width: '100%', justifyContent: 'center' }
			}, 'コピー'));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIReadabilityChecker = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var check = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_check_ai_readability',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.readability) {
						setResult(response.data.readability);
					} else {
						setError(response.data && response.data.message ? response.data.message : '読みやすさチェックに失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI読みやすさチェック'));
		children.push(createElement('p', { className: 'description' }, '文章の読みやすさを評価します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: check,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '読みやすさチェック'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			var scoreColor = result.score >= 70 ? '#2e7d32' : (result.score >= 50 ? '#ed6c02' : '#c62828');
			
			children.push(createElement('div', {
				style: { textAlign: 'center', marginBottom: '12px' }
			}, [
				createElement('div', { style: { fontSize: '32px', fontWeight: 700, color: scoreColor } }, result.score),
				createElement('div', { style: { fontSize: '12px', color: '#666' } }, '読みやすさスコア')
			]));
			
			if (result.target_audience) {
				children.push(createElement('div', {
					style: { background: '#e3f2fd', padding: '8px', borderRadius: '4px', fontSize: '12px', marginBottom: '8px' }
				}, '想定読者: ' + result.target_audience));
			}
			
			if (result.suggestions && result.suggestions.length > 0) {
				children.push(createElement('div', { style: { fontSize: '11px', color: '#666' } }, [
					createElement('strong', null, '改善提案:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						result.suggestions.map(function(s, i) {
							return createElement('li', { key: i }, s);
						})
					)
				]));
			}
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AISentimentAnalyzer = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var analyze = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_analyze_ai_sentiment',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.sentiment) {
						setResult(response.data.sentiment);
					} else {
						setError(response.data && response.data.message ? response.data.message : '感情分析に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI感情分析'));
		children.push(createElement('p', { className: 'description' }, '記事のトーンと感情を分析します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: analyze,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '感情分析'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			children.push(createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', marginBottom: '8px' }
			}, [
				result.tone ? createElement('div', { style: { fontSize: '12px', marginBottom: '6px' } }, [
					createElement('strong', null, 'トーン: '),
					result.tone
				]) : null,
				result.emotion && result.emotion.tendency ? createElement('div', { style: { fontSize: '12px', marginBottom: '6px' } }, [
					createElement('strong', null, '感情傾向: '),
					result.emotion.tendency + ' (' + result.emotion.intensity + '/100)'
				]) : null,
				result.scores ? createElement('div', { style: { fontSize: '12px', marginBottom: '6px' } }, [
					createElement('div', null, '説得力: ' + result.scores.persuasion + '/100'),
					createElement('div', null, '信頼性: ' + result.scores.credibility + '/100')
				]) : null,
				result.purpose ? createElement('div', { style: { fontSize: '12px', marginTop: '8px', padding: '6px', background: '#e8f5e9', borderRadius: '4px' } }, [
					createElement('strong', null, '記事の目的: '),
					result.purpose
				]) : null
			]));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIDuplicateChecker = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var check = function() {
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_check_ai_duplicate',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.duplicate) {
						setResult(response.data.duplicate);
					} else {
						setError(response.data && response.data.message ? response.data.message : '重複チェックに失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI重複コンテンツチェック'));
		children.push(createElement('p', { className: 'description' }, '重複表現や類似記事を検出します。'));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: check,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '重複チェック'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			var scoreColor = result.originality_score >= 70 ? '#2e7d32' : (result.originality_score >= 50 ? '#ed6c02' : '#c62828');
			
			children.push(createElement('div', {
				style: { textAlign: 'center', marginBottom: '12px' }
			}, [
				createElement('div', { style: { fontSize: '32px', fontWeight: 700, color: scoreColor } }, result.originality_score),
				createElement('div', { style: { fontSize: '12px', color: '#666' } }, 'オリジナリティスコア')
			]));
			
			if (result.similar_posts && result.similar_posts.length > 0) {
				children.push(createElement('div', { style: { fontSize: '11px', marginBottom: '8px' } }, [
					createElement('strong', null, '類似記事:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						result.similar_posts.map(function(post, i) {
							return createElement('li', { key: i }, post.title + ' (' + post.similarity + '%)');
						})
					)
				]));
			}
			
			if (result.suggestions && result.suggestions.length > 0) {
				children.push(createElement('div', { style: { fontSize: '11px', color: '#666' } }, [
					createElement('strong', null, '改善提案:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						result.suggestions.map(function(s, i) {
							return createElement('li', { key: i }, s);
						})
					)
				]));
			}
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AITargetAnalyzer = createSimpleAIComponent({
		title: 'AIターゲット読者分析',
		description: '想定読者層を分析します。',
		buttonText: 'ターゲット分析',
		action: 'probonoseo_analyze_ai_target',
		errorMessage: 'ターゲット分析に失敗しました',
		renderResult: function(data) {
			if (!data.target) return null;
			var t = data.target;
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, [
				t.age_group ? createElement('div', { style: { marginBottom: '4px' } }, '年齢層: ' + t.age_group) : null,
				t.knowledge_level ? createElement('div', { style: { marginBottom: '4px' } }, '知識レベル: ' + t.knowledge_level) : null,
				t.interests && t.interests.length > 0 ? createElement('div', null, '関心事: ' + t.interests.join(', ')) : null
			]);
		}
	});
	
	var AIIntentAnalyzer = function() {
		var _state = useState(false);
		var isLoading = _state[0];
		var setIsLoading = _state[1];
		
		var _keywordState = useState('');
		var keyword = _keywordState[0];
		var setKeyword = _keywordState[1];
		
		var _resultState = useState(null);
		var result = _resultState[0];
		var setResult = _resultState[1];
		
		var _errorState = useState(null);
		var error = _errorState[0];
		var setError = _errorState[1];
		
		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});
		
		var analyze = function() {
			if (!keyword) {
				alert('分析するキーワードを入力してください。');
				return;
			}
			
			setIsLoading(true);
			setError(null);
			setResult(null);
			
			jQuery.ajax({
				url: probonoseoGutenberg.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_analyze_ai_intent',
					nonce: probonoseoGutenberg.nonce,
					post_id: postId,
					keyword: keyword
				},
				success: function(response) {
					setIsLoading(false);
					if (response.success && response.data.intent) {
						setResult(response.data.intent);
					} else {
						setError(response.data && response.data.message ? response.data.message : '検索意図分析に失敗しました');
					}
				},
				error: function() {
					setIsLoading(false);
					setError('通信エラーが発生しました');
				}
			});
		};
		
		var children = [];
		
		children.push(createElement('h4', { style: { marginTop: 0 } }, 'AI検索意図分析'));
		children.push(createElement('p', { className: 'description' }, 'キーワードの検索意図を分析します。'));
		
		children.push(createElement(TextControl, {
			label: '分析キーワード',
			value: keyword,
			onChange: setKeyword,
			placeholder: '例：WordPress SEO 設定'
		}));
		
		children.push(createElement(Button, {
			isPrimary: true,
			onClick: analyze,
			disabled: isLoading,
			style: { width: '100%', justifyContent: 'center', marginBottom: '12px' }
		}, isLoading ? createElement(Spinner, null) : '検索意図分析'));
		
		if (error) {
			children.push(createElement('div', {
				style: { background: '#ffebee', border: '1px solid #ef5350', borderRadius: '4px', padding: '10px', color: '#c62828', fontSize: '12px', marginBottom: '12px' }
			}, error));
		}
		
		if (result) {
			children.push(createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, [
				result.primary_intent ? createElement('div', { style: { marginBottom: '6px' } }, [
					createElement('strong', null, '主な検索意図: '),
					result.primary_intent
				]) : null,
				result.user_needs && result.user_needs.length > 0 ? createElement('div', null, [
					createElement('strong', null, 'ユーザーのニーズ:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						result.user_needs.map(function(need, i) {
							return createElement('li', { key: i }, need);
						})
					)
				]) : null
			]));
		}
		
		return createElement('div', { style: { marginBottom: '20px' } }, children);
	};
	
	var AIGapAnalyzer = createSimpleAIComponent({
		title: 'AIコンテンツギャップ分析',
		description: '不足しているコンテンツを提案します。',
		buttonText: 'ギャップ分析',
		action: 'probonoseo_analyze_ai_gap',
		errorMessage: 'ギャップ分析に失敗しました',
		renderResult: function(data) {
			if (!data.gap) return null;
			var g = data.gap;
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, [
				g.missing_topics && g.missing_topics.length > 0 ? createElement('div', null, [
					createElement('strong', null, '不足トピック:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						g.missing_topics.map(function(t, i) {
							return createElement('li', { key: i }, t);
						})
					)
				]) : null
			]);
		}
	});
	
	var AICaptionGenerator = createSimpleAIComponent({
		title: 'AI画像キャプション生成',
		description: '記事内画像のキャプションを生成します。',
		buttonText: 'キャプション生成',
		action: 'probonoseo_generate_ai_caption',
		errorMessage: 'キャプション生成に失敗しました',
		renderResult: function(data) {
			if (!data.captions || data.captions.length === 0) return createElement('div', { style: { fontSize: '12px', color: '#666' } }, '画像が見つかりませんでした。');
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, data.captions.map(function(cap, i) {
				return createElement('div', { key: i, style: { marginBottom: '8px', paddingBottom: '8px', borderBottom: i < data.captions.length - 1 ? '1px solid #e0e0e0' : 'none' } }, [
					createElement('div', { style: { fontWeight: 600, marginBottom: '4px' } }, 'キャプション: ' + cap.caption),
					createElement('div', { style: { color: '#666' } }, 'Alt: ' + cap.alt)
				]);
			}));
		}
	});
	
	var AIInternalLinkSuggester = createSimpleAIComponent({
		title: 'AI内部リンク提案',
		description: '関連する内部リンク先を提案します。',
		buttonText: '内部リンク提案',
		action: 'probonoseo_suggest_ai_internal',
		errorMessage: '内部リンク提案に失敗しました',
		renderResult: function(data) {
			if (!data.links || data.links.length === 0) return createElement('div', { style: { fontSize: '12px', color: '#666' } }, '提案できるリンクがありませんでした。');
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, data.links.map(function(link, i) {
				return createElement('div', { key: i, style: { marginBottom: '8px' } }, [
					createElement('a', { href: link.url, target: '_blank', style: { fontWeight: 600 } }, link.title),
					link.anchor_text ? createElement('div', { style: { color: '#666', fontSize: '11px' } }, 'アンカーテキスト案: ' + link.anchor_text) : null
				]);
			}));
		}
	});
	
	var AIExternalLinkSuggester = createSimpleAIComponent({
		title: 'AI外部リンク提案',
		description: '信頼性の高い外部リンク先を提案します。',
		buttonText: '外部リンク提案',
		action: 'probonoseo_suggest_ai_external',
		errorMessage: '外部リンク提案に失敗しました',
		renderResult: function(data) {
			if (!data.links || data.links.length === 0) return createElement('div', { style: { fontSize: '12px', color: '#666' } }, '提案できるリンクがありませんでした。');
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, data.links.map(function(link, i) {
				return createElement('div', { key: i, style: { marginBottom: '8px' } }, [
					createElement('div', { style: { fontWeight: 600 } }, link.title || link.domain),
					createElement('div', { style: { color: '#666', fontSize: '11px' } }, link.reason)
				]);
			}));
		}
	});
	
	var AIUpdateSuggester = createSimpleAIComponent({
		title: 'AIコンテンツ更新提案',
		description: '更新すべき箇所を提案します。',
		buttonText: '更新提案',
		action: 'probonoseo_suggest_ai_update',
		errorMessage: '更新提案に失敗しました',
		renderResult: function(data) {
			if (!data.updates) return null;
			var u = data.updates;
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px', fontSize: '12px' }
			}, [
				u.outdated && u.outdated.length > 0 ? createElement('div', { style: { marginBottom: '8px' } }, [
					createElement('strong', null, '古い情報:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						u.outdated.map(function(item, i) {
							return createElement('li', { key: i }, item);
						})
					)
				]) : null,
				u.suggestions && u.suggestions.length > 0 ? createElement('div', null, [
					createElement('strong', null, '更新提案:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						u.suggestions.map(function(s, i) {
							return createElement('li', { key: i }, s);
						})
					)
				]) : null
			]);
		}
	});
	
	var AIPerformancePredictor = createSimpleAIComponent({
		title: 'AI記事パフォーマンス予測',
		description: 'SEOパフォーマンスを予測します。',
		buttonText: 'パフォーマンス予測',
		action: 'probonoseo_predict_ai_performance',
		errorMessage: 'パフォーマンス予測に失敗しました',
		renderResult: function(data) {
			if (!data.performance) return null;
			var p = data.performance;
			var scoreColor = p.overall_score >= 70 ? '#2e7d32' : (p.overall_score >= 50 ? '#ed6c02' : '#c62828');
			return createElement('div', {
				style: { background: '#f9f9f9', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '10px' }
			}, [
				createElement('div', { style: { textAlign: 'center', marginBottom: '12px' } }, [
					createElement('div', { style: { fontSize: '32px', fontWeight: 700, color: scoreColor } }, p.overall_score),
					createElement('div', { style: { fontSize: '12px', color: '#666' } }, '予測スコア')
				]),
				p.ranking_potential ? createElement('div', { style: { fontSize: '12px', marginBottom: '4px' } }, '順位ポテンシャル: ' + p.ranking_potential) : null,
				p.traffic_estimate ? createElement('div', { style: { fontSize: '12px', marginBottom: '4px' } }, '予測トラフィック: ' + p.traffic_estimate) : null,
				p.recommendations && p.recommendations.length > 0 ? createElement('div', { style: { fontSize: '11px', marginTop: '8px' } }, [
					createElement('strong', null, '改善推奨:'),
					createElement('ul', { style: { margin: '4px 0 0 16px', padding: 0 } },
						p.recommendations.map(function(r, i) {
							return createElement('li', { key: i }, r);
						})
					)
				]) : null
			]);
		}
	});
	
	var ProbonoSEOSidebar = function() {
		var sidebarContent = [];
		var sectionCount = 0;
		
		if (enabledFeatures.title) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-title' }));
			sidebarContent.push(createElement(AITitleGenerator, { key: 'title' }));
			sectionCount++;
		}
		
		if (enabledFeatures.metadesc) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-metadesc' }));
			sidebarContent.push(createElement(AIMetaDescGenerator, { key: 'metadesc' }));
			sectionCount++;
		}
		
		if (enabledFeatures.heading) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-heading' }));
			sidebarContent.push(createElement(AIHeadingGenerator, { key: 'heading' }));
			sectionCount++;
		}
		
		if (enabledFeatures.outline) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-outline' }));
			sidebarContent.push(createElement(AIOutlineGenerator, { key: 'outline' }));
			sectionCount++;
		}
		
		if (enabledFeatures.body) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-body' }));
			sidebarContent.push(createElement(AIBodyGenerator, { key: 'body' }));
			sectionCount++;
		}
		
		if (enabledFeatures.summary) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-summary' }));
			sidebarContent.push(createElement(AISummaryGenerator, { key: 'summary' }));
			sectionCount++;
		}
		
		if (enabledFeatures.faq) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-faq' }));
			sidebarContent.push(createElement(AIFAQGenerator, { key: 'faq' }));
			sectionCount++;
		}
		
		if (enabledFeatures.keywords) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-keywords' }));
			sidebarContent.push(createElement(AIKeywordsGenerator, { key: 'keywords' }));
			sectionCount++;
		}
		
		if (enabledFeatures.rewrite) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-rewrite' }));
			sidebarContent.push(createElement(AIRewriteGenerator, { key: 'rewrite' }));
			sectionCount++;
		}
		
		if (enabledFeatures.readability) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-readability' }));
			sidebarContent.push(createElement(AIReadabilityChecker, { key: 'readability' }));
			sectionCount++;
		}
		
		if (enabledFeatures.sentiment) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-sentiment' }));
			sidebarContent.push(createElement(AISentimentAnalyzer, { key: 'sentiment' }));
			sectionCount++;
		}
		
		if (enabledFeatures.duplicate) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-duplicate' }));
			sidebarContent.push(createElement(AIDuplicateChecker, { key: 'duplicate' }));
			sectionCount++;
		}
		
		if (enabledFeatures.target) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-target' }));
			sidebarContent.push(createElement(AITargetAnalyzer, { key: 'target' }));
			sectionCount++;
		}
		
		if (enabledFeatures.intent) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-intent' }));
			sidebarContent.push(createElement(AIIntentAnalyzer, { key: 'intent' }));
			sectionCount++;
		}
		
		if (enabledFeatures.gap) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-gap' }));
			sidebarContent.push(createElement(AIGapAnalyzer, { key: 'gap' }));
			sectionCount++;
		}
		
		if (enabledFeatures.caption) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-caption' }));
			sidebarContent.push(createElement(AICaptionGenerator, { key: 'caption' }));
			sectionCount++;
		}
		
		if (enabledFeatures.internalLink) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-internal' }));
			sidebarContent.push(createElement(AIInternalLinkSuggester, { key: 'internal' }));
			sectionCount++;
		}
		
		if (enabledFeatures.externalLink) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-external' }));
			sidebarContent.push(createElement(AIExternalLinkSuggester, { key: 'external' }));
			sectionCount++;
		}
		
		if (enabledFeatures.update) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-update' }));
			sidebarContent.push(createElement(AIUpdateSuggester, { key: 'update' }));
			sectionCount++;
		}
		
		if (enabledFeatures.performance) {
			if (sectionCount > 0) sidebarContent.push(createElement('hr', { key: 'hr-performance' }));
			sidebarContent.push(createElement(AIPerformancePredictor, { key: 'performance' }));
			sectionCount++;
		}
		
		if (sectionCount > 0) {
			sidebarContent.push(createElement('div', {
				key: 'help',
				style: { marginTop: '20px', paddingTop: '12px', borderTop: '1px solid #e0e0e0' }
			}, createElement('p', {
				style: { margin: 0, color: '#888', fontSize: '11px' }
			}, [
				'※ 生成にはOpenAI APIを使用します。',
				createElement('a', {
					href: 'https://platform.openai.com/settings/organization/limits',
					target: '_blank',
					style: { marginLeft: '4px' }
				}, '使用状況を確認')
			])));
		} else {
			sidebarContent.push(createElement('div', {
				key: 'no-features',
				style: { padding: '20px', textAlign: 'center', color: '#666' }
			}, [
				createElement('p', null, 'AI機能が有効になっていません。'),
				createElement('a', {
					href: '/wp-admin/admin.php?page=probonoseo&tab=ai'
				}, '設定画面で有効化')
			]));
		}
		
		return createElement(Fragment, null, [
			createElement(PluginSidebarMoreMenuItem, {
				target: 'probonoseo-ai-sidebar',
				icon: 'chart-area'
			}, 'ProbonoSEO AI'),
			createElement(PluginSidebar, {
				name: 'probonoseo-ai-sidebar',
				icon: 'chart-area',
				title: 'ProbonoSEO AI補助'
			}, createElement('div', { style: { padding: '16px' } }, sidebarContent))
		]);
	};
	
	registerPlugin('probonoseo-ai', {
		render: ProbonoSEOSidebar,
		icon: 'chart-area'
	});
	
})(window.wp);