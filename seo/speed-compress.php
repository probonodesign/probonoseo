<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Compress {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('wp_handle_upload', array($this, 'compress_image'), 5, 2);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_compress', '1') === '1';
	}

	public function compress_image($upload, $context) {
		if (!$this->is_enabled()) {
			return $upload;
		}

		$file_path = $upload['file'];
		$file_type = $upload['type'];

		if (!in_array($file_type, array('image/jpeg', 'image/png'))) {
			return $upload;
		}

		$quality = (int) get_option('probonoseo_speed_pro_compress_quality', 82);

		switch ($file_type) {
			case 'image/jpeg':
				$this->compress_jpeg($file_path, $quality);
				break;
			case 'image/png':
				$this->compress_png($file_path);
				break;
		}

		return $upload;
	}

	private function compress_jpeg($file_path, $quality) {
		if (!file_exists($file_path)) {
			return false;
		}

		$image = @imagecreatefromjpeg($file_path);
		if (!$image) {
			return false;
		}

		$result = @imagejpeg($image, $file_path, $quality);
		imagedestroy($image);

		return $result;
	}

	private function compress_png($file_path) {
		if (!file_exists($file_path)) {
			return false;
		}

		$image = @imagecreatefrompng($file_path);
		if (!$image) {
			return false;
		}

		imagepalettetotruecolor($image);
		imagealphablending($image, false);
		imagesavealpha($image, true);

		$result = @imagepng($image, $file_path, 9);
		imagedestroy($image);

		return $result;
	}

	public function optimize_existing_image($attachment_id) {
		if (!$this->is_enabled()) {
			return false;
		}

		$file_path = get_attached_file($attachment_id);
		if (!$file_path || !file_exists($file_path)) {
			return false;
		}

		$mime_type = get_post_mime_type($attachment_id);
		$quality = (int) get_option('probonoseo_speed_pro_compress_quality', 82);

		switch ($mime_type) {
			case 'image/jpeg':
				return $this->compress_jpeg($file_path, $quality);
			case 'image/png':
				return $this->compress_png($file_path);
		}

		return false;
	}
}

ProbonoSEO_Speed_Compress::get_instance();