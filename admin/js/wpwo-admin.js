/**
 * WP WebOptimizer Pro - Admin Scripts
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Tab navigation
		$('.wpwo-tab-link').on('click', function(e) {
			e.preventDefault();
			var targetTab = $(this).attr('href');

			// Update active tab
			$('.wpwo-tab-link').removeClass('active');
			$(this).addClass('active');

			// Show target content
			$('.wpwo-tab-content').removeClass('active');
			$(targetTab).addClass('active');
		});

		// Auto-save functionality
		var saveTimeout;
		var $inputs = $('.wpwo-settings-table input, .wpwo-settings-table textarea, .wpwo-settings-table select');

		$inputs.on('change', function() {
			clearTimeout(saveTimeout);
			saveTimeout = setTimeout(function() {
				saveSettings();
			}, 1000);
		});

		// Manual save trigger (for immediate changes)
		function saveSettings() {
			var options = {};

			// Collect all settings
			$('.wpwo-settings-table input[type="checkbox"]').each(function() {
				options[$(this).attr('name')] = $(this).is(':checked') ? 'true' : 'false';
			});

			$('.wpwo-settings-table input[type="number"], .wpwo-settings-table input[type="text"]').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			$('.wpwo-settings-table textarea').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			$('.wpwo-settings-table select').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			// Send AJAX request
			$.ajax({
				url: wpwoAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpwo_save_options',
					nonce: wpwoAjax.nonce,
					options: options
				},
				success: function(response) {
					if (response.success) {
						showNotification(response.data.message, 'success');
					} else {
						showNotification(response.data.message, 'error');
					}
				},
				error: function() {
					showNotification('Lỗi khi lưu cài đặt', 'error');
				}
			});
		}

		// Show notification
		function showNotification(message, type) {
			var $notification = $('#wpwo-save-notification');
			var bgColor = type === 'success' ? '#46b450' : '#dc3232';

			$notification.css('background-color', bgColor);
			$notification.find('.wpwo-notification-text').text(message);
			$notification.addClass('show');

			setTimeout(function() {
				$notification.removeClass('show');
			}, 3000);
		}

		// Clear cache button
		$('.wpwo-clear-cache').on('click', function() {
			var $button = $(this);
			$button.prop('disabled', true).text('Đang xóa...');

			$.ajax({
				url: wpwoAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpwo_clear_cache',
					nonce: wpwoAjax.nonce
				},
				success: function(response) {
					$button.prop('disabled', false).text('Xóa Cache');
					if (response.success) {
						showNotification('Cache đã được xóa thành công!', 'success');
					} else {
						showNotification('Lỗi khi xóa cache', 'error');
					}
				},
				error: function() {
					$button.prop('disabled', false).text('Xóa Cache');
					showNotification('Lỗi khi xóa cache', 'error');
				}
			});
		});

		// Optimize database button
		$('.wpwo-optimize-db').on('click', function() {
			if (!confirm('Bạn có chắc chắn muốn tối ưu database? Nên tạo backup trước khi thực hiện.')) {
				return;
			}

			var $button = $(this);
			$button.prop('disabled', true).text('Đang tối ưu...');

			$.ajax({
				url: wpwoAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpwo_optimize_database',
					nonce: wpwoAjax.nonce
				},
				success: function(response) {
					$button.prop('disabled', false).text('Tối ưu Database');
					if (response.success) {
						showNotification('Database đã được tối ưu thành công!', 'success');
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						showNotification('Lỗi khi tối ưu database', 'error');
					}
				},
				error: function() {
					$button.prop('disabled', false).text('Tối ưu Database');
					showNotification('Lỗi khi tối ưu database', 'error');
				}
			});
		});

		// Export settings
		$('.wpwo-export-settings').on('click', function() {
			var options = {};

			$('.wpwo-settings-table input[type="checkbox"]').each(function() {
				options[$(this).attr('name')] = $(this).is(':checked');
			});

			$('.wpwo-settings-table input[type="number"], .wpwo-settings-table input[type="text"]').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			$('.wpwo-settings-table textarea').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			$('.wpwo-settings-table select').each(function() {
				options[$(this).attr('name')] = $(this).val();
			});

			var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(options, null, 2));
			var downloadAnchorNode = document.createElement('a');
			downloadAnchorNode.setAttribute("href", dataStr);
			downloadAnchorNode.setAttribute("download", "wpwo-settings-" + Date.now() + ".json");
			document.body.appendChild(downloadAnchorNode);
			downloadAnchorNode.click();
			downloadAnchorNode.remove();

			showNotification('Đã export settings thành công!', 'success');
		});

		// Import settings
		$('.wpwo-import-settings').on('click', function() {
			var input = document.createElement('input');
			input.type = 'file';
			input.accept = '.json';

			input.onchange = function(e) {
				var file = e.target.files[0];
				var reader = new FileReader();

				reader.onload = function(event) {
					try {
						var options = JSON.parse(event.target.result);

						// Apply imported settings
						for (var key in options) {
							var $input = $('.wpwo-settings-table [name="' + key + '"]');
							if ($input.length) {
								if ($input.attr('type') === 'checkbox') {
									$input.prop('checked', options[key]);
								} else {
									$input.val(options[key]);
								}
							}
						}

						// Save the imported settings
						saveSettings();
						showNotification('Đã import settings thành công!', 'success');
					} catch (error) {
						showNotification('Lỗi khi đọc file settings', 'error');
					}
				};

				reader.readAsText(file);
			};

			input.click();
		});

		// Performance mode selector
		$('select[name="performance_mode"]').on('change', function() {
			var mode = $(this).val();
			
			if (confirm('Thay đổi chế độ hiệu suất sẽ cập nhật tất cả các cài đặt tối ưu. Bạn có muốn tiếp tục?')) {
				$.ajax({
					url: wpwoAjax.ajaxurl,
					type: 'POST',
					data: {
						action: 'wpwo_apply_performance_mode',
						nonce: wpwoAjax.nonce,
						mode: mode
					},
					success: function(response) {
						if (response.success) {
							showNotification('Đã áp dụng chế độ hiệu suất thành công!', 'success');
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					}
				});
			}
		});

		// PageSpeed Test Now button
		$('.wpwo-test-now-btn').on('click', function() {
			var $button = $(this);
			var strategy = $('input[name="test_strategy"]:checked').val();
			var url = window.location.origin;

			// Show loading state
			$button.prop('disabled', true);
			$('.wpwo-test-spinner').show();
			$('.wpwo-test-results').hide();
			$('.wpwo-recommendations-section').hide();

			$.ajax({
				url: wpwoAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpwo_run_pagespeed_test',
					nonce: wpwoAjax.nonce,
					url: url,
					strategy: strategy
				},
				timeout: 90000, // 90 seconds timeout
				success: function(response) {
					$button.prop('disabled', false);
					$('.wpwo-test-spinner').hide();

					if (response.success) {
						displayTestResults(response.data.results);
						displayRecommendations(response.data.recommendations);
						loadTestHistory();
						showNotification('Test hoàn tất thành công!', 'success');
					} else {
						showNotification('Lỗi: ' + response.data.message, 'error');
					}
				},
				error: function(xhr, status, error) {
					$button.prop('disabled', false);
					$('.wpwo-test-spinner').hide();
					
					var errorMsg = 'Không thể kết nối với PageSpeed API. ';
					if (status === 'timeout') {
						errorMsg += 'Request timeout. Vui lòng thử lại.';
					} else {
						errorMsg += 'Vui lòng thử lại sau.';
					}
					showNotification(errorMsg, 'error');
				}
			});
		});

		// Display test results
		function displayTestResults(results) {
			if (!results) return;

			$('.wpwo-test-results').show();

			// Display scores
			if (results.scores) {
				$.each(results.scores, function(key, value) {
					var $scoreEl = $('[data-score="' + key + '"]');
					$scoreEl.text(value);
					$scoreEl.removeClass('good needs-improvement poor');
					if (value >= 90) {
						$scoreEl.addClass('good');
					} else if (value >= 50) {
						$scoreEl.addClass('needs-improvement');
					} else {
						$scoreEl.addClass('poor');
					}
				});
			}

			// Display metrics
			if (results.metrics) {
				// FCP
				if (results.metrics.fcp) {
					var fcpSec = (results.metrics.fcp / 1000).toFixed(2);
					$('[data-metric="fcp"]').text(fcpSec + 's')
						.removeClass('good needs-improvement poor')
						.addClass(results.metrics.fcp_rating || 'good');
				}

				// LCP
				if (results.metrics.lcp) {
					var lcpSec = (results.metrics.lcp / 1000).toFixed(2);
					$('[data-metric="lcp"]').text(lcpSec + 's')
						.removeClass('good needs-improvement poor')
						.addClass(results.metrics.lcp_rating || 'good');
				}

				// CLS
				if (results.metrics.cls !== undefined) {
					$('[data-metric="cls"]').text(results.metrics.cls.toFixed(3))
						.removeClass('good needs-improvement poor')
						.addClass(results.metrics.cls_rating || 'good');
				}

				// TBT
				if (results.metrics.tbt) {
					$('[data-metric="tbt"]').text(results.metrics.tbt + 'ms')
						.removeClass('good needs-improvement poor')
						.addClass(results.metrics.tbt_rating || 'good');
				}

				// Speed Index
				if (results.metrics.speed_index) {
					var siSec = (results.metrics.speed_index / 1000).toFixed(2);
					$('[data-metric="speed_index"]').text(siSec + 's')
						.removeClass('good needs-improvement poor')
						.addClass(results.metrics.speed_index_rating || 'good');
				}
			}
		}

		// Display recommendations
		function displayRecommendations(recommendations) {
			if (!recommendations || recommendations.length === 0) {
				$('.wpwo-recommendations-section').hide();
				return;
			}

			$('.wpwo-recommendations-section').show();
			var $list = $('.wpwo-recommendations-list');
			$list.empty();

			recommendations.forEach(function(rec) {
				var severityClass = 'severity-' + rec.severity;
				var severityLabel = rec.severity === 'high' ? 'Cao' : (rec.severity === 'medium' ? 'Trung bình' : 'Thấp');
				
				var solutionsHtml = '';
				if (rec.solutions && rec.solutions.length > 0) {
					solutionsHtml = '<ul class="wpwo-rec-solutions">';
					rec.solutions.forEach(function(solution) {
						solutionsHtml += '<li>' + solution + '</li>';
					});
					solutionsHtml += '</ul>';
				}

				var autoFixBtn = '';
				if (rec.auto_fix) {
					autoFixBtn = '<button type="button" class="button button-small wpwo-auto-fix-btn" data-fix-id="' + rec.auto_fix + '">✨ Tối ưu ngay</button>';
				}

				var html = '<div class="wpwo-recommendation-card ' + severityClass + '">' +
					'<div class="wpwo-rec-header">' +
					'<span class="wpwo-severity-badge">' + severityLabel + '</span>' +
					'<strong>' + rec.issue + '</strong>' +
					'</div>' +
					'<div class="wpwo-rec-description">' + rec.description + '</div>' +
					solutionsHtml +
					'<div class="wpwo-rec-actions">' + autoFixBtn + '</div>' +
					'</div>';

				$list.append(html);
			});

			// Bind auto-fix button handlers
			$('.wpwo-auto-fix-btn').on('click', function() {
				var $btn = $(this);
				var fixId = $btn.data('fix-id');

				if (!confirm('Bạn có chắc muốn áp dụng tối ưu này? Plugin sẽ tự động bật các tính năng cần thiết.')) {
					return;
				}

				$btn.prop('disabled', true).text('Đang áp dụng...');

				$.ajax({
					url: wpwoAjax.ajaxurl,
					type: 'POST',
					data: {
						action: 'wpwo_auto_fix',
						nonce: wpwoAjax.nonce,
						fix_id: fixId
					},
					success: function(response) {
						$btn.prop('disabled', false).text('✨ Tối ưu ngay');

						if (response.success) {
							showNotification(response.data.message, 'success');
							$btn.text('✅ Đã áp dụng').addClass('applied');
						} else {
							showNotification('Lỗi: ' + response.data.message, 'error');
						}
					},
					error: function() {
						$btn.prop('disabled', false).text('✨ Tối ưu ngay');
						showNotification('Lỗi khi áp dụng tối ưu', 'error');
					}
				});
			});
		}

		// Load test history
		function loadTestHistory() {
			// Get history from localStorage or show from backend
			$('.wpwo-test-history-section').show();
			// For now, we'll let PHP handle this on page load
			// Could be enhanced with AJAX endpoint to get history
		}
	});

})(jQuery);
