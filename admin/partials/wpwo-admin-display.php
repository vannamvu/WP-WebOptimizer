<?php
/**
 * Admin display template
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$options = get_option( 'wpwo_options', array() );
$db_info = WPWO_Database_Optimizer::get_database_info();
$perf_stats = WPWO_Performance_Monitor::get_performance_stats();
?>

<div class="wrap wpwo-admin-wrap">
	<div class="wpwo-header">
		<h1>
			<span class="dashicons dashicons-performance"></span>
			<?php echo esc_html__( 'WP WebOptimizer Pro', 'wp-weboptimizer' ); ?>
		</h1>
		<p class="wpwo-subtitle"><?php echo esc_html__( 'Tối ưu toàn diện Core Web Vitals để đạt điểm tối đa trên PageSpeed Insights', 'wp-weboptimizer' ); ?></p>
		<div class="wpwo-author-info">
			<?php echo esc_html__( 'Tác giả:', 'wp-weboptimizer' ); ?> <strong>Vũ Văn Nam Việt</strong> | 
			<a href="https://vuvannamviet.com" target="_blank">vuvannamviet.com</a> | 
			<?php echo esc_html__( 'Hotline:', 'wp-weboptimizer' ); ?> <strong>0971.735.735</strong>
		</div>
	</div>

	<div class="wpwo-save-notification" id="wpwo-save-notification">
		<span class="dashicons dashicons-yes-alt"></span>
		<span class="wpwo-notification-text"></span>
	</div>

	<div class="wpwo-tabs-wrapper">
		<nav class="wpwo-tabs-nav">
			<a href="#tab-dashboard" class="wpwo-tab-link active"><?php echo esc_html__( 'Tổng quan', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-assets" class="wpwo-tab-link"><?php echo esc_html__( 'Assets', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-images" class="wpwo-tab-link"><?php echo esc_html__( 'Hình ảnh', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-fonts" class="wpwo-tab-link"><?php echo esc_html__( 'Font chữ', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-cache" class="wpwo-tab-link"><?php echo esc_html__( 'Cache', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-scripts" class="wpwo-tab-link"><?php echo esc_html__( 'Scripts', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-database" class="wpwo-tab-link"><?php echo esc_html__( 'Database', 'wp-weboptimizer' ); ?></a>
			<a href="#tab-advanced" class="wpwo-tab-link"><?php echo esc_html__( 'Nâng cao', 'wp-weboptimizer' ); ?></a>
		</nav>

		<div class="wpwo-tabs-content">
			<!-- Dashboard Tab -->
			<div id="tab-dashboard" class="wpwo-tab-content active">
				<h2><?php echo esc_html__( 'Tổng quan hiệu suất', 'wp-weboptimizer' ); ?></h2>
				
				<!-- PageSpeed Test Section -->
				<div class="wpwo-pagespeed-section">
					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'PageSpeed Insights Test', 'wp-weboptimizer' ); ?></h3>
						<p><?php echo esc_html__( 'Kiểm tra hiệu suất website trực tiếp với Google PageSpeed Insights', 'wp-weboptimizer' ); ?></p>
						
						<div class="wpwo-test-controls">
							<div class="wpwo-test-strategy">
								<label>
									<input type="radio" name="test_strategy" value="mobile" checked>
									<?php echo esc_html__( 'Mobile', 'wp-weboptimizer' ); ?>
								</label>
								<label>
									<input type="radio" name="test_strategy" value="desktop">
									<?php echo esc_html__( 'Desktop', 'wp-weboptimizer' ); ?>
								</label>
							</div>
							<button type="button" class="button button-primary wpwo-test-now-btn">
								<?php echo esc_html__( 'Test Now', 'wp-weboptimizer' ); ?>
							</button>
							<span class="wpwo-test-spinner" style="display:none;">
								<span class="spinner is-active"></span>
								<?php echo esc_html__( 'Đang test... (30-60s)', 'wp-weboptimizer' ); ?>
							</span>
						</div>

						<div class="wpwo-test-results" style="display:none;">
							<div class="wpwo-scores-grid">
								<div class="wpwo-score-card">
									<div class="wpwo-score-label"><?php echo esc_html__( 'Performance', 'wp-weboptimizer' ); ?></div>
									<div class="wpwo-score-value" data-score="performance">-</div>
								</div>
								<div class="wpwo-score-card">
									<div class="wpwo-score-label"><?php echo esc_html__( 'Accessibility', 'wp-weboptimizer' ); ?></div>
									<div class="wpwo-score-value" data-score="accessibility">-</div>
								</div>
								<div class="wpwo-score-card">
									<div class="wpwo-score-label"><?php echo esc_html__( 'Best Practices', 'wp-weboptimizer' ); ?></div>
									<div class="wpwo-score-value" data-score="best-practices">-</div>
								</div>
								<div class="wpwo-score-card">
									<div class="wpwo-score-label"><?php echo esc_html__( 'SEO', 'wp-weboptimizer' ); ?></div>
									<div class="wpwo-score-value" data-score="seo">-</div>
								</div>
							</div>

							<h4><?php echo esc_html__( 'Core Web Vitals (PageSpeed)', 'wp-weboptimizer' ); ?></h4>
							<div class="wpwo-metrics-grid">
								<div class="wpwo-metric-card">
									<div class="wpwo-metric-name">FCP</div>
									<div class="wpwo-metric-value-large" data-metric="fcp">-</div>
									<div class="wpwo-metric-description"><?php echo esc_html__( 'First Contentful Paint', 'wp-weboptimizer' ); ?></div>
								</div>
								<div class="wpwo-metric-card">
									<div class="wpwo-metric-name">LCP</div>
									<div class="wpwo-metric-value-large" data-metric="lcp">-</div>
									<div class="wpwo-metric-description"><?php echo esc_html__( 'Largest Contentful Paint', 'wp-weboptimizer' ); ?></div>
								</div>
								<div class="wpwo-metric-card">
									<div class="wpwo-metric-name">CLS</div>
									<div class="wpwo-metric-value-large" data-metric="cls">-</div>
									<div class="wpwo-metric-description"><?php echo esc_html__( 'Cumulative Layout Shift', 'wp-weboptimizer' ); ?></div>
								</div>
								<div class="wpwo-metric-card">
									<div class="wpwo-metric-name">TBT</div>
									<div class="wpwo-metric-value-large" data-metric="tbt">-</div>
									<div class="wpwo-metric-description"><?php echo esc_html__( 'Total Blocking Time', 'wp-weboptimizer' ); ?></div>
								</div>
								<div class="wpwo-metric-card">
									<div class="wpwo-metric-name">SI</div>
									<div class="wpwo-metric-value-large" data-metric="speed_index">-</div>
									<div class="wpwo-metric-description"><?php echo esc_html__( 'Speed Index', 'wp-weboptimizer' ); ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Core Web Vitals (Real User Data) -->
				<div class="wpwo-dashboard-grid">
					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'Core Web Vitals (Người dùng thực)', 'wp-weboptimizer' ); ?></h3>
						<p class="wpwo-card-description"><?php echo esc_html__( 'Dữ liệu thu thập từ người dùng thật truy cập website', 'wp-weboptimizer' ); ?></p>
						<?php if ( $perf_stats['count'] > 0 ) : ?>
							<div class="wpwo-metric">
								<div class="wpwo-metric-label">FCP (First Contentful Paint)</div>
								<div class="wpwo-metric-value <?php echo $perf_stats['fcp_avg'] > 0 && $perf_stats['fcp_avg'] < 1800 ? 'good' : ( $perf_stats['fcp_avg'] < 3000 ? 'needs-improvement' : 'poor' ); ?>">
									<?php echo $perf_stats['fcp_avg'] > 0 ? number_format( $perf_stats['fcp_avg'], 0 ) . 'ms' : 'N/A'; ?>
								</div>
							</div>
							<div class="wpwo-metric">
								<div class="wpwo-metric-label">LCP (Largest Contentful Paint)</div>
								<div class="wpwo-metric-value <?php echo $perf_stats['lcp_avg'] > 0 && $perf_stats['lcp_avg'] < 2500 ? 'good' : ( $perf_stats['lcp_avg'] < 4000 ? 'needs-improvement' : 'poor' ); ?>">
									<?php echo $perf_stats['lcp_avg'] > 0 ? number_format( $perf_stats['lcp_avg'], 0 ) . 'ms' : 'N/A'; ?>
								</div>
							</div>
							<div class="wpwo-metric">
								<div class="wpwo-metric-label">CLS (Cumulative Layout Shift)</div>
								<div class="wpwo-metric-value <?php echo $perf_stats['cls_avg'] >= 0 && $perf_stats['cls_avg'] < 0.1 ? 'good' : ( $perf_stats['cls_avg'] < 0.25 ? 'needs-improvement' : 'poor' ); ?>">
									<?php echo $perf_stats['cls_avg'] >= 0 ? number_format( $perf_stats['cls_avg'], 3 ) : 'N/A'; ?>
								</div>
							</div>
							<p class="wpwo-metric-info">
								<small><?php echo sprintf( esc_html__( 'Dữ liệu từ %d lượt truy cập', 'wp-weboptimizer' ), $perf_stats['count'] ); ?></small>
							</p>
						<?php else : ?>
							<div class="wpwo-notice wpwo-notice-info">
								<p><?php echo esc_html__( '📊 Chưa có dữ liệu. Metrics sẽ được thu thập tự động khi có người dùng truy cập website (không tính admin). Hãy bật "Performance Monitor" trong tab Nâng cao.', 'wp-weboptimizer' ); ?></p>
							</div>
						<?php endif; ?>
					</div>

					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'Thông tin Database', 'wp-weboptimizer' ); ?></h3>
						<p><strong><?php echo esc_html__( 'Kích thước:', 'wp-weboptimizer' ); ?></strong> <?php echo esc_html( $db_info['size'] ); ?></p>
						<p><strong><?php echo esc_html__( 'Bài viết:', 'wp-weboptimizer' ); ?></strong> <?php echo esc_html( $db_info['posts'] ); ?></p>
						<p><strong><?php echo esc_html__( 'Bình luận:', 'wp-weboptimizer' ); ?></strong> <?php echo esc_html( $db_info['comments'] ); ?></p>
						<p><strong><?php echo esc_html__( 'Revisions:', 'wp-weboptimizer' ); ?></strong> <?php echo esc_html( $db_info['revisions'] ); ?></p>
					</div>

					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'Hành động nhanh', 'wp-weboptimizer' ); ?></h3>
						<button type="button" class="button button-primary wpwo-clear-cache"><?php echo esc_html__( 'Xóa Cache', 'wp-weboptimizer' ); ?></button>
						<button type="button" class="button button-secondary wpwo-optimize-db"><?php echo esc_html__( 'Tối ưu Database', 'wp-weboptimizer' ); ?></button>
					</div>
				</div>

				<!-- Recommendations Section -->
				<div class="wpwo-recommendations-section" style="display:none;">
					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'Gợi ý tối ưu', 'wp-weboptimizer' ); ?></h3>
						<div class="wpwo-recommendations-list"></div>
					</div>
				</div>

				<!-- Test History Section -->
				<div class="wpwo-test-history-section" style="display:none;">
					<div class="wpwo-card">
						<h3><?php echo esc_html__( 'Lịch sử Test', 'wp-weboptimizer' ); ?></h3>
						<div class="wpwo-history-table-container">
							<table class="wpwo-history-table">
								<thead>
									<tr>
										<th><?php echo esc_html__( 'Thời gian', 'wp-weboptimizer' ); ?></th>
										<th><?php echo esc_html__( 'Strategy', 'wp-weboptimizer' ); ?></th>
										<th><?php echo esc_html__( 'Performance', 'wp-weboptimizer' ); ?></th>
										<th><?php echo esc_html__( 'FCP', 'wp-weboptimizer' ); ?></th>
										<th><?php echo esc_html__( 'LCP', 'wp-weboptimizer' ); ?></th>
										<th><?php echo esc_html__( 'CLS', 'wp-weboptimizer' ); ?></th>
									</tr>
								</thead>
								<tbody class="wpwo-history-body"></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<!-- Assets Tab -->
			<div id="tab-assets" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Tối ưu Assets (CSS & JavaScript)', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Minify CSS', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="assets_minify_css" <?php checked( ! empty( $options['assets_minify_css'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Tự động minify các file CSS', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Minify JavaScript', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="assets_minify_js" <?php checked( ! empty( $options['assets_minify_js'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Tự động minify các file JavaScript', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Defer JavaScript', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="assets_defer_js" <?php checked( ! empty( $options['assets_defer_js'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Defer loading JavaScript để cải thiện FCP', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Defer CSS', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="assets_defer_css" <?php checked( ! empty( $options['assets_defer_css'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Defer loading CSS không quan trọng', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Loại trừ CSS', 'wp-weboptimizer' ); ?></th>
						<td>
							<textarea name="excluded_css" rows="4" class="large-text code"><?php echo esc_textarea( ! empty( $options['excluded_css'] ) ? $options['excluded_css'] : '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Mỗi dòng một handle hoặc URL cần loại trừ', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Loại trừ JavaScript', 'wp-weboptimizer' ); ?></th>
						<td>
							<textarea name="excluded_js" rows="4" class="large-text code"><?php echo esc_textarea( ! empty( $options['excluded_js'] ) ? $options['excluded_js'] : '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Mỗi dòng một handle hoặc URL cần loại trừ', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Images Tab -->
			<div id="tab-images" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Tối ưu hình ảnh', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Lazy Load Images', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="lazyload_images" <?php checked( ! empty( $options['lazyload_images'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Lazy load hình ảnh để cải thiện LCP', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Lazy Load Iframes', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="lazyload_iframes" <?php checked( ! empty( $options['lazyload_iframes'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Lazy load iframes (YouTube, Google Maps, v.v.)', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'WebP Conversion', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="image_webp_conversion" <?php checked( ! empty( $options['image_webp_conversion'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Tự động chuyển đổi hình ảnh sang WebP', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Fonts Tab -->
			<div id="tab-fonts" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Tối ưu Font chữ', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Font Display Swap', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="font_display_swap" <?php checked( ! empty( $options['font_display_swap'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Thêm font-display: swap để hiển thị text nhanh hơn', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Preload Fonts', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="font_preload" <?php checked( ! empty( $options['font_preload'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Preload các font chữ quan trọng', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Cache Tab -->
			<div id="tab-cache" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Quản lý Cache', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Bật Cache', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="cache_enable" <?php checked( ! empty( $options['cache_enable'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Bật page cache để tăng tốc website', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'GZIP Compression', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="cache_gzip" <?php checked( ! empty( $options['cache_gzip'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Bật nén GZIP để giảm kích thước file', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Thời gian Cache (giây)', 'wp-weboptimizer' ); ?></th>
						<td>
							<input type="number" name="cache_lifetime" value="<?php echo esc_attr( ! empty( $options['cache_lifetime'] ) ? $options['cache_lifetime'] : 3600 ); ?>" min="60" max="604800" class="regular-text">
							<p class="description"><?php echo esc_html__( 'Thời gian lưu cache (mặc định: 3600 giây = 1 giờ)', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Scripts Tab -->
			<div id="tab-scripts" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Tối ưu Third-party Scripts', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Tối ưu Scripts', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="scripts_optimize" <?php checked( ! empty( $options['scripts_optimize'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Tối ưu loading các script từ bên thứ 3', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Defer Third-party Scripts', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="scripts_defer_third_party" <?php checked( ! empty( $options['scripts_defer_third_party'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Defer loading scripts từ Google Analytics, Facebook, v.v.', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Resource Hints', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="hints_preconnect" <?php checked( ! empty( $options['hints_preconnect'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Thêm preconnect hints cho external resources', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Preconnect URLs', 'wp-weboptimizer' ); ?></th>
						<td>
							<textarea name="preconnect_urls" rows="4" class="large-text code"><?php echo esc_textarea( ! empty( $options['preconnect_urls'] ) ? $options['preconnect_urls'] : '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Mỗi dòng một URL cần preconnect', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Database Tab -->
			<div id="tab-database" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Tối ưu Database', 'wp-weboptimizer' ); ?></h2>
				
				<div class="wpwo-notice wpwo-notice-warning">
					<p><?php echo esc_html__( '⚠️ Cảnh báo: Tạo backup trước khi tối ưu database!', 'wp-weboptimizer' ); ?></p>
				</div>

				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Tự động tối ưu', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="database_auto_optimize" <?php checked( ! empty( $options['database_auto_optimize'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Tự động tối ưu database hàng tuần', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>

				<div class="wpwo-database-info">
					<h3><?php echo esc_html__( 'Thông tin hiện tại', 'wp-weboptimizer' ); ?></h3>
					<ul>
						<li><?php echo esc_html__( 'Revisions:', 'wp-weboptimizer' ); ?> <strong><?php echo esc_html( $db_info['revisions'] ); ?></strong></li>
						<li><?php echo esc_html__( 'Auto-drafts:', 'wp-weboptimizer' ); ?> <strong><?php echo esc_html( $db_info['auto_drafts'] ); ?></strong></li>
						<li><?php echo esc_html__( 'Trashed posts:', 'wp-weboptimizer' ); ?> <strong><?php echo esc_html( $db_info['trashed_posts'] ); ?></strong></li>
						<li><?php echo esc_html__( 'Spam comments:', 'wp-weboptimizer' ); ?> <strong><?php echo esc_html( $db_info['spam_comments'] ); ?></strong></li>
						<li><?php echo esc_html__( 'Trashed comments:', 'wp-weboptimizer' ); ?> <strong><?php echo esc_html( $db_info['trashed_comments'] ); ?></strong></li>
					</ul>
				</div>
			</div>

			<!-- Advanced Tab -->
			<div id="tab-advanced" class="wpwo-tab-content">
				<h2><?php echo esc_html__( 'Cài đặt nâng cao', 'wp-weboptimizer' ); ?></h2>
				
				<table class="form-table wpwo-settings-table">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Chế độ hiệu suất', 'wp-weboptimizer' ); ?></th>
						<td>
							<?php $performance_modes = WPWO_Advanced_Settings::get_performance_modes(); ?>
							<select name="performance_mode" class="regular-text">
								<?php foreach ( $performance_modes as $mode => $data ) : ?>
									<option value="<?php echo esc_attr( $mode ); ?>" <?php selected( ! empty( $options['performance_mode'] ) ? $options['performance_mode'] : 'balanced', $mode ); ?>>
										<?php echo esc_html( $data['label'] ); ?> - <?php echo esc_html( $data['description'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php echo esc_html__( 'Chọn chế độ tối ưu phù hợp với website của bạn', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Performance Monitor', 'wp-weboptimizer' ); ?></th>
						<td>
							<label class="wpwo-switch">
								<input type="checkbox" name="monitor_enable" <?php checked( ! empty( $options['monitor_enable'] ) ); ?>>
								<span class="wpwo-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Theo dõi Core Web Vitals metrics', 'wp-weboptimizer' ); ?></p>
						</td>
					</tr>
				</table>

				<div class="wpwo-export-import">
					<h3><?php echo esc_html__( 'Export/Import Settings', 'wp-weboptimizer' ); ?></h3>
					<button type="button" class="button wpwo-export-settings"><?php echo esc_html__( 'Export Settings', 'wp-weboptimizer' ); ?></button>
					<button type="button" class="button wpwo-import-settings"><?php echo esc_html__( 'Import Settings', 'wp-weboptimizer' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
