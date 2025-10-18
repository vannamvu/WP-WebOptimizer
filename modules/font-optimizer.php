<?php
/**
 * Font Optimizer Module
 * 
 * Tối ưu fonts: font-display swap, preload, local hosting
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Font Optimizer
 */
class WP_WebOptimizer_Font_Optimizer {
    
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
        // Font display swap
        if ( WP_WebOptimizer::get_option( 'font_optimizer.font_display_swap', true ) ) {
            add_filter( 'style_loader_tag', array( $this, 'add_font_display_swap' ), 10, 4 );
        }
        
        // Preload fonts
        if ( WP_WebOptimizer::get_option( 'font_optimizer.preload_fonts', false ) ) {
            add_action( 'wp_head', array( $this, 'preload_fonts' ), 1 );
        }
        
        // Optimize Google Fonts
        add_filter( 'style_loader_tag', array( $this, 'optimize_google_fonts' ), 10, 4 );
    }
    
    /**
     * Thêm font-display: swap vào Google Fonts
     * 
     * @param string $html HTML tag
     * @param string $handle Handle
     * @param string $href URL
     * @param string $media Media attribute
     * @return string Modified HTML
     */
    public function add_font_display_swap( $html, $handle, $href, $media ) {
        // Kiểm tra nếu là Google Fonts
        if ( strpos( $href, 'fonts.googleapis.com' ) !== false ) {
            // Thêm display=swap parameter
            if ( strpos( $href, 'display=' ) === false ) {
                $separator = ( strpos( $href, '?' ) !== false ) ? '&' : '?';
                $href = $href . $separator . 'display=swap';
                $html = str_replace( $handle, $handle, $html );
                $html = preg_replace( '/href=["\']([^"\']+)["\']/', 'href="' . esc_url( $href ) . '"', $html );
            }
        }
        
        return $html;
    }
    
    /**
     * Preload critical fonts
     */
    public function preload_fonts() {
        $fonts = WP_WebOptimizer::get_option( 'font_optimizer.preload_fonts_list', array() );
        
        if ( ! empty( $fonts ) && is_array( $fonts ) ) {
            foreach ( $fonts as $font ) {
                if ( ! empty( $font['url'] ) ) {
                    printf(
                        '<link rel="preload" href="%s" as="font" type="font/%s" crossorigin>',
                        esc_url( $font['url'] ),
                        esc_attr( $font['type'] ?? 'woff2' )
                    );
                    echo "\n";
                }
            }
        }
    }
    
    /**
     * Optimize Google Fonts loading
     * 
     * @param string $html HTML tag
     * @param string $handle Handle
     * @param string $href URL
     * @param string $media Media attribute
     * @return string Modified HTML
     */
    public function optimize_google_fonts( $html, $handle, $href, $media ) {
        // Kiểm tra nếu là Google Fonts
        if ( strpos( $href, 'fonts.googleapis.com' ) !== false ) {
            // Thêm preconnect cho Google Fonts
            if ( ! has_action( 'wp_head', array( $this, 'add_google_fonts_preconnect' ) ) ) {
                add_action( 'wp_head', array( $this, 'add_google_fonts_preconnect' ), 0 );
            }
        }
        
        return $html;
    }
    
    /**
     * Thêm preconnect cho Google Fonts
     */
    public function add_google_fonts_preconnect() {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }
}

// Khởi tạo module
new WP_WebOptimizer_Font_Optimizer();
