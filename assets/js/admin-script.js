/**
 * Admin JavaScript
 * 
 * @package WP_WebOptimizer
 * @since 2.0.0
 */

(function($) {
    'use strict';
    
    var WPWO = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Settings form submit
            $('.wpwo-settings-form').on('submit', this.handleSettingsSubmit);
            
            // Clear cache button
            $('.wpwo-clear-cache').on('click', this.handleClearCache);
            
            // Database optimization buttons
            $('#wpwo-optimize-tables').on('click', this.handleOptimizeTables);
            $('#wpwo-clean-transients').on('click', this.handleCleanTransients);
            $('#wpwo-clean-revisions').on('click', this.handleCleanRevisions);
            $('#wpwo-clean-spam').on('click', this.handleCleanSpam);
        },
        
        /**
         * Handle settings form submit
         */
        handleSettingsSubmit: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var module = $form.data('module');
            
            if (!module) {
                WPWO.showNotice('error', 'Invalid module');
                return;
            }
            
            // Get form data
            var settings = {};
            $form.find('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                
                if (!name) return;
                
                if ($field.attr('type') === 'checkbox') {
                    settings[name] = $field.is(':checked');
                } else {
                    settings[name] = $field.val();
                }
            });
            
            // Save settings
            $button.prop('disabled', true).text(wpwoAdmin.strings.saving);
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_save_settings',
                    nonce: wpwoAdmin.nonce,
                    module: module,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', wpwoAdmin.strings.saved);
                    } else {
                        WPWO.showNotice('error', response.data.message || wpwoAdmin.strings.error);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', wpwoAdmin.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Save Settings');
                }
            });
        },
        
        /**
         * Handle clear cache
         */
        handleClearCache: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            if (!confirm('Are you sure you want to clear all cache?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_clear_cache',
                    nonce: wpwoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', response.data.message);
                    } else {
                        WPWO.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', 'Error clearing cache');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clear All Cache');
                }
            });
        },
        
        /**
         * Handle optimize tables
         */
        handleOptimizeTables: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            if (!confirm('Are you sure you want to optimize database tables?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Optimizing...');
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_optimize_tables',
                    nonce: wpwoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', response.data.message);
                    } else {
                        WPWO.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', 'Error optimizing tables');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Optimize Database Tables');
                }
            });
        },
        
        /**
         * Handle clean transients
         */
        handleCleanTransients: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            $button.prop('disabled', true).text('Cleaning...');
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_clean_transients',
                    nonce: wpwoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', response.data.message);
                    } else {
                        WPWO.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', 'Error cleaning transients');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clean Expired Transients');
                }
            });
        },
        
        /**
         * Handle clean revisions
         */
        handleCleanRevisions: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            if (!confirm('Are you sure you want to clean post revisions?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Cleaning...');
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_clean_revisions',
                    nonce: wpwoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', response.data.message);
                    } else {
                        WPWO.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', 'Error cleaning revisions');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clean Post Revisions');
                }
            });
        },
        
        /**
         * Handle clean spam
         */
        handleCleanSpam: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            if (!confirm('Are you sure you want to clean spam comments?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Cleaning...');
            
            $.ajax({
                url: wpwoAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpwo_clean_spam',
                    nonce: wpwoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPWO.showNotice('success', response.data.message);
                    } else {
                        WPWO.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WPWO.showNotice('error', 'Error cleaning spam');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clean Spam Comments');
                }
            });
        },
        
        /**
         * Show notice
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="wpwo-notice ' + type + '">' + message + '</div>');
            
            $('.wpwo-admin-content').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 3000);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        WPWO.init();
    });
    
})(jQuery);
