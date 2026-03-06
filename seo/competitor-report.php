<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Competitor_Report {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('wp_ajax_probonoseo_generate_competitor_report', array($this, 'ajax_generate_report'));
	}
	
	public function ajax_generate_report() {
		check_ajax_referer('probonoseo_competitor_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => '権限がありません。'));
			return;
		}
		
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			wp_send_json_error(array('message' => 'Pro版の機能です。'));
			return;
		}
		
		if (get_option('probonoseo_competitor_report', '1') !== '1') {
			wp_send_json_error(array('message' => 'レポート生成機能が無効になっています。'));
			return;
		}
		
		$last_analysis = get_option('probonoseo_last_analysis', array());
		
		if (empty($last_analysis)) {
			wp_send_json_error(array('message' => '分析データがありません。まず競合分析を実行してください。'));
			return;
		}
		
		$tcpdf_path = PROBONOSEO_PATH . 'vendor/tcpdf/tcpdf.php';
		
		if (!file_exists($tcpdf_path)) {
			$html = $this->generate_html_report($last_analysis);
			$upload_dir = wp_upload_dir();
			$report_dir = $upload_dir['basedir'] . '/probonoseo-reports';
			if (!file_exists($report_dir)) {
				wp_mkdir_p($report_dir);
			}
			$filename = 'competitor-report-' . wp_date('Y-m-d-His') . '.html';
			$filepath = $report_dir . '/' . $filename;
			file_put_contents($filepath, $html);
			$url = $upload_dir['baseurl'] . '/probonoseo-reports/' . $filename;
			wp_send_json_success(array('url' => $url, 'type' => 'html'));
			return;
		}
		
		require_once $tcpdf_path;
		
		$pdf = $this->generate_pdf_report($last_analysis);
		
		$upload_dir = wp_upload_dir();
		$report_dir = $upload_dir['basedir'] . '/probonoseo-reports';
		
		if (!file_exists($report_dir)) {
			wp_mkdir_p($report_dir);
		}
		
		$filename = 'competitor-report-' . wp_date('Y-m-d-His') . '.pdf';
		$filepath = $report_dir . '/' . $filename;
		
		$pdf->Output($filepath, 'F');
		
		$url = $upload_dir['baseurl'] . '/probonoseo-reports/' . $filename;
		
		wp_send_json_success(array('url' => $url, 'type' => 'pdf'));
	}
	
	private function generate_pdf_report($data) {
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		
		$pdf->SetCreator('ProbonoSEO');
		$pdf->SetAuthor(get_bloginfo('name'));
		$pdf->SetTitle('競合分析レポート');
		$pdf->SetSubject('競合分析レポート');
		
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		$pdf->SetMargins(15, 15, 15);
		$pdf->SetAutoPageBreak(true, 15);
		
		$pdf->SetFont('kozgopromedium', '', 10);
		
		$pdf->AddPage();
		
		$site_name = get_bloginfo('name');
		$date = wp_date('Y年m月d日 H:i');
		
		$pdf->SetFont('kozgopromedium', 'B', 20);
		$pdf->SetTextColor(74, 144, 226);
		$pdf->Cell(0, 15, '競合分析レポート', 0, 1, 'C');
		
		$pdf->SetFont('kozgopromedium', '', 10);
		$pdf->SetTextColor(102, 102, 102);
		$pdf->Cell(0, 8, $site_name . ' | 生成日: ' . $date, 0, 1, 'C');
		
		$pdf->Ln(10);
		
		if (!empty($data['scores'])) {
			$pdf->SetFont('kozgopromedium', 'B', 14);
			$pdf->SetTextColor(74, 144, 226);
			$pdf->Cell(0, 10, '総合スコア', 0, 1, 'L');
			
			$pdf->SetFont('kozgopromedium', '', 10);
			$pdf->SetTextColor(51, 51, 51);
			
			$score_html = '<table border="1" cellpadding="8" style="border-collapse: collapse;">';
			$score_html .= '<tr style="background-color: #4a90e2; color: #ffffff;">';
			foreach ($data['scores'] as $label => $score) {
				$bg = (strpos($label, '自サイト') !== false) ? ' style="background-color: #28a745; color: #ffffff;"' : '';
				$score_html .= '<th' . $bg . '>' . esc_html($label) . '</th>';
			}
			$score_html .= '</tr><tr>';
			foreach ($data['scores'] as $score) {
				$color = $score >= 70 ? '#28a745' : ($score >= 40 ? '#ffc107' : '#dc3545');
				$score_html .= '<td style="text-align: center;"><span style="color: ' . $color . '; font-weight: bold; font-size: 16px;">' . esc_html($score) . '点</span></td>';
			}
			$score_html .= '</tr></table>';
			
			$pdf->writeHTML($score_html, true, false, true, false, '');
			$pdf->Ln(10);
		}
		
		$pdf->SetFont('kozgopromedium', 'B', 14);
		$pdf->SetTextColor(74, 144, 226);
		$pdf->Cell(0, 10, '詳細比較', 0, 1, 'L');
		
		$table_html = '<table border="1" cellpadding="6" style="border-collapse: collapse; font-size: 9px;">';
		$table_html .= '<tr style="background-color: #4a90e2; color: #ffffff;">';
		$table_html .= '<th style="width: 25%;">項目</th>';
		
		if (isset($data['target'])) {
			$table_html .= '<th style="background-color: #28a745; color: #ffffff;">自サイト</th>';
		}
		
		if (!empty($data['competitors'])) {
			foreach ($data['competitors'] as $i => $c) {
				$table_html .= '<th>競合' . esc_html($i + 1) . '</th>';
			}
		}
		$table_html .= '</tr>';
		
		$rows = array(
			'タイトル文字数' => 'title',
			'メタD文字数' => 'meta_description',
			'本文文字数' => 'word_count',
			'画像数' => 'image_count',
			'H1数' => 'h1',
			'H2数' => 'h2',
			'内部リンク数' => 'internal_links',
			'外部リンク数' => 'external_links',
			'schema数' => 'schema'
		);
		
		$row_num = 0;
		foreach ($rows as $label => $key) {
			$bg = ($row_num % 2 === 0) ? ' style="background-color: #f9f9f9;"' : '';
			$table_html .= '<tr' . $bg . '>';
			$table_html .= '<td style="font-weight: bold; background-color: #f5f5f5;">' . esc_html($label) . '</td>';
			
			if (isset($data['target'])) {
				$table_html .= '<td style="text-align: center;">' . esc_html($this->get_value($data['target'], $key)) . '</td>';
			}
			
			if (!empty($data['competitors'])) {
				foreach ($data['competitors'] as $c) {
					$table_html .= '<td style="text-align: center;">' . esc_html($this->get_value($c, $key)) . '</td>';
				}
			}
			$table_html .= '</tr>';
			$row_num++;
		}
		$table_html .= '</table>';
		
		$pdf->writeHTML($table_html, true, false, true, false, '');
		$pdf->Ln(10);
		
		if (isset($data['target']) && !empty($data['competitors'])) {
			$analyzer = ProbonoSEO_Competitor_Analyzer::get_instance();
			$valid_competitors = array_filter($data['competitors'], function($c) {
				return !isset($c['error']) || !$c['error'];
			});
			
			if (!empty($valid_competitors)) {
				$suggestions = $analyzer->get_improvement_suggestions($data['target'], $valid_competitors);
				
				if (!empty($suggestions)) {
					$pdf->SetFont('kozgopromedium', 'B', 14);
					$pdf->SetTextColor(74, 144, 226);
					$pdf->Cell(0, 10, '改善提案', 0, 1, 'L');
					
					$pdf->SetFont('kozgopromedium', '', 10);
					$pdf->SetTextColor(51, 51, 51);
					
					$suggest_html = '<div style="background-color: #f8f9fa; border-left: 4px solid #4a90e2; padding: 10px;">';
					$suggest_html .= '<ul>';
					foreach ($suggestions as $s) {
						$suggest_html .= '<li style="margin-bottom: 5px;">' . esc_html($s['message']) . '</li>';
					}
					$suggest_html .= '</ul></div>';
					
					$pdf->writeHTML($suggest_html, true, false, true, false, '');
				}
			}
		}
		
		$pdf->Ln(15);
		$pdf->SetFont('kozgopromedium', '', 8);
		$pdf->SetTextColor(153, 153, 153);
		$pdf->Cell(0, 5, 'ProbonoSEO 競合分析レポート | Generated by ProbonoSEO Pro', 0, 1, 'C');
		
		return $pdf;
	}
	
	public function generate_html_report($data) {
		$site_name = get_bloginfo('name');
		$date = wp_date('Y年m月d日 H:i');
		
		ob_start();
		?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>競合分析レポート - <?php echo esc_html($site_name); ?></title>
	<style>
		* { box-sizing: border-box; }
		body { font-family: 'Hiragino Kaku Gothic Pro', 'メイリオ', sans-serif; line-height: 1.6; color: #333; max-width: 1000px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
		.report-container { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
		.report-header { text-align: center; border-bottom: 3px solid #4a90e2; padding-bottom: 20px; margin-bottom: 30px; }
		.report-header h1 { margin: 0 0 10px 0; color: #4a90e2; font-size: 28px; }
		.report-header p { margin: 0; color: #666; }
		.report-section { margin-bottom: 30px; }
		.report-section h2 { color: #4a90e2; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px; }
		table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
		th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
		th { background: #4a90e2; color: #fff; }
		th.target { background: #28a745; }
		td:first-child { text-align: left; font-weight: bold; background: #f9f9f9; }
		tr:hover { background: #f5f9ff; }
		.score-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-weight: bold; color: #fff; }
		.score-good { background: #28a745; }
		.score-warning { background: #ffc107; color: #333; }
		.score-bad { background: #dc3545; }
		.summary-box { background: #f8f9fa; border-left: 4px solid #4a90e2; padding: 15px 20px; margin-bottom: 20px; }
		.summary-box h3 { margin: 0 0 10px 0; color: #333; }
		.summary-box ul { margin: 0; padding-left: 20px; }
		.summary-box li { margin-bottom: 5px; }
		.footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
		.print-btn { text-align: center; margin-bottom: 20px; }
		.print-btn button { background: #4a90e2; color: #fff; border: none; padding: 10px 30px; border-radius: 5px; font-size: 14px; cursor: pointer; }
		.print-btn button:hover { background: #357abd; }
		@media print { body { background: #fff; } .report-container { box-shadow: none; } .print-btn { display: none; } }
	</style>
</head>
<body>
	<div class="print-btn">
		<button onclick="window.print()">このレポートを印刷 / PDF保存</button>
	</div>
	<div class="report-container">
		<div class="report-header">
			<h1>競合分析レポート</h1>
			<p><?php echo esc_html($site_name); ?> | 生成日: <?php echo esc_html($date); ?></p>
		</div>
		
		<?php if (!empty($data['scores'])) : ?>
		<div class="report-section">
			<h2>総合スコア</h2>
			<table>
				<tr>
					<?php foreach ($data['scores'] as $label => $score) : ?>
						<th <?php echo (strpos($label, '自サイト') !== false) ? 'class="target"' : ''; ?>><?php echo esc_html($label); ?></th>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php foreach ($data['scores'] as $score) : 
						$class = $score >= 70 ? 'score-good' : ($score >= 40 ? 'score-warning' : 'score-bad');
					?>
						<td><span class="score-badge <?php echo esc_attr($class); ?>"><?php echo esc_html($score); ?>点</span></td>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
		<?php endif; ?>
		
		<div class="report-section">
			<h2>詳細比較</h2>
			<table>
				<thead>
					<tr>
						<th>項目</th>
						<?php if (isset($data['target'])) : ?>
							<th class="target">自サイト</th>
						<?php endif; ?>
						<?php if (!empty($data['competitors'])) : foreach ($data['competitors'] as $i => $c) : ?>
							<th>競合<?php echo esc_html($i + 1); ?></th>
						<?php endforeach; endif; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$rows = array(
						'タイトル文字数' => 'title',
						'メタD文字数' => 'meta_description',
						'本文文字数' => 'word_count',
						'画像数' => 'image_count',
						'H1数' => 'h1',
						'H2数' => 'h2',
						'内部リンク数' => 'internal_links',
						'外部リンク数' => 'external_links',
						'schema数' => 'schema'
					);
					foreach ($rows as $label => $key) :
					?>
					<tr>
						<td><?php echo esc_html($label); ?></td>
						<?php if (isset($data['target'])) : ?>
							<td><?php echo esc_html($this->get_value($data['target'], $key)); ?></td>
						<?php endif; ?>
						<?php if (!empty($data['competitors'])) : foreach ($data['competitors'] as $c) : ?>
							<td><?php echo esc_html($this->get_value($c, $key)); ?></td>
						<?php endforeach; endif; ?>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<?php
		if (isset($data['target']) && !empty($data['competitors'])) {
			$analyzer = ProbonoSEO_Competitor_Analyzer::get_instance();
			$valid_competitors = array_filter($data['competitors'], function($c) {
				return !isset($c['error']) || !$c['error'];
			});
			if (!empty($valid_competitors)) {
				$suggestions = $analyzer->get_improvement_suggestions($data['target'], $valid_competitors);
				if (!empty($suggestions)) :
		?>
		<div class="report-section">
			<h2>改善提案</h2>
			<div class="summary-box">
				<h3>優先度の高い改善点</h3>
				<ul>
					<?php foreach ($suggestions as $s) : ?>
						<li><?php echo esc_html($s['message']); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
				endif;
			}
		}
		?>
		
		<div class="footer">
			<p>ProbonoSEO 競合分析レポート | Generated by ProbonoSEO Pro</p>
		</div>
	</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}
	
	private function get_value($data, $key) {
		if (isset($data['error']) && $data['error']) {
			return '-';
		}
		
		switch ($key) {
			case 'title':
				return mb_strlen($data['title'] ?? '');
			case 'meta_description':
				return mb_strlen($data['meta_description'] ?? '');
			case 'word_count':
				return $data['word_count'] ?? 0;
			case 'image_count':
				return $data['image_count'] ?? 0;
			case 'h1':
				return count($data['headings']['h1'] ?? array());
			case 'h2':
				return count($data['headings']['h2'] ?? array());
			case 'internal_links':
				return $data['internal_links'] ?? 0;
			case 'external_links':
				return $data['external_links'] ?? 0;
			case 'schema':
				return count($data['schema'] ?? array());
			default:
				return 0;
		}
	}
}

ProbonoSEO_Competitor_Report::get_instance();