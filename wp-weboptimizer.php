<?php
/**
 * Plugin Name: WP WebOptimizer Pro
 * Plugin URI: https://vuvannamviet.com/wp-weboptimizer
 * Description: Plugin tối ưu hiệu suất WordPress toàn diện - Cải thiện FCP, LCP, TBT, SI, CLS để đạt điểm tối đa trên PageSpeed Insights
 * Version: 2.0.0
 * Author: Vũ Văn Nam Việt
 * Author URI: https://vuvannamviet.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-weboptimizer
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * 
 * @package WP_WebOptimizer
 * @author Vũ Văn Nam Việt
 * @copyright 2024 Vũ Văn Nam Việt
 * @license GPL-2.0-or-later
 * 
 * Support: Hotline 0971.735.735
 * Update Server: https://pluginaz.com/wp-json/plugin68-license-manager/v1/update-check
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Định nghĩa các hằng số plugin
define( 'WP_WEBOPTIMIZER_VERSION', '2.0.0' );
define( 'WP_WEBOPTIMIZER_FILE', __FILE__ );
define( 'WP_WEBOPTIMIZER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_WEBOPTIMIZER_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_WEBOPTIMIZER_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Class chính của plugin WP WebOptimizer
 * 
 * @since 2.0.0
 */
class WP_WebOptimizer {
    
    /**
     * Instance duy nhất của class
     * 
     * @var WP_WebOptimizer
     */
    private static $instance = null;
    
    /**
     * Các module đã tải
     * 
     * @var array
     */
    private $modules = array();
    
    /**
     * Lấy instance của class (Singleton pattern)
     * 
     * @return WP_WebOptimizer
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo hooks
     */
    private function init_hooks() {
        // Hook khi plugin được kích hoạt
        register_activation_hook( WP_WEBOPTIMIZER_FILE, array( $this, 'activate' ) );
        
        // Hook khi plugin bị vô hiệu hóa
        register_deactivation_hook( WP_WEBOPTIMIZER_FILE, array( $this, 'deactivate' ) );
        
        // Khởi tạo plugin sau khi WordPress load xong
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        
        // Load text domain cho đa ngôn ngữ
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
        // Thêm settings link trong danh sách plugins
        add_filter( 'plugin_action_links_' . WP_WEBOPTIMIZER_BASENAME, array( $this, 'add_settings_link' ) );
    }
    
    /**
     * Khởi tạo plugin
     */
    public function init() {
        // Load các module
        $this->load_modules();
        
        // Load admin interface nếu đang ở admin
        if ( is_admin() ) {
            $this->load_admin();
        }
        
        // Hook để các module khởi tạo
        do_action( 'wp_weboptimizer_init' );
    }
    
    /**
     * Load các module của plugin
     */
    private function load_modules() {
        $modules = array(
            'assets-optimizer'      => 'modules/assets-optimizer.php',
            'lazy-load'            => 'modules/lazy-load.php',
            'font-optimizer'       => 'modules/font-optimizer.php',
            'image-optimizer'      => 'modules/image-optimizer.php',
            'cache-manager'        => 'modules/cache-manager.php',
            'resource-hints'       => 'modules/resource-hints.php',
            'third-party-optimizer' => 'modules/third-party-optimizer.php',
            'database-optimizer'   => 'modules/database-optimizer.php',
            'performance-monitor'  => 'modules/performance-monitor.php',
            'advanced-settings'    => 'modules/advanced-settings.php',
        );
        
        foreach ( $modules as $key => $file ) {
            $path = WP_WEBOPTIMIZER_PATH . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
                $this->modules[ $key ] = true;
            }
        }
        
