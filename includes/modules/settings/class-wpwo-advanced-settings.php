<?php
/**
 * Advanced Settings Module
 *
 * Handles advanced configuration options
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Advanced_Settings {

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
	 * Get available performance modes.
	 *
	 * @return array Performance modes.
	 */
	public static function get_performance_modes() {
		return array(
			'off'       => array(
				'label'       => __( 'Tắt', 'wp-weboptimizer' ),
				'description' => __( 'Tắt tất cả tối ưu', 'wp-weboptimizer' ),
			),
			'safe'      => array(
				'label'       => __( 'An toàn', 'wp-weboptimizer' ),
				'description' => __( 'Chỉ bật tối ưu cơ bản, an toàn nhất', 'wp-weboptimizer' ),
			),
			'balanced'  => array(
				'label'       => __( 'Cân bằng', 'wp-weboptimizer' ),
				'description' => __( 'Cân bằng giữa hiệu suất và khả năng tương thích', 'wp-weboptimizer' ),
			),
			'aggressive' => array(
				'label'       => __( 'Tích cực', 'wp-weboptimizer' ),
				'description' => __( 'Bật tất cả tối ưu, hiệu suất cao nhất', 'wp-weboptimizer' ),
			),
		);
	}

	/**
	 * Apply performance mode.
	 *
	 * @param string $mode Performance mode.
	 * @return array Updated options.
	 */
	public static function apply_performance_mode( $mode ) {
		$options = get_option( 'wpwo_options', array() );

		switch ( $mode ) {
			case 'off':
				// Disable all optimizations
				$options['assets_minify_css']        = false;
				$options['assets_minify_js']         = false;
				$options['assets_defer_js']          = false;
				$options['lazyload_images']          = false;
				$options['lazyload_iframes']         = false;
				$options['font_display_swap']        = false;
				$options['image_webp_conversion']    = false;
				$options['cache_enable']             = false;
				$options['hints_preconnect']         = false;
				$options['scripts_optimize']         = false;
				$options['database_optimize']        = false;
				break;

			case 'safe':
				// Enable only safe optimizations
				$options['assets_minify_css']        = false;
				$options['assets_minify_js']         = false;
				$options['assets_defer_js']          = false;
				$options['lazyload_images']          = true;
				$options['lazyload_iframes']         = true;
				$options['font_display_swap']        = true;
				$options['image_webp_conversion']    = false;
				$options['cache_enable']             = false;
				$options['hints_preconnect']         = true;
				$options['scripts_optimize']         = false;
				$options['database_optimize']        = false;
				break;

			case 'balanced':
				// Enable balanced optimizations
				$options['assets_minify_css']        = true;
				$options['assets_minify_js']         = true;
				$options['assets_defer_js']          = true;
				$options['lazyload_images']          = true;
				$options['lazyload_iframes']         = true;
				$options['font_display_swap']        = true;
				$options['image_webp_conversion']    = true;
				$options['cache_enable']             = false;
				$options['hints_preconnect']         = true;
				$options['scripts_optimize']         = true;
				$options['database_optimize']        = false;
				break;

			case 'aggressive':
				// Enable all optimizations
				$options['assets_minify_css']        = true;
				$options['assets_minify_js']         = true;
				$options['assets_defer_js']          = true;
				$options['assets_defer_css']         = true;
				$options['lazyload_images']          = true;
				$options['lazyload_iframes']         = true;
				$options['lazyload_videos']          = true;
				$options['font_display_swap']        = true;
				$options['font_preload']             = true;
				$options['image_webp_conversion']    = true;
				$options['cache_enable']             = true;
				$options['cache_gzip']               = true;
				$options['hints_preconnect']         = true;
				$options['hints_dns_prefetch']       = true;
				$options['hints_prefetch']           = true;
				$options['scripts_optimize']         = true;
				$options['scripts_defer_third_party'] = true;
				$options['database_optimize']        = true;
				$options['database_auto_optimize']   = true;
				break;
		}

		$options['performance_mode'] = $mode;
		update_option( 'wpwo_options', $options );

		return $options;
	}
}
