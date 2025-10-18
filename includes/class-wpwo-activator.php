<?php
/**
 * Fired during plugin activation
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Activator {

	/**
	 * Plugin activation handler.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Set default options
		$default_options = array(
			'assets_minify_css'           => true,
			'assets_minify_js'            => true,
			'assets_defer_js'             => true,
			'lazyload_images'             => true,
			'lazyload_iframes'            => true,
			'font_display_swap'           => true,
			'image_webp_conversion'       => true,
			'cache_enable'                => false,
			'hints_preconnect'            => true,
			'scripts_optimize'            => true,
			'database_optimize'           => false,
			'monitor_enable'              => true,
			'performance_mode'            => 'balanced',
		);

		// Add default options if they don't exist
		if ( ! get_option( 'wpwo_options' ) ) {
			add_option( 'wpwo_options', $default_options );
		}

		// Clear any existing cache
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		// Set activation flag
		set_transient( 'wpwo_activation_redirect', true, 30 );
	}
}
