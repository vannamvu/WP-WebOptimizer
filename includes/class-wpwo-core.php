<?php
/**
 * The core plugin class
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var WPWO_Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Initialize the core plugin.
	 */
	public function __construct() {
		$this->plugin_name = 'wp-weboptimizer';
		$this->version     = WPWO_VERSION;
		$this->options     = get_option( 'wpwo_options', array() );

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once WPWO_PLUGIN_DIR . 'includes/class-wpwo-loader.php';
		require_once WPWO_PLUGIN_DIR . 'includes/class-wpwo-admin.php';

		// Load optimization modules
		require_once WPWO_PLUGIN_DIR . 'includes/modules/assets/class-wpwo-assets-optimizer.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/lazyload/class-wpwo-lazyload.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/fonts/class-wpwo-font-optimizer.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/images/class-wpwo-image-optimizer.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/cache/class-wpwo-cache-manager.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/hints/class-wpwo-resource-hints.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/scripts/class-wpwo-scripts-optimizer.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/database/class-wpwo-database-optimizer.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/monitor/class-wpwo-performance-monitor.php';
		require_once WPWO_PLUGIN_DIR . 'includes/modules/settings/class-wpwo-advanced-settings.php';

		$this->loader = new WPWO_Loader();
	}

	/**
	 * Register admin hooks.
	 */
	private function define_admin_hooks() {
		$admin = new WPWO_Admin( $this->get_plugin_name(), $this->get_version(), $this->options );

		$this->loader->add_action( 'admin_menu', $admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_wpwo_save_options', $admin, 'ajax_save_options' );
		$this->loader->add_action( 'admin_init', $admin, 'check_activation_redirect' );
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks() {
		// Initialize optimization modules
		$assets_optimizer = new WPWO_Assets_Optimizer( $this->options );
		$lazyload         = new WPWO_Lazyload( $this->options );
		$font_optimizer   = new WPWO_Font_Optimizer( $this->options );
		$image_optimizer  = new WPWO_Image_Optimizer( $this->options );
		$cache_manager    = new WPWO_Cache_Manager( $this->options );
		$resource_hints   = new WPWO_Resource_Hints( $this->options );
		$scripts_optimizer = new WPWO_Scripts_Optimizer( $this->options );
		$performance_monitor = new WPWO_Performance_Monitor( $this->options );

		// Register hooks for each module
		$assets_optimizer->register_hooks( $this->loader );
		$lazyload->register_hooks( $this->loader );
		$font_optimizer->register_hooks( $this->loader );
		$image_optimizer->register_hooks( $this->loader );
		$cache_manager->register_hooks( $this->loader );
		$resource_hints->register_hooks( $this->loader );
		$scripts_optimizer->register_hooks( $this->loader );
		$performance_monitor->register_hooks( $this->loader );
	}

	/**
	 * Run the loader to execute all of the hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin.
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The version number of the plugin.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
