<?php
/**
 * Cache Manager Module
 *
 * Handles page caching and cache management
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Cache_Manager {

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
		if ( ! empty( $this->options['cache_enable'] ) ) {
			$loader->add_action( 'init', $this, 'start_buffer', 0 );
			$loader->add_action( 'shutdown', $this, 'end_buffer', 999 );
			$loader->add_action( 'save_post', $this, 'clear_post_cache', 10, 1 );
			$loader->add_action( 'comment_post', $this, 'clear_post_cache_by_comment', 10, 1 );
		}

		if ( ! empty( $this->options['cache_gzip'] ) ) {
			$loader->add_action( 'init', $this, 'enable_gzip_compression', 1 );
		}
	}

	/**
	 * Start output buffering.
	 */
	public function start_buffer() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		ob_start( array( $this, 'process_buffer' ) );
	}

	/**
	 * End output buffering.
	 */
	public function end_buffer() {
		if ( ob_get_level() > 0 ) {
			ob_end_flush();
		}
	}

	/**
	 * Process the output buffer.
	 *
	 * @param string $buffer The buffer content.
	 * @return string Processed buffer.
	 */
	public function process_buffer( $buffer ) {
		if ( is_admin() || empty( $buffer ) ) {
			return $buffer;
		}

		// Skip caching for logged-in users
		if ( is_user_logged_in() ) {
			return $buffer;
		}

		// Get cache key
		$cache_key = $this->get_cache_key();

		// Save to cache
		set_transient( $cache_key, $buffer, $this->get_cache_lifetime() );

		return $buffer;
	}

	/**
	 * Get cache key for current request.
	 *
	 * @return string Cache key.
	 */
	private function get_cache_key() {
		$protocol = is_ssl() ? 'https' : 'http';
		$url      = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		return 'wpwo_cache_' . md5( $url );
	}

	/**
	 * Get cache lifetime in seconds.
	 *
	 * @return int Cache lifetime.
	 */
	private function get_cache_lifetime() {
		$lifetime = ! empty( $this->options['cache_lifetime'] ) ? absint( $this->options['cache_lifetime'] ) : 3600;
		return $lifetime;
	}

	/**
	 * Clear cache for a specific post.
	 *
	 * @param int $post_id Post ID.
	 */
	public function clear_post_cache( $post_id ) {
		$post_url = get_permalink( $post_id );
		if ( $post_url ) {
			$cache_key = 'wpwo_cache_' . md5( $post_url );
			delete_transient( $cache_key );
		}

		// Clear home page cache
		delete_transient( 'wpwo_cache_' . md5( home_url() ) );
	}

	/**
	 * Clear cache for a post by comment.
	 *
	 * @param int $comment_id Comment ID.
	 */
	public function clear_post_cache_by_comment( $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( $comment ) {
			$this->clear_post_cache( $comment->comment_post_ID );
		}
	}

	/**
	 * Enable GZIP compression.
	 */
	public function enable_gzip_compression() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		if ( ! headers_sent() && extension_loaded( 'zlib' ) && ! ini_get( 'zlib.output_compression' ) ) {
			if ( ! ob_start( 'ob_gzhandler' ) ) {
				ob_start();
			}
		}
	}

	/**
	 * Clear all cache.
	 */
	public static function clear_all_cache() {
		global $wpdb;

		// Delete all transients starting with wpwo_cache_
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpwo_cache_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpwo_cache_%'" );

		// Clear WordPress cache
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}
