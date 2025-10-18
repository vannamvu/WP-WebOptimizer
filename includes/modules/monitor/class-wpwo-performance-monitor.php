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
	}

	/**
	 * Register hooks for this module.
	 *
	 * @param WPWO_Loader $loader The loader instance.
	 */
	public function register_hooks( $loader ) {
		if ( ! empty( $this->options['monitor_enable'] ) ) {
			$loader->add_action( 'wp_footer', $this, 'add_performance_monitoring_script', 999 );
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
}
