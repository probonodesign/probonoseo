<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_Object_Cache {

	private static $probonoseo_instance = null;
	private $probonoseo_cache = array();
	private $probonoseo_cache_hits = 0;
	private $probonoseo_cache_misses = 0;

	public static function get_instance() {
		if (self::$probonoseo_instance === null) {
			self::$probonoseo_instance = new self();
		}
		return self::$probonoseo_instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_action('init', array($this, 'init_cache'));
			add_action('shutdown', array($this, 'save_cache'));
		}
	}

	public function is_enabled() {
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_object_cache', '1') === '1';
	}

	public function init_cache() {
		if (!$this->is_enabled()) {
			return;
		}

		$this->probonoseo_cache = array();
	}

	public function get($probonoseo_key, $probonoseo_group = 'default') {
		$probonoseo_cache_key = $this->build_key($probonoseo_key, $probonoseo_group);

		if (isset($this->probonoseo_cache[$probonoseo_cache_key])) {
			$this->probonoseo_cache_hits++;
			return $this->probonoseo_cache[$probonoseo_cache_key];
		}

		$probonoseo_transient_key = 'probonoseo_oc_' . md5($probonoseo_cache_key);
		$probonoseo_value = get_transient($probonoseo_transient_key);

		if ($probonoseo_value !== false) {
			$this->probonoseo_cache[$probonoseo_cache_key] = $probonoseo_value;
			$this->probonoseo_cache_hits++;
			return $probonoseo_value;
		}

		$this->probonoseo_cache_misses++;
		return false;
	}

	public function set($probonoseo_key, $probonoseo_value, $probonoseo_group = 'default', $probonoseo_expiration = 3600) {
		$probonoseo_cache_key = $this->build_key($probonoseo_key, $probonoseo_group);

		$this->probonoseo_cache[$probonoseo_cache_key] = $probonoseo_value;

		$probonoseo_transient_key = 'probonoseo_oc_' . md5($probonoseo_cache_key);
		set_transient($probonoseo_transient_key, $probonoseo_value, $probonoseo_expiration);

		return true;
	}

	public function delete($probonoseo_key, $probonoseo_group = 'default') {
		$probonoseo_cache_key = $this->build_key($probonoseo_key, $probonoseo_group);

		if (isset($this->probonoseo_cache[$probonoseo_cache_key])) {
			unset($this->probonoseo_cache[$probonoseo_cache_key]);
		}

		$probonoseo_transient_key = 'probonoseo_oc_' . md5($probonoseo_cache_key);
		delete_transient($probonoseo_transient_key);

		return true;
	}

	public function flush() {
		$this->probonoseo_cache = array();

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%probonoseo_oc_%'");

		return true;
	}

	private function build_key($probonoseo_key, $probonoseo_group) {
		return $probonoseo_group . ':' . $probonoseo_key;
	}

	public function save_cache() {
		if (!$this->is_enabled()) {
			return;
		}
	}

	public function get_stats() {
		return array(
			'hits' => $this->probonoseo_cache_hits,
			'misses' => $this->probonoseo_cache_misses,
			'ratio' => $this->probonoseo_cache_hits + $this->probonoseo_cache_misses > 0 
				? round($this->probonoseo_cache_hits / ($this->probonoseo_cache_hits + $this->probonoseo_cache_misses) * 100, 2) 
				: 0,
		);
	}
}

ProbonoSEO_Speed_Object_Cache::get_instance();