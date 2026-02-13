<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_AVIF {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('wp_handle_upload', array($this, 'convert_to_avif'), 11, 2);
			add_filter('wp_generate_attachment_metadata', array($this, 'convert_thumbnails_to_avif'), 11, 2);
			add_filter('the_content', array($this, 'replace_images_with_avif'), 98);
			add_filter('post_thumbnail_html', array($this, 'replace_thumbnail_with_avif'), 98);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		if (!function_exists('imageavif')) {
			return false;
		}
		return get_option('probonoseo_speed_pro_avif', '0') === '1';
	}

	public function convert_to_avif($upload, $context) {
		if (!$this->is_enabled()) {
			return $upload;
		}

		$file_path = $upload['file'];
		$file_type = $upload['type'];

		if (!in_array($file_type, array('image/jpeg', 'image/png'))) {
			return $upload;
		}

		$avif_path = $this->create_avif($file_path, $file_type);

		if ($avif_path) {
			update_option('probonoseo_avif_' . md5($file_path), $avif_path);
		}

		return $upload;
	}

	public function convert_thumbnails_to_avif($metadata, $attachment_id) {
		if (!$this->is_enabled()) {
			return $metadata;
		}

		if (empty($metadata['sizes'])) {
			return $metadata;
		}

		$upload_dir = wp_upload_dir();
		$base_dir = trailingslashit($upload_dir['basedir']);

		if (!empty($metadata['file'])) {
			$file_dir = trailingslashit(dirname($metadata['file']));
		} else {
			$file_dir = '';
		}

		foreach ($metadata['sizes'] as $size => $size_data) {
			$file_path = $base_dir . $file_dir . $size_data['file'];
			$mime_type = $size_data['mime-type'];

			if (in_array($mime_type, array('image/jpeg', 'image/png'))) {
				$this->create_avif($file_path, $mime_type);
			}
		}

		return $metadata;
	}

	private function create_avif($file_path, $mime_type) {
		if (!file_exists($file_path)) {
			return false;
		}

		if (!function_exists('imageavif')) {
			return false;
		}

		$quality = (int) get_option('probonoseo_speed_pro_compress_quality', 82);
		$avif_path = preg_replace('/\.(jpe?g|png)$/i', '.avif', $file_path);

		if (file_exists($avif_path)) {
			return $avif_path;
		}

		$image = null;

		switch ($mime_type) {
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($file_path);
				break;
			case 'image/png':
				$image = @imagecreatefrompng($file_path);
				if ($image) {
					imagepalettetotruecolor($image);
					imagealphablending($image, true);
					imagesavealpha($image, true);
				}
				break;
		}

		if (!$image) {
			return false;
		}

		$result = @imageavif($image, $avif_path, $quality);
		imagedestroy($image);

		if ($result && file_exists($avif_path)) {
			return $avif_path;
		}

		return false;
	}

	public function replace_images_with_avif($content) {
		if (!$this->is_enabled()) {
			return $content;
		}

		$content = preg_replace_callback(
			'/<picture>.*?<\/picture>|<img[^>]+src=["\']([^"\']+)\.(jpe?g|png)["\'][^>]*>/is',
			array($this, 'replace_image_callback'),
			$content
		);

		return $content;
	}

	private function replace_image_callback($matches) {
		$original_tag = $matches[0];

		if (strpos($original_tag, '<picture>') === 0) {
			preg_match('/src=["\']([^"\']+)\.(jpe?g|png)["\']/i', $original_tag, $src_match);
			if (empty($src_match)) {
				return $original_tag;
			}
			$base_src = $src_match[1];
		} else {
			$base_src = $matches[1];
		}

		$avif_src = $base_src . '.avif';
		$upload_dir = wp_upload_dir();
		$avif_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $avif_src);

		if (!file_exists($avif_path)) {
			return $original_tag;
		}

		if (strpos($original_tag, '<picture>') === 0) {
			$avif_source = '<source srcset="' . esc_url($avif_src) . '" type="image/avif">';
			return preg_replace('/<picture>/', '<picture>' . $avif_source, $original_tag, 1);
		}

		$picture = '<picture>';
		$picture .= '<source srcset="' . esc_url($avif_src) . '" type="image/avif">';
		$picture .= $original_tag;
		$picture .= '</picture>';

		return $picture;
	}

	public function replace_thumbnail_with_avif($html) {
		if (!$this->is_enabled()) {
			return $html;
		}

		return $this->replace_images_with_avif($html);
	}
}

ProbonoSEO_Speed_AVIF::get_instance();