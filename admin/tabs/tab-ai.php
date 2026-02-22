<?php
if (!defined('ABSPATH')) {
	exit;
}

$probonoseo_license = ProbonoSEO_License::get_instance();
$probonoseo_license_active = $probonoseo_license->is_pro_active();
?>

<div class="probonoseo-section pro-section">
	<h2 class="probonoseo-section-title">AI日本語SEO補助（Pro版）</h2>
	<p class="probonoseo-section-description">AIを活用してタイトルや見出し、本文の作成・改善を支援します。</p>

	<div class="probonoseo-cards-wrap">

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">タイトル提案AI</h3>
					<p class="probonoseo-card-description">記事内容から魅力的な日本語タイトル案を自動生成します。初心者でも高品質なタイトル作成が可能になります。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_title_ai', 'タイトル提案AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">見出し改善AI</h3>
					<p class="probonoseo-card-description">H2/H3の見出しを自然かつ読みやすい形に整えます。不自然な語順や長すぎる見出しを自動調整します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_heading_ai', '見出し改善AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">記事構成案生成AI</h3>
					<p class="probonoseo-card-description">キーワードとテーマから適切なH2/H3の記事構成案を生成します。初心者でも論理的な記事構成を作成できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_outline_ai', '記事構成案生成AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">説明文改善AI</h3>
					<p class="probonoseo-card-description">本文の文章を読みやすく、伝わりやすい自然な表現に整えます。冗長な表現や分かりにくい文章を改善します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_body_ai', '説明文改善AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">要点サマリー生成</h3>
					<p class="probonoseo-card-description">本文から重要なポイントを抽出し、箇条書きでまとめます。記事冒頭の要点まとめとして利用できます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_summary_ai', '要点サマリー生成', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">FAQ 自動生成</h3>
					<p class="probonoseo-card-description">本文内容から質問と回答を自動生成し、FAQ schemaに反映できます。読者が疑問に思うポイントを自動抽出します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_faq_ai', 'FAQ 自動生成', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">高品質メタディスクリプション生成</h3>
					<p class="probonoseo-card-description">無料版よりも訴求力・自然さを重視した説明文を生成します。記事全体の流れを理解した上で説明文を生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_metadesc_ai', '高品質ディスクリプション', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">関連キーワード抽出AI</h3>
					<p class="probonoseo-card-description">記事内容からメイン・サブ・関連・ロングテールキーワードを自動抽出します。SEO戦略の立案に役立ちます。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_keywords_ai', '関連キーワード抽出AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">リライト提案AI</h3>
					<p class="probonoseo-card-description">文章を様々なスタイルでリライト提案します。シンプル・プロフェッショナル・フレンドリーなど複数の改善パターンを生成。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_rewrite_ai', 'リライト提案AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">読みやすさチェックAI</h3>
					<p class="probonoseo-card-description">文章の読みやすさを多角的に評価します。文の長さ、漢字バランス、接続詞の使い方など詳細な分析と改善提案を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_readability_ai', '読みやすさチェックAI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">感情分析AI</h3>
					<p class="probonoseo-card-description">記事のトーンと感情傾向を分析します。説得力・信頼性スコアや、読者に与える印象を数値化して改善提案を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_sentiment_ai', '感情分析AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">重複コンテンツチェックAI</h3>
					<p class="probonoseo-card-description">記事内の重複表現や冗長な部分を検出します。サイト内の類似記事も検出し、オリジナリティスコアを算出します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_duplicate_ai', '重複チェックAI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">ターゲット読者分析AI</h3>
					<p class="probonoseo-card-description">記事の想定読者層を分析します。年齢層、知識レベル、関心事項などを推定し、読者に最適化するための提案を行います。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_target_ai', 'ターゲット読者分析AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">検索意図分析AI</h3>
					<p class="probonoseo-card-description">キーワードの検索意図を分析します。情報収集・比較検討・購入意図などを判定し、コンテンツ最適化の方向性を提案します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_intent_ai', '検索意図分析AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">コンテンツギャップ分析AI</h3>
					<p class="probonoseo-card-description">記事に不足しているコンテンツを分析します。読者が期待する情報と現在の内容を比較し、追加すべきトピックを提案します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_gap_ai', 'コンテンツギャップAI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">画像キャプション生成AI</h3>
					<p class="probonoseo-card-description">記事内の画像に最適なキャプション・alt属性を生成します。SEOとアクセシビリティの両面で画像を最適化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_caption_ai', '画像キャプションAI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">内部リンク提案AI（高度版）</h3>
					<p class="probonoseo-card-description">記事内容に関連する内部リンク先を提案します。文脈に合った自然なリンクテキストも同時に生成します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_internal_link_ai', '内部リンク提案AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">外部リンク提案AI</h3>
					<p class="probonoseo-card-description">記事の信頼性を高める外部リンク先を提案します。権威性のあるソースや参考になるリソースを推奨します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_external_link_ai', '外部リンク提案AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">コンテンツ更新提案AI</h3>
					<p class="probonoseo-card-description">既存記事の更新すべき箇所を提案します。古い情報、リンク切れ、追加すべき最新情報などを検出します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_update_ai', 'コンテンツ更新提案AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">記事パフォーマンス予測AI</h3>
					<p class="probonoseo-card-description">公開前に記事のSEOパフォーマンスを予測します。予想順位、トラフィック、エンゲージメントなどを数値化します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_performance_ai', 'パフォーマンス予測AI', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

		<div class="probonoseo-card pro-feature">
			<div class="probonoseo-card-inner">
				<div class="probonoseo-card-left">
					<h3 class="probonoseo-card-title">日本語形態素解析SEO診断</h3>
					<p class="probonoseo-card-description">日本語文章の品質を形態素解析で診断します。「です・ます」「だ・である」の混在、助詞の過剰使用、文末表現の単調さを検出し改善提案します。</p>
				</div>
				<div class="probonoseo-card-right">
					<?php probonoseo_render_switch('probonoseo_pro_morphological_analysis', '形態素解析SEO診断', false, !$probonoseo_license_active, 'Pro版の機能です'); ?>
				</div>
			</div>
		</div>

	</div>
</div>