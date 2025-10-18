<?php
/**
 * Resource Hints Module
 *
 * Handles DNS prefetch, preconnect, and prefetch hints
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Resource_Hints {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Initialize the module.
	 *
	 * @param array $options Plugin options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Register hooks for this module.
	 *
	 * @param WPWO_Loader $loader The loader instance.
	 */
	public function register_hooks( $loader ) {
		if ( ! empty( $this->options['hints_preconnect'] ) ) {
			$loader->add_action( 'wp_head', $this, 'add_preconnect_hints', 1 );
		}

		if ( ! empty( $this->options['hints_dns_prefetch'] ) ) {
			$loader->add_action( 'wp_head', $this, 'add_dns_prefetch_hints', 1 );
		}

		if ( ! empty( $this->options['hints_prefetch'] ) ) {
			$loader->add_action( 'wp_head', $this, 'add_prefetch_hints', 1 );
		}
	}

	/**
	 * Add preconnect hints.
	 */
	public function add_preconnect_hints() {
		if ( is_admin() ) {
			return;
		}

		$preconnect_urls = $this->get_preconnect_urls();

		foreach ( $preconnect_urls as $url ) {
			echo '<link rel="preconnect" href="' . esc_url( $url ) . '" crossorigin>' . "\n";
		}
	}

	/**
	 * Add DNS prefetch hints.
	 */
	public function add_dns_prefetch_hints() {
		if ( is_admin() ) {
			return;
		}

		$dns_prefetch_urls = $this->get_preconnect_urls();

		foreach ( $dns_prefetch_urls as $url ) {
			echo '<link rel="dns-prefetch" href="' . esc_url( $url ) . '">' . "\n";
		}
	}

	/**
	 * Add prefetch hints.
	 */
	public function add_prefetch_hints() {
		if ( is_admin() ) {
			return;
		}

		// Add prefetch for common resources
		$prefetch_resources = apply_filters( 'wpwo_prefetch_resources', array() );

		foreach ( $prefetch_resources as $resource ) {
			echo '<link rel="prefetch" href="' . esc_url( $resource ) . '">' . "\n";
		}
	}

	/**
	 * Get list of URLs to preconnect.
	 *
	 * @return array List of URLs.
	 */
	private function get_preconnect_urls() {
		$default_urls = array(
			'https://fonts.googleapis.com',
			'https://fonts.gstatic.com',
		);

		// Add custom URLs from settings
		if ( ! empty( $this->options['preconnect_urls'] ) ) {
			$custom_urls = array_map( 'trim', explode( "\n", $this->options['preconnect_urls'] ) );
			$custom_urls = array_filter( $custom_urls );
			$default_urls = array_merge( $default_urls, $custom_urls );
		}

		// Allow filtering
		$urls = apply_filters( 'wpwo_preconnect_urls', $default_urls );

		return array_unique( $urls );
	}
}