        // Hook sau khi load modules
        do_action( 'wp_weboptimizer_modules_loaded', $this->modules );
    }
    
    /**
     * Load admin interface
     */
    private function load_admin() {
        $admin_file = WP_WEBOPTIMIZER_PATH . 'admin/admin-ui.php';
        if ( file_exists( $admin_file ) ) {
            require_once $admin_file;
        }
    }
    
    /**
     * Load text domain cho đa ngôn ngữ
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-weboptimizer',
            false,
            dirname( WP_WEBOPTIMIZER_BASENAME ) . '/languages'
        );
    }
    
    /**
     * Hàm chạy khi plugin được kích hoạt
     */
    public function activate() {
        // Tạo các option mặc định
        $default_options = array(
            'version' => WP_WEBOPTIMIZER_VERSION,
            'assets_optimizer' => array(
                'minify_css' => true,
                'minify_js' => true,
                'defer_js' => true,
                'critical_css' => false,
            ),
            'lazy_load' => array(
                'images' => true,
                'iframes' => true,
                'videos' => false,
            ),
            'font_optimizer' => array(
                'font_display_swap' => true,
                'preload_fonts' => false,
            ),
            'image_optimizer' => array(
                'auto_webp' => true,
                'compression' => true,
                'responsive' => true,
            ),
            'cache_manager' => array(
                'browser_cache' => true,
                'html_cache' => false,
            ),
            'database_optimizer' => array(
                'auto_optimize' => false,
            ),
            'advanced_settings' => array(
                'disable_embeds' => true,
                'remove_query_strings' => true,
                'disable_emojis' => true,
            ),
        );
        
        // Chỉ thêm options nếu chưa tồn tại
        if ( ! get_option( 'wp_weboptimizer_options' ) ) {
            add_option( 'wp_weboptimizer_options', $default_options );
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Hook khi plugin được activate
        do_action( 'wp_weboptimizer_activated' );
    }
    
    /**
     * Hàm chạy khi plugin bị vô hiệu hóa
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Hook khi plugin được deactivate
        do_action( 'wp_weboptimizer_deactivated' );
    }
    
    /**
     * Thêm link Settings vào plugin list
     * 
     * @param array $links Mảng các link hiện tại
     * @return array Mảng link đã được thêm
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=wp-weboptimizer' ),
            __( 'Settings', 'wp-weboptimizer' )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }
    
    /**
     * Lấy options của plugin
     * 
     * @param string $key Key của option cần lấy
     * @param mixed $default Giá trị mặc định nếu không tìm thấy
     * @return mixed Giá trị của option
     */
    public static function get_option( $key = '', $default = false ) {
        $options = get_option( 'wp_weboptimizer_options', array() );
        
        if ( empty( $key ) ) {
            return $options;
        }
        
        // Hỗ trợ nested key với dấu chấm
        $keys = explode( '.', $key );
        $value = $options;
        
        foreach ( $keys as $k ) {
            if ( isset( $value[ $k ] ) ) {
                $value = $value[ $k ];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Cập nhật options của plugin
     * 
     * @param string|array $key Key của option hoặc mảng options
     * @param mixed $value Giá trị cần cập nhật
     * @return bool True nếu thành công
     */
    public static function update_option( $key, $value = '' ) {
        $options = get_option( 'wp_weboptimizer_options', array() );
        
        if ( is_array( $key ) ) {
            $options = array_merge( $options, $key );
        } else {
            // Hỗ trợ nested key với dấu chấm
            $keys = explode( '.', $key );
            $temp = &$options;
            
            foreach ( $keys as $k ) {
                if ( ! isset( $temp[ $k ] ) ) {
                    $temp[ $k ] = array();
                }
                if ( $k === end( $keys ) ) {
                    $temp[ $k ] = $value;
                } else {
                    $temp = &$temp[ $k ];
                }
            }
        }
        
        return update_option( 'wp_weboptimizer_options', $options );
    }
}

/**
 * Hàm helper để lấy instance của plugin
 * 
 * @return WP_WebOptimizer
 */
function wp_weboptimizer() {
    return WP_WebOptimizer::instance();
}

// Khởi tạo plugin
wp_weboptimizer();
