<?php
/**
 * Assets Optimizer Module
 * 
 * Tối ưu CSS/JS: minify, defer, async, critical CSS, remove unused CSS
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Assets Optimizer
 */
class WP_WebOptimizer_Assets_Optimizer {
    
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
        // Defer JavaScript
        if ( WP_WebOptimizer::get_option( 'assets_optimizer.defer_js', true ) ) {
            add_filter( 'script_loader_tag', array( $this, 'defer_scripts' ), 10, 3 );
        }
        
        // Minify inline CSS/JS
        if ( WP_WebOptimizer::get_option( 'assets_optimizer.minify_css', true ) ) {
            add_filter( 'style_loader_tag', array( $this, 'minify_inline_css' ), 10, 2 );
        }
        
        if ( WP_WebOptimizer::get_option( 'assets_optimizer.minify_js', true ) ) {
            add_filter( 'script_loader_tag', array( $this, 'minify_inline_js' ), 10, 2 );
        }
        
        // Critical CSS
        if ( WP_WebOptimizer::get_option( 'assets_optimizer.critical_css', false ) ) {
            add_action( 'wp_head', array( $this, 'inject_critical_css' ), 1 );
            add_filter( 'style_loader_tag', array( $this, 'defer_non_critical_css' ), 10, 4 );
        }
        
        // Remove render-blocking resources
        add_action( 'wp_enqueue_scripts', array( $this, 'optimize_enqueue' ), 999 );
    }
    
    /**
     * Defer JavaScript files
     * 
     * @param string $tag HTML tag của script
     * @param string $handle Handle của script
     * @param string $src URL của script
     * @return string Modified tag
     */
    public function defer_scripts( $tag, $handle, $src ) {
        // Danh sách scripts không nên defer
        $exclude = array( 'jquery', 'jquery-core', 'jquery-migrate' );
        
        // Không defer các script trong danh sách exclude
        if ( in_array( $handle, $exclude, true ) ) {
            return $tag;
        }
        
        // Thêm defer nếu chưa có defer hay async
        if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
            $tag = str_replace( ' src', ' defer src', $tag );
        }
        
        return $tag;
    }
    
    /**
     * Minify inline CSS
     * 
     * @param string $html HTML tag của style
     * @param string $handle Handle của style
     * @return string Modified HTML
     */
    public function minify_inline_css( $html, $handle ) {
        // Chỉ minify inline styles
        if ( preg_match( '/<style[^>]*>(.*?)<\/style>/is', $html, $matches ) ) {
            $minified = $this->minify_css( $matches[1] );
            $html = str_replace( $matches[1], $minified, $html );
        }
        
        return $html;
    }
    
    /**
     * Minify inline JavaScript
     * 
     * @param string $html HTML tag của script
     * @param string $handle Handle của script
     * @return string Modified HTML
     */
    public function minify_inline_js( $html, $handle ) {
        // Chỉ minify inline scripts
        if ( strpos( $html, '<script' ) !== false && strpos( $html, 'src=' ) === false ) {
            if ( preg_match( '/<script[^>]*>(.*?)<\/script>/is', $html, $matches ) ) {
                $minified = $this->minify_js( $matches[1] );
                $html = str_replace( $matches[1], $minified, $html );
            }
        }
        
        return $html;
    }
    
    /**
     * Minify CSS code
     * 
     * @param string $css CSS code
     * @return string Minified CSS
     */
    private function minify_css( $css ) {
        // Remove comments
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
        
        // Remove whitespace
        $css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );
        $css = preg_replace( '/\s+/', ' ', $css );
        
        // Remove spaces around operators
        $css = preg_replace( '/\s*([:;{}])\s*/', '$1', $css );
        
        return trim( $css );
    }
    
    /**
     * Minify JavaScript code
     * 
     * @param string $js JavaScript code
     * @return string Minified JavaScript
     */
    private function minify_js( $js ) {
        // Remove comments (simple approach)
        $js = preg_replace( '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<![\\:])\/\/.*))/', '', $js );
        
        // Remove whitespace
        $js = preg_replace( '/\s+/', ' ', $js );
        $js = trim( $js );
        
        return $js;
    }
    
    /**
     * Inject Critical CSS
     */
    public function inject_critical_css() {
        $critical_css = WP_WebOptimizer::get_option( 'assets_optimizer.critical_css_code', '' );
        
        if ( ! empty( $critical_css ) ) {
            echo '<style id="critical-css">' . wp_strip_all_tags( $critical_css ) . '</style>';
        }
    }
    
    /**
     * Defer non-critical CSS
     * 
     * @param string $html HTML tag
     * @param string $handle Handle
     * @param string $href URL
     * @param string $media Media attribute
     * @return string Modified HTML
     */
    public function defer_non_critical_css( $html, $handle, $href, $media ) {
        // Không defer admin styles và critical styles
        $exclude = array( 'admin-bar', 'dashicons' );
        
        if ( in_array( $handle, $exclude, true ) ) {
            return $html;
        }
        
        // Load CSS với media="print" rồi chuyển sang "all" khi load xong
        $html = str_replace( "media='$media'", "media='print' onload=\"this.media='$media'\"", $html );
        $html = str_replace( 'media="' . $media . '"', 'media="print" onload="this.media=\'' . $media . '\'"', $html );
        
        // Thêm noscript fallback
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '" media="' . esc_attr( $media ) . '"></noscript>';
        
        return $html;
    }
    
    /**
     * Tối ưu enqueue scripts/styles
     */
    public function optimize_enqueue() {
        // Remove jQuery Migrate nếu không cần thiết
        if ( ! is_admin() && WP_WebOptimizer::get_option( 'assets_optimizer.remove_jquery_migrate', false ) ) {
            wp_deregister_script( 'jquery-migrate' );
        }
    }
}

// Khởi tạo module
new WP_WebOptimizer_Assets_Optimizer();
