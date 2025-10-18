<?php
/**
 * Performance Monitor Module
 *
 * Monitors Core Web Vitals (FCP, LCP, TBT, SI, CLS)
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Performance_Monitor {

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

		// Register AJAX handlers for PageSpeed testing
		add_action( 'wp_ajax_wpwo_run_pagespeed_test', array( $this, 'ajax_run_pagespeed_test' ) );
		add_action( 'wp_ajax_wpwo_auto_fix', array( $this, 'ajax_auto_fix' ) );
		add_action( 'wp_ajax_wpwo_save_metric', array( $this, 'ajax_save_metric' ) );
		add_action( 'wp_ajax_nopriv_wpwo_save_metric', array( $this, 'ajax_save_metric' ) );
	}

	/**
	 * Register hooks for this module.
	 *
	 * @param WPWO_Loader $loader The loader instance.
	 */
	public function register_hooks( $loader ) {
		if ( ! empty( $this->options['monitor_enable'] ) ) {
			$loader->add_action( 'wp_footer', $this, 'add_performance_monitoring_script', 999 );
			$loader->add_action( 'wp_footer', $this, 'collect_frontend_metrics', 999 );
			$loader->add_action( 'wp_ajax_wpwo_track_performance', $this, 'track_performance_data' );
			$loader->add_action( 'wp_ajax_nopriv_wpwo_track_performance', $this, 'track_performance_data' );
		}
	}

	/**
	 * Add performance monitoring script.
	 */
	public function add_performance_monitoring_script() {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		?>
		<script>
		(function() {
			if (!window.PerformanceObserver) return;
			
			var wpwoPerf = {
				fcp: null,
				lcp: null,
				cls: null,
				fid: null
			};

			// First Contentful Paint (FCP)
			new PerformanceObserver(function(list) {
				var entries = list.getEntries();
				entries.forEach(function(entry) {
					if (entry.name === 'first-contentful-paint') {
						wpwoPerf.fcp = entry.startTime;
					}
				});
			}).observe({entryTypes: ['paint']});

			// Largest Contentful Paint (LCP)
			new PerformanceObserver(function(list) {
				var entries = list.getEntries();
				var lastEntry = entries[entries.length - 1];
				wpwoPerf.lcp = lastEntry.startTime;
			}).observe({entryTypes: ['largest-contentful-paint']});

			// Cumulative Layout Shift (CLS)
			var clsValue = 0;
			new PerformanceObserver(function(list) {
				list.getEntries().forEach(function(entry) {
					if (!entry.hadRecentInput) {
						clsValue += entry.value;
						wpwoPerf.cls = clsValue;
					}
				});
			}).observe({entryTypes: ['layout-shift']});

			// First Input Delay (FID)
			new PerformanceObserver(function(list) {
				var entries = list.getEntries();
				entries.forEach(function(entry) {
					wpwoPerf.fid = entry.processingStart - entry.startTime;
				});
			}).observe({entryTypes: ['first-input']});

			// Send data after page load
			window.addEventListener('load', function() {
				setTimeout(function() {
					if (wpwoPerf.fcp || wpwoPerf.lcp) {
						var xhr = new XMLHttpRequest();
						xhr.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', true);
						xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						xhr.send('action=wpwo_track_performance&data=' + encodeURIComponent(JSON.stringify(wpwoPerf)));
					}
				}, 3000);
			});
		})();
		</script>
		<?php
	}

	/**
	 * Track performance data via AJAX.
	 */
	public function track_performance_data() {
		if ( ! isset( $_POST['data'] ) ) {
			wp_die();
		}

		$data = json_decode( stripslashes( $_POST['data'] ), true );

		if ( ! $data ) {
			wp_die();
		}

		// Store data in transient (for demo purposes)
		$stored_data = get_transient( 'wpwo_performance_data' );
		if ( ! $stored_data ) {
			$stored_data = array();
		}

		$stored_data[] = array(
			'timestamp' => current_time( 'timestamp' ),
			'url'       => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'fcp'       => isset( $data['fcp'] ) ? floatval( $data['fcp'] ) : null,
			'lcp'       => isset( $data['lcp'] ) ? floatval( $data['lcp'] ) : null,
			'cls'       => isset( $data['cls'] ) ? floatval( $data['cls'] ) : null,
			'fid'       => isset( $data['fid'] ) ? floatval( $data['fid'] ) : null,
		);

		// Keep only last 100 entries
		if ( count( $stored_data ) > 100 ) {
			$stored_data = array_slice( $stored_data, -100 );
		}

		set_transient( 'wpwo_performance_data', $stored_data, WEEK_IN_SECONDS );

		wp_die();
	}

	/**
	 * Get performance statistics.
	 *
	 * @return array Performance stats.
	 */
	public static function get_performance_stats() {
		$data = get_transient( 'wpwo_performance_data' );

		if ( ! $data || empty( $data ) ) {
			return array(
				'fcp_avg' => 0,
				'lcp_avg' => 0,
				'cls_avg' => 0,
				'fid_avg' => 0,
				'count'   => 0,
			);
		}

		$fcp_values = array_filter( array_column( $data, 'fcp' ) );
		$lcp_values = array_filter( array_column( $data, 'lcp' ) );
		$cls_values = array_filter( array_column( $data, 'cls' ) );
		$fid_values = array_filter( array_column( $data, 'fid' ) );

		return array(
			'fcp_avg' => ! empty( $fcp_values ) ? array_sum( $fcp_values ) / count( $fcp_values ) : 0,
			'lcp_avg' => ! empty( $lcp_values ) ? array_sum( $lcp_values ) / count( $lcp_values ) : 0,
			'cls_avg' => ! empty( $cls_values ) ? array_sum( $cls_values ) / count( $cls_values ) : 0,
			'fid_avg' => ! empty( $fid_values ) ? array_sum( $fid_values ) / count( $fid_values ) : 0,
			'count'   => count( $data ),
		);
	}

	/**
	 * AJAX handler to run PageSpeed test.
	 */
	public function ajax_run_pagespeed_test() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		$url      = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : home_url();
		$strategy = isset( $_POST['strategy'] ) ? sanitize_text_field( $_POST['strategy'] ) : 'mobile';

		// Load required classes
		require_once WPWO_PLUGIN_DIR . 'includes/class-pagespeed-api.php';
		require_once WPWO_PLUGIN_DIR . 'includes/class-performance-analyzer.php';

		$pagespeed_api = new WPWO_PageSpeed_API();
		$results       = $pagespeed_api->run_test( $url, $strategy );

		if ( is_wp_error( $results ) ) {
			wp_send_json_error( array(
				'message' => $results->get_error_message(),
			) );
		}

		// Save test results
		$pagespeed_api->save_test_results( $results, $url, $strategy );

		// Analyze results and get recommendations
		$analyzer        = new WPWO_Performance_Analyzer();
		$recommendations = $analyzer->analyze_results( $results );

		// Add ratings to metrics
		foreach ( $results['metrics'] as $metric => $value ) {
			$results['metrics'][ $metric . '_rating' ] = $analyzer->get_metric_rating( $metric, $value );
		}

		wp_send_json_success( array(
			'results'         => $results,
			'recommendations' => $recommendations,
			'message'         => __( 'Test completed successfully!', 'wp-weboptimizer' ),
		) );
	}

	/**
	 * AJAX handler to auto-fix optimization issues.
	 */
	public function ajax_auto_fix() {
		check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-weboptimizer' ) ) );
		}

		$fix_id = isset( $_POST['fix_id'] ) ? sanitize_text_field( $_POST['fix_id'] ) : '';

		if ( empty( $fix_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid fix ID', 'wp-weboptimizer' ) ) );
		}

		// Load analyzer
		require_once WPWO_PLUGIN_DIR . 'includes/class-performance-analyzer.php';
		$analyzer = new WPWO_Performance_Analyzer();

		// Apply auto-fix
		$success = $analyzer->auto_fix( $fix_id );

		if ( $success ) {
			// Clear cache
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}

			wp_send_json_success( array(
				'message' => __( 'Đã áp dụng tối ưu thành công! Các thay đổi sẽ có hiệu lực ngay lập tức.', 'wp-weboptimizer' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Không thể áp dụng tối ưu này.', 'wp-weboptimizer' ),
			) );
		}
	}

	/**
	 * AJAX handler to save metric from frontend.
	 */
	public function ajax_save_metric() {
		// Simple metric saving - no nonce needed for frontend tracking
		if ( ! isset( $_POST['metric'] ) || ! isset( $_POST['value'] ) ) {
			wp_die();
		}

		$metric = sanitize_text_field( $_POST['metric'] );
		$value  = floatval( $_POST['value'] );

		// Store in transient
		$stored_data = get_transient( 'wpwo_performance_data' );
		if ( ! $stored_data ) {
			$stored_data = array();
		}

		// Create or update entry for current page
		$url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';

		$stored_data[] = array(
			'timestamp' => current_time( 'timestamp' ),
			'url'       => $url,
			$metric     => $value,
		);

		// Keep only last 100 entries
		if ( count( $stored_data ) > 100 ) {
			$stored_data = array_slice( $stored_data, -100 );
		}

		set_transient( 'wpwo_performance_data', $stored_data, WEEK_IN_SECONDS );

		wp_die();
	}

	/**
	 * Collect frontend metrics using Performance Observer API.
	 */
	public function collect_frontend_metrics() {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		?>
		<script>
		(function() {
			if (!window.PerformanceObserver) return;

			var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

			function sendMetric(metric, value) {
				if (navigator.sendBeacon) {
					var data = new URLSearchParams();
					data.append('action', 'wpwo_save_metric');
					data.append('metric', metric);
					data.append('value', value);
					navigator.sendBeacon(ajaxUrl, data);
				}
			}

			// FCP - First Contentful Paint
			new PerformanceObserver(function(list) {
				var entries = list.getEntries();
				entries.forEach(function(entry) {
					if (entry.name === 'first-contentful-paint') {
						sendMetric('fcp', entry.startTime);
					}
				});
			}).observe({type: 'paint', buffered: true});

			// LCP - Largest Contentful Paint
			new PerformanceObserver(function(list) {
				var entries = list.getEntries();
				var lastEntry = entries[entries.length - 1];
				sendMetric('lcp', lastEntry.renderTime || lastEntry.loadTime);
			}).observe({type: 'largest-contentful-paint', buffered: true});

			// CLS - Cumulative Layout Shift
			var clsValue = 0;
			var clsEntries = [];
			new PerformanceObserver(function(list) {
				for (var entry of list.getEntries()) {
					if (!entry.hadRecentInput) {
						clsValue += entry.value;
						clsEntries.push(entry);
					}
				}
			}).observe({type: 'layout-shift', buffered: true});

			// Send CLS after page is about to unload
			window.addEventListener('visibilitychange', function() {
				if (document.visibilityState === 'hidden') {
					sendMetric('cls', clsValue);
				}
			});

			// Fallback - send after 10 seconds
			setTimeout(function() {
				if (clsValue > 0) {
					sendMetric('cls', clsValue);
				}
			}, 10000);
		})();
		</script>
		<?php
	}

	/**
	 * Get average metrics from collected data.
	 *
	 * @return array Average metrics.
	 */
	public function get_average_metrics() {
		return self::get_performance_stats();
	}
}
