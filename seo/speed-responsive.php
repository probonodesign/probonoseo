<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Responsive {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_filter('wp_calculate_image_sizes', array($this, 'optimize_image_sizes'), 10, 5);
			add_filter('wp_calculate_image_srcset', array($this, 'optimize_image_srcset'), 10, 5);
			add_filter('intermediate_image_sizes_advanced', array($this, 'add_responsive_sizes'));
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_responsive', '1') === '1';
	}

	public function get_responsive_sizes() {
		$sizes_string = get_option('probonoseo_speed_pro_responsive_sizes', '320,640,768,1024,1280,1536');
		$sizes = array_map('trim', explode(',', $sizes_string));
		$sizes = array_filter($sizes, 'is_numeric');
		$sizes = array_map('intval', $sizes);
		sort($sizes);
		return $sizes;
	}

	public function add_responsive_sizes($sizes) {
		if (!$this->is_enabled()) {
			return $sizes;
		}

		$responsive_sizes = $this->get_responsive_sizes();

		foreach ($responsive_sizes as $width) {
			$size_name = 'probonoseo-responsive-' . $width;
			if (!isset($sizes[$size_name])) {
				$sizes[$size_name] = array(
					'width' => $width,
					'height' => 9999,
					'crop' => false,
				);
			}
		}

		return $sizes;
	}

	public function optimize_image_sizes($sizes, $size, $image_src, $image_meta, $attachment_id) {
		if (!$this->is_enabled()) {
			return $sizes;
		}

		$responsive_sizes = $this->get_responsive_sizes();
		$max_width = max($responsive_sizes);

		$sizes = '(max-width: ' . $max_width . 'px) 100vw, ' . $max_width . 'px';

		return $sizes;
	}

	public function optimize_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
		if (!$this->is_enabled()) {
			return $sources;
		}

		if (empty($image_meta['sizes'])) {
			return $sources;
		}

		$upload_dir = wp_upload_dir();
		$base_url = trailingslashit($upload_dir['baseurl']);

		if (!empty($image_meta['file'])) {
			$file_dir = trailingslashit(dirname($image_meta['file']));
		} else {
			$file_dir = '';
		}

		$responsive_sizes = $this->get_responsive_sizes();

		foreach ($image_meta['sizes'] as $size_name => $size_data) {
			if (strpos($size_name, 'probonoseo-responsive-') !== 0) {
				continue;
			}

			$width = $size_data['width'];

			if (!in_array($width, $responsive_sizes)) {
				continue;
			}

			if (isset($sources[$width])) {
				continue;
			}

			$sources[$width] = array(
				'url' => $base_url . $file_dir . $size_data['file'],
				'descriptor' => 'w',
				'value' => $width,
			);
		}

		ksort($sources);

		return $sources;
	}
}

ProbonoSEO_Speed_Responsive::get_instance();