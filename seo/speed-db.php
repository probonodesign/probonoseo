<?php
if (!defined('ABSPATH')) {
	exit;
}

class ProbonoSEO_Speed_DB {

	private static $probonoseo_instance = null;

	public static function get_instance() {
		if (self::$probonoseo_instance === null) {
			self::$probonoseo_instance = new self();
		}
		return self::$probonoseo_instance;
	}

	private function __construct() {
		if ($this->is_enabled()) {
			add_action('init', array($this, 'optimize_queries'));
			add_filter('posts_request', array($this, 'optimize_post_query'), 10, 2);
			add_filter('found_posts_query', array($this, 'optimize_found_posts'), 10, 2);
		}
	}

	public function is_enabled() {
		$probonoseo_license = ProbonoSEO_License::get_instance();
		if (!$probonoseo_license->is_pro_active()) {
			return false;
		}
		return get_option('probonoseo_speed_pro_db', '1') === '1';
	}

	public function optimize_queries() {
		if (!$this->is_enabled()) {
			return;
		}

		if (!is_admin()) {
			add_filter('pre_get_posts', array($this, 'disable_unnecessary_queries'));
		}
	}

	public function disable_unnecessary_queries($probonoseo_query) {
		if (!$probonoseo_query->is_main_query()) {
			return $probonoseo_query;
		}

		if ($probonoseo_query->is_singular()) {
			$probonoseo_query->set('no_found_rows', true);
		}

		return $probonoseo_query;
	}

	public function optimize_post_query($probonoseo_request, $probonoseo_query) {
		if (!$this->is_enabled()) {
			return $probonoseo_request;
		}

		return $probonoseo_request;
	}

	public function optimize_found_posts($probonoseo_sql, $probonoseo_query) {
		if (!$this->is_enabled()) {
			return $probonoseo_sql;
		}

		if ($probonoseo_query->get('no_found_rows')) {
			return '';
		}

		return $probonoseo_sql;
	}

	public function cleanup_database() {
		if (!$this->is_enabled()) {
			return false;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup operation
		$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup operation
		$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'");

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup operation
		$wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup operation
		$wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'trash'");

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup operation
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Optimize tables
		$wpdb->query("OPTIMIZE TABLE {$wpdb->posts}");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Optimize tables
		$wpdb->query("OPTIMIZE TABLE {$wpdb->postmeta}");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Optimize tables
		$wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Optimize tables
		$wpdb->query("OPTIMIZE TABLE {$wpdb->comments}");

		return true;
	}

	public function get_database_stats() {
		global $wpdb;

		$probonoseo_stats = array();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Stats query
		$probonoseo_stats['revisions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Stats query
		$probonoseo_stats['auto_drafts'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Stats query
		$probonoseo_stats['spam_comments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Stats query
		$probonoseo_stats['trash_comments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Stats query
		$probonoseo_stats['transients'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");

		return $probonoseo_stats;
	}
}

ProbonoSEO_Speed_DB::get_instance();