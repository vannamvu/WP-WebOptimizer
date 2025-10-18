<?php
/**
 * Image Optimizer Module
 * 
 * Tối ưu hình ảnh: WebP conversion, compression, responsive images
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

// Ngăn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Image Optimizer
 */
class WP_WebOptimizer_Image_Optimizer {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo hooks
     */
    private function init_hooks() {
        // Auto WebP conversion
        if ( WP_WebOptimizer::get_option( 'image_optimizer.auto_webp', true ) ) {
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_webp_on_upload' ), 10, 2 );
        }
        
        // Image compression
        if ( WP_WebOptimizer::get_option( 'image_optimizer.compression', true ) ) {
            add_filter( 'wp_editor_set_quality', array( $this, 'set_image_quality' ) );
        }
        
        // Responsive images
        if ( WP_WebOptimizer::get_option( 'image_optimizer.responsive', true ) ) {
            add_filter( 'the_content', array( $this, 'add_responsive_images' ), 999 );
        }
        
        // Remove image metadata
        if ( WP_WebOptimizer::get_option( 'image_optimizer.remove_metadata', true ) ) {
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'remove_image_metadata' ), 10, 2 );
        }
        
        // Add width/height attributes
        add_filter( 'the_content', array( $this, 'add_image_dimensions' ), 999 );
    }
    
    /**
     * Generate WebP version khi upload image
     * 
     * @param array $metadata Metadata của image
     * @param int $attachment_id ID của attachment
     * @return array Modified metadata
     */
    public function generate_webp_on_upload( $metadata, $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        
        if ( ! $file || ! file_exists( $file ) ) {
            return $metadata;
        }
        
        // Chỉ convert các định dạng JPG, PNG
        $mime_type = get_post_mime_type( $attachment_id );
        if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png' ), true ) ) {
            return $metadata;
        }
        
        // Convert sang WebP
        $this->convert_to_webp( $file );
        
        // Convert các thumbnail sizes
        if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
            $upload_dir = wp_upload_dir();
            $base_dir = dirname( $file );
            
            foreach ( $metadata['sizes'] as $size => $size_data ) {
                $thumb_path = $base_dir . '/' . $size_data['file'];
                if ( file_exists( $thumb_path ) ) {
                    $this->convert_to_webp( $thumb_path );
                }
            }
        }
        
        return $metadata;
    }
    
    /**
     * Convert image sang WebP
     * 
     * @param string $file_path Đường dẫn đến file
     * @return bool True nếu thành công
     */
    private function convert_to_webp( $file_path ) {
        if ( ! function_exists( 'imagewebp' ) ) {
            return false;
        }
        
        $file_info = pathinfo( $file_path );
        $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
        
        // Nếu file WebP đã tồn tại, skip
        if ( file_exists( $webp_path ) ) {
            return true;
        }
        
        $image = null;
        $mime_type = mime_content_type( $file_path );
        
        // Load image dựa vào mime type
        switch ( $mime_type ) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg( $file_path );
                break;
            case 'image/png':
                $image = imagecreatefrompng( $file_path );
                // Preserve transparency
                imagealphablending( $image, true );
                imagesavealpha( $image, true );
                break;
            default:
                return false;
        }
        
        if ( ! $image ) {
            return false;
        }
        
        // Tạo WebP với chất lượng 85%
        $quality = WP_WebOptimizer::get_option( 'image_optimizer.webp_quality', 85 );
        $result = imagewebp( $image, $webp_path, $quality );
        
        imagedestroy( $image );
        
        return $result;
    }
    
    /**
     * Set image quality cho compression
     * 
     * @param int $quality Chất lượng hiện tại
     * @return int Chất lượng mới
     */
    public function set_image_quality( $quality ) {
        return WP_WebOptimizer::get_option( 'image_optimizer.jpeg_quality', 85 );
    }
    
    /**
     * Thêm srcset cho responsive images
     * 
     * @param string $content HTML content
     * @return string Modified content
     */
    public function add_responsive_images( $content ) {
        if ( is_admin() || is_feed() ) {
            return $content;
        }
        
        // WordPress đã tự động thêm srcset, chỉ cần đảm bảo nó được apply
        return $content;
    }
    
    /**
     * Remove EXIF metadata từ images
     * 
     * @param array $metadata Metadata của image
     * @param int $attachment_id ID của attachment
     * @return array Modified metadata
     */
    public function remove_image_metadata( $metadata, $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        
        if ( ! $file || ! file_exists( $file ) ) {
            return $metadata;
        }
        
        // Chỉ xử lý JPEG
        $mime_type = get_post_mime_type( $attachment_id );
        if ( $mime_type !== 'image/jpeg' ) {
            return $metadata;
        }
        
        // Remove EXIF data bằng cách load và save lại image
        if ( function_exists( 'imagecreatefromjpeg' ) ) {
            $image = imagecreatefromjpeg( $file );
            if ( $image ) {
                imagejpeg( $image, $file, $this->set_image_quality( 85 ) );
                imagedestroy( $image );
            }
        }
        
        return $metadata;
    }
    
    /**
     * Thêm width và height attributes cho images
     * 
     * @param string $content HTML content
     * @return string Modified content
     */
    public function add_image_dimensions( $content ) {
        if ( is_admin() || is_feed() ) {
            return $content;
        }
        
        // Tìm images không có width/height
        if ( preg_match_all( '/<img[^>]+>/i', $content, $matches ) ) {
            foreach ( $matches[0] as $img_tag ) {
                // Skip nếu đã có width và height
                if ( strpos( $img_tag, 'width=' ) !== false && strpos( $img_tag, 'height=' ) !== false ) {
                    continue;
                }
                
                // Lấy src
                if ( preg_match( '/src=["\']([^"\']+)["\']/', $img_tag, $src_match ) ) {
                    $src = $src_match[1];
                    
                    // Lấy attachment ID từ URL
                    $attachment_id = attachment_url_to_postid( $src );
                    
                    if ( $attachment_id ) {
                        $metadata = wp_get_attachment_metadata( $attachment_id );
                        
                        if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
                            $new_img_tag = str_replace(
                                '<img',
                                sprintf(
                                    '<img width="%d" height="%d"',
                                    absint( $metadata['width'] ),
                                    absint( $metadata['height'] )
                                ),
                                $img_tag
                            );
                            
                            $content = str_replace( $img_tag, $new_img_tag, $content );
                        }
                    }
                }
            }
        }
        
        return $content;
    }
}

// Khởi tạo module
new WP_WebOptimizer_Image_Optimizer();
