<?php
/**
 * Fired during plugin deactivation
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Deactivator {

	/**
	 * Plugin deactivation handler.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear cache on deactivation
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		// Clear transients
		delete_transient( 'wpwo_activation_redirect' );
		delete_transient( 'wpwo_performance_data' );
	}
}
