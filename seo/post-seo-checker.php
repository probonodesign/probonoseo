<?php
if (!defined('ABSPATH')) exit;

class ProbonoSEO_Post_SEO_Checker {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function analyze_content($content, $title, $description, $focus_keyword) {
        $content = wp_strip_all_tags($content);
        $site_url = home_url();
        
        $word_count = mb_strlen($content, 'UTF-8');
        $read_time = ceil($word_count / 500);
        
        $heading_pattern = '/<h([1-6])[^>]*>(.*?)<\/h\1>/is';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Analysis function, no form processing
        $probonoseo_raw_content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        preg_match_all($heading_pattern, $probonoseo_raw_content, $heading_matches);
        $headings = array();
        $heading_count = 0;
        if (!empty($heading_matches[0])) {
            $heading_count = count($heading_matches[0]);
            foreach ($heading_matches[1] as $index => $level) {
                $headings[] = array(
                    'level' => intval($level),
                    'text' => wp_strip_all_tags($heading_matches[2][$index])
                );
            }
        }
        
        $internal_links = 0;
        $external_links = 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Analysis function, no form processing
        $raw_content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $raw_content, $link_matches);
        if (!empty($link_matches[1])) {
            foreach ($link_matches[1] as $link) {
                if (strpos($link, $site_url) !== false || strpos($link, '/') === 0) {
                    $internal_links++;
                } else if (strpos($link, 'http') === 0) {
                    $external_links++;
                }
            }
        }
        
        $image_count = 0;
        $images_with_alt = 0;
        preg_match_all('/<img[^>]+>/i', $raw_content, $image_matches);
        if (!empty($image_matches[0])) {
            $image_count = count($image_matches[0]);
            foreach ($image_matches[0] as $img) {
                if (preg_match('/alt=["\'][^"\']+["\']/i', $img)) {
                    $images_with_alt++;
                }
            }
        }
        
        $keyword_count = 0;
        $keyword_density = 0;
        if (!empty($focus_keyword) && $word_count > 0) {
            $keyword_count = mb_substr_count(mb_strtolower($content, 'UTF-8'), mb_strtolower($focus_keyword, 'UTF-8'));
            $keyword_density = round(($keyword_count / $word_count) * 100, 2);
        }
        
        $keyword_in_title = false;
        $keyword_in_desc = false;
        $keyword_in_headings = false;
        $keyword_in_first_paragraph = false;
        
        if (!empty($focus_keyword)) {
            $keyword_lower = mb_strtolower($focus_keyword, 'UTF-8');
            $keyword_in_title = mb_strpos(mb_strtolower($title, 'UTF-8'), $keyword_lower) !== false;
            $keyword_in_desc = mb_strpos(mb_strtolower($description, 'UTF-8'), $keyword_lower) !== false;
            
            foreach ($headings as $heading) {
                if (mb_strpos(mb_strtolower($heading['text'], 'UTF-8'), $keyword_lower) !== false) {
                    $keyword_in_headings = true;
                    break;
                }
            }
            
            $paragraphs = preg_split('/<\/p>/i', $raw_content);
            if (!empty($paragraphs[0])) {
                $first_para = wp_strip_all_tags($paragraphs[0]);
                $keyword_in_first_paragraph = mb_strpos(mb_strtolower($first_para, 'UTF-8'), $keyword_lower) !== false;
            }
        }
        
