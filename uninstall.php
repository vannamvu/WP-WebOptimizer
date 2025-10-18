<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'wpwo_options' );

// Delete transients
delete_transient( 'wpwo_activation_redirect' );
delete_transient( 'wpwo_performance_data' );

// Clear all cache transients
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpwo_cache_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpwo_cache_%'" );

// Clear WordPress cache
if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
}
