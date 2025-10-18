<?php
/**
 * Advanced Settings Module
 * 
 * WordPress advanced optimizations: disable embeds, emojis, query strings, etc.
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Advanced Settings
 */
class WP_WebOptimizer_Advanced_Settings {
    
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
        // Disable emojis
        if ( WP_WebOptimizer::get_option( 'advanced_settings.disable_emojis', true ) ) {
            $this->disable_emojis();
        }
        
        // Remove query strings
        if ( WP_WebOptimizer::get_option( 'advanced_settings.remove_query_strings', true ) ) {
            add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ), 15 );
            add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ), 15 );
        }
        
        // Disable JSON API for guests
        if ( WP_WebOptimizer::get_option( 'advanced_settings.disable_json_api', false ) ) {
            add_filter( 'rest_authentication_errors', array( $this, 'disable_json_api' ) );
        }
        
        // Disable pingbacks
        if ( WP_WebOptimizer::get_option( 'advanced_settings.disable_pingbacks', true ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', array( $this, 'remove_x_pingback' ) );
        }
        
        // Remove RSD link
        if ( WP_WebOptimizer::get_option( 'advanced_settings.remove_rsd', true ) ) {
            remove_action( 'wp_head', 'rsd_link' );
        }
        
        // Remove shortlink
        if ( WP_WebOptimizer::get_option( 'advanced_settings.remove_shortlink', true ) ) {
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }
        
        // Remove WP version
        if ( WP_WebOptimizer::get_option( 'advanced_settings.remove_version', true ) ) {
            remove_action( 'wp_head', 'wp_generator' );
            add_filter( 'the_generator', '__return_empty_string' );
        }
        
        // Limit post revisions
        if ( WP_WebOptimizer::get_option( 'advanced_settings.limit_revisions', false ) ) {
            $this->limit_post_revisions();
        }
        
        // Disable heartbeat
        if ( WP_WebOptimizer::get_option( 'advanced_settings.disable_heartbeat', false ) ) {
            add_action( 'init', array( $this, 'disable_heartbeat' ), 1 );
        }
        
        // Remove jQuery Migrate
        if ( WP_WebOptimizer::get_option( 'advanced_settings.remove_jquery_migrate', false ) ) {
            add_action( 'wp_default_scripts', array( $this, 'remove_jquery_migrate' ) );
        }
    }
    
    /**
     * Disable emojis
     */
    private function disable_emojis() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        
        // Remove from TinyMCE
        add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
        add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_dns_prefetch' ), 10, 2 );
    }
    
    /**
     * Disable emojis trong TinyMCE
     * 
     * @param array $plugins Plugins array
     * @return array Modified plugins
     */
    public function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }
        return array();
    }
    
    /**
     * Remove emoji DNS prefetch
     * 
     * @param array $urls URLs array
     * @param string $relation_type Relation type
     * @return array Modified URLs
     */
    public function disable_emojis_dns_prefetch( $urls, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
            $urls = array_diff( $urls, array( $emoji_svg_url ) );
        }
        return $urls;
    }
    
    /**
     * Remove query strings từ static resources
     * 
     * @param string $src Source URL
     * @return string Modified URL
     */
    public function remove_query_strings( $src ) {
        if ( strpos( $src, '?ver=' ) !== false ) {
            $src = remove_query_arg( 'ver', $src );
        }
        return $src;
    }
    
    /**
     * Disable JSON API cho guests
     * 
     * @param mixed $access Current access
     * @return mixed Modified access
     */
    public function disable_json_api( $access ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error(
                'rest_disabled',
                __( 'The REST API is disabled for guests.', 'wp-weboptimizer' ),
                array( 'status' => 403 )
            );
        }
        return $access;
    }
    
    /**
     * Remove X-Pingback header
     * 
     * @param array $headers Headers array
     * @return array Modified headers
     */
    public function remove_x_pingback( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    }
    
    /**
     * Limit post revisions
     */
    private function limit_post_revisions() {
        $limit = WP_WebOptimizer::get_option( 'advanced_settings.revisions_limit', 5 );
        
        if ( ! defined( 'WP_POST_REVISIONS' ) ) {
            define( 'WP_POST_REVISIONS', absint( $limit ) );
        }
    }
    
    /**
     * Disable Heartbeat API
     */
    public function disable_heartbeat() {
        $mode = WP_WebOptimizer::get_option( 'advanced_settings.heartbeat_mode', 'disable' );
        
        if ( $mode === 'disable' ) {
            wp_deregister_script( 'heartbeat' );
        } elseif ( $mode === 'post_only' ) {
            // Chỉ cho phép trong post editor
            global $pagenow;
            if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
                wp_deregister_script( 'heartbeat' );
            }
        }
    }
    
    /**
     * Remove jQuery Migrate
     * 
     * @param WP_Scripts $scripts Scripts object
     */
    public function remove_jquery_migrate( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            
            if ( $script->deps ) {
                $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
            }
        }
    }
}

// Khởi tạo module
new WP_WebOptimizer_Advanced_Settings();
