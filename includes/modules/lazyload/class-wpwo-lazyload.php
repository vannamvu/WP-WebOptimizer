<?php
/**
 * Lazy Load Module
 *
 * Handles lazy loading for images, iframes, and videos
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Lazyload {

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
		if ( ! empty( $this->options['lazyload_images'] ) ) {
			$loader->add_filter( 'the_content', $this, 'add_lazyload_to_images', 999 );
			$loader->add_filter( 'post_thumbnail_html', $this, 'add_lazyload_to_images', 999 );
			$loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_lazyload_scripts' );
		}

		if ( ! empty( $this->options['lazyload_iframes'] ) ) {
			$loader->add_filter( 'the_content', $this, 'add_lazyload_to_iframes', 999 );
		}
	}

	/**
	 * Enqueue lazy load scripts.
	 */
	public function enqueue_lazyload_scripts() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script(
			'wpwo-lazyload',
			WPWO_PLUGIN_URL . 'assets/js/lazyload.js',
			array(),
			WPWO_VERSION,
			true
		);
	}

	/**
	 * Add lazy loading to images.
	 *
	 * @param string $content The content.
	 * @return string Modified content.
	 */
	public function add_lazyload_to_images( $content ) {
		if ( is_admin() || is_feed() || is_preview() ) {
			return $content;
		}

		// Find all img tags
		$content = preg_replace_callback(
			'/<img([^>]*)>/i',
			array( $this, 'process_image_tag' ),
			$content
		);

		return $content;
	}

	/**
	 * Process individual image tag.
	 *
	 * @param array $matches Regex matches.
	 * @return string Modified image tag.
	 */
	private function process_image_tag( $matches ) {
		$img_tag = $matches[0];

		// Skip if already has loading attribute
		if ( strpos( $img_tag, 'loading=' ) !== false ) {
			return $img_tag;
		}

		// Skip specific images (logo, avatar, etc.)
		if ( strpos( $img_tag, 'skip-lazy' ) !== false || strpos( $img_tag, 'no-lazy' ) !== false ) {
			return $img_tag;
		}

		// Add loading="lazy" attribute
		$img_tag = str_replace( '<img', '<img loading="lazy"', $img_tag );

		return $img_tag;
	}

	/**
	 * Add lazy loading to iframes.
	 *
	 * @param string $content The content.
	 * @return string Modified content.
	 */
	public function add_lazyload_to_iframes( $content ) {
		if ( is_admin() || is_feed() || is_preview() ) {
			return $content;
		}

		// Find all iframe tags
		$content = preg_replace_callback(
			'/<iframe([^>]*)>/i',
			array( $this, 'process_iframe_tag' ),
			$content
		);

		return $content;
	}

	/**
	 * Process individual iframe tag.
	 *
	 * @param array $matches Regex matches.
	 * @return string Modified iframe tag.
	 */
	private function process_iframe_tag( $matches ) {
		$iframe_tag = $matches[0];

		// Skip if already has loading attribute
		if ( strpos( $iframe_tag, 'loading=' ) !== false ) {
			return $iframe_tag;
		}

		// Skip specific iframes
		if ( strpos( $iframe_tag, 'skip-lazy' ) !== false || strpos( $iframe_tag, 'no-lazy' ) !== false ) {
			return $iframe_tag;
		}

		// Add loading="lazy" attribute
		$iframe_tag = str_replace( '<iframe', '<iframe loading="lazy"', $iframe_tag );

		return $iframe_tag;
	}
}
