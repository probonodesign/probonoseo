<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis_PDF_Report {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('wp_ajax_probonoseo_generate_pdf_report', array($this, 'handle_generate_pdf'));
	}

	public function is_enabled() {
		if (!class_exists('ProbonoSEO_License')) {
			return false;
		}
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_diagnosis_pro_pdf', '0') === '1';
	}

	public function handle_generate_pdf() {
		check_ajax_referer('probonoseo_pdf_report', 'probonoseo_pdf_nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません'));
			return;
		}
		
		$cached_results = get_transient('probonoseo_diagnosis_pro_results');
		
		if (empty($cached_results)) {
			wp_send_json_error(array('message' => '診断結果がありません。先に診断を実行してください。'));
			return;
		}
		
		$pdf_url = $this->generate_pdf($cached_results);
		
		if ($pdf_url) {
			wp_send_json_success(array('url' => $pdf_url));
		} else {
			wp_send_json_error(array('message' => 'PDFの生成に失敗しました'));
		}
	}

	public function generate_pdf($results) {
		$upload_dir = wp_upload_dir();
		$pdf_dir = $upload_dir['basedir'] . '/probonoseo-reports';
		
		if (!file_exists($pdf_dir)) {
			wp_mkdir_p($pdf_dir);
		}
		
		$filename = 'seo-diagnosis-' . wp_date('Y-m-d-His') . '.html';
		$filepath = $pdf_dir . '/' . $filename;
		
		$html = $this->generate_html_report($results);
		
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$wp_filesystem->put_contents($filepath, $html, FS_CHMOD_FILE);
		
		$pdf_url = $upload_dir['baseurl'] . '/probonoseo-reports/' . $filename;
		
		return $pdf_url;
	}

	private function generate_html_report($results) {
		$site_name = get_bloginfo('name');
		$site_url = home_url();
		$date = wp_date('Y年m月d日 H:i');
		
		$total_score = 0;
		if (isset($results['total_score']) && isset($results['total_score']['total_score'])) {
			$total_score = $results['total_score']['total_score'];
		}
		
		$html = '<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>SEO診断レポート - ' . esc_html($site_name) . '</title>
<style>
body { font-family: "Hiragino Sans", "Meiryo", sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px 20px; }
h1 { color: #4a90e2; border-bottom: 3px solid #4a90e2; padding-bottom: 10px; }
h2 { color: #333; border-left: 4px solid #4a90e2; padding-left: 12px; margin-top: 30px; }
.header-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
.score-box { text-align: center; padding: 30px; background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%); color: white; border-radius: 8px; margin: 20px 0; }
.score-number { font-size: 72px; font-weight: bold; }
.score-label { font-size: 18px; }
.category-scores { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
.category-score { background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; }
.category-score-value { font-size: 24px; font-weight: bold; }
.category-score-label { font-size: 12px; color: #666; }
.section { margin: 25px 0; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
.section-title { display: flex; align-items: center; gap: 8px; margin: 0 0 15px 0; font-size: 16px; }
.item { padding: 8px 0; border-bottom: 1px solid #eee; }
.item:last-child { border-bottom: none; }
.item-success { color: #28a745; }
.item-warning { color: #ffc107; }
.item-error { color: #dc3545; }
.item-info { color: #17a2b8; }
.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center; color: #666; font-size: 12px; }
@media print { body { padding: 20px; } .score-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>
</head>
<body>
<h1>SEO診断レポート</h1>
<div class="header-info">
<p><strong>サイト名:</strong> ' . esc_html($site_name) . '</p>
<p><strong>URL:</strong> ' . esc_html($site_url) . '</p>
<p><strong>診断日時:</strong> ' . esc_html($date) . '</p>
</div>';

		if ($total_score > 0) {
			$html .= '
<div class="score-box">
<div class="score-number">' . esc_html($total_score) . '</div>
<div class="score-label">総合SEOスコア / 100点</div>
</div>';

			if (isset($results['total_score']['category_scores'])) {
				$labels = array(
					'security' => 'セキュリティ',
					'performance' => 'パフォーマンス',
					'indexing' => 'インデックス',
					'mobile' => 'モバイル',
					'technical' => '技術的SEO',
					'content' => 'コンテンツ'
				);
				
				$html .= '<div class="category-scores">';
				foreach ($results['total_score']['category_scores'] as $cat => $score) {
					$label = isset($labels[$cat]) ? $labels[$cat] : $cat;
					$html .= '<div class="category-score">
<div class="category-score-value">' . esc_html($score) . '</div>
<div class="category-score-label">' . esc_html($label) . '</div>
</div>';
				}
				$html .= '</div>';
			}
		}

		$html .= '<h2>診断結果詳細</h2>';

		foreach ($results as $key => $section) {
			if ($key === 'total_score') {
				continue;
			}
			
			if (!isset($section['title']) || !isset($section['items'])) {
				continue;
			}
			
			$html .= '<div class="section">
<h3 class="section-title">' . esc_html($section['title']) . '</h3>';
			
			foreach ($section['items'] as $item) {
				$type_class = isset($item['type']) ? 'item-' . esc_attr($item['type']) : '';
				$prefix = '';
				switch ($item['type']) {
					case 'success': $prefix = '✓ '; break;
					case 'warning': $prefix = '⚠ '; break;
					case 'error': $prefix = '✗ '; break;
					case 'info': $prefix = 'ℹ '; break;
				}
				$html .= '<div class="item ' . $type_class . '">' . $prefix . esc_html($item['message']) . '</div>';
			}
			
			$html .= '</div>';
		}

		$html .= '
<div class="footer">
<p>このレポートはProbonoSEOによって生成されました。</p>
<p>© ' . esc_html(wp_date('Y')) . ' ProbonoSEO - 日本語サイト向けSEOプラグイン</p>
</div>
</body>
</html>';

		return $html;
	}
}

ProbonoSEO_Diagnosis_PDF_Report::get_instance();