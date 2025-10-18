<?php
/**
 * Plugin Name: WP WebOptimizer Pro
 * Plugin URI: https://vuvannamviet.com/wp-weboptimizer
 * Description: Plugin tối ưu hiệu suất WordPress chuyên nghiệp - Tối ưu toàn diện Core Web Vitals (FCP, LCP, TBT, SI, CLS) để đạt điểm tối đa trên PageSpeed Insights
 * Version: 1.0.0
 * Author: Vũ Văn Nam Việt
 * Author URI: https://vuvannamviet.com
 * Text Domain: wp-weboptimizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Hotline: 0971.735.735
 * 
 * @package WP_WebOptimizer
 * @author Vũ Văn Nam Việt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WPWO_VERSION', '1.0.0' );
define( 'WPWO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPWO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPWO_PLUGIN_FILE', __FILE__ );
define( 'WPWO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_weboptimizer() {
	require_once WPWO_PLUGIN_DIR . 'includes/class-wpwo-activator.php';
	WPWO_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_weboptimizer() {
	require_once WPWO_PLUGIN_DIR . 'includes/class-wpwo-deactivator.php';
	WPWO_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_weboptimizer' );
register_deactivation_hook( __FILE__, 'deactivate_wp_weboptimizer' );

/**
 * The core plugin class.
 */
require_once WPWO_PLUGIN_DIR . 'includes/class-wpwo-core.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_weboptimizer() {
	$plugin = new WPWO_Core();
	$plugin->run();
}

run_wp_weboptimizer();
