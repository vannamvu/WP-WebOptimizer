<?php
/**
 * Resource Hints Module
 * 
 * DNS-prefetch, preconnect, preload, prefetch
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Resource Hints
 */
class WP_WebOptimizer_Resource_Hints {
    
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
        // DNS prefetch
        add_action( 'wp_head', array( $this, 'add_dns_prefetch' ), 0 );
        
        // Preconnect
        add_action( 'wp_head', array( $this, 'add_preconnect' ), 0 );
        
        // Preload key resources
        add_action( 'wp_head', array( $this, 'add_preload' ), 1 );
        
        // Remove default WordPress dns-prefetch
        remove_action( 'wp_head', 'wp_resource_hints', 2 );
    }
    
    /**
     * Add DNS prefetch hints
     */
    public function add_dns_prefetch() {
        $domains = WP_WebOptimizer::get_option( 'resource_hints.dns_prefetch', array() );
        
        // Default domains
        $default_domains = array(
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//ajax.googleapis.com',
        );
        
        // Merge custom domains
        if ( ! empty( $domains ) && is_array( $domains ) ) {
            $domains = array_merge( $default_domains, $domains );
        } else {
            $domains = $default_domains;
        }
        
        // Remove duplicates
        $domains = array_unique( $domains );
        
        foreach ( $domains as $domain ) {
            if ( ! empty( $domain ) ) {
                printf(
                    '<link rel="dns-prefetch" href="%s">' . "\n",
                    esc_url( $domain )
                );
            }
        }
    }
    
    /**
     * Add preconnect hints
     */
    public function add_preconnect() {
        $domains = WP_WebOptimizer::get_option( 'resource_hints.preconnect', array() );
        
        // Default critical domains
        $default_domains = array(
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
        );
        
        // Merge custom domains
        if ( ! empty( $domains ) && is_array( $domains ) ) {
            $domains = array_merge( $default_domains, $domains );
        } else {
            $domains = $default_domains;
        }
        
        // Remove duplicates
        $domains = array_unique( $domains );
        
        foreach ( $domains as $domain ) {
            if ( ! empty( $domain ) ) {
                $crossorigin = ( strpos( $domain, 'fonts' ) !== false ) ? ' crossorigin' : '';
                printf(
                    '<link rel="preconnect" href="%s"%s>' . "\n",
                    esc_url( $domain ),
                    $crossorigin
                );
            }
        }
    }
    
    /**
     * Add preload hints cho key resources
     */
    public function add_preload() {
        $resources = WP_WebOptimizer::get_option( 'resource_hints.preload', array() );
        
        if ( empty( $resources ) || ! is_array( $resources ) ) {
            return;
        }
        
        foreach ( $resources as $resource ) {
            if ( empty( $resource['url'] ) || empty( $resource['as'] ) ) {
                continue;
            }
            
            $attributes = sprintf(
                'rel="preload" href="%s" as="%s"',
                esc_url( $resource['url'] ),
                esc_attr( $resource['as'] )
            );
            
            // Thêm type nếu có
            if ( ! empty( $resource['type'] ) ) {
                $attributes .= sprintf( ' type="%s"', esc_attr( $resource['type'] ) );
            }
            
            // Thêm crossorigin nếu cần
            if ( ! empty( $resource['crossorigin'] ) ) {
                $attributes .= ' crossorigin';
            }
            
            printf( '<link %s>' . "\n", $attributes );
        }
    }
}

// Khởi tạo module
new WP_WebOptimizer_Resource_Hints();
