<?php
/**
 * Lazy Load Module
 * 
 * Lazy load images, iframes, videos với native loading và Intersection Observer
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Lazy Load
 */
class WP_WebOptimizer_Lazy_Load {
    
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
        // Lazy load images
        if ( WP_WebOptimizer::get_option( 'lazy_load.images', true ) ) {
            add_filter( 'the_content', array( $this, 'add_lazy_load_images' ), 999 );
            add_filter( 'post_thumbnail_html', array( $this, 'add_lazy_load_images' ), 999 );
            add_filter( 'get_avatar', array( $this, 'add_lazy_load_images' ), 999 );
            add_filter( 'widget_text', array( $this, 'add_lazy_load_images' ), 999 );
        }
        
        // Lazy load iframes
        if ( WP_WebOptimizer::get_option( 'lazy_load.iframes', true ) ) {
            add_filter( 'the_content', array( $this, 'add_lazy_load_iframes' ), 999 );
            add_filter( 'widget_text', array( $this, 'add_lazy_load_iframes' ), 999 );
        }
        
        // Lazy load videos
        if ( WP_WebOptimizer::get_option( 'lazy_load.videos', false ) ) {
            add_filter( 'the_content', array( $this, 'add_lazy_load_videos' ), 999 );
        }
        
        // Enqueue frontend script
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    /**
     * Thêm lazy load cho images
     * 
     * @param string $content HTML content
     * @return string Modified content
     */
    public function add_lazy_load_images( $content ) {
        // Không lazy load trong admin hay feed
        if ( is_admin() || is_feed() || wp_doing_ajax() ) {
            return $content;
        }
        
        // Tìm tất cả thẻ img
        if ( preg_match_all( '/<img[^>]+>/i', $content, $matches ) ) {
            foreach ( $matches[0] as $img_tag ) {
                // Skip nếu đã có loading attribute
                if ( strpos( $img_tag, 'loading=' ) !== false ) {
                    continue;
                }
                
                // Skip nếu có class skip-lazy
                if ( strpos( $img_tag, 'skip-lazy' ) !== false ) {
                    continue;
                }
                
                // Thêm loading="lazy"
                $new_img_tag = str_replace( '<img', '<img loading="lazy"', $img_tag );
                
                // Thêm decoding="async" để cải thiện hiệu suất
                if ( strpos( $new_img_tag, 'decoding=' ) === false ) {
                    $new_img_tag = str_replace( '<img', '<img decoding="async"', $new_img_tag );
                }
                
                $content = str_replace( $img_tag, $new_img_tag, $content );
            }
        }
        
        return $content;
    }
    
    /**
     * Thêm lazy load cho iframes
     * 
     * @param string $content HTML content
     * @return string Modified content
     */
    public function add_lazy_load_iframes( $content ) {
        // Không lazy load trong admin hay feed
        if ( is_admin() || is_feed() || wp_doing_ajax() ) {
            return $content;
        }
        
        // Tìm tất cả thẻ iframe
        if ( preg_match_all( '/<iframe[^>]+>/i', $content, $matches ) ) {
            foreach ( $matches[0] as $iframe_tag ) {
                // Skip nếu đã có loading attribute
                if ( strpos( $iframe_tag, 'loading=' ) !== false ) {
                    continue;
                }
                
                // Thêm loading="lazy"
                $new_iframe_tag = str_replace( '<iframe', '<iframe loading="lazy"', $iframe_tag );
                
                $content = str_replace( $iframe_tag, $new_iframe_tag, $content );
            }
        }
        
        return $content;
    }
    
    /**
     * Thêm lazy load cho videos
     * 
     * @param string $content HTML content
     * @return string Modified content
     */
    public function add_lazy_load_videos( $content ) {
        // Không lazy load trong admin hay feed
        if ( is_admin() || is_feed() || wp_doing_ajax() ) {
            return $content;
        }
        
        // Tìm tất cả thẻ video
        if ( preg_match_all( '/<video[^>]+>/i', $content, $matches ) ) {
            foreach ( $matches[0] as $video_tag ) {
                // Skip nếu đã có preload="none"
                if ( strpos( $video_tag, 'preload=' ) !== false ) {
                    continue;
                }
                
                // Thêm preload="none" để không tải video cho đến khi cần
                $new_video_tag = str_replace( '<video', '<video preload="none"', $video_tag );
                
                $content = str_replace( $video_tag, $new_video_tag, $content );
            }
        }
        
        return $content;
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        // Không cần enqueue script vì sử dụng native loading
        // Nhưng có thể thêm CSS cho placeholder effect
        $custom_css = '
            img[loading="lazy"] {
                background: #f0f0f0;
            }
        ';
        
        if ( WP_WebOptimizer::get_option( 'lazy_load.placeholder_effect', false ) ) {
            wp_add_inline_style( 'wp-block-library', $custom_css );
        }
    }
}

// Khởi tạo module
new WP_WebOptimizer_Lazy_Load();
