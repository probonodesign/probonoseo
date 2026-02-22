<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Competitor_Analyzer {
	
	private static $instance = null;
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
	}
	
	public function calculate_scores($results) {
		$scores = array();
		
		if (isset($results['target']) && !$results['target']['error']) {
			$scores['自サイト'] = $this->calculate_single_score($results['target']);
		}
		
		if (!empty($results['competitors'])) {
			foreach ($results['competitors'] as $index => $comp) {
				if (!$comp['error']) {
					$scores['競合' . ($index + 1)] = $this->calculate_single_score($comp);
				}
			}
		}
		
		$results['scores'] = $scores;
		
		return $results;
	}
	
	private function calculate_single_score($data) {
		$score = 0;
		$max_score = 100;
		
		$title_len = mb_strlen($data['title'] ?? '');
		if ($title_len >= 30 && $title_len <= 60) {
			$score += 15;
		} elseif ($title_len > 0 && $title_len < 80) {
			$score += 8;
		}
		
		$meta_len = mb_strlen($data['meta_description'] ?? '');
		if ($meta_len >= 70 && $meta_len <= 160) {
			$score += 15;
		} elseif ($meta_len > 0) {
			$score += 8;
		}
		
		$h1_count = count($data['headings']['h1'] ?? array());
		if ($h1_count === 1) {
			$score += 10;
		} elseif ($h1_count > 0) {
			$score += 5;
		}
		
		$h2_count = count($data['headings']['h2'] ?? array());
		if ($h2_count >= 3 && $h2_count <= 10) {
			$score += 10;
		} elseif ($h2_count > 0) {
			$score += 5;
		}
		
		$word_count = $data['word_count'] ?? 0;
		if ($word_count >= 2000) {
			$score += 15;
		} elseif ($word_count >= 1000) {
			$score += 10;
		} elseif ($word_count >= 500) {
			$score += 5;
		}
		
		$image_count = $data['image_count'] ?? 0;
		if ($image_count >= 3 && $image_count <= 20) {
			$score += 10;
		} elseif ($image_count > 0) {
			$score += 5;
		}
		
		$internal = $data['internal_links'] ?? 0;
		if ($internal >= 5) {
			$score += 10;
		} elseif ($internal >= 2) {
			$score += 5;
		}
		
		$external = $data['external_links'] ?? 0;
		if ($external >= 1 && $external <= 10) {
			$score += 5;
		}
		
		$schema_count = count($data['schema'] ?? array());
		if ($schema_count >= 2) {
			$score += 10;
		} elseif ($schema_count >= 1) {
			$score += 5;
		}
		
		return min($score, $max_score);
	}
	
	public function analyze_title($data) {
		$title = $data['title'] ?? '';
		$length = mb_strlen($title);
		
		$result = array(
			'title' => $title,
			'length' => $length,
			'status' => 'good',
			'message' => ''
		);
		
		if ($length === 0) {
			$result['status'] = 'bad';
			$result['message'] = 'タイトルが設定されていません。';
		} elseif ($length < 30) {
			$result['status'] = 'warning';
			$result['message'] = 'タイトルが短すぎます。30文字以上を推奨します。';
		} elseif ($length > 60) {
			$result['status'] = 'warning';
			$result['message'] = 'タイトルが長すぎます。60文字以下を推奨します。';
		} else {
			$result['message'] = '適切な長さです。';
		}
		
		return $result;
	}
	
	public function analyze_meta_description($data) {
		$meta = $data['meta_description'] ?? '';
		$length = mb_strlen($meta);
		
		$result = array(
			'description' => $meta,
			'length' => $length,
			'status' => 'good',
			'message' => ''
		);
		
		if ($length === 0) {
			$result['status'] = 'bad';
			$result['message'] = 'メタディスクリプションが設定されていません。';
		} elseif ($length < 70) {
			$result['status'] = 'warning';
			$result['message'] = 'メタディスクリプションが短すぎます。70文字以上を推奨します。';
		} elseif ($length > 160) {
			$result['status'] = 'warning';
			$result['message'] = 'メタディスクリプションが長すぎます。160文字以下を推奨します。';
		} else {
			$result['message'] = '適切な長さです。';
		}
		
		return $result;
	}
	
	public function analyze_headings($data) {
		$headings = $data['headings'] ?? array();
		
		$result = array(
			'headings' => $headings,
			'structure' => array(),
			'issues' => array()
		);
		
		$h1_count = count($headings['h1'] ?? array());
		if ($h1_count === 0) {
			$result['issues'][] = 'H1タグがありません。';
		} elseif ($h1_count > 1) {
			$result['issues'][] = 'H1タグが複数あります（' . $h1_count . '個）。1つにすることを推奨します。';
		}
		
		$h2_count = count($headings['h2'] ?? array());
		if ($h2_count === 0) {
			$result['issues'][] = 'H2タグがありません。適切な見出し構造を推奨します。';
		}
		
		$h3_count = count($headings['h3'] ?? array());
		if ($h3_count > 0 && $h2_count === 0) {
			$result['issues'][] = 'H2タグなしでH3タグが使用されています。';
		}
		
		$result['structure'] = array(
			'h1' => $h1_count,
			'h2' => $h2_count,
			'h3' => $h3_count,
			'h4' => count($headings['h4'] ?? array()),
			'h5' => count($headings['h5'] ?? array()),
			'h6' => count($headings['h6'] ?? array())
		);
		
		return $result;
	}
	
	public function analyze_content_length($data, $competitors_data = array()) {
		$word_count = $data['word_count'] ?? 0;
		
		$avg = 0;
		if (!empty($competitors_data)) {
			$counts = array_map(function($c) {
				return $c['word_count'] ?? 0;
			}, $competitors_data);
			$counts = array_filter($counts);
			if (count($counts) > 0) {
				$avg = array_sum($counts) / count($counts);
			}
		}
		
		$result = array(
			'word_count' => $word_count,
			'competitors_avg' => round($avg),
			'status' => 'good',
			'message' => ''
		);
		
		if ($word_count < 500) {
			$result['status'] = 'bad';
			$result['message'] = '文字数が少なすぎます。500文字以上を推奨します。';
		} elseif ($word_count < 1000) {
			$result['status'] = 'warning';
			$result['message'] = '文字数がやや少なめです。競合平均は' . round($avg) . '文字です。';
		} else {
			$result['message'] = '適切な文字数です。';
		}
		
		if ($avg > 0 && $word_count < $avg * 0.7) {
			$result['status'] = 'warning';
			$result['message'] = '競合平均（' . round($avg) . '文字）より少ないです。';
		}
		
		return $result;
	}
	
	public function analyze_images($data) {
		$count = $data['image_count'] ?? 0;
		
		$result = array(
			'count' => $count,
			'status' => 'good',
			'message' => ''
		);
		
		if ($count === 0) {
			$result['status'] = 'warning';
			$result['message'] = '画像がありません。視覚的なコンテンツを追加することを推奨します。';
		} elseif ($count > 30) {
			$result['status'] = 'warning';
			$result['message'] = '画像が多すぎる可能性があります。ページ速度に影響する場合があります。';
		} else {
			$result['message'] = '適切な画像数です。';
		}
		
		return $result;
	}
	
	public function analyze_links($data) {
		$internal = $data['internal_links'] ?? 0;
		$external = $data['external_links'] ?? 0;
		
		$result = array(
			'internal' => $internal,
			'external' => $external,
			'issues' => array()
		);
		
		if ($internal < 2) {
			$result['issues'][] = '内部リンクが少なすぎます。関連ページへのリンクを追加することを推奨します。';
		}
		
		if ($external > 20) {
			$result['issues'][] = '外部リンクが多すぎる可能性があります。';
		}
		
		return $result;
	}
	
	public function analyze_schema($data) {
		$schemas = $data['schema'] ?? array();
		
		$result = array(
			'schemas' => $schemas,
			'count' => count($schemas),
			'status' => 'good',
			'message' => ''
		);
		
		if (count($schemas) === 0) {
			$result['status'] = 'warning';
			$result['message'] = 'schema構造化データがありません。リッチスニペット表示のために追加を推奨します。';
		} else {
			$result['message'] = count($schemas) . '種類のschemaが検出されました: ' . implode(', ', $schemas);
		}
		
		return $result;
	}
	
	public function analyze_mobile($data) {
		$viewport = $data['viewport'] ?? false;
		
		$result = array(
			'viewport' => $viewport,
			'status' => $viewport ? 'good' : 'bad',
			'message' => $viewport 
				? 'viewportメタタグが設定されています。' 
				: 'viewportメタタグがありません。モバイル対応が必要です。'
		);
		
		return $result;
	}
	
	public function analyze_keywords($data) {
		$keywords = $data['keywords'] ?? array();
		
		$result = array(
			'keywords' => $keywords,
			'top_10' => array_slice($keywords, 0, 10, true)
		);
		
		return $result;
	}
	
	public function get_improvement_suggestions($target_data, $competitors_data) {
		$suggestions = array();
		
		$target_word_count = $target_data['word_count'] ?? 0;
		$avg_word_count = 0;
		if (!empty($competitors_data)) {
			$counts = array_map(function($c) {
				return $c['word_count'] ?? 0;
			}, $competitors_data);
			$counts = array_filter($counts);
			if (count($counts) > 0) {
				$avg_word_count = array_sum($counts) / count($counts);
			}
		}
		
		if ($target_word_count < $avg_word_count * 0.8) {
			$diff = round($avg_word_count - $target_word_count);
			$suggestions[] = array(
				'type' => 'content',
				'priority' => 'high',
				'message' => '競合サイトと比較して文字数が約' . $diff . '文字少ないです。コンテンツの追加を検討してください。'
			);
		}
		
		$target_h2 = count($target_data['headings']['h2'] ?? array());
		$avg_h2 = 0;
		if (!empty($competitors_data)) {
			$h2_counts = array_map(function($c) {
				return count($c['headings']['h2'] ?? array());
			}, $competitors_data);
			if (count($h2_counts) > 0) {
				$avg_h2 = array_sum($h2_counts) / count($h2_counts);
			}
		}
		
		if ($target_h2 < $avg_h2 * 0.7) {
			$suggestions[] = array(
				'type' => 'structure',
				'priority' => 'medium',
				'message' => '見出し（H2）の数が競合平均（' . round($avg_h2) . '個）より少ないです。構成を見直すことを検討してください。'
			);
		}
		
		$target_images = $target_data['image_count'] ?? 0;
		$avg_images = 0;
		if (!empty($competitors_data)) {
			$img_counts = array_map(function($c) {
				return $c['image_count'] ?? 0;
			}, $competitors_data);
			if (count($img_counts) > 0) {
				$avg_images = array_sum($img_counts) / count($img_counts);
			}
		}
		
		if ($target_images < $avg_images * 0.5 && $avg_images >= 3) {
			$suggestions[] = array(
				'type' => 'media',
				'priority' => 'medium',
				'message' => '画像数が競合平均（' . round($avg_images) . '枚）より少ないです。視覚的なコンテンツの追加を検討してください。'
			);
		}
		
		$target_schema = count($target_data['schema'] ?? array());
		if ($target_schema === 0) {
			$suggestions[] = array(
				'type' => 'schema',
				'priority' => 'medium',
				'message' => 'schema構造化データがありません。リッチスニペット表示のために追加を推奨します。'
			);
		}
		
		$target_internal = $target_data['internal_links'] ?? 0;
		if ($target_internal < 3) {
			$suggestions[] = array(
				'type' => 'links',
				'priority' => 'medium',
				'message' => '内部リンクが少なすぎます。関連ページへのリンクを追加してください。'
			);
		}
		
		return $suggestions;
	}
}

ProbonoSEO_Competitor_Analyzer::get_instance();