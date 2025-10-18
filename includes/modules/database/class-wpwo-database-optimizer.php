<?php
/**
 * Database Optimizer Module
 *
 * Handles database optimization and cleanup
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Database_Optimizer {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Initialize the module.
	 *
	 * @param array $options Plugin options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Register hooks for this module.
	 *
	 * @param WPWO_Loader $loader The loader instance.
	 */
	public function register_hooks( $loader ) {
		if ( ! empty( $this->options['database_auto_optimize'] ) ) {
			$loader->add_action( 'wp_scheduled_delete', $this, 'auto_optimize_database' );
		}
	}

	/**
	 * Optimize database tables.
	 *
	 * @return array Optimization results.
	 */
	public static function optimize_database() {
		global $wpdb;

		$results = array(
			'revisions'       => 0,
			'auto_drafts'     => 0,
			'trashed_posts'   => 0,
			'spam_comments'   => 0,
			'trashed_comments' => 0,
			'expired_transients' => 0,
			'tables_optimized' => 0,
		);

		// Delete post revisions (keep last 5)
		$results['revisions'] = $wpdb->query(
			"DELETE FROM $wpdb->posts WHERE post_type = 'revision' 
			AND ID NOT IN (
				SELECT * FROM (
					SELECT ID FROM $wpdb->posts 
					WHERE post_type = 'revision' 
					ORDER BY post_modified DESC 
					LIMIT 5
				) AS temp
			)"
		);

		// Delete auto-drafts older than 7 days
		$results['auto_drafts'] = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft' AND post_modified < %s",
				date( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		// Delete trashed posts older than 30 days
		$results['trashed_posts'] = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->posts WHERE post_status = 'trash' AND post_modified < %s",
				date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		// Delete spam comments
		$results['spam_comments'] = $wpdb->query(
			"DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'"
		);

		// Delete trashed comments
		$results['trashed_comments'] = $wpdb->query(
			"DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'"
		);

		// Delete expired transients
		$results['expired_transients'] = $wpdb->query(
			$wpdb->prepare(
				"DELETE a, b FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
				AND b.option_value < %d",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Optimize tables
		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
		foreach ( $tables as $table ) {
			$wpdb->query( "OPTIMIZE TABLE {$table[0]}" );
			$results['tables_optimized']++;
		}

		return $results;
	}

	/**
	 * Auto-optimize database on schedule.
	 */
	public function auto_optimize_database() {
		self::optimize_database();
	}

	/**
	 * Get database size information.
	 *
	 * @return array Database size info.
	 */
	public static function get_database_info() {
		global $wpdb;

		$size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) 
				FROM information_schema.TABLES 
				WHERE table_schema = %s",
				DB_NAME
			)
		);

		$post_count = wp_count_posts();
		$comment_count = wp_count_comments();

		return array(
			'size'            => size_format( $size ),
			'posts'           => $post_count->publish,
			'comments'        => $comment_count->approved,
			'revisions'       => $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'" ),
			'auto_drafts'     => $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft'" ),
			'trashed_posts'   => $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash'" ),
			'spam_comments'   => $comment_count->spam,
			'trashed_comments' => $comment_count->trash,
		);
	}
}
