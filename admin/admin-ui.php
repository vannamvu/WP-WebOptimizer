<?php
/**
 * Admin UI
 * 
 * Giao diện quản trị plugin
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Admin UI
 */
class WP_WebOptimizer_Admin_UI {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_wpwo_save_settings', array( $this, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_wpwo_clear_cache', array( $this, 'ajax_clear_cache' ) );
    }
    
    /**
     * Thêm menu vào admin
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'WP WebOptimizer Pro', 'wp-weboptimizer' ),
            __( 'WebOptimizer', 'wp-weboptimizer' ),
            'manage_options',
            'wp-weboptimizer',
            array( $this, 'render_admin_page' ),
            'dashicons-performance',
            65
        );
    }
    
    /**
     * Enqueue admin assets
     * 
     * @param string $hook Hook name
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_wp-weboptimizer' !== $hook ) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'wp-weboptimizer-admin',
            WP_WEBOPTIMIZER_URL . 'assets/css/admin-style.css',
            array(),
            WP_WEBOPTIMIZER_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script(
            'wp-weboptimizer-admin',
            WP_WEBOPTIMIZER_URL . 'assets/js/admin-script.js',
            array( 'jquery' ),
            WP_WEBOPTIMIZER_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'wp-weboptimizer-admin',
            'wpwoAdmin',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wpwo_ajax_nonce' ),
                'strings' => array(
                    'saving' => __( 'Saving...', 'wp-weboptimizer' ),
                    'saved' => __( 'Settings saved!', 'wp-weboptimizer' ),
                    'error' => __( 'Error saving settings', 'wp-weboptimizer' ),
                ),
            )
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';
        ?>
        <div class="wrap wpwo-admin-wrap">
            <h1><?php esc_html_e( 'WP WebOptimizer Pro', 'wp-weboptimizer' ); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=wp-weboptimizer&tab=dashboard" class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Dashboard', 'wp-weboptimizer' ); ?>
                </a>
                <a href="?page=wp-weboptimizer&tab=assets" class="nav-tab <?php echo $active_tab === 'assets' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'CSS/JS Optimization', 'wp-weboptimizer' ); ?>
                </a>
                <a href="?page=wp-weboptimizer&tab=images" class="nav-tab <?php echo $active_tab === 'images' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Image Optimization', 'wp-weboptimizer' ); ?>
                </a>
                <a href="?page=wp-weboptimizer&tab=cache" class="nav-tab <?php echo $active_tab === 'cache' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Cache', 'wp-weboptimizer' ); ?>
                </a>
                <a href="?page=wp-weboptimizer&tab=database" class="nav-tab <?php echo $active_tab === 'database' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Database', 'wp-weboptimizer' ); ?>
                </a>
                <a href="?page=wp-weboptimizer&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Advanced', 'wp-weboptimizer' ); ?>
                </a>
            </nav>
            
            <div class="wpwo-admin-content">
                <?php
                switch ( $active_tab ) {
                    case 'assets':
                        $this->render_assets_tab();
                        break;
                    case 'images':
                        $this->render_images_tab();
                        break;
                    case 'cache':
                        $this->render_cache_tab();
                        break;
                    case 'database':
                        $this->render_database_tab();
                        break;
                    case 'advanced':
                        $this->render_advanced_tab();
                        break;
                    default:
                        $this->render_dashboard_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Dashboard tab
     */
    private function render_dashboard_tab() {
        ?>
        <div class="wpwo-dashboard">
            <div class="wpwo-card">
                <h2><?php esc_html_e( 'Welcome to WP WebOptimizer Pro', 'wp-weboptimizer' ); ?></h2>
                <p><?php esc_html_e( 'Tối ưu hiệu suất WordPress toàn diện để đạt điểm cao trên PageSpeed Insights.', 'wp-weboptimizer' ); ?></p>
                
                <div class="wpwo-quick-stats">
                    <div class="wpwo-stat-item">
                        <div class="wpwo-stat-label"><?php esc_html_e( 'Active Optimizations', 'wp-weboptimizer' ); ?></div>
                        <div class="wpwo-stat-value">8</div>
                    </div>
                    <div class="wpwo-stat-item">
                        <div class="wpwo-stat-label"><?php esc_html_e( 'Cache Size', 'wp-weboptimizer' ); ?></div>
                        <div class="wpwo-stat-value">0 MB</div>
                    </div>
                    <div class="wpwo-stat-item">
                        <div class="wpwo-stat-label"><?php esc_html_e( 'Performance Score', 'wp-weboptimizer' ); ?></div>
                        <div class="wpwo-stat-value">A+</div>
                    </div>
                </div>
            </div>
            
            <div class="wpwo-card">
                <h3><?php esc_html_e( 'Quick Actions', 'wp-weboptimizer' ); ?></h3>
                <button type="button" class="button button-primary wpwo-clear-cache">
                    <?php esc_html_e( 'Clear All Cache', 'wp-weboptimizer' ); ?>
                </button>
                <button type="button" class="button wpwo-optimize-db">
                    <?php esc_html_e( 'Optimize Database', 'wp-weboptimizer' ); ?>
                </button>
            </div>
            
            <div class="wpwo-card">
                <h3><?php esc_html_e( 'Support', 'wp-weboptimizer' ); ?></h3>
                <p>
                    <?php esc_html_e( 'Author: Vũ Văn Nam Việt', 'wp-weboptimizer' ); ?><br>
                    <?php esc_html_e( 'Website:', 'wp-weboptimizer' ); ?> <a href="https://vuvannamviet.com" target="_blank">vuvannamviet.com</a><br>
                    <?php esc_html_e( 'Hotline: 0971.735.735', 'wp-weboptimizer' ); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Assets tab
     */
    private function render_assets_tab() {
        $options = WP_WebOptimizer::get_option( 'assets_optimizer', array() );
        ?>
        <form class="wpwo-settings-form" data-module="assets_optimizer">
            <div class="wpwo-card">
                <h2><?php esc_html_e( 'CSS/JS Optimization', 'wp-weboptimizer' ); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Minify CSS', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="minify_css" value="1" <?php checked( $options['minify_css'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable CSS minification', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Minify JavaScript', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="minify_js" value="1" <?php checked( $options['minify_js'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable JavaScript minification', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Defer JavaScript', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="defer_js" value="1" <?php checked( $options['defer_js'] ?? true ); ?>>
                                <?php esc_html_e( 'Defer non-critical JavaScript', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Remove jQuery Migrate', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="remove_jquery_migrate" value="1" <?php checked( $options['remove_jquery_migrate'] ?? false ); ?>>
                                <?php esc_html_e( 'Remove jQuery Migrate (may break old plugins)', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'wp-weboptimizer' ); ?>
                    </button>
                </p>
            </div>
        </form>
        <?php
    }
    
    /**
     * Render Images tab
     */
    private function render_images_tab() {
        $options = WP_WebOptimizer::get_option( 'image_optimizer', array() );
        ?>
        <form class="wpwo-settings-form" data-module="image_optimizer">
            <div class="wpwo-card">
                <h2><?php esc_html_e( 'Image Optimization', 'wp-weboptimizer' ); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Auto WebP Conversion', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_webp" value="1" <?php checked( $options['auto_webp'] ?? true ); ?>>
                                <?php esc_html_e( 'Automatically convert images to WebP on upload', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Image Compression', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="compression" value="1" <?php checked( $options['compression'] ?? true ); ?>>
                                <?php esc_html_e( 'Compress images on upload', 'wp-weboptimizer' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'JPEG Quality:', 'wp-weboptimizer' ); ?>
                                <input type="number" name="jpeg_quality" value="<?php echo absint( $options['jpeg_quality'] ?? 85 ); ?>" min="1" max="100" style="width: 60px;">%
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Lazy Load Images', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="lazy_load" value="1" <?php checked( WP_WebOptimizer::get_option( 'lazy_load.images', true ) ); ?>>
                                <?php esc_html_e( 'Enable lazy loading for images', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Responsive Images', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="responsive" value="1" <?php checked( $options['responsive'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable responsive images (srcset)', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'wp-weboptimizer' ); ?>
                    </button>
                </p>
            </div>
        </form>
        <?php
    }
    
    /**
     * Render Cache tab
     */
    private function render_cache_tab() {
        $options = WP_WebOptimizer::get_option( 'cache_manager', array() );
        ?>
        <form class="wpwo-settings-form" data-module="cache_manager">
            <div class="wpwo-card">
                <h2><?php esc_html_e( 'Cache Settings', 'wp-weboptimizer' ); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Browser Caching', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="browser_cache" value="1" <?php checked( $options['browser_cache'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable browser caching headers', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'HTML Page Caching', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="html_cache" value="1" <?php checked( $options['html_cache'] ?? false ); ?>>
                                <?php esc_html_e( 'Enable HTML page caching', 'wp-weboptimizer' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Cache TTL (seconds):', 'wp-weboptimizer' ); ?>
                                <input type="number" name="html_cache_ttl" value="<?php echo absint( $options['html_cache_ttl'] ?? 3600 ); ?>" min="60" style="width: 100px;">
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Mobile Cache', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="mobile_cache" value="1" <?php checked( $options['mobile_cache'] ?? false ); ?>>
                                <?php esc_html_e( 'Separate cache for mobile devices', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'wp-weboptimizer' ); ?>
                    </button>
                    <button type="button" class="button wpwo-clear-cache">
                        <?php esc_html_e( 'Clear All Cache', 'wp-weboptimizer' ); ?>
                    </button>
                </p>
            </div>
        </form>
        <?php
    }
    
    /**
     * Render Database tab
     */
    private function render_database_tab() {
        $options = WP_WebOptimizer::get_option( 'database_optimizer', array() );
        ?>
        <div class="wpwo-card">
            <h2><?php esc_html_e( 'Database Optimization', 'wp-weboptimizer' ); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Optimize Tables', 'wp-weboptimizer' ); ?></th>
                    <td>
                        <button type="button" class="button" id="wpwo-optimize-tables">
                            <?php esc_html_e( 'Optimize Database Tables', 'wp-weboptimizer' ); ?>
                        </button>
                        <p class="description"><?php esc_html_e( 'Optimize all database tables to improve performance', 'wp-weboptimizer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Clean Transients', 'wp-weboptimizer' ); ?></th>
                    <td>
                        <button type="button" class="button" id="wpwo-clean-transients">
                            <?php esc_html_e( 'Clean Expired Transients', 'wp-weboptimizer' ); ?>
                        </button>
                        <p class="description"><?php esc_html_e( 'Remove expired and orphaned transients', 'wp-weboptimizer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Clean Revisions', 'wp-weboptimizer' ); ?></th>
                    <td>
                        <button type="button" class="button" id="wpwo-clean-revisions">
                            <?php esc_html_e( 'Clean Post Revisions', 'wp-weboptimizer' ); ?>
                        </button>
                        <p class="description"><?php esc_html_e( 'Keep only the latest 5 revisions per post', 'wp-weboptimizer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Clean Spam', 'wp-weboptimizer' ); ?></th>
                    <td>
                        <button type="button" class="button" id="wpwo-clean-spam">
                            <?php esc_html_e( 'Clean Spam Comments', 'wp-weboptimizer' ); ?>
                        </button>
                        <p class="description"><?php esc_html_e( 'Remove all spam comments', 'wp-weboptimizer' ); ?></p>
                    </td>
                </tr>
            </table>
            
            <form class="wpwo-settings-form" data-module="database_optimizer" style="margin-top: 20px;">
                <h3><?php esc_html_e( 'Auto Optimization', 'wp-weboptimizer' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Auto Optimize', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_optimize" value="1" <?php checked( $options['auto_optimize'] ?? false ); ?>>
                                <?php esc_html_e( 'Run optimization automatically daily', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'wp-weboptimizer' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render Advanced tab
     */
    private function render_advanced_tab() {
        $options = WP_WebOptimizer::get_option( 'advanced_settings', array() );
        ?>
        <form class="wpwo-settings-form" data-module="advanced_settings">
            <div class="wpwo-card">
                <h2><?php esc_html_e( 'Advanced Settings', 'wp-weboptimizer' ); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Disable Emojis', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="disable_emojis" value="1" <?php checked( $options['disable_emojis'] ?? true ); ?>>
                                <?php esc_html_e( 'Remove emoji scripts and styles', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Remove Query Strings', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="remove_query_strings" value="1" <?php checked( $options['remove_query_strings'] ?? true ); ?>>
                                <?php esc_html_e( 'Remove query strings from static resources', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Disable Embeds', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="disable_embeds" value="1" <?php checked( WP_WebOptimizer::get_option( 'third_party_optimizer.remove_embeds', true ) ); ?>>
                                <?php esc_html_e( 'Disable WordPress embeds', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Disable Pingbacks', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="disable_pingbacks" value="1" <?php checked( $options['disable_pingbacks'] ?? true ); ?>>
                                <?php esc_html_e( 'Disable XML-RPC pingbacks', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Remove Version', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="remove_version" value="1" <?php checked( $options['remove_version'] ?? true ); ?>>
                                <?php esc_html_e( 'Remove WordPress version from head', 'wp-weboptimizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Limit Post Revisions', 'wp-weboptimizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="limit_revisions" value="1" <?php checked( $options['limit_revisions'] ?? false ); ?>>
                                <?php esc_html_e( 'Limit post revisions to', 'wp-weboptimizer' ); ?>
                                <input type="number" name="revisions_limit" value="<?php echo absint( $options['revisions_limit'] ?? 5 ); ?>" min="0" max="50" style="width: 60px;">
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'wp-weboptimizer' ); ?>
                    </button>
                </p>
            </div>
        </form>
        <?php
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $module = isset( $_POST['module'] ) ? sanitize_text_field( $_POST['module'] ) : '';
        $settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
        
        if ( empty( $module ) ) {
            wp_send_json_error( array( 'message' => 'Invalid module' ) );
        }
        
        // Sanitize settings
        $sanitized = array();
        foreach ( $settings as $key => $value ) {
            $sanitized[ sanitize_key( $key ) ] = $this->sanitize_setting_value( $value );
        }
        
        // Update options
        $options = WP_WebOptimizer::get_option();
        $options[ $module ] = $sanitized;
        WP_WebOptimizer::update_option( $options );
        
        wp_send_json_success( array( 'message' => 'Settings saved successfully' ) );
    }
    
    /**
     * Sanitize setting value
     * 
     * @param mixed $value Value to sanitize
     * @return mixed Sanitized value
     */
    private function sanitize_setting_value( $value ) {
        if ( is_array( $value ) ) {
            return array_map( array( $this, 'sanitize_setting_value' ), $value );
        }
        
        if ( is_numeric( $value ) ) {
            return absint( $value );
        }
        
        if ( is_bool( $value ) || $value === 'true' || $value === 'false' ) {
            return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
        }
        
        return sanitize_text_field( $value );
    }
    
    /**
     * AJAX: Clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'wpwo_ajax_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        // Clear cache
        $cache_dir = WP_CONTENT_DIR . '/cache/wp-weboptimizer/';
        if ( file_exists( $cache_dir ) ) {
            $files = glob( $cache_dir . '*.html' );
            foreach ( $files as $file ) {
                @unlink( $file );
            }
        }
        
        wp_send_json_success( array( 'message' => 'Cache cleared successfully' ) );
    }
}

// Khởi tạo Admin UI
new WP_WebOptimizer_Admin_UI();
