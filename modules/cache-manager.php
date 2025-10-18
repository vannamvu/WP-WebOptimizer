<?php
/**
 * Cache Manager Module
 * 
 * Quản lý cache: browser cache, HTML cache, object cache
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Cache Manager
 */
class WP_WebOptimizer_Cache_Manager {
    
    /**
     * Cache directory
     * 
     * @var string
     */
    private $cache_dir;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/wp-weboptimizer/';
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo hooks
     */
    private function init_hooks() {
        // Browser caching
        if ( WP_WebOptimizer::get_option( 'cache_manager.browser_cache', true ) ) {
            add_action( 'init', array( $this, 'setup_browser_cache' ) );
        }
        
        // HTML page caching
        if ( WP_WebOptimizer::get_option( 'cache_manager.html_cache', false ) ) {
            add_action( 'template_redirect', array( $this, 'maybe_serve_cache' ), 1 );
            add_action( 'shutdown', array( $this, 'maybe_cache_page' ) );
            
            // Clear cache hooks
            add_action( 'save_post', array( $this, 'clear_page_cache' ) );
            add_action( 'deleted_post', array( $this, 'clear_page_cache' ) );
            add_action( 'switch_theme', array( $this, 'clear_all_cache' ) );
        }
    }
    
    /**
     * Setup browser cache headers
     */
    public function setup_browser_cache() {
        if ( is_admin() ) {
            return;
        }
        
        // Set cache headers cho static resources
        add_action( 'send_headers', function() {
            $expires = WP_WebOptimizer::get_option( 'cache_manager.browser_cache_ttl', 31536000 ); // 1 year
            
            // Chỉ set cho static files
            if ( $this->is_static_resource() ) {
                header( 'Cache-Control: public, max-age=' . $expires );
                header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
            }
        });
    }
    
    /**
     * Kiểm tra nếu là static resource
     * 
     * @return bool
     */
    private function is_static_resource() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $extensions = array( '.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.webp', '.woff', '.woff2', '.ttf', '.svg' );
        
        foreach ( $extensions as $ext ) {
            if ( strpos( $request_uri, $ext ) !== false ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Serve cached page nếu có
     */
    public function maybe_serve_cache() {
        // Không cache cho logged in users, POST requests, v.v.
        if ( $this->should_skip_cache() ) {
            return;
        }
        
        $cache_file = $this->get_cache_file_path();
        
        if ( file_exists( $cache_file ) ) {
            $cache_time = filemtime( $cache_file );
            $cache_ttl = WP_WebOptimizer::get_option( 'cache_manager.html_cache_ttl', 3600 ); // 1 hour
            
            // Kiểm tra cache có còn valid không
            if ( time() - $cache_time < $cache_ttl ) {
                // Serve cache
                header( 'X-WP-WebOptimizer-Cache: HIT' );
                readfile( $cache_file );
                exit;
            } else {
                // Cache expired, xóa file
                @unlink( $cache_file );
            }
        }
    }
    
    /**
     * Cache page HTML
     */
    public function maybe_cache_page() {
        // Không cache cho logged in users, POST requests, v.v.
        if ( $this->should_skip_cache() ) {
            return;
        }
        
        // Chỉ cache thành công responses
        if ( http_response_code() !== 200 ) {
            return;
        }
        
        $output = ob_get_contents();
        
        if ( empty( $output ) ) {
            return;
        }
        
        $cache_file = $this->get_cache_file_path();
        $cache_dir = dirname( $cache_file );
        
        // Tạo directory nếu chưa tồn tại
        if ( ! file_exists( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
        }
        
        // Save cache
        file_put_contents( $cache_file, $output );
    }
    
    /**
     * Kiểm tra có nên skip cache không
     * 
     * @return bool
     */
    private function should_skip_cache() {
        // Không cache trong admin
        if ( is_admin() ) {
            return true;
        }
        
        // Không cache cho logged in users
        if ( is_user_logged_in() ) {
            return true;
        }
        
        // Không cache POST requests
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            return true;
        }
        
        // Không cache có query string (trừ utm_*)
        if ( ! empty( $_GET ) ) {
            foreach ( $_GET as $key => $value ) {
                if ( strpos( $key, 'utm_' ) !== 0 ) {
                    return true;
                }
            }
        }
        
        // Không cache 404, search results
        if ( is_404() || is_search() ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Lấy cache file path
     * 
     * @return string
     */
    private function get_cache_file_path() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $host = $_SERVER['HTTP_HOST'] ?? 'default';
        
        // Tạo cache key từ URL
        $cache_key = md5( $host . $request_uri );
        
        // Mobile cache separation
        $mobile_suffix = '';
        if ( wp_is_mobile() && WP_WebOptimizer::get_option( 'cache_manager.mobile_cache', false ) ) {
            $mobile_suffix = '-mobile';
        }
        
        return $this->cache_dir . $cache_key . $mobile_suffix . '.html';
    }
    
    /**
     * Clear cache cho một page
     * 
     * @param int $post_id Post ID
     */
    public function clear_page_cache( $post_id = 0 ) {
        if ( $post_id ) {
            $permalink = get_permalink( $post_id );
            if ( $permalink ) {
                $cache_key = md5( $_SERVER['HTTP_HOST'] . parse_url( $permalink, PHP_URL_PATH ) );
                $cache_file = $this->cache_dir . $cache_key . '.html';
                
                if ( file_exists( $cache_file ) ) {
                    @unlink( $cache_file );
                }
                
                // Clear mobile version
                $mobile_file = $this->cache_dir . $cache_key . '-mobile.html';
                if ( file_exists( $mobile_file ) ) {
                    @unlink( $mobile_file );
                }
            }
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear_all_cache() {
        if ( file_exists( $this->cache_dir ) ) {
            $files = glob( $this->cache_dir . '*.html' );
            foreach ( $files as $file ) {
                @unlink( $file );
            }
        }
    }
}

// Khởi tạo module
new WP_WebOptimizer_Cache_Manager();
