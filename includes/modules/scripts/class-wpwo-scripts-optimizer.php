<?php
/**
 * Third-party Scripts Optimizer Module
 *
 * Handles optimization of third-party scripts like Google Analytics, Facebook Pixel, etc.
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Scripts_Optimizer {

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
		if ( ! empty( $this->options['scripts_optimize'] ) ) {
			$loader->add_filter( 'script_loader_tag', $this, 'optimize_third_party_scripts', 10, 3 );
		}

		if ( ! empty( $this->options['scripts_defer_third_party'] ) ) {
			$loader->add_filter( 'script_loader_tag', $this, 'defer_third_party_scripts', 10, 3 );
		}
	}

	/**
	 * Optimize third-party scripts.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function optimize_third_party_scripts( $tag, $handle, $src ) {
		if ( is_admin() ) {
			return $tag;
		}

		// List of third-party script domains
		$third_party_domains = array(
			'google-analytics.com',
			'googletagmanager.com',
			'facebook.net',
			'connect.facebook.net',
			'twitter.com',
			'platform.twitter.com',
			'instagram.com',
			'linkedin.com',
			'youtube.com',
			'vimeo.com',
			'doubleclick.net',
		);

		$is_third_party = false;
		foreach ( $third_party_domains as $domain ) {
			if ( strpos( $src, $domain ) !== false ) {
				$is_third_party = true;
				break;
			}
		}

		if ( $is_third_party ) {
			// Add async or defer attribute
			if ( strpos( $tag, 'async' ) === false && strpos( $tag, 'defer' ) === false ) {
				$tag = str_replace( ' src', ' async src', $tag );
			}
		}

		return $tag;
	}

	/**
	 * Defer third-party scripts.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function defer_third_party_scripts( $tag, $handle, $src ) {
		if ( is_admin() ) {
			return $tag;
		}

		// List of third-party script domains
		$third_party_domains = array(
			'google-analytics.com',
			'googletagmanager.com',
			'facebook.net',
			'connect.facebook.net',
		);

		$is_third_party = false;
		foreach ( $third_party_domains as $domain ) {
			if ( strpos( $src, $domain ) !== false ) {
				$is_third_party = true;
				break;
			}
		}

		if ( $is_third_party && strpos( $tag, 'defer' ) === false ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}

		return $tag;
	}
}
