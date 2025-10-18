<?php
/**
 * Database Optimizer Module
 * 
 * Tối ưu database: optimize tables, clean data, transient cleaner
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Database Optimizer
 */
class WP_WebOptimizer_Database_Optimizer {
    
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
        // Auto optimization schedule
        if ( WP_WebOptimizer::get_option( 'database_optimizer.auto_optimize', false ) ) {
            add_action( 'wp_weboptimizer_daily_cleanup', array( $this, 'run_auto_optimization' ) );
            
            if ( ! wp_next_scheduled( 'wp_weboptimizer_daily_cleanup' ) ) {
                wp_schedule_event( time(), 'daily', 'wp_weboptimizer_daily_cleanup' );
            }
        }
        
        // AJAX handlers
        add_action( 'wp_ajax_wpwo_optimize_tables', array( $this, 'ajax_optimize_tables' ) );
        add_action( 'wp_ajax_wpwo_clean_transients', array( $this, 'ajax_clean_transients' ) );
        add_action( 'wp_ajax_wpwo_clean_revisions', array( $this, 'ajax_clean_revisions' ) );
        add_action( 'wp_ajax_wpwo_clean_spam', array( $this, 'ajax_clean_spam' ) );
    }
    
    /**
     * Run auto optimization
     */
    public function run_auto_optimization() {
        $this->optimize_database_tables();
        $this->clean_expired_transients();
        $this->clean_post_revisions();
        $this->clean_spam_comments();
    }
    
    /**
     * Optimize database tables
     * 
     * @return array Kết quả optimization
     */
    public function optimize_database_tables() {
        global $wpdb;
        
        $results = array();
        
        // Lấy tất cả tables
        $tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
        
        foreach ( $tables as $table ) {
            $table_name = $table[0];
            
            // Optimize table
            $result = $wpdb->query( "OPTIMIZE TABLE `{$table_name}`" );
            
            $results[ $table_name ] = array(
                'success' => $result !== false,
                'message' => $result !== false ? 'Optimized' : 'Failed',
            );
        }
        
        return $results;
    }
    
    /**
     * Clean expired transients
     * 
     * @return int Số transients đã xóa
     */
    public function clean_expired_transients() {
        global $wpdb;
        
        // Clean expired transients
        $time = time();
        $expired = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                AND option_value < %d",
                $wpdb->esc_like( '_transient_timeout_' ) . '%',
                $time
            )
        );
        
        // Clean orphaned transient options
        $orphaned = $wpdb->query(
            "DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_%'
            AND option_name NOT LIKE '_transient_timeout_%'
            AND option_name NOT IN (
                SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_%'
            )"
        );
        
        return absint( $expired ) + absint( $orphaned );
    }
    
    /**
     * Clean post revisions
     * 
     * @param int $keep_revisions Số revisions giữ lại
     * @return int Số revisions đã xóa
     */
    public function clean_post_revisions( $keep_revisions = 5 ) {
        global $wpdb;
        
        $keep_revisions = absint( $keep_revisions );
        
        if ( $keep_revisions <= 0 ) {
            $keep_revisions = 5;
        }
        
        // Lấy revisions cần xóa
        $revisions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->posts}
                WHERE post_type = 'revision'
                AND post_parent IN (
                    SELECT ID FROM {$wpdb->posts}
                    WHERE post_type NOT IN ('revision', 'attachment')
                )
                ORDER BY post_modified DESC"
            )
        );
        
        $deleted = 0;
        $revision_counts = array();
        
        foreach ( $revisions as $revision ) {
            if ( ! isset( $revision_counts[ $revision->post_parent ] ) ) {
                $revision_counts[ $revision->post_parent ] = 0;
            }
            
            $revision_counts[ $revision->post_parent ]++;
            
            // Xóa nếu vượt quá số lượng giữ lại
            if ( $revision_counts[ $revision->post_parent ] > $keep_revisions ) {
                wp_delete_post_revision( $revision->ID );
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Clean spam comments
     * 
     * @return int Số comments đã xóa
     */
    public function clean_spam_comments() {
        global $wpdb;
        
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->comments}
            WHERE comment_approved = 'spam'"
        );
        
        // Clean comment meta
        $wpdb->query(
            "DELETE FROM {$wpdb->commentmeta}
            WHERE comment_id NOT IN (
                SELECT comment_ID FROM {$wpdb->comments}
            )"
        );
        
        return absint( $deleted );
    }
    
    /**
     * Clean orphaned post meta
     * 
     * @return int Số meta đã xóa
     */
    public function clean_orphaned_postmeta() {
        global $wpdb;
        
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->postmeta}
            WHERE post_id NOT IN (
                SELECT ID FROM {$wpdb->posts}
            )"
        );
        
        return absint( $deleted );
    }
    
    /**
     * Get database size
     * 
     * @return array Database size info
     */
    public function get_database_size() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    table_name AS 'table',
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'size'
                FROM information_schema.TABLES
                WHERE table_schema = %s
                ORDER BY (data_length + index_length) DESC",
                DB_NAME
            )
        );
        
        $total_size = 0;
        $tables = array();
        
        foreach ( $results as $row ) {
            $total_size += $row->size;
            $tables[] = array(
                'name' => $row->table,
                'size' => $row->size,
            );
        }
        
        return array(
            'total' => $total_size,
            'tables' => $tables,
        );
    }
    
    /**
     * AJAX: Optimize tables
     */
    public function ajax_optimize_tables() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $results = $this->optimize_database_tables();
        
        wp_send_json_success( array(
            'message' => 'Database optimized successfully',
            'results' => $results,
        ) );
    }
    
    /**
     * AJAX: Clean transients
     */
    public function ajax_clean_transients() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $deleted = $this->clean_expired_transients();
        
        wp_send_json_success( array(
            'message' => sprintf( 'Deleted %d transients', $deleted ),
            'deleted' => $deleted,
        ) );
    }
    
    /**
     * AJAX: Clean revisions
     */
    public function ajax_clean_revisions() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $deleted = $this->clean_post_revisions();
        
        wp_send_json_success( array(
            'message' => sprintf( 'Deleted %d revisions', $deleted ),
            'deleted' => $deleted,
        ) );
    }
    
    /**
     * AJAX: Clean spam
     */
    public function ajax_clean_spam() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $deleted = $this->clean_spam_comments();
        
        wp_send_json_success( array(
            'message' => sprintf( 'Deleted %d spam comments', $deleted ),
            'deleted' => $deleted,
        ) );
    }
}

// Khởi tạo module
new WP_WebOptimizer_Database_Optimizer();
