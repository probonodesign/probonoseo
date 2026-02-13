<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_WebP {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('wp_handle_upload', array($this, 'convert_to_webp'), 10, 2);
			add_filter('wp_generate_attachment_metadata', array($this, 'convert_thumbnails_to_webp'), 10, 2);
			add_filter('the_content', array($this, 'replace_images_with_webp'), 99);
			add_filter('post_thumbnail_html', array($this, 'replace_thumbnail_with_webp'), 99);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_webp', '1') === '1';
	}

	public function convert_to_webp($upload, $context) {
		if (!$this->is_enabled()) {
			return $upload;
		}

		$file_path = $upload['file'];
		$file_type = $upload['type'];

		if (!in_array($file_type, array('image/jpeg', 'image/png', 'image/gif'))) {
			return $upload;
		}

		$webp_path = $this->create_webp($file_path, $file_type);

		if ($webp_path) {
			update_option('probonoseo_webp_' . md5($file_path), $webp_path);
		}

		return $upload;
	}

	public function convert_thumbnails_to_webp($metadata, $attachment_id) {
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

			if (in_array($mime_type, array('image/jpeg', 'image/png', 'image/gif'))) {
				$this->create_webp($file_path, $mime_type);
			}
		}

		return $metadata;
	}

	private function create_webp($file_path, $mime_type) {
		if (!file_exists($file_path)) {
			return false;
		}

		$quality = (int) get_option('probonoseo_speed_pro_compress_quality', 82);
		$webp_path = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $file_path);

		if (file_exists($webp_path)) {
			return $webp_path;
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
			case 'image/gif':
				$image = @imagecreatefromgif($file_path);
				break;
		}

		if (!$image) {
			return false;
		}

		$result = @imagewebp($image, $webp_path, $quality);
		imagedestroy($image);

		if ($result && file_exists($webp_path)) {
			return $webp_path;
		}

		return false;
	}

	public function replace_images_with_webp($content) {
		if (!$this->is_enabled()) {
			return $content;
		}

		$content = preg_replace_callback(
			'/<img[^>]+src=["\']([^"\']+)\.(jpe?g|png|gif)["\'][^>]*>/i',
			array($this, 'replace_image_callback'),
			$content
		);

		return $content;
	}

	private function replace_image_callback($matches) {
		$original_tag = $matches[0];
		$src = $matches[1] . '.' . $matches[2];
		$webp_src = $matches[1] . '.webp';

		$upload_dir = wp_upload_dir();
		$webp_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $webp_src);

		if (!file_exists($webp_path)) {
			return $original_tag;
		}

		$picture = '<picture>';
		$picture .= '<source srcset="' . esc_url($webp_src) . '" type="image/webp">';
		$picture .= $original_tag;
		$picture .= '</picture>';

		return $picture;
	}

	public function replace_thumbnail_with_webp($html) {
		if (!$this->is_enabled()) {
			return $html;
		}

		return $this->replace_images_with_webp($html);
	}
}

ProbonoSEO_Speed_WebP::get_instance();