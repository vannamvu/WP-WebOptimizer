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
	});

})(jQuery);
