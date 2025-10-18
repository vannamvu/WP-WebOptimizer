<?php
/**
 * Performance Analyzer
 *
 * Analyzes PageSpeed results and provides recommendations with auto-fix capabilities
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Performance_Analyzer {

	/**
	 * Performance thresholds for Core Web Vitals.
	 *
	 * @var array
	 */
	private $thresholds = array(
		'lcp'         => array( 'good' => 2500, 'poor' => 4000 ),
		'fcp'         => array( 'good' => 1800, 'poor' => 3000 ),
		'cls'         => array( 'good' => 0.1, 'poor' => 0.25 ),
		'tbt'         => array( 'good' => 200, 'poor' => 600 ),
		'speed_index' => array( 'good' => 3400, 'poor' => 5800 ),
	);

	/**
	 * Analyze PageSpeed test results and generate recommendations.
	 *
	 * @param array $results PageSpeed test results.
	 * @return array Recommendations with auto-fix options.
	 */
	public function analyze_results( $results ) {
		$recommendations = array();

		// Analyze metrics
		if ( isset( $results['metrics'] ) ) {
			$metrics = $results['metrics'];

			// Check LCP
			if ( isset( $metrics['lcp'] ) && $metrics['lcp'] > $this->thresholds['lcp']['good'] ) {
				$severity = $metrics['lcp'] > $this->thresholds['lcp']['poor'] ? 'high' : 'medium';
				$recommendations[] = array(
					'severity'    => $severity,
					'issue'       => __( 'LCP (Largest Contentful Paint) quá chậm', 'wp-weboptimizer' ),
					'description' => sprintf(
						__( 'LCP hiện tại: %.1fs. Mục tiêu: < 2.5s', 'wp-weboptimizer' ),
						$metrics['lcp'] / 1000
					),
					'solutions'   => array(
						__( 'Bật lazy load cho hình ảnh', 'wp-weboptimizer' ),
						__( 'Chuyển đổi hình ảnh sang WebP', 'wp-weboptimizer' ),
						__( 'Preload hình ảnh LCP', 'wp-weboptimizer' ),
						__( 'Tối ưu CSS render-blocking', 'wp-weboptimizer' ),
					),
					'auto_fix'    => 'enable_image_optimization',
				);
			}

			// Check FCP
			if ( isset( $metrics['fcp'] ) && $metrics['fcp'] > $this->thresholds['fcp']['good'] ) {
				$severity = $metrics['fcp'] > $this->thresholds['fcp']['poor'] ? 'high' : 'medium';
				$recommendations[] = array(
					'severity'    => $severity,
					'issue'       => __( 'FCP (First Contentful Paint) cần cải thiện', 'wp-weboptimizer' ),
					'description' => sprintf(
						__( 'FCP hiện tại: %.1fs. Mục tiêu: < 1.8s', 'wp-weboptimizer' ),
						$metrics['fcp'] / 1000
					),
					'solutions'   => array(
						__( 'Defer JavaScript', 'wp-weboptimizer' ),
						__( 'Minify CSS và JS', 'wp-weboptimizer' ),
						__( 'Preconnect đến external domains', 'wp-weboptimizer' ),
					),
					'auto_fix'    => 'enable_assets_optimization',
				);
			}

			// Check CLS
			if ( isset( $metrics['cls'] ) && $metrics['cls'] > $this->thresholds['cls']['good'] ) {
				$severity = $metrics['cls'] > $this->thresholds['cls']['poor'] ? 'high' : 'medium';
				$recommendations[] = array(
					'severity'    => $severity,
					'issue'       => __( 'CLS (Cumulative Layout Shift) cao', 'wp-weboptimizer' ),
					'description' => sprintf(
						__( 'CLS hiện tại: %.3f. Mục tiêu: < 0.1', 'wp-weboptimizer' ),
						$metrics['cls']
					),
					'solutions'   => array(
						__( 'Thêm width/height cho hình ảnh', 'wp-weboptimizer' ),
						__( 'Preload font chữ', 'wp-weboptimizer' ),
						__( 'Tránh chèn content động phía trên', 'wp-weboptimizer' ),
					),
					'auto_fix'    => 'fix_layout_shift',
				);
			}

			// Check Speed Index
			if ( isset( $metrics['speed_index'] ) && $metrics['speed_index'] > $this->thresholds['speed_index']['good'] ) {
				$severity = $metrics['speed_index'] > $this->thresholds['speed_index']['poor'] ? 'high' : 'medium';
				$recommendations[] = array(
					'severity'    => $severity,
					'issue'       => __( 'Speed Index cần được cải thiện', 'wp-weboptimizer' ),
					'description' => sprintf(
						__( 'Speed Index hiện tại: %.1fs. Mục tiêu: < 3.4s', 'wp-weboptimizer' ),
						$metrics['speed_index'] / 1000
					),
					'solutions'   => array(
						__( 'Bật page caching', 'wp-weboptimizer' ),
						__( 'Tối ưu critical CSS', 'wp-weboptimizer' ),
						__( 'Giảm kích thước trang', 'wp-weboptimizer' ),
					),
					'auto_fix'    => 'enable_cache',
				);
			}

			// Check TBT
			if ( isset( $metrics['tbt'] ) && $metrics['tbt'] > $this->thresholds['tbt']['good'] ) {
				$severity = $metrics['tbt'] > $this->thresholds['tbt']['poor'] ? 'high' : 'medium';
				$recommendations[] = array(
					'severity'    => $severity,
					'issue'       => __( 'TBT (Total Blocking Time) cao', 'wp-weboptimizer' ),
					'description' => sprintf(
						__( 'TBT hiện tại: %dms. Mục tiêu: < 200ms', 'wp-weboptimizer' ),
						$metrics['tbt']
					),
					'solutions'   => array(
						__( 'Defer JavaScript loading', 'wp-weboptimizer' ),
						__( 'Tối ưu third-party scripts', 'wp-weboptimizer' ),
						__( 'Code splitting', 'wp-weboptimizer' ),
					),
					'auto_fix'    => 'enable_defer_js',
				);
			}
		}

		// Analyze opportunities
		if ( isset( $results['opportunities'] ) ) {
			foreach ( $results['opportunities'] as $opportunity ) {
				$mapped = $this->map_opportunity_to_recommendation( $opportunity );
				if ( $mapped ) {
					$recommendations[] = $mapped;
				}
			}
		}

		// Sort by severity
		usort( $recommendations, function( $a, $b ) {
			$severity_order = array( 'high' => 0, 'medium' => 1, 'low' => 2 );
			return $severity_order[ $a['severity'] ] - $severity_order[ $b['severity'] ];
		});

		return $recommendations;
	}

	/**
	 * Map PageSpeed opportunity to recommendation.
	 *
	 * @param array $opportunity PageSpeed opportunity data.
	 * @return array|null Recommendation or null.
	 */
	private function map_opportunity_to_recommendation( $opportunity ) {
		$mapping = array(
			'render-blocking-resources'      => array(
				'issue'    => __( 'Tài nguyên chặn render', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_defer_js',
			),
			'unused-css-rules'               => array(
				'issue'    => __( 'CSS không sử dụng', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_assets_optimization',
			),
			'unused-javascript'              => array(
				'issue'    => __( 'JavaScript không sử dụng', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_assets_optimization',
			),
			'unminified-css'                 => array(
				'issue'    => __( 'CSS chưa minify', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_assets_optimization',
			),
			'unminified-javascript'          => array(
				'issue'    => __( 'JavaScript chưa minify', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_assets_optimization',
			),
			'offscreen-images'               => array(
				'issue'    => __( 'Hình ảnh ngoài viewport', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_lazy_load',
			),
			'uses-webp-images'               => array(
				'issue'    => __( 'Chưa sử dụng định dạng WebP', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_webp_conversion',
			),
			'uses-optimized-images'          => array(
				'issue'    => __( 'Hình ảnh chưa được tối ưu', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_image_optimization',
			),
			'modern-image-formats'           => array(
				'issue'    => __( 'Cần sử dụng định dạng ảnh hiện đại', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_webp_conversion',
			),
			'uses-text-compression'          => array(
				'issue'    => __( 'Chưa bật nén text', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_gzip',
			),
			'uses-responsive-images'         => array(
				'issue'    => __( 'Hình ảnh chưa responsive', 'wp-weboptimizer' ),
				'auto_fix' => 'enable_image_optimization',
			),
		);

		if ( ! isset( $mapping[ $opportunity['id'] ] ) ) {
			return null;
		}

		$map = $mapping[ $opportunity['id'] ];
		$savings = isset( $opportunity['savings'] ) ? $opportunity['savings'] : 0;

		// Determine severity based on savings
		$severity = 'low';
		if ( $savings > 1000 ) {
			$severity = 'high';
		} elseif ( $savings > 500 ) {
			$severity = 'medium';
		}

		return array(
			'severity'    => $severity,
			'issue'       => $map['issue'],
			'description' => isset( $opportunity['title'] ) ? $opportunity['title'] : '',
			'solutions'   => array( isset( $opportunity['description'] ) ? wp_strip_all_tags( $opportunity['description'] ) : '' ),
			'auto_fix'    => $map['auto_fix'],
			'savings'     => $savings,
		);
	}

	/**
	 * Auto-fix optimization issue.
	 *
	 * @param string $fix_id Fix identifier.
	 * @return bool Success status.
	 */
	public function auto_fix( $fix_id ) {
		$options = get_option( 'wpwo_options', array() );

		switch ( $fix_id ) {
			case 'enable_image_optimization':
				$options['lazyload_images']       = true;
				$options['image_webp_conversion'] = true;
				break;

			case 'fix_layout_shift':
				$options['font_preload']     = true;
				$options['font_display_swap'] = true;
				break;

			case 'enable_assets_optimization':
				$options['assets_minify_css'] = true;
				$options['assets_minify_js']  = true;
				break;

			case 'enable_defer_js':
				$options['assets_defer_js'] = true;
				$options['scripts_defer_third_party'] = true;
				break;

			case 'enable_lazy_load':
				$options['lazyload_images']  = true;
				$options['lazyload_iframes'] = true;
				break;

			case 'enable_webp_conversion':
				$options['image_webp_conversion'] = true;
				break;

			case 'enable_cache':
				$options['cache_enable'] = true;
				$options['cache_gzip']   = true;
				break;

			case 'enable_gzip':
				$options['cache_gzip'] = true;
				break;

			default:
				return false;
		}

		return update_option( 'wpwo_options', $options );
	}

	/**
	 * Get performance rating for a metric value.
	 *
	 * @param string $metric Metric name.
	 * @param mixed  $value  Metric value.
	 * @return string Rating: 'good', 'needs-improvement', or 'poor'.
	 */
	public function get_metric_rating( $metric, $value ) {
		if ( ! isset( $this->thresholds[ $metric ] ) ) {
			return 'good';
		}

		$threshold = $this->thresholds[ $metric ];

		if ( $value <= $threshold['good'] ) {
			return 'good';
		} elseif ( $value <= $threshold['poor'] ) {
			return 'needs-improvement';
		} else {
			return 'poor';
		}
	}

	/**
	 * Get severity badge HTML.
	 *
	 * @param string $severity Severity level.
	 * @return string Badge HTML.
	 */
	public function get_severity_badge( $severity ) {
		$labels = array(
			'high'   => __( 'Cao', 'wp-weboptimizer' ),
			'medium' => __( 'Trung bình', 'wp-weboptimizer' ),
			'low'    => __( 'Thấp', 'wp-weboptimizer' ),
		);

		$label = isset( $labels[ $severity ] ) ? $labels[ $severity ] : $severity;

		return sprintf(
			'<span class="wpwo-severity-badge wpwo-severity-%s">%s</span>',
			esc_attr( $severity ),
			esc_html( $label )
		);
	}
}
