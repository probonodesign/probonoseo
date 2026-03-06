<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Post_SEO_Score {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function calculate_scores($analysis, $title, $description, $focus_keyword) {
        $title_score = $this->calculate_title_score($title, $focus_keyword, $analysis);
        $meta_score = $this->calculate_meta_score($description, $focus_keyword, $analysis);
        $content_score = $this->calculate_content_score($analysis);
        $keyword_score = $this->calculate_keyword_score($analysis, $focus_keyword);
        $links_score = $this->calculate_links_score($analysis);
        
        $total_score = round(
            ($title_score * 0.20) +
            ($meta_score * 0.15) +
            ($content_score * 0.30) +
            ($keyword_score * 0.20) +
            ($links_score * 0.15)
        );
        
        return array(
            'total' => $total_score,
            'title' => $title_score,
            'meta' => $meta_score,
            'content' => $content_score,
            'keyword' => $keyword_score,
            'links' => $links_score
        );
    }
    
    private function calculate_title_score($title, $focus_keyword, $analysis) {
        $score = 0;
        $length = mb_strlen($title, 'UTF-8');
        
        if ($length >= 30 && $length <= 60) {
            $score += 50;
        } else if ($length >= 20 && $length < 30) {
            $score += 30;
        } else if ($length > 60 && $length <= 70) {
            $score += 30;
        } else if ($length > 0) {
            $score += 10;
        }
        
        if (!empty($focus_keyword) && $analysis['keyword_in_title']) {
            $score += 50;
        } else if (empty($focus_keyword)) {
            $score += 25;
        }
        
        return min(100, $score);
    }
    
    private function calculate_meta_score($description, $focus_keyword, $analysis) {
        $score = 0;
        $length = mb_strlen($description, 'UTF-8');
        
        if ($length >= 70 && $length <= 120) {
            $score += 50;
        } else if ($length >= 50 && $length < 70) {
            $score += 35;
        } else if ($length > 120 && $length <= 155) {
            $score += 30;
        } else if ($length > 0) {
            $score += 15;
        }
        
        if (!empty($focus_keyword) && $analysis['keyword_in_desc']) {
            $score += 50;
        } else if (empty($focus_keyword)) {
            $score += 25;
        } else if (!empty($description)) {
            $score += 15;
        }
        
        return min(100, $score);
    }
    
    private function calculate_content_score($analysis) {
        $score = 0;
        
        $word_count = $analysis['word_count'];
        if ($word_count >= 2000) {
            $score += 35;
        } else if ($word_count >= 1000) {
            $score += 30;
        } else if ($word_count >= 500) {
            $score += 20;
        } else if ($word_count >= 300) {
            $score += 10;
        }
        
        $heading_count = $analysis['heading_count'];
        if ($heading_count >= 4) {
            $score += 25;
        } else if ($heading_count >= 2) {
            $score += 20;
        } else if ($heading_count >= 1) {
            $score += 10;
        }
        
        if ($analysis['image_count'] > 0) {
            $score += 15;
            
            $parts = explode('/', $analysis['images_with_alt']);
            $with_alt = intval($parts[0]);
            $total = intval($parts[1]);
            if ($total > 0 && $with_alt === $total) {
                $score += 15;
            } else if ($total > 0 && $with_alt > 0) {
                $score += 8;
            }
        }
        
        if ($analysis['keyword_in_first_paragraph']) {
            $score += 10;
        }
        
        return min(100, $score);
    }
    
    private function calculate_keyword_score($analysis, $focus_keyword) {
        if (empty($focus_keyword)) {
            return 50;
        }
        
        $score = 0;
        
        $density = floatval($analysis['keyword_density']);
        if ($density >= 0.5 && $density <= 2.5) {
            $score += 40;
        } else if ($density > 0 && $density < 0.5) {
            $score += 20;
        } else if ($density > 2.5 && $density <= 4) {
            $score += 15;
        }
        
        if ($analysis['keyword_in_title']) {
            $score += 25;
        }
        
        if ($analysis['keyword_in_desc']) {
            $score += 15;
        }
        
        if ($analysis['keyword_in_headings']) {
            $score += 15;
        }
        
        if ($analysis['keyword_count'] >= 3) {
            $score += 5;
        }
        
        return min(100, $score);
    }
    
    private function calculate_links_score($analysis) {
        $score = 0;
        
        $internal = $analysis['internal_links'];
        if ($internal >= 3) {
            $score += 50;
        } else if ($internal >= 2) {
            $score += 40;
        } else if ($internal >= 1) {
            $score += 25;
        }
        
        $external = $analysis['external_links'];
        if ($external >= 2) {
            $score += 50;
        } else if ($external >= 1) {
            $score += 35;
        } else {
            $score += 10;
        }
        
        return min(100, $score);
    }
}