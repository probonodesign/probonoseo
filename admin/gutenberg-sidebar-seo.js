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
	var useEffect = wp.element.useEffect;
	var useSelect = wp.data.useSelect;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var Spinner = wp.components.Spinner;

	var isPro = (typeof probonoseoSEOSidebar !== 'undefined' && probonoseoSEOSidebar.is_pro === '1');

	var seoIcon = createElement('svg', {
		width: 24,
		height: 24,
		viewBox: '0 0 24 24',
		fill: 'none',
		xmlns: 'http://www.w3.org/2000/svg'
	}, [
		createElement('path', {
			key: 'p1',
			d: 'M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z',
			fill: 'currentColor'
		}),
		createElement('path', {
			key: 'p2',
			d: 'M9.5 7C8.12 7 7 8.12 7 9.5S8.12 12 9.5 12 12 10.88 12 9.5 10.88 7 9.5 7zm0 4C8.67 11 8 10.33 8 9.5S8.67 8 9.5 8s1.5.67 1.5 1.5S10.33 11 9.5 11z',
			fill: 'currentColor'
		})
	]);

	function CharCounter(props) {
		var length = (props.value || '').length;
		var max = props.max || 60;
		var statusText = '';
		var statusColor = '#888';

		if (props.type === 'title') {
			if (length >= 30 && length <= 60) {
				statusText = '最適';
				statusColor = '#2e7d32';
			} else if (length > 60) {
				statusText = '長すぎ';
				statusColor = '#c62828';
			} else if (length > 0) {
				statusText = '短め';
				statusColor = '#ed6c02';
			}
		} else {
			if (length >= 70 && length <= 120) {
				statusText = '最適';
				statusColor = '#2e7d32';
			} else if (length > 120) {
				statusText = '長すぎ';
				statusColor = '#c62828';
			} else if (length > 0) {
				statusText = '短め';
				statusColor = '#ed6c02';
			}
		}

		return createElement('div', {
			className: 'probonoseo-sidebar-char-counter'
		}, [
			createElement('span', { key: 'count' }, length + ' / ' + max + '文字'),
			statusText ? createElement('span', {
				key: 'status',
				className: 'probonoseo-sidebar-status probonoseo-sidebar-status-' + (statusText === '最適' ? 'good' : (statusText === '短め' ? 'warn' : 'bad'))
			}, statusText) : null
		]);
	}

	function SERPPreview(props) {
		var title = props.customTitle || props.postTitle || 'タイトル未設定';
		var desc = props.customDesc || '説明文がここに表示されます...';
		var url = props.homeUrl || '';

		return createElement('div', { className: 'probonoseo-sidebar-serp' }, [
			createElement('h4', { key: 'label', className: 'probonoseo-sidebar-section-title' }, 'Google検索プレビュー'),
			createElement('div', { key: 'preview', className: 'probonoseo-sidebar-serp-card' }, [
				createElement('div', { key: 'url', className: 'probonoseo-sidebar-serp-url' }, url),
				createElement('div', { key: 'title', className: 'probonoseo-sidebar-serp-title' }, title),
				createElement('div', { key: 'desc', className: 'probonoseo-sidebar-serp-desc' }, desc)
			])
		]);
	}

	function SocialPreview(props) {
		var title = props.ogTitle || props.customTitle || props.postTitle || 'タイトル未設定';
		var desc = props.ogDesc || props.customDesc || '説明文';
		var image = props.ogImage || '';
		var domain = props.domain || '';

		var imageEl;
		if (image) {
			imageEl = createElement('img', { src: image, alt: '', className: 'probonoseo-sidebar-social-img' });
		} else {
			imageEl = createElement('div', { className: 'probonoseo-sidebar-social-noimg' }, '画像未設定');
		}

		return createElement('div', { className: 'probonoseo-sidebar-social' }, [
			createElement('h4', { key: 'label', className: 'probonoseo-sidebar-section-title' }, 'SNSプレビュー'),
			createElement('div', { key: 'card', className: 'probonoseo-sidebar-social-card' }, [
				createElement('div', { key: 'img' }, imageEl),
				createElement('div', { key: 'content', className: 'probonoseo-sidebar-social-content' }, [
					createElement('div', { key: 'domain', className: 'probonoseo-sidebar-social-domain' }, domain),
					createElement('div', { key: 'title', className: 'probonoseo-sidebar-social-title' }, title),
					createElement('div', { key: 'desc', className: 'probonoseo-sidebar-social-desc' }, desc)
				])
			])
		]);
	}

	function ImageSelector(props) {
		var openMedia = function() {
			var uploader = wp.media({
				title: 'OGP画像を選択',
				button: { text: '選択' },
				multiple: false
			});

			uploader.on('select', function() {
				var attachment = uploader.state().get('selection').first().toJSON();
				props.onChange(attachment.url);
			});

			uploader.open();
		};

		return createElement('div', { className: 'probonoseo-sidebar-image-selector' }, [
			createElement('div', { key: 'label', className: 'probonoseo-sidebar-field-label' }, 'OGP画像'),
			createElement('div', { key: 'field', className: 'probonoseo-sidebar-image-field' }, [
				createElement('input', {
					key: 'input',
					type: 'text',
					value: props.value || '',
					onChange: function(e) { props.onChange(e.target.value); },
					placeholder: 'https://example.com/image.jpg',
					className: 'probonoseo-sidebar-image-input'
				}),
				createElement(Button, {
					key: 'btn',
					isSecondary: true,
					isSmall: true,
					onClick: openMedia
				}, '選択')
			]),
			props.value ? createElement('img', {
				key: 'thumb',
				src: props.value,
				alt: '',
				className: 'probonoseo-sidebar-image-thumb'
			}) : null,
			props.value ? createElement(Button, {
				key: 'remove',
				isDestructive: true,
				isSmall: true,
				onClick: function() { props.onChange(''); },
				className: 'probonoseo-sidebar-image-remove'
			}, '削除') : null
		]);
	}

	var ProbonoSEOSidebarSEO = function() {
		var _titleState = useState('');
		var customTitle = _titleState[0];
		var setCustomTitle = _titleState[1];

		var _descState = useState('');
		var customDesc = _descState[0];
		var setCustomDesc = _descState[1];

		var _ogTitleState = useState('');
		var ogTitle = _ogTitleState[0];
		var setOgTitle = _ogTitleState[1];

		var _ogDescState = useState('');
		var ogDesc = _ogDescState[0];
		var setOgDesc = _ogDescState[1];

		var _ogImageState = useState('');
		var ogImage = _ogImageState[0];
		var setOgImage = _ogImageState[1];

		var _focusKwState = useState('');
		var focusKeyword = _focusKwState[0];
		var setFocusKeyword = _focusKwState[1];

		var _tabState = useState('seo');
		var activeTab = _tabState[0];
		var setActiveTab = _tabState[1];

		var _loadedState = useState(false);
		var loaded = _loadedState[0];
		var setLoaded = _loadedState[1];

		var _savingState = useState(false);
		var isSaving = _savingState[0];
		var setIsSaving = _savingState[1];

		var _savedState = useState(false);
		var saved = _savedState[0];
		var setSaved = _savedState[1];

		var _scoresState = useState(null);
		var scores = _scoresState[0];
		var setScores = _scoresState[1];

		var _analysisState = useState(null);
		var analysis = _analysisState[0];
		var setAnalysis = _analysisState[1];

		var postId = useSelect(function(select) {
			return select('core/editor').getCurrentPostId();
		});

		var postTitle = useSelect(function(select) {
			return select('core/editor').getEditedPostAttribute('title') || '';
		});

		useEffect(function() {
			if (!postId || loaded) return;

			jQuery.ajax({
				url: probonoseoSEOSidebar.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_load_seo_meta',
					nonce: probonoseoSEOSidebar.nonce,
					post_id: postId
				},
				success: function(response) {
					if (response.success && response.data) {
						setCustomTitle(response.data.custom_title || '');
						setCustomDesc(response.data.custom_description || '');
						setOgTitle(response.data.og_title || '');
						setOgDesc(response.data.og_description || '');
						setOgImage(response.data.og_image || '');
						setFocusKeyword(response.data.focus_keyword || '');
					}
					setLoaded(true);
				},
				error: function() {
					setLoaded(true);
				}
			});
		}, [postId]);

		var saveMeta = function() {
			setIsSaving(true);
			setSaved(false);

			var data = {
				action: 'probonoseo_save_seo_meta_ajax',
				nonce: probonoseoSEOSidebar.nonce,
				post_id: postId,
				custom_title: customTitle,
				custom_description: customDesc,
				og_title: ogTitle,
				og_description: ogDesc,
				og_image: ogImage
			};

			if (isPro) {
				data.focus_keyword = focusKeyword;
			}

			jQuery.ajax({
				url: probonoseoSEOSidebar.ajaxurl,
				type: 'POST',
				data: data,
				success: function(response) {
					setIsSaving(false);
					if (response.success) {
						setSaved(true);
						setTimeout(function() { setSaved(false); }, 2000);
					}
				},
				error: function() {
					setIsSaving(false);
				}
			});
		};

		var runAnalysis = function() {
			if (!isPro) return;

			var content = '';
			if (wp.data && wp.data.select('core/editor')) {
				content = wp.data.select('core/editor').getEditedPostContent();
			}

			jQuery.ajax({
				url: probonoseoSEOSidebar.ajaxurl,
				type: 'POST',
				data: {
					action: 'probonoseo_calculate_seo_score',
					nonce: probonoseoSEOSidebar.nonce,
					post_id: postId,
					content: content,
					title: customTitle || postTitle,
					description: customDesc,
					focus_keyword: focusKeyword
				},
				success: function(response) {
					if (response.success) {
						setScores(response.data.scores);
						setAnalysis(response.data.analysis);
					}
				}
			});
		};

		if (!loaded) {
			return createElement(Fragment, null, [
				createElement(PluginSidebarMoreMenuItem, {
					key: 'menu',
					target: 'probonoseo-seo-sidebar',
					icon: seoIcon
				}, 'ProbonoSEO SEO'),
				createElement(PluginSidebar, {
					key: 'sidebar',
					name: 'probonoseo-seo-sidebar',
					icon: seoIcon,
					title: 'ProbonoSEO SEO設定'
				}, createElement('div', { className: 'probonoseo-sidebar-loading' }, createElement(Spinner, null)))
			]);
		}

		var tabs = [
			{ key: 'seo', label: 'SEO設定' },
			{ key: 'preview', label: 'プレビュー' },
			{ key: 'social', label: 'SNS' }
		];

		if (isPro) {
			tabs.push({ key: 'analysis', label: '分析' });
		}

		var tabBar = createElement('div', {
			className: 'probonoseo-sidebar-tabs'
		}, tabs.map(function(tab) {
			return createElement('button', {
				key: tab.key,
				type: 'button',
				onClick: function() { setActiveTab(tab.key); },
				className: 'probonoseo-sidebar-tab' + (activeTab === tab.key ? ' probonoseo-sidebar-tab-active' : '')
			}, tab.label);
		}));

		var seoPanel = createElement('div', {
			key: 'seo-panel',
			className: 'probonoseo-sidebar-panel' + (activeTab === 'seo' ? ' probonoseo-sidebar-panel-active' : '')
		}, [
			createElement(TextControl, {
				key: 'title',
				label: 'SEOタイトル',
				value: customTitle,
				onChange: setCustomTitle,
				placeholder: '空欄で投稿タイトルを使用'
			}),
			createElement(CharCounter, { key: 'title-counter', value: customTitle, max: 60, type: 'title' }),
			createElement('div', { key: 'spacer1', className: 'probonoseo-sidebar-spacer' }),
			createElement(TextareaControl, {
				key: 'desc',
				label: 'メタディスクリプション',
				value: customDesc,
				onChange: setCustomDesc,
				placeholder: '検索結果に表示される説明文',
				rows: 3
			}),
			createElement(CharCounter, { key: 'desc-counter', value: customDesc, max: 120, type: 'desc' }),
			isPro ? createElement('div', { key: 'spacer2', className: 'probonoseo-sidebar-spacer' }) : null,
			isPro ? createElement(TextControl, {
				key: 'keyword',
				label: 'フォーカスキーワード',
				value: focusKeyword,
				onChange: setFocusKeyword,
				placeholder: 'メインキーワード'
			}) : null,
			createElement('div', { key: 'spacer3', className: 'probonoseo-sidebar-spacer-lg' }),
			createElement('hr', { key: 'hr-ogp', className: 'probonoseo-sidebar-hr' }),
			createElement('h4', { key: 'ogp-label', className: 'probonoseo-sidebar-section-title' }, 'OGP設定'),
			createElement(TextControl, {
				key: 'og-title',
				label: 'OGPタイトル',
				value: ogTitle,
				onChange: setOgTitle,
				placeholder: '空欄でSEOタイトルを使用'
			}),
			createElement(TextareaControl, {
				key: 'og-desc',
				label: 'OGP説明文',
				value: ogDesc,
				onChange: setOgDesc,
				placeholder: '空欄でメタDを使用',
				rows: 2
			}),
			createElement(ImageSelector, {
				key: 'og-image',
				value: ogImage,
				onChange: setOgImage
			}),
			createElement('div', { key: 'spacer4', className: 'probonoseo-sidebar-spacer-lg' }),
			createElement(Button, {
				key: 'save-btn',
				isPrimary: true,
				onClick: saveMeta,
				disabled: isSaving,
				className: 'probonoseo-sidebar-save-btn'
			}, isSaving ? createElement(Spinner, null) : '保存'),
			saved ? createElement('div', {
				key: 'saved-msg',
				className: 'probonoseo-sidebar-saved-msg'
			}, '保存しました') : null
		]);

		var previewPanel = createElement('div', {
			key: 'preview-panel',
			className: 'probonoseo-sidebar-panel' + (activeTab === 'preview' ? ' probonoseo-sidebar-panel-active' : '')
		}, [
			createElement(SERPPreview, {
				key: 'serp',
				customTitle: customTitle,
				postTitle: postTitle,
				customDesc: customDesc,
				homeUrl: probonoseoSEOSidebar.home_url || ''
			})
		]);

		var socialPanel = createElement('div', {
			key: 'social-panel',
			className: 'probonoseo-sidebar-panel' + (activeTab === 'social' ? ' probonoseo-sidebar-panel-active' : '')
		}, [
			createElement(SocialPreview, {
				key: 'social',
				ogTitle: ogTitle,
				customTitle: customTitle,
				postTitle: postTitle,
				ogDesc: ogDesc,
				customDesc: customDesc,
				ogImage: ogImage,
				domain: probonoseoSEOSidebar.domain || ''
			})
		]);

		var analysisPanel = null;
		if (isPro) {
			var analysisContent = [];

			analysisContent.push(createElement(Button, {
				key: 'analyze-btn',
				isPrimary: true,
				onClick: runAnalysis,
				className: 'probonoseo-sidebar-analyze-btn'
			}, 'SEO分析を実行'));

			if (scores) {
				var totalColor = scores.total >= 70 ? 'good' : (scores.total >= 50 ? 'warn' : 'bad');

				analysisContent.push(createElement('div', {
					key: 'score-display',
					className: 'probonoseo-sidebar-score-display'
				}, [
					createElement('div', { key: 'num', className: 'probonoseo-sidebar-score-num probonoseo-sidebar-score-' + totalColor }, scores.total),
					createElement('div', { key: 'label', className: 'probonoseo-sidebar-score-label' }, 'SEOスコア')
				]));

				var scoreItems = [
					{ key: 'title', label: 'タイトル', value: scores.title },
					{ key: 'meta', label: 'メタD', value: scores.meta },
					{ key: 'content', label: 'コンテンツ', value: scores.content },
					{ key: 'keyword', label: 'キーワード', value: scores.keyword },
					{ key: 'links', label: 'リンク', value: scores.links }
				];

				scoreItems.forEach(function(item) {
					var barColor = item.value >= 70 ? 'good' : (item.value >= 50 ? 'warn' : 'bad');
					analysisContent.push(createElement('div', {
						key: 'score-' + item.key,
						className: 'probonoseo-sidebar-score-row'
					}, [
						createElement('span', { key: 'l', className: 'probonoseo-sidebar-score-row-label' }, item.label),
						createElement('div', { key: 'bar', className: 'probonoseo-sidebar-score-bar' },
							createElement('div', { className: 'probonoseo-sidebar-score-bar-fill probonoseo-sidebar-score-bar-' + barColor, style: { width: item.value + '%' } })
						),
						createElement('span', { key: 'v', className: 'probonoseo-sidebar-score-row-value' }, item.value)
					]));
				});
			}

			if (analysis) {
				analysisContent.push(createElement('hr', { key: 'hr-analysis', className: 'probonoseo-sidebar-hr' }));
				analysisContent.push(createElement('h4', { key: 'analysis-label', className: 'probonoseo-sidebar-section-title' }, 'コンテンツ分析'));

				var metrics = [
					{ key: 'word_count', label: '文字数', icon: '📝' },
					{ key: 'read_time', label: '読了時間', icon: '⏱️' },
					{ key: 'keyword_density', label: 'KW密度', icon: '🔑' },
					{ key: 'heading_count', label: '見出し', icon: '📊' },
					{ key: 'internal_links', label: '内部リンク', icon: '🔗' },
					{ key: 'external_links', label: '外部リンク', icon: '🌐' },
					{ key: 'image_count', label: '画像', icon: '🖼️' },
					{ key: 'images_with_alt', label: 'alt属性', icon: '✅' }
				];

				var metricItems = metrics.map(function(m) {
					var val = analysis[m.key] !== undefined ? analysis[m.key] : '--';
					if (m.key === 'word_count' && typeof val === 'number') {
						val = val.toLocaleString();
					}
					return createElement('div', {
						key: m.key,
						className: 'probonoseo-sidebar-metric-row'
					}, [
						createElement('span', { key: 'l', className: 'probonoseo-sidebar-metric-label' }, m.icon + ' ' + m.label),
						createElement('span', { key: 'v', className: 'probonoseo-sidebar-metric-value' }, val)
					]);
				});

				analysisContent.push(createElement('div', { key: 'metrics' }, metricItems));
			}

			analysisPanel = createElement('div', {
				key: 'analysis-panel',
				className: 'probonoseo-sidebar-panel' + (activeTab === 'analysis' ? ' probonoseo-sidebar-panel-active' : '')
			}, analysisContent);
		}

		var sidebarContent = [tabBar, seoPanel, previewPanel, socialPanel];
		if (analysisPanel) {
			sidebarContent.push(analysisPanel);
		}

		return createElement(Fragment, null, [
			createElement(PluginSidebarMoreMenuItem, {
				key: 'menu',
				target: 'probonoseo-seo-sidebar',
				icon: seoIcon
			}, 'ProbonoSEO SEO'),
			createElement(PluginSidebar, {
				key: 'sidebar',
				name: 'probonoseo-seo-sidebar',
				icon: seoIcon,
				title: 'ProbonoSEO SEO設定'
			}, createElement('div', { className: 'probonoseo-sidebar-wrap' }, sidebarContent))
		]);
	};

	registerPlugin('probonoseo-seo', {
		render: ProbonoSEOSidebarSEO,
		icon: seoIcon
	});

})(window.wp);