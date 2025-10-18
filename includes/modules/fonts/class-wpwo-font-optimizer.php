<?php
/**
 * Font Optimizer Module
 *
 * Handles font optimization including font-display swap and preloading
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Font_Optimizer {

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
		if ( ! empty( $this->options['font_display_swap'] ) ) {
			$loader->add_filter( 'style_loader_tag', $this, 'add_font_display_swap', 10, 4 );
			$loader->add_action( 'wp_head', $this, 'add_font_display_css', 1 );
		}

		if ( ! empty( $this->options['font_preload'] ) ) {
			$loader->add_action( 'wp_head', $this, 'preload_fonts', 2 );
		}
	}

	/**
	 * Add font-display: swap to external font stylesheets.
	 *
	 * @param string $html   The link tag.
	 * @param string $handle The style handle.
	 * @param string $href   The stylesheet URL.
	 * @param string $media  The media attribute.
	 * @return string Modified link tag.
	 */
	public function add_font_display_swap( $html, $handle, $href, $media ) {
		// Check if this is a Google Fonts or font-related stylesheet
		if ( strpos( $href, 'fonts.googleapis.com' ) !== false || strpos( $href, 'fonts.gstatic.com' ) !== false ) {
			// Add display=swap parameter if not present
			if ( strpos( $href, 'display=' ) === false ) {
				$href = add_query_arg( 'display', 'swap', $href );
				$html = str_replace( "href='", "href='", $html );
				$html = preg_replace( "/href='[^']*'/", "href='$href'", $html );
			}
		}

		return $html;
	}

	/**
	 * Add CSS for font-display: swap to all @font-face rules.
	 */
	public function add_font_display_css() {
		if ( is_admin() ) {
			return;
		}

		echo "<style id='wpwo-font-display'>@font-face{font-display:swap;}</style>\n";
	}

	/**
	 * Preload important fonts.
	 */
	public function preload_fonts() {
		if ( is_admin() ) {
			return;
		}

		// Get theme's primary fonts (common patterns)
		$font_files = array();

		// Allow themes to register their fonts
		$font_files = apply_filters( 'wpwo_preload_fonts', $font_files );

		foreach ( $font_files as $font_file ) {
			if ( ! empty( $font_file ) ) {
				echo '<link rel="preload" href="' . esc_url( $font_file ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
			}
		}
	}
}
