<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Initialize the class.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 * @param array  $options     The plugin options.
	 */
	public function __construct( $plugin_name, $version, $options ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->options     = $options;

		// Register additional AJAX actions
		add_action( 'wp_ajax_wpwo_clear_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_wpwo_optimize_database', array( $this, 'ajax_optimize_database' ) );
		add_action( 'wp_ajax_wpwo_apply_performance_mode', array( $this, 'ajax_apply_performance_mode' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wp-weboptimizer' ) {
			wp_enqueue_style( $this->plugin_name, WPWO_PLUGIN_URL . 'admin/css/wpwo-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wp-weboptimizer' ) {
			wp_enqueue_script( $this->plugin_name, WPWO_PLUGIN_URL . 'admin/js/wpwo-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'wpwoAjax',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'wpwo_ajax_nonce' ),
				)
			);
		}
	}

	/**
	 * Add options page.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'WP WebOptimizer', 'wp-weboptimizer' ),
			__( 'WP WebOptimizer', 'wp-weboptimizer' ),
			'manage_options',
			'wp-weboptimizer',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-performance',
			65
		);
	}

	/**
	 * Render the settings page.
	 */
	public function display_plugin_admin_page() {
		require_once WPWO_PLUGIN_DIR . 'admin/partials/wpwo-admin-display.php';
	}

	/**
	 * Handle AJAX save options.
	 */
	public function ajax_save_options() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		$options = isset( $_POST['options'] ) ? $_POST['options'] : array();

		// Sanitize options
		$sanitized_options = $this->sanitize_options( $options );

		// Update options
		update_option( 'wpwo_options', $sanitized_options );

		// Clear cache
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'wp-weboptimizer' ) ) );
	}

	/**
	 * Sanitize plugin options.
	 *
	 * @param array $options Raw options array.
	 * @return array Sanitized options.
	 */
	private function sanitize_options( $options ) {
		$sanitized = array();

		// Boolean options
		$boolean_fields = array(
			'assets_minify_css',
			'assets_minify_js',
			'assets_combine_css',
			'assets_combine_js',
			'assets_defer_js',
			'assets_defer_css',
			'lazyload_images',
			'lazyload_iframes',
			'lazyload_videos',
			'font_display_swap',
			'font_preload',
			'image_webp_conversion',
			'image_lazy_load',
			'cache_enable',
			'cache_gzip',
			'hints_preconnect',
			'hints_prefetch',
			'hints_dns_prefetch',
			'scripts_optimize',
			'scripts_defer_third_party',
			'database_optimize',
			'database_auto_optimize',
			'monitor_enable',
			'monitor_track_fcp',
			'monitor_track_lcp',
		);

		foreach ( $boolean_fields as $field ) {
			$sanitized[ $field ] = isset( $options[ $field ] ) && $options[ $field ] === 'true';
		}

		// Text fields
		if ( isset( $options['performance_mode'] ) ) {
			$sanitized['performance_mode'] = sanitize_text_field( $options['performance_mode'] );
		}

		if ( isset( $options['cache_lifetime'] ) ) {
			$sanitized['cache_lifetime'] = absint( $options['cache_lifetime'] );
		}

		if ( isset( $options['excluded_css'] ) ) {
			$sanitized['excluded_css'] = sanitize_textarea_field( $options['excluded_css'] );
		}

		if ( isset( $options['excluded_js'] ) ) {
			$sanitized['excluded_js'] = sanitize_textarea_field( $options['excluded_js'] );
		}

		if ( isset( $options['preconnect_urls'] ) ) {
			$sanitized['preconnect_urls'] = sanitize_textarea_field( $options['preconnect_urls'] );
		}

		return $sanitized;
	}

	/**
	 * Check for activation redirect.
	 */
	public function check_activation_redirect() {
		if ( get_transient( 'wpwo_activation_redirect' ) ) {
			delete_transient( 'wpwo_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wp-weboptimizer' ) );
				exit;
			}
		}
	}

	/**
	 * Handle AJAX clear cache.
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		WPWO_Cache_Manager::clear_all_cache();

		wp_send_json_success( array( 'message' => __( 'Cache cleared successfully!', 'wp-weboptimizer' ) ) );
	}

	/**
	 * Handle AJAX optimize database.
	 */
	public function ajax_optimize_database() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		$results = WPWO_Database_Optimizer::optimize_database();

		wp_send_json_success( array(
			'message' => __( 'Database optimized successfully!', 'wp-weboptimizer' ),
			'results' => $results,
		) );
	}

	/**
	 * Handle AJAX apply performance mode.
	 */
	public function ajax_apply_performance_mode() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'balanced';

		$updated_options = WPWO_Advanced_Settings::apply_performance_mode( $mode );

		// Clear cache
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		wp_send_json_success( array(
			'message' => __( 'Performance mode applied successfully!', 'wp-weboptimizer' ),
			'options' => $updated_options,
		) );
	}
}
