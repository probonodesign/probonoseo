<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_DNS {

	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_action('wp_head', array($this, 'output_dns_prefetch'), 1);
			add_action('wp_head', array($this, 'output_preconnect'), 1);
		}
	}

	public function is_enabled() {
		$license = ProbonoSEO_License::get_instance();
		if (!$license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_dns', '1') === '1';
	}

	public function get_dns_prefetch_domains() {
		$custom_domains = get_option('probonoseo_speed_pro_dns_domains', '');
		$domains = array_filter(array_map('trim', explode("\n", $custom_domains)));

		$default_domains = array(
			'fonts.googleapis.com',
			'fonts.gstatic.com',
			'www.google-analytics.com',
			'www.googletagmanager.com',
		);

		return array_unique(array_merge($default_domains, $domains));
	}

	public function get_preconnect_domains() {
		$custom_domains = get_option('probonoseo_speed_pro_preconnect_domains', '');
		$domains = array_filter(array_map('trim', explode("\n", $custom_domains)));

		$default_domains = array(
			'fonts.gstatic.com',
		);

		return array_unique(array_merge($default_domains, $domains));
	}

	public function output_dns_prefetch() {
		if (!$this->is_enabled()) {
			return;
		}

		$domains = $this->get_dns_prefetch_domains();

		foreach ($domains as $domain) {
			if (empty($domain)) {
				continue;
			}
			$domain = $this->sanitize_domain($domain);
			echo '<link rel="dns-prefetch" href="//' . esc_attr($domain) . '">' . "\n";
		}
	}

	public function output_preconnect() {
		if (!$this->is_enabled()) {
			return;
		}

		$domains = $this->get_preconnect_domains();

		foreach ($domains as $domain) {
			if (empty($domain)) {
				continue;
			}
			$domain = $this->sanitize_domain($domain);
			echo '<link rel="preconnect" href="https://' . esc_attr($domain) . '" crossorigin>' . "\n";
		}
	}

	private function sanitize_domain($domain) {
		$domain = preg_replace('#^https?://#', '', $domain);
		$domain = preg_replace('#/.*$#', '', $domain);
		return $domain;
	}
}

ProbonoSEO_Speed_DNS::get_instance();