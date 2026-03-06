<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Diagnosis {
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
	}
	
	public static function run_diagnosis() {
		$results = array();
		
		if (get_option('probonoseo_diagnosis_title_duplicate', '1') === '1') {
			$results['title_duplicates'] = self::check_title_duplicates();
		} else {
			$results['title_duplicates'] = 'disabled';
		}
		
		if (get_option('probonoseo_diagnosis_meta_duplicate', '1') === '1') {
			$results['meta_duplicates'] = self::check_meta_duplicates();
		} else {
			$results['meta_duplicates'] = 'disabled';
		}
		
		if (get_option('probonoseo_diagnosis_speed', '1') === '1') {
			$results['speed_issues'] = self::check_speed_issues();
		} else {
			$results['speed_issues'] = 'disabled';
		}
		
		$results['debug'] = array(
			'timestamp' => current_time('mysql'),
			'title_check_enabled' => get_option('probonoseo_diagnosis_title_duplicate', '1'),
			'meta_check_enabled' => get_option('probonoseo_diagnosis_meta_duplicate', '1'),
			'speed_check_enabled' => get_option('probonoseo_diagnosis_speed', '1')
		);
		
		update_option('probonoseo_diagnosis_results', $results);
	}
	
	private static function check_title_duplicates() {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$duplicates = $wpdb->get_results("
			SELECT post_title, COUNT(*) as count, GROUP_CONCAT(ID) as post_ids
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_title != ''
			GROUP BY post_title
			HAVING count > 1
			ORDER BY count DESC
			LIMIT 10
		");
		
		$issues = array();
		
		if (!empty($duplicates)) {
			foreach ($duplicates as $duplicate) {
				$post_ids = explode(',', $duplicate->post_ids);
				$issues[] = array(
					'title' => $duplicate->post_title,
					'count' => $duplicate->count,
					'post_ids' => $post_ids
				);
			}
		}
		
		return $issues;
	}
	
	private static function check_meta_duplicates() {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$duplicates = $wpdb->get_results("
			SELECT meta_value, COUNT(*) as count, GROUP_CONCAT(post_id) as post_ids
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_probonoseo_meta_description'
			AND meta_value != ''
			GROUP BY meta_value
			HAVING count > 1
			ORDER BY count DESC
			LIMIT 10
		");
		
		$issues = array();
		
		if (!empty($duplicates)) {
			foreach ($duplicates as $duplicate) {
				$post_ids = explode(',', $duplicate->post_ids);
				$issues[] = array(
					'description' => mb_substr($duplicate->meta_value, 0, 50) . '...',
					'count' => $duplicate->count,
					'post_ids' => $post_ids
				);
			}
		}
		
		return $issues;
	}
	
	private static function check_speed_issues() {
		$issues = array();
		
		$active_plugins = get_option('active_plugins');
		$plugin_count = count($active_plugins);
		
		if ($plugin_count > 20) {
			$issues[] = array(
				'type' => 'warning',
				'message' => 'プラグインが多すぎます（' . $plugin_count . '個）。20個以下を推奨します。'
			);
		}
		
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		
		if (file_exists($upload_path)) {
			$image_files = glob($upload_path . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
			$large_images = 0;
			
			if ($image_files) {
				foreach ($image_files as $file) {
					if (file_exists($file)) {
						$size = filesize($file);
						if ($size > 500000) {
							$large_images++;
						}
					}
				}
			}
			
			if ($large_images > 10) {
				$issues[] = array(
					'type' => 'warning',
					'message' => '500KB以上の大きな画像が' . $large_images . '個あります。画像圧縮を推奨します。'
				);
			}
		}
		
		if (!function_exists('wp_cache_get') || !wp_using_ext_object_cache()) {
			$issues[] = array(
				'type' => 'info',
				'message' => 'キャッシュプラグインが検出されませんでした。WP Super CacheやW3 Total Cacheの導入を推奨します。'
			);
		}
		
		$theme = wp_get_theme();
		$theme_size = 0;
		$theme_path = get_template_directory();
		
		if (file_exists($theme_path)) {
			try {
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($theme_path, RecursiveDirectoryIterator::SKIP_DOTS)
				);
				foreach ($files as $file) {
					if ($file->isFile()) {
						$theme_size += $file->getSize();
					}
				}
				
				$theme_size_mb = round($theme_size / 1048576, 2);
				
				if ($theme_size_mb > 10) {
					$issues[] = array(
						'type' => 'warning',
						'message' => 'テーマのサイズが大きすぎます（' . $theme_size_mb . 'MB）。不要なファイルを削除してください。'
					);
				}
			} catch (Exception $e) {
			}
		}
		
		if (empty($issues)) {
			$issues[] = array(
				'type' => 'success',
				'message' => '特に大きな速度問題は検出されませんでした。'
			);
		}
		
		return $issues;
	}
	
	public static function display_results() {
		$results = get_option('probonoseo_diagnosis_results', array());
		
		if (empty($results)) {
			echo '<p style="color: #666;">診断結果がありません。「診断を実行」ボタンをクリックして診断を実行してください。</p>';
			return;
		}
		
		if (isset($results['debug'])) {
			echo '<div style="margin-bottom: 20px; padding: 10px; background: #e7f5fe; border-left: 4px solid #00a0d2;">';
			echo '<p style="margin: 0; font-size: 12px; color: #666;">診断実行時刻: ' . esc_html($results['debug']['timestamp']) . '</p>';
			echo '</div>';
		}
		
		if (isset($results['title_duplicates'])) {
			if ($results['title_duplicates'] === 'disabled') {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #999; margin-bottom: 10px;">− タイトル重複チェック</h4>';
				echo '<p style="margin-left: 20px; color: #999;">この診断は無効になっています。有効にするには上のスイッチをONにして「設定を保存」してください。</p>';
				echo '</div>';
			} elseif (!empty($results['title_duplicates'])) {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #d63638; margin-bottom: 10px;">⚠ タイトル重複（' . esc_html(count($results['title_duplicates'])) . '件）</h4>';
				echo '<ul style="list-style: disc; margin-left: 20px;">';
				foreach ($results['title_duplicates'] as $duplicate) {
					echo '<li>';
					echo '<strong>' . esc_html($duplicate['title']) . '</strong> ';
					echo '（' . esc_html($duplicate['count']) . '件重複 - ID: ' . esc_html(implode(', ', $duplicate['post_ids'])) . '）';
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
			} else {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #00a32a; margin-bottom: 10px;">✓ タイトル重複</h4>';
				echo '<p style="margin-left: 20px; color: #666;">重複タイトルは検出されませんでした。</p>';
				echo '</div>';
			}
		}
		
		if (isset($results['meta_duplicates'])) {
			if ($results['meta_duplicates'] === 'disabled') {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #999; margin-bottom: 10px;">− メタディスクリプション重複チェック</h4>';
				echo '<p style="margin-left: 20px; color: #999;">この診断は無効になっています。有効にするには上のスイッチをONにして「設定を保存」してください。</p>';
				echo '</div>';
			} elseif (!empty($results['meta_duplicates'])) {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #d63638; margin-bottom: 10px;">⚠ メタディスクリプション重複（' . esc_html(count($results['meta_duplicates'])) . '件）</h4>';
				echo '<ul style="list-style: disc; margin-left: 20px;">';
				foreach ($results['meta_duplicates'] as $duplicate) {
					echo '<li>';
					echo esc_html($duplicate['description']) . ' ';
					echo '（' . esc_html($duplicate['count']) . '件重複）';
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
			} else {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #00a32a; margin-bottom: 10px;">✓ メタディスクリプション重複</h4>';
				echo '<p style="margin-left: 20px; color: #666;">重複メタディスクリプションは検出されませんでした。</p>';
				echo '</div>';
			}
		}
		
		if (isset($results['speed_issues'])) {
			if ($results['speed_issues'] === 'disabled') {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #999; margin-bottom: 10px;">− サイト高速化診断</h4>';
				echo '<p style="margin-left: 20px; color: #999;">この診断は無効になっています。有効にするには上のスイッチをONにして「設定を保存」してください。</p>';
				echo '</div>';
			} elseif (!empty($results['speed_issues'])) {
				echo '<div style="margin-bottom: 20px;">';
				echo '<h4 style="color: #2271b1; margin-bottom: 10px;">🔍 サイト高速化診断</h4>';
				echo '<ul style="list-style: disc; margin-left: 20px;">';
				foreach ($results['speed_issues'] as $issue) {
					$color = $issue['type'] === 'warning' ? '#d63638' : ($issue['type'] === 'success' ? '#00a32a' : '#666');
					echo '<li style="color: ' . esc_attr($color) . ';">' . esc_html($issue['message']) . '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}
		}
	}
}

function probonoseo_init_diagnosis() {
	ProbonoSEO_Diagnosis::get_instance();
}
add_action('init', 'probonoseo_init_diagnosis');