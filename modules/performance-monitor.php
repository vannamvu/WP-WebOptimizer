<?php
/**
 * Performance Monitor Module
 * 
 * Monitoring Core Web Vitals, page load time, performance metrics
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Performance Monitor
 */
class WP_WebOptimizer_Performance_Monitor {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo hooks
     */
    private function init_hooks() {
        // Inject monitoring script
        if ( WP_WebOptimizer::get_option( 'performance_monitor.enabled', false ) ) {
            add_action( 'wp_footer', array( $this, 'inject_monitoring_script' ), 999 );
        }
        
        // AJAX handlers
        add_action( 'wp_ajax_wpwo_save_metrics', array( $this, 'ajax_save_metrics' ) );
        add_action( 'wp_ajax_nopriv_wpwo_save_metrics', array( $this, 'ajax_save_metrics' ) );
        add_action( 'wp_ajax_wpwo_get_metrics', array( $this, 'ajax_get_metrics' ) );
    }
    
    /**
     * Inject monitoring script vào footer
     */
    public function inject_monitoring_script() {
        // Không monitor trong admin
        if ( is_admin() ) {
            return;
        }
        
        ?>
        <script>
        // Performance Monitoring Script
        (function() {
            // Kiểm tra Web Vitals API support
            if (!('PerformanceObserver' in window)) return;
            
            var metrics = {
                url: window.location.href,
                timestamp: Date.now()
            };
            
            // Monitor FCP (First Contentful Paint)
            try {
                new PerformanceObserver(function(list) {
                    var entries = list.getEntries();
                    entries.forEach(function(entry) {
                        if (entry.name === 'first-contentful-paint') {
                            metrics.fcp = Math.round(entry.startTime);
                        }
                    });
                }).observe({entryTypes: ['paint']});
            } catch(e) {}
            
            // Monitor LCP (Largest Contentful Paint)
            try {
                new PerformanceObserver(function(list) {
                    var entries = list.getEntries();
                    var lastEntry = entries[entries.length - 1];
                    metrics.lcp = Math.round(lastEntry.renderTime || lastEntry.loadTime);
                }).observe({entryTypes: ['largest-contentful-paint']});
            } catch(e) {}
            
            // Monitor FID (First Input Delay)
            try {
                new PerformanceObserver(function(list) {
                    var entries = list.getEntries();
                    entries.forEach(function(entry) {
                        metrics.fid = Math.round(entry.processingStart - entry.startTime);
                    });
                }).observe({entryTypes: ['first-input']});
            } catch(e) {}
            
            // Monitor CLS (Cumulative Layout Shift)
            try {
                var clsValue = 0;
                new PerformanceObserver(function(list) {
                    var entries = list.getEntries();
                    entries.forEach(function(entry) {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                        }
                    });
                    metrics.cls = Math.round(clsValue * 1000) / 1000;
                }).observe({entryTypes: ['layout-shift']});
            } catch(e) {}
            
            // Monitor TTFB (Time to First Byte)
            try {
                var navTiming = performance.getEntriesByType('navigation')[0];
                if (navTiming) {
                    metrics.ttfb = Math.round(navTiming.responseStart - navTiming.requestStart);
                }
            } catch(e) {}
            
            // Send metrics sau 10 giây
            setTimeout(function() {
                // Chỉ send nếu có ít nhất 1 metric
                if (Object.keys(metrics).length > 2) {
                    sendMetrics(metrics);
                }
            }, 10000);
            
            function sendMetrics(data) {
                if (!navigator.sendBeacon) return;
                
                var formData = new FormData();
                formData.append('action', 'wpwo_save_metrics');
                formData.append('metrics', JSON.stringify(data));
                
                navigator.sendBeacon(
                    '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                    formData
                );
            }
        })();
        </script>
        <?php
    }
    
    /**
     * AJAX: Save metrics
     */
    public function ajax_save_metrics() {
        // Không cần nonce check vì đây là anonymous tracking
        
        $metrics_json = isset( $_POST['metrics'] ) ? wp_unslash( $_POST['metrics'] ) : '';
        
        if ( empty( $metrics_json ) ) {
            wp_die();
        }
        
        $metrics = json_decode( $metrics_json, true );
        
        if ( ! is_array( $metrics ) ) {
            wp_die();
        }
        
        // Save vào transient hoặc custom table
        $this->save_metrics( $metrics );
        
        wp_die();
    }
    
    /**
     * Save metrics vào database
     * 
     * @param array $metrics Metrics data
     */
    private function save_metrics( $metrics ) {
        // Lấy metrics history
        $history = get_option( 'wp_weboptimizer_metrics_history', array() );
        
        // Giới hạn 100 records gần nhất
        if ( count( $history ) >= 100 ) {
            array_shift( $history );
        }
        
        // Thêm metrics mới
        $history[] = array(
            'timestamp' => time(),
            'url' => sanitize_url( $metrics['url'] ?? '' ),
            'fcp' => absint( $metrics['fcp'] ?? 0 ),
            'lcp' => absint( $metrics['lcp'] ?? 0 ),
            'fid' => absint( $metrics['fid'] ?? 0 ),
            'cls' => floatval( $metrics['cls'] ?? 0 ),
            'ttfb' => absint( $metrics['ttfb'] ?? 0 ),
        );
        
        update_option( 'wp_weboptimizer_metrics_history', $history, false );
    }
    
    /**
     * AJAX: Get metrics
     */
    public function ajax_get_metrics() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $history = get_option( 'wp_weboptimizer_metrics_history', array() );
        
        // Calculate averages
        $totals = array(
            'fcp' => 0,
            'lcp' => 0,
            'fid' => 0,
            'cls' => 0,
            'ttfb' => 0,
            'count' => 0,
        );
        
        foreach ( $history as $record ) {
            $totals['fcp'] += $record['fcp'];
            $totals['lcp'] += $record['lcp'];
            $totals['fid'] += $record['fid'];
            $totals['cls'] += $record['cls'];
            $totals['ttfb'] += $record['ttfb'];
            $totals['count']++;
        }
        
        $averages = array();
        if ( $totals['count'] > 0 ) {
            $averages = array(
                'fcp' => round( $totals['fcp'] / $totals['count'] ),
                'lcp' => round( $totals['lcp'] / $totals['count'] ),
                'fid' => round( $totals['fid'] / $totals['count'] ),
                'cls' => round( $totals['cls'] / $totals['count'], 3 ),
                'ttfb' => round( $totals['ttfb'] / $totals['count'] ),
            );
        }
        
        wp_send_json_success( array(
            'history' => array_slice( $history, -20 ), // 20 records gần nhất
            'averages' => $averages,
            'total_records' => $totals['count'],
        ) );
    }
    
    /**
     * Get performance score
     * 
     * @return array Performance score
     */
    public function get_performance_score() {
        $history = get_option( 'wp_weboptimizer_metrics_history', array() );
        
        if ( empty( $history ) ) {
            return array(
                'score' => 0,
                'grade' => 'N/A',
            );
        }
        
        // Lấy 10 records gần nhất
        $recent = array_slice( $history, -10 );
        
        $scores = array();
        
        foreach ( $recent as $record ) {
            $score = 0;
            
            // FCP score (0-25 points)
            if ( $record['fcp'] < 1800 ) {
                $score += 25;
            } elseif ( $record['fcp'] < 3000 ) {
                $score += 15;
            }
            
            // LCP score (0-25 points)
            if ( $record['lcp'] < 2500 ) {
                $score += 25;
            } elseif ( $record['lcp'] < 4000 ) {
                $score += 15;
            }
            
            // FID score (0-25 points)
            if ( $record['fid'] < 100 ) {
                $score += 25;
            } elseif ( $record['fid'] < 300 ) {
                $score += 15;
            }
            
            // CLS score (0-25 points)
            if ( $record['cls'] < 0.1 ) {
                $score += 25;
            } elseif ( $record['cls'] < 0.25 ) {
                $score += 15;
            }
            
            $scores[] = $score;
        }
        
        $avg_score = array_sum( $scores ) / count( $scores );
        
        // Determine grade
        $grade = 'F';
        if ( $avg_score >= 90 ) {
            $grade = 'A';
        } elseif ( $avg_score >= 80 ) {
            $grade = 'B';
        } elseif ( $avg_score >= 70 ) {
            $grade = 'C';
        } elseif ( $avg_score >= 60 ) {
            $grade = 'D';
        }
        
        return array(
            'score' => round( $avg_score ),
            'grade' => $grade,
        );
    }
}

// Khởi tạo module
new WP_WebOptimizer_Performance_Monitor();
