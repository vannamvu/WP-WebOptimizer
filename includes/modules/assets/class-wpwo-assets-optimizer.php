<?php
/**
 * Assets Optimizer Module
 *
 * Handles CSS and JavaScript minification, combination, and optimization
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Assets_Optimizer {

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
		if ( ! empty( $this->options['assets_minify_css'] ) ) {
			$loader->add_filter( 'style_loader_tag', $this, 'optimize_css_output', 10, 4 );
		}

		if ( ! empty( $this->options['assets_minify_js'] ) ) {
			$loader->add_filter( 'script_loader_tag', $this, 'optimize_js_output', 10, 3 );
		}

		if ( ! empty( $this->options['assets_defer_js'] ) ) {
			$loader->add_filter( 'script_loader_tag', $this, 'defer_javascript', 10, 3 );
		}

		if ( ! empty( $this->options['assets_defer_css'] ) ) {
			$loader->add_filter( 'style_loader_tag', $this, 'defer_css', 10, 4 );
		}
	}

	/**
	 * Optimize CSS output.
	 *
	 * @param string $html   The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 * @return string Modified HTML.
	 */
	public function optimize_css_output( $html, $handle, $href, $media ) {
		// Skip optimization for admin area
		if ( is_admin() ) {
			return $html;
		}

		// Check if this CSS should be excluded
		if ( $this->is_excluded_css( $handle, $href ) ) {
			return $html;
		}

		return $html;
	}

	/**
	 * Optimize JavaScript output.
	 *
	 * @param string $tag    The script tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 * @return string Modified HTML.
	 */
	public function optimize_js_output( $tag, $handle, $src ) {
		// Skip optimization for admin area
		if ( is_admin() ) {
			return $tag;
		}

		// Check if this JS should be excluded
		if ( $this->is_excluded_js( $handle, $src ) ) {
			return $tag;
		}

		return $tag;
	}

	/**
	 * Defer JavaScript loading.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function defer_javascript( $tag, $handle, $src ) {
		// Skip optimization for admin area
		if ( is_admin() ) {
			return $tag;
		}

		// Don't defer jQuery and scripts that have dependencies
		if ( 'jquery' === $handle || 'jquery-core' === $handle || 'jquery-migrate' === $handle ) {
			return $tag;
		}

		// Check if this JS should be excluded
		if ( $this->is_excluded_js( $handle, $src ) ) {
			return $tag;
		}

		// Add defer attribute if not already present
		if ( strpos( $tag, 'defer' ) === false && strpos( $tag, 'async' ) === false ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}

		return $tag;
	}

	/**
	 * Defer CSS loading using media attribute trick.
	 *
	 * @param string $html   The link tag.
	 * @param string $handle The style handle.
	 * @param string $href   The stylesheet URL.
	 * @param string $media  The media attribute.
	 * @return string Modified link tag.
	 */
	public function defer_css( $html, $handle, $href, $media ) {
		// Skip optimization for admin area
		if ( is_admin() ) {
			return $html;
		}

		// Check if this CSS should be excluded
		if ( $this->is_excluded_css( $handle, $href ) ) {
			return $html;
		}

		// Use media="print" and onload to defer CSS
		$html = str_replace( "media='$media'", "media='print' onload=\"this.media='$media'\"", $html );

		return $html;
	}

	/**
	 * Check if CSS should be excluded from optimization.
	 *
	 * @param string $handle The style handle.
	 * @param string $href   The stylesheet URL.
	 * @return bool
	 */
	private function is_excluded_css( $handle, $href ) {
		$excluded = array();

		if ( ! empty( $this->options['excluded_css'] ) ) {
			$excluded = array_map( 'trim', explode( "\n", $this->options['excluded_css'] ) );
		}

		foreach ( $excluded as $pattern ) {
			if ( strpos( $handle, $pattern ) !== false || strpos( $href, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if JavaScript should be excluded from optimization.
	 *
	 * @param string $handle The script handle.
	 * @param string $src    The script URL.
	 * @return bool
	 */
	private function is_excluded_js( $handle, $src ) {
		$excluded = array();

		if ( ! empty( $this->options['excluded_js'] ) ) {
			$excluded = array_map( 'trim', explode( "\n", $this->options['excluded_js'] ) );
		}

		foreach ( $excluded as $pattern ) {
			if ( strpos( $handle, $pattern ) !== false || strpos( $src, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
