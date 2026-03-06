<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Competitor_Score {
	
	private static $instance = null;
	
	private $weights = array(
		'title' => 15,
		'meta_description' => 15,
		'headings' => 15,
		'content' => 15,
		'images' => 10,
		'internal_links' => 10,
		'external_links' => 5,
		'schema' => 10,
		'mobile' => 5
	);
	
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
	}
	
	public function calculate_comprehensive_score($data) {
		$scores = array();
		
		$scores['title'] = $this->score_title($data);
		$scores['meta_description'] = $this->score_meta_description($data);
		$scores['headings'] = $this->score_headings($data);
		$scores['content'] = $this->score_content($data);
		$scores['images'] = $this->score_images($data);
		$scores['internal_links'] = $this->score_internal_links($data);
		$scores['external_links'] = $this->score_external_links($data);
		$scores['schema'] = $this->score_schema($data);
		$scores['mobile'] = $this->score_mobile($data);
		
		$total_score = 0;
		foreach ($scores as $key => $score) {
			$total_score += $score * ($this->weights[$key] / 100);
		}
		
		return array(
			'total' => round($total_score),
			'details' => $scores,
			'weights' => $this->weights
		);
	}
	
	private function score_title($data) {
		$title = $data['title'] ?? '';
		$length = mb_strlen($title);
		
		if ($length === 0) {
			return 0;
		}
		
		if ($length >= 30 && $length <= 60) {
			return 100;
		} elseif ($length >= 20 && $length <= 70) {
			return 80;
		} elseif ($length < 20) {
			return 40;
		}
		return 60;
	}
	
	private function score_meta_description($data) {
		$meta = $data['meta_description'] ?? '';
		$length = mb_strlen($meta);
		
		if ($length === 0) {
			return 0;
		}
		
		if ($length >= 70 && $length <= 160) {
			return 100;
		} elseif ($length >= 50 && $length <= 180) {
			return 80;
		} elseif ($length < 50) {
			return 40;
		}
		return 60;
	}
	
	private function score_headings($data) {
		$headings = $data['headings'] ?? array();
		$score = 0;
		
		$h1_count = count($headings['h1'] ?? array());
		if ($h1_count === 1) {
			$score += 40;
		} elseif ($h1_count > 1) {
			$score += 20;
		}
		
		$h2_count = count($headings['h2'] ?? array());
		if ($h2_count >= 3 && $h2_count <= 10) {
			$score += 40;
		} elseif ($h2_count >= 1) {
			$score += 20;
		}
		
		$h3_count = count($headings['h3'] ?? array());
		if ($h3_count >= 1 && $h2_count >= 1) {
			$score += 20;
		} elseif ($h3_count >= 1) {
			$score += 10;
		}
		
		return min($score, 100);
	}
	
	private function score_content($data) {
		$word_count = $data['word_count'] ?? 0;
		
		if ($word_count === 0) {
			return 0;
		}
		
		if ($word_count >= 3000) {
			return 100;
		} elseif ($word_count >= 2000) {
			return 90;
		} elseif ($word_count >= 1500) {
			return 80;
		} elseif ($word_count >= 1000) {
			return 70;
		} elseif ($word_count >= 500) {
			return 50;
		}
		return 30;
	}
	
	private function score_images($data) {
		$count = $data['image_count'] ?? 0;
		
		if ($count === 0) {
			return 30;
		}
		
		if ($count >= 3 && $count <= 15) {
			return 100;
		} elseif ($count >= 1 && $count <= 20) {
			return 80;
		} elseif ($count > 20) {
			return 60;
		}
		return 50;
	}
	
	private function score_internal_links($data) {
		$count = $data['internal_links'] ?? 0;
		
		if ($count >= 5 && $count <= 20) {
			return 100;
		} elseif ($count >= 3) {
			return 80;
		} elseif ($count >= 1) {
			return 50;
		}
		return 20;
	}
	
	private function score_external_links($data) {
		$count = $data['external_links'] ?? 0;
		
		if ($count >= 1 && $count <= 5) {
			return 100;
		} elseif ($count >= 1 && $count <= 10) {
			return 80;
		} elseif ($count > 10) {
			return 50;
		}
		return 60;
	}
	
	private function score_schema($data) {
		$schemas = $data['schema'] ?? array();
		$count = count($schemas);
		
		if ($count >= 3) {
			return 100;
		} elseif ($count >= 2) {
			return 80;
		} elseif ($count >= 1) {
			return 60;
		}
		return 0;
	}
	
	private function score_mobile($data) {
		$viewport = $data['viewport'] ?? false;
		return $viewport ? 100 : 0;
	}
}

ProbonoSEO_Competitor_Score::get_instance();