        return array(
            'word_count' => $word_count,
            'read_time' => $read_time . '分',
            'heading_count' => $heading_count,
            'headings' => $headings,
            'internal_links' => $internal_links,
            'external_links' => $external_links,
            'image_count' => $image_count,
            'images_with_alt' => $images_with_alt . '/' . $image_count,
            'keyword_count' => $keyword_count,
            'keyword_density' => $keyword_density . '%',
            'keyword_in_title' => $keyword_in_title,
            'keyword_in_desc' => $keyword_in_desc,
            'keyword_in_headings' => $keyword_in_headings,
            'keyword_in_first_paragraph' => $keyword_in_first_paragraph
        );
    }
    
    public function get_checklist($analysis, $title, $description, $focus_keyword) {
        $checklist = array(
            'title' => array(),
            'meta' => array(),
            'content' => array(),
            'images' => array(),
            'links' => array()
        );
        
        $title_length = mb_strlen($title, 'UTF-8');
        if ($title_length >= 30 && $title_length <= 60) {
            $checklist['title'][] = array('status' => 'pass', 'message' => 'タイトルの長さが適切です（' . $title_length . '文字）');
        } else if ($title_length < 30) {
            $checklist['title'][] = array('status' => 'warning', 'message' => 'タイトルが短すぎます（' . $title_length . '文字 / 推奨30〜60文字）');
        } else {
            $checklist['title'][] = array('status' => 'fail', 'message' => 'タイトルが長すぎます（' . $title_length . '文字 / 推奨30〜60文字）');
        }
        
        if (!empty($focus_keyword)) {
            if ($analysis['keyword_in_title']) {
                $checklist['title'][] = array('status' => 'pass', 'message' => 'タイトルにキーワードが含まれています');
            } else {
                $checklist['title'][] = array('status' => 'fail', 'message' => 'タイトルにキーワードを含めてください');
            }
        }
        
        $desc_length = mb_strlen($description, 'UTF-8');
        if ($desc_length >= 70 && $desc_length <= 120) {
            $checklist['meta'][] = array('status' => 'pass', 'message' => 'メタディスクリプションの長さが適切です（' . $desc_length . '文字）');
        } else if ($desc_length < 70 && $desc_length > 0) {
            $checklist['meta'][] = array('status' => 'warning', 'message' => 'メタディスクリプションが短めです（' . $desc_length . '文字 / 推奨70〜120文字）');
        } else if ($desc_length > 120) {
            $checklist['meta'][] = array('status' => 'fail', 'message' => 'メタディスクリプションが長すぎます（' . $desc_length . '文字 / 推奨70〜120文字）');
        } else {
            $checklist['meta'][] = array('status' => 'fail', 'message' => 'メタディスクリプションが未設定です');
        }
        
        if (!empty($focus_keyword) && $desc_length > 0) {
            if ($analysis['keyword_in_desc']) {
                $checklist['meta'][] = array('status' => 'pass', 'message' => 'メタディスクリプションにキーワードが含まれています');
            } else {
                $checklist['meta'][] = array('status' => 'warning', 'message' => 'メタディスクリプションにキーワードを含めることを推奨します');
            }
        }
        
        if ($analysis['word_count'] >= 1000) {
            $checklist['content'][] = array('status' => 'pass', 'message' => '十分なコンテンツ量があります（' . number_format($analysis['word_count']) . '文字）');
        } else if ($analysis['word_count'] >= 500) {
            $checklist['content'][] = array('status' => 'warning', 'message' => 'コンテンツ量がやや少なめです（' . number_format($analysis['word_count']) . '文字 / 推奨1000文字以上）');
        } else {
            $checklist['content'][] = array('status' => 'fail', 'message' => 'コンテンツ量が不足しています（' . number_format($analysis['word_count']) . '文字 / 推奨1000文字以上）');
        }
        
        if ($analysis['heading_count'] >= 2) {
            $checklist['content'][] = array('status' => 'pass', 'message' => '見出しが適切に使用されています（' . $analysis['heading_count'] . '個）');
        } else if ($analysis['heading_count'] == 1) {
            $checklist['content'][] = array('status' => 'warning', 'message' => '見出しを増やすことを検討してください（現在' . $analysis['heading_count'] . '個）');
        } else {
            $checklist['content'][] = array('status' => 'fail', 'message' => '見出し（H2/H3）を追加してください');
        }
        
        if (!empty($focus_keyword)) {
            $density = floatval($analysis['keyword_density']);
            if ($density >= 0.5 && $density <= 2.5) {
                $checklist['content'][] = array('status' => 'pass', 'message' => 'キーワード密度が適切です（' . $analysis['keyword_density'] . '）');
            } else if ($density < 0.5) {
                $checklist['content'][] = array('status' => 'warning', 'message' => 'キーワードの使用回数を増やすことを検討してください（現在' . $analysis['keyword_density'] . '）');
            } else {
                $checklist['content'][] = array('status' => 'fail', 'message' => 'キーワードの使用が多すぎます（' . $analysis['keyword_density'] . ' / 推奨0.5〜2.5%）');
            }
            
            if ($analysis['keyword_in_first_paragraph']) {
                $checklist['content'][] = array('status' => 'pass', 'message' => '最初の段落にキーワードが含まれています');
            } else {
                $checklist['content'][] = array('status' => 'warning', 'message' => '最初の段落にキーワードを含めることを推奨します');
            }
        }
        
        if ($analysis['image_count'] > 0) {
            $checklist['images'][] = array('status' => 'pass', 'message' => '画像が使用されています（' . $analysis['image_count'] . '枚）');
            
            $parts = explode('/', $analysis['images_with_alt']);
            $with_alt = intval($parts[0]);
            $total = intval($parts[1]);
            if ($with_alt === $total) {
                $checklist['images'][] = array('status' => 'pass', 'message' => 'すべての画像にalt属性が設定されています');
            } else {
                $missing = $total - $with_alt;
                $checklist['images'][] = array('status' => 'fail', 'message' => $missing . '枚の画像にalt属性がありません');
            }
        } else {
            $checklist['images'][] = array('status' => 'warning', 'message' => '画像を追加することを検討してください');
        }
        
        if ($analysis['internal_links'] >= 2) {
            $checklist['links'][] = array('status' => 'pass', 'message' => '内部リンクが適切に設定されています（' . $analysis['internal_links'] . '件）');
        } else if ($analysis['internal_links'] == 1) {
            $checklist['links'][] = array('status' => 'warning', 'message' => '内部リンクを増やすことを推奨します（現在' . $analysis['internal_links'] . '件）');
        } else {
            $checklist['links'][] = array('status' => 'fail', 'message' => '内部リンクを追加してください');
        }
        
        if ($analysis['external_links'] >= 1) {
            $checklist['links'][] = array('status' => 'pass', 'message' => '外部リンクが設定されています（' . $analysis['external_links'] . '件）');
        } else {
            $checklist['links'][] = array('status' => 'warning', 'message' => '信頼性のある外部ソースへのリンクを検討してください');
        }
        
        return $checklist;
    }
    
    public function get_suggestions($analysis, $title, $description, $focus_keyword) {
        $suggestions = array();
        
        $title_length = mb_strlen($title, 'UTF-8');
        if ($title_length > 60) {
            $suggestions[] = array('type' => 'error', 'message' => 'タイトルを60文字以内に短縮してください（現在' . $title_length . '文字）');
        }
        
        $desc_length = mb_strlen($description, 'UTF-8');
        if ($desc_length == 0) {
            $suggestions[] = array('type' => 'error', 'message' => 'メタディスクリプションを設定してください');
        } else if ($desc_length > 120) {
            $suggestions[] = array('type' => 'warning', 'message' => 'メタディスクリプションを120文字以内に調整してください');
        }
        
        if (empty($focus_keyword)) {
            $suggestions[] = array('type' => 'warning', 'message' => 'フォーカスキーワードを設定するとSEO分析が有効になります');
        } else {
            if (!$analysis['keyword_in_title']) {
                $suggestions[] = array('type' => 'warning', 'message' => 'タイトルにフォーカスキーワードを含めてください');
            }
            if (!$analysis['keyword_in_headings']) {
                $suggestions[] = array('type' => 'warning', 'message' => '見出し（H2/H3）にキーワードを含めることを検討してください');
            }
        }
        
        if ($analysis['word_count'] < 1000) {
            $suggestions[] = array('type' => 'warning', 'message' => 'コンテンツを1000文字以上に増やすことを推奨します');
        }
        
        if ($analysis['heading_count'] < 2) {
            $suggestions[] = array('type' => 'warning', 'message' => '記事の構造を明確にするため、見出しを追加してください');
        }
        
        if ($analysis['internal_links'] == 0) {
            $suggestions[] = array('type' => 'warning', 'message' => '関連記事への内部リンクを追加してください');
        }
        
        $parts = explode('/', $analysis['images_with_alt']);
        $with_alt = intval($parts[0]);
        $total = intval($parts[1]);
        if ($total > 0 && $with_alt < $total) {
            $suggestions[] = array('type' => 'error', 'message' => 'すべての画像にalt属性を設定してください');
        }
        
        if (empty($suggestions)) {
            $suggestions[] = array('type' => 'success', 'message' => 'すべてのSEO要素が最適化されています！');
        }
        
        return $suggestions;
    }
}