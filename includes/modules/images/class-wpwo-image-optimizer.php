<?php
/**
 * Image Optimizer Module
 *
 * Handles WebP conversion and image optimization
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_Image_Optimizer {

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
		if ( ! empty( $this->options['image_webp_conversion'] ) ) {
			$loader->add_filter( 'wp_generate_attachment_metadata', $this, 'generate_webp_on_upload', 10, 2 );
			$loader->add_filter( 'the_content', $this, 'replace_images_with_webp', 999 );
		}

		// Add responsive images support
		$loader->add_filter( 'wp_calculate_image_srcset', $this, 'add_webp_to_srcset', 10, 5 );
	}

	/**
	 * Generate WebP version when image is uploaded.
	 *
	 * @param array $metadata      Image metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array Modified metadata.
	 */
	public function generate_webp_on_upload( $metadata, $attachment_id ) {
		if ( ! function_exists( 'imagewebp' ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return $metadata;
		}

		$this->create_webp_image( $file );

		// Process image sizes
		if ( ! empty( $metadata['sizes'] ) ) {
			$upload_dir = wp_upload_dir();
			$base_dir   = dirname( $file );

			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$size_file = $base_dir . '/' . $size_data['file'];
				if ( file_exists( $size_file ) ) {
					$this->create_webp_image( $size_file );
				}
			}
		}

		return $metadata;
	}

	/**
	 * Create WebP version of an image.
	 *
	 * @param string $file Image file path.
	 * @return bool Success status.
	 */
	private function create_webp_image( $file ) {
		$info = pathinfo( $file );
		$ext  = strtolower( $info['extension'] );

		// Skip if not a supported image format
		if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
			return false;
		}

		$webp_file = $info['dirname'] . '/' . $info['filename'] . '.webp';

		// Skip if WebP already exists
		if ( file_exists( $webp_file ) ) {
			return true;
		}

		// Create image resource
		$image = null;
		if ( 'jpg' === $ext || 'jpeg' === $ext ) {
			$image = imagecreatefromjpeg( $file );
		} elseif ( 'png' === $ext ) {
			$image = imagecreatefrompng( $file );
			imagepalettetotruecolor( $image );
			imagealphablending( $image, true );
			imagesavealpha( $image, true );
		}

		if ( ! $image ) {
			return false;
		}

		// Create WebP
		$result = imagewebp( $image, $webp_file, 80 );
		imagedestroy( $image );

		return $result;
	}

	/**
	 * Replace images with WebP versions in content.
	 *
	 * @param string $content The content.
	 * @return string Modified content.
	 */
	public function replace_images_with_webp( $content ) {
		if ( is_admin() || ! $this->browser_supports_webp() ) {
			return $content;
		}

		// Replace image sources with WebP versions
		$content = preg_replace_callback(
			'/<img([^>]*)src=["\']([^"\']*\.(jpg|jpeg|png))["\']([^>]*)>/i',
			array( $this, 'replace_image_source' ),
			$content
		);

		return $content;
	}

	/**
	 * Replace image source with WebP version.
	 *
	 * @param array $matches Regex matches.
	 * @return string Modified image tag.
	 */
	private function replace_image_source( $matches ) {
		$img_url  = $matches[2];
		$webp_url = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $img_url );

		// Check if WebP version exists
		$upload_dir = wp_upload_dir();
		$webp_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $webp_url );

		if ( file_exists( $webp_path ) ) {
			return str_replace( $img_url, $webp_url, $matches[0] );
		}

		return $matches[0];
	}

	/**
	 * Add WebP versions to srcset.
	 *
	 * @param array  $sources       Array of image sources.
	 * @param array  $size_array    Array of width and height values.
	 * @param string $image_src     The 'src' of the image.
	 * @param array  $image_meta    The image meta data.
	 * @param int    $attachment_id Image attachment ID.
	 * @return array Modified sources.
	 */
	public function add_webp_to_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! $this->browser_supports_webp() ) {
			return $sources;
		}

		foreach ( $sources as $width => $source ) {
			$webp_url = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $source['url'] );
			$upload_dir = wp_upload_dir();
			$webp_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $webp_url );

			if ( file_exists( $webp_path ) ) {
				$sources[ $width ]['url'] = $webp_url;
			}
		}

		return $sources;
	}

	/**
	 * Check if browser supports WebP.
	 *
	 * @return bool
	 */
	private function browser_supports_webp() {
		if ( ! isset( $_SERVER['HTTP_ACCEPT'] ) ) {
			return false;
		}

		return strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false;
	}
}
