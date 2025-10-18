<?php
/**
 * Third Party Optimizer Module
 * 
 * Delay loading Google Analytics, GTM, Facebook Pixel, social widgets
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Third Party Optimizer
 */
class WP_WebOptimizer_Third_Party_Optimizer {
    
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
        // Delay Google Analytics
        if ( WP_WebOptimizer::get_option( 'third_party_optimizer.delay_analytics', true ) ) {
            add_action( 'wp_footer', array( $this, 'delay_google_analytics' ), 999 );
        }
        
        // Delay Facebook Pixel
        if ( WP_WebOptimizer::get_option( 'third_party_optimizer.delay_fb_pixel', false ) ) {
            add_action( 'wp_footer', array( $this, 'delay_facebook_pixel' ), 999 );
        }
        
        // Remove WordPress embeds
        if ( WP_WebOptimizer::get_option( 'third_party_optimizer.remove_embeds', true ) ) {
            add_action( 'init', array( $this, 'remove_wp_embeds' ) );
        }
    }
    
    /**
     * Delay Google Analytics loading
     */
    public function delay_google_analytics() {
        $ga_id = WP_WebOptimizer::get_option( 'third_party_optimizer.ga_tracking_id', '' );
        
        if ( empty( $ga_id ) ) {
            return;
        }
        
        ?>
        <script>
        // Delay Google Analytics
        (function() {
            var gaDelay = <?php echo absint( WP_WebOptimizer::get_option( 'third_party_optimizer.ga_delay', 3000 ) ); ?>;
            var gaLoaded = false;
            
            function loadGA() {
                if (gaLoaded) return;
                gaLoaded = true;
                
                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $ga_id ); ?>';
                document.head.appendChild(script);
                
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '<?php echo esc_js( $ga_id ); ?>');
            }
            
            // Load sau một khoảng thời gian hoặc khi user tương tác
            setTimeout(loadGA, gaDelay);
            
            ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'].forEach(function(event) {
                document.addEventListener(event, loadGA, {once: true, passive: true});
            });
        })();
        </script>
        <?php
    }
    
    /**
     * Delay Facebook Pixel loading
     */
    public function delay_facebook_pixel() {
        $pixel_id = WP_WebOptimizer::get_option( 'third_party_optimizer.fb_pixel_id', '' );
        
        if ( empty( $pixel_id ) ) {
            return;
        }
        
        ?>
        <script>
        // Delay Facebook Pixel
        (function() {
            var fbDelay = <?php echo absint( WP_WebOptimizer::get_option( 'third_party_optimizer.fb_delay', 3000 ) ); ?>;
            var fbLoaded = false;
            
            function loadFBPixel() {
                if (fbLoaded) return;
                fbLoaded = true;
                
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
                fbq('track', 'PageView');
            }
            
            // Load sau một khoảng thời gian hoặc khi user tương tác
            setTimeout(loadFBPixel, fbDelay);
            
            ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'].forEach(function(event) {
                document.addEventListener(event, loadFBPixel, {once: true, passive: true});
            });
        })();
        </script>
        <?php
    }
    
    /**
     * Remove WordPress embeds
     */
    public function remove_wp_embeds() {
        // Remove the REST API endpoint
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        
        // Turn off oEmbed auto discovery
        add_filter( 'embed_oembed_discover', '__return_false' );
        
        // Don't filter oEmbed results
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        
        // Remove oEmbed discovery links
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        
        // Remove oEmbed-specific JavaScript from the front-end and back-end
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        
        // Remove all embeds rewrite rules
        add_filter( 'rewrite_rules_array', array( $this, 'disable_embeds_rewrites' ) );
    }
    
    /**
     * Disable embeds rewrites
     * 
     * @param array $rules Rewrite rules
     * @return array Modified rules
     */
    public function disable_embeds_rewrites( $rules ) {
        foreach ( $rules as $rule => $rewrite ) {
            if ( strpos( $rewrite, 'embed=true' ) !== false ) {
                unset( $rules[ $rule ] );
            }
        }
        return $rules;
    }
}

// Khởi tạo module
new WP_WebOptimizer_Third_Party_Optimizer();
