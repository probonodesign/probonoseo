<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_AI_Metabox {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('enqueue_block_editor_assets', array($this, 'enqueue_gutenberg_assets'));
	}
	
	public function add_meta_boxes($post_type, $post) {
		if (!class_exists('ProbonoSEO_License')) {
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		
		if (!$license->is_pro_active()) {
			return;
		}
		
		$post_types = array('post', 'page');
		
		if (!in_array($post_type, $post_types)) {
			return;
		}
		
		if (!$this->has_any_ai_enabled()) {
			return;
		}
		
		add_meta_box(
			'probonoseo_ai_metabox',
			'ProbonoSEO AI補助',
			array($this, 'render_metabox'),
			$post_type,
			'side',
			'high'
		);
	}
	
	private function has_any_ai_enabled() {
		$ai_options = array(
			'probonoseo_pro_title_ai',
			'probonoseo_pro_metadesc_ai',
			'probonoseo_pro_heading_ai',
			'probonoseo_pro_outline_ai',
			'probonoseo_pro_body_ai',
			'probonoseo_pro_summary_ai',
			'probonoseo_pro_faq_ai',
			'probonoseo_pro_keywords_ai',
			'probonoseo_pro_rewrite_ai',
			'probonoseo_pro_readability_ai',
			'probonoseo_pro_sentiment_ai',
			'probonoseo_pro_duplicate_ai',
			'probonoseo_pro_target_ai',
			'probonoseo_pro_intent_ai',
			'probonoseo_pro_gap_ai',
			'probonoseo_pro_caption_ai',
			'probonoseo_pro_internal_link_ai',
			'probonoseo_pro_external_link_ai',
			'probonoseo_pro_update_ai',
			'probonoseo_pro_performance_ai'
		);
		
		foreach ($ai_options as $option) {
			if (get_option($option, '0') === '1') {
				return true;
			}
		}
		
		return false;
	}
	
	public function render_metabox($post) {
		if (!class_exists('ProbonoSEO_License')) {
			echo '<p>Pro版の機能です。</p>';
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		
		if (!$license->is_pro_active()) {
			echo '<p>Pro版の機能です。</p>';
			return;
		}
		
		if (!class_exists('ProbonoSEO_OpenAI_API')) {
			echo '<p>OpenAI APIモジュールが見つかりません。</p>';
			return;
		}
		
		$openai = ProbonoSEO_OpenAI_API::get_instance();
		$api_set = $openai->is_api_key_set();
		
		$title_ai = get_option('probonoseo_pro_title_ai', '0') === '1';
		$metadesc_ai = get_option('probonoseo_pro_metadesc_ai', '0') === '1';
		$heading_ai = get_option('probonoseo_pro_heading_ai', '0') === '1';
		$outline_ai = get_option('probonoseo_pro_outline_ai', '0') === '1';
		$body_ai = get_option('probonoseo_pro_body_ai', '0') === '1';
		$summary_ai = get_option('probonoseo_pro_summary_ai', '0') === '1';
		$faq_ai = get_option('probonoseo_pro_faq_ai', '0') === '1';
		$keywords_ai = get_option('probonoseo_pro_keywords_ai', '0') === '1';
		$rewrite_ai = get_option('probonoseo_pro_rewrite_ai', '0') === '1';
		$readability_ai = get_option('probonoseo_pro_readability_ai', '0') === '1';
		$sentiment_ai = get_option('probonoseo_pro_sentiment_ai', '0') === '1';
		$duplicate_ai = get_option('probonoseo_pro_duplicate_ai', '0') === '1';
		$target_ai = get_option('probonoseo_pro_target_ai', '0') === '1';
		$intent_ai = get_option('probonoseo_pro_intent_ai', '0') === '1';
		$gap_ai = get_option('probonoseo_pro_gap_ai', '0') === '1';
		$caption_ai = get_option('probonoseo_pro_caption_ai', '0') === '1';
		$internal_link_ai = get_option('probonoseo_pro_internal_link_ai', '0') === '1';
		$external_link_ai = get_option('probonoseo_pro_external_link_ai', '0') === '1';
		$update_ai = get_option('probonoseo_pro_update_ai', '0') === '1';
		$performance_ai = get_option('probonoseo_pro_performance_ai', '0') === '1';
		
		wp_nonce_field('probonoseo_ai_nonce', 'probonoseo_ai_nonce_field');
		?>
		<div class="probonoseo-ai-metabox">
			<?php if (!$api_set) : ?>
				<div class="probonoseo-ai-warning">
					<span class="dashicons dashicons-warning"></span>
					<p>OpenAI APIキーが設定されていません。<br>
					<a href="<?php echo esc_url(admin_url('admin.php?page=probonoseo&tab=openai')); ?>">設定画面へ</a></p>
				</div>
			<?php else : ?>
				<?php $section_count = 0; ?>
				
				<?php if ($title_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIタイトル提案</h4>
						<p class="description">記事内容から最適なタイトルを3パターン提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-title" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-edit"></span> タイトル提案を生成
						</button>
						<div id="probonoseo-title-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($metadesc_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIメタディスクリプション生成</h4>
						<p class="description">記事内容から最適なメタディスクリプションを生成します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-metadesc" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-text"></span> メタD生成
						</button>
						<div id="probonoseo-metadesc-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($heading_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI見出し提案</h4>
						<p class="description">記事内のH2/H3見出しを改善提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-heading" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-heading"></span> 見出し提案を生成
						</button>
						<div id="probonoseo-heading-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($outline_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI記事構成案</h4>
						<p class="description">キーワードから記事構成（目次）を生成します。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-outline-keyword">キーワード（任意）</label>
							<input type="text" id="probonoseo-outline-keyword" placeholder="例：WordPress SEO">
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-outline" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-list-view"></span> 構成案を生成
						</button>
						<div id="probonoseo-outline-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($body_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI本文生成補助</h4>
						<p class="description">見出しに対する本文を生成します。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-body-heading">見出し</label>
							<input type="text" id="probonoseo-body-heading" placeholder="例：WordPressのSEO設定方法">
						</div>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-body-context">追加指示（任意）</label>
							<textarea id="probonoseo-body-context" rows="2" placeholder="例：初心者向けに説明"></textarea>
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-body" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-editor-paragraph"></span> 本文を生成
						</button>
						<div id="probonoseo-body-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($summary_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI要約生成</h4>
						<p class="description">記事の要点をまとめます。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-summary-style">要約形式</label>
							<select id="probonoseo-summary-style">
								<option value="points">箇条書き（ポイント）</option>
								<option value="paragraph">1段落の文章</option>
								<option value="learn">この記事でわかること</option>
							</select>
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-summary" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-editor-justify"></span> 要約を生成
						</button>
						<div id="probonoseo-summary-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($faq_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI FAQ生成</h4>
						<p class="description">記事内容からQ&Aを自動生成します。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-faq-count">生成数</label>
							<select id="probonoseo-faq-count">
								<option value="3">3個</option>
								<option value="5" selected>5個</option>
								<option value="7">7個</option>
								<option value="10">10個</option>
							</select>
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-faq" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-format-chat"></span> FAQ生成
						</button>
						<div id="probonoseo-faq-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($keywords_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI関連キーワード抽出</h4>
						<p class="description">記事からメイン・サブ・関連キーワードを抽出します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-keywords" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-tag"></span> キーワード抽出
						</button>
						<div id="probonoseo-keywords-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($rewrite_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIリライト提案</h4>
						<p class="description">文章を改善・リライトします。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-rewrite-text">リライト対象テキスト</label>
							<textarea id="probonoseo-rewrite-text" rows="3" placeholder="リライトしたい文章を入力"></textarea>
						</div>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-rewrite-style">リライトスタイル</label>
							<select id="probonoseo-rewrite-style">
								<option value="improve">全般的な改善</option>
								<option value="simple">シンプルに</option>
								<option value="professional">プロフェッショナルに</option>
								<option value="friendly">フレンドリーに</option>
								<option value="concise">簡潔に</option>
								<option value="detailed">詳細に</option>
								<option value="seo">SEO最適化</option>
							</select>
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-rewrite" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-editor-paste-text"></span> リライト生成
						</button>
						<div id="probonoseo-rewrite-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($readability_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI読みやすさチェック</h4>
						<p class="description">文章の読みやすさを多角的に評価します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-check-readability" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-visibility"></span> 読みやすさチェック
						</button>
						<div id="probonoseo-readability-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($sentiment_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI感情分析</h4>
						<p class="description">記事のトーンと感情傾向を分析します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-analyze-sentiment" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-heart"></span> 感情分析
						</button>
						<div id="probonoseo-sentiment-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($duplicate_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI重複コンテンツチェック</h4>
						<p class="description">重複表現や類似記事を検出します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-check-duplicate" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-admin-page"></span> 重複チェック
						</button>
						<div id="probonoseo-duplicate-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($target_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIターゲット読者分析</h4>
						<p class="description">想定読者層を分析・提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-analyze-target" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-groups"></span> ターゲット分析
						</button>
						<div id="probonoseo-target-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($intent_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI検索意図分析</h4>
						<p class="description">キーワードの検索意図を分析します。</p>
						<div class="probonoseo-ai-input-group">
							<label for="probonoseo-intent-keyword">分析キーワード</label>
							<input type="text" id="probonoseo-intent-keyword" placeholder="例：WordPress SEO 設定">
						</div>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-analyze-intent" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-search"></span> 検索意図分析
						</button>
						<div id="probonoseo-intent-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($gap_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIコンテンツギャップ分析</h4>
						<p class="description">不足しているコンテンツを提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-analyze-gap" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-chart-bar"></span> ギャップ分析
						</button>
						<div id="probonoseo-gap-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($caption_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI画像キャプション生成</h4>
						<p class="description">記事内画像のキャプション・altを生成します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-generate-caption" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-format-image"></span> キャプション生成
						</button>
						<div id="probonoseo-caption-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($internal_link_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI内部リンク提案</h4>
						<p class="description">関連する内部リンク先を提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-suggest-internal" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-admin-links"></span> 内部リンク提案
						</button>
						<div id="probonoseo-internal-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($external_link_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI外部リンク提案</h4>
						<p class="description">信頼性の高い外部リンク先を提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-suggest-external" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-external"></span> 外部リンク提案
						</button>
						<div id="probonoseo-external-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($update_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AIコンテンツ更新提案</h4>
						<p class="description">更新すべき箇所を提案します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-suggest-update" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-update"></span> 更新提案
						</button>
						<div id="probonoseo-update-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($performance_ai) : ?>
					<?php if ($section_count > 0) : ?><hr><?php endif; ?>
					<div class="probonoseo-ai-section">
						<h4>AI記事パフォーマンス予測</h4>
						<p class="description">記事のSEOパフォーマンスを予測します。</p>
						<button type="button" class="button probonoseo-ai-btn" id="probonoseo-predict-performance" data-post-id="<?php echo esc_attr($post->ID); ?>">
							<span class="dashicons dashicons-chart-line"></span> パフォーマンス予測
						</button>
						<div id="probonoseo-performance-results" class="probonoseo-ai-results"></div>
					</div>
					<?php $section_count++; ?>
				<?php endif; ?>
				
				<?php if ($section_count > 0) : ?>
					<div class="probonoseo-ai-help">
						<p><small>※ 生成にはOpenAI APIを使用します。<a href="https://platform.openai.com/settings/organization/limits" target="_blank">使用状況を確認</a></small></p>
					</div>
				<?php else : ?>
					<div class="probonoseo-ai-warning">
						<span class="dashicons dashicons-info"></span>
						<p>AI機能が有効になっていません。<br>
						<a href="<?php echo esc_url(admin_url('admin.php?page=probonoseo&tab=ai')); ?>">設定画面で有効化</a></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
	
	public function enqueue_scripts($hook) {
		if (!in_array($hook, array('post.php', 'post-new.php'))) {
			return;
		}
		
		if (!class_exists('ProbonoSEO_License')) {
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return;
		}
		
		$screen = get_current_screen();
		if ($screen && $screen->is_block_editor()) {
			return;
		}
		
		wp_enqueue_style(
			'probonoseo-ai-metabox',
			PROBONOSEO_URL . 'admin/metabox-ai.css',
			array(),
			PROBONOSEO_VERSION
		);
		
		wp_enqueue_script(
			'probonoseo-ai-metabox',
			PROBONOSEO_URL . 'admin/metabox-ai.js',
			array('jquery'),
			PROBONOSEO_VERSION,
			true
		);
		
		wp_localize_script('probonoseo-ai-metabox', 'probonoseoAI', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('probonoseo_ai_nonce')
		));
	}
	
	public function enqueue_gutenberg_assets() {
		if (!class_exists('ProbonoSEO_License')) {
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return;
		}
		
		if (!class_exists('ProbonoSEO_OpenAI_API')) {
			return;
		}
		
		$openai = ProbonoSEO_OpenAI_API::get_instance();
		if (!$openai->is_api_key_set()) {
			return;
		}
		
		if (!$this->has_any_ai_enabled()) {
			return;
		}
		
		wp_enqueue_script(
			'probonoseo-gutenberg-sidebar',
			PROBONOSEO_URL . 'admin/gutenberg-sidebar.js',
			array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'jquery'),
			PROBONOSEO_VERSION,
			true
		);
		
		wp_localize_script('probonoseo-gutenberg-sidebar', 'probonoseoGutenberg', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('probonoseo_ai_nonce'),
			'enabledFeatures' => array(
				'title' => get_option('probonoseo_pro_title_ai', '0') === '1',
				'metadesc' => get_option('probonoseo_pro_metadesc_ai', '0') === '1',
				'heading' => get_option('probonoseo_pro_heading_ai', '0') === '1',
				'outline' => get_option('probonoseo_pro_outline_ai', '0') === '1',
				'body' => get_option('probonoseo_pro_body_ai', '0') === '1',
				'summary' => get_option('probonoseo_pro_summary_ai', '0') === '1',
				'faq' => get_option('probonoseo_pro_faq_ai', '0') === '1',
				'keywords' => get_option('probonoseo_pro_keywords_ai', '0') === '1',
				'rewrite' => get_option('probonoseo_pro_rewrite_ai', '0') === '1',
				'readability' => get_option('probonoseo_pro_readability_ai', '0') === '1',
				'sentiment' => get_option('probonoseo_pro_sentiment_ai', '0') === '1',
				'duplicate' => get_option('probonoseo_pro_duplicate_ai', '0') === '1',
				'target' => get_option('probonoseo_pro_target_ai', '0') === '1',
				'intent' => get_option('probonoseo_pro_intent_ai', '0') === '1',
				'gap' => get_option('probonoseo_pro_gap_ai', '0') === '1',
				'caption' => get_option('probonoseo_pro_caption_ai', '0') === '1',
				'internalLink' => get_option('probonoseo_pro_internal_link_ai', '0') === '1',
				'externalLink' => get_option('probonoseo_pro_external_link_ai', '0') === '1',
				'update' => get_option('probonoseo_pro_update_ai', '0') === '1',
				'performance' => get_option('probonoseo_pro_performance_ai', '0') === '1'
			)
		));
	}
}

ProbonoSEO_AI_Metabox::get_instance();