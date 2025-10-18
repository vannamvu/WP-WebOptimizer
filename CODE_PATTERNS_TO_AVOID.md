# Code Patterns to Avoid: External Update Systems

## Purpose

This document provides specific code patterns and implementations that must be avoided when developing WP WebOptimizer Pro (PR #2). These patterns relate to external update systems and license management that violate WordPress best practices.

## ❌ Prohibited Code Patterns

### 1. External Update Server Connections

**DON'T:**
```php
<?php
// BAD: External update checker
class WPWO_External_Updater {
    private $update_url = 'https://pluginaz.com/wp-json/plugin68-license-manager/v1/update-check';
    
    public function __construct() {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
    }
    
    public function check_update($transient) {
        // Connecting to external server - PROHIBITED
        $remote = wp_remote_post($this->update_url, array(
            'body' => array(
                'action' => 'check_update',
                'plugin_slug' => 'wp-weboptimizer',
                'version' => WPWO_VERSION,
                'site_url' => home_url(), // Privacy violation!
            )
        ));
        // ... processing code
    }
}
```

**DO:**
```php
<?php
// GOOD: Use WordPress native update system
/**
 * Plugin updates are handled by WordPress.org repository
 * or manual updates as documented in README.md
 * No external server connections required
 */
```

### 2. License Key Validation

**DON'T:**
```php
<?php
// BAD: License validation against external server
function wpwo_validate_license() {
    $license_key = get_option('wpwo_license_key');
    
    // External API call - PROHIBITED
    $response = wp_remote_post('https://pluginaz.com/api/validate-license', array(
        'body' => array(
            'license' => $license_key,
            'domain' => $_SERVER['HTTP_HOST'], // Privacy violation!
            'product' => 'wp-weboptimizer-pro'
        )
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $license_data = json_decode(wp_remote_retrieve_body($response));
    update_option('wpwo_license_status', $license_data->license);
    
    return $license_data->license === 'valid';
}
```

**DO:**
```php
<?php
// GOOD: No license validation needed for GPL plugins
/**
 * This is a GPL-licensed plugin distributed freely
 * No license keys or activation required
 * All features are available to all users
 */
```

### 3. Third-Party Update Checker Libraries

**DON'T:**
```php
<?php
// BAD: Including external update libraries
require_once plugin_dir_path(__FILE__) . 'lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://pluginaz.com/updates/wp-weboptimizer.json',
    __FILE__,
    'wp-weboptimizer'
);

// Even worse: Authentication with external server
$myUpdateChecker->setAuthentication('your-token-here');
```

**DO:**
```php
<?php
// GOOD: No third-party update libraries
/**
 * Plugin updates are managed through:
 * 1. WordPress.org repository (automatic updates)
 * 2. GitHub releases (manual download)
 * 3. Direct download from official sources
 */
```

### 4. Telemetry and Usage Tracking

**DON'T:**
```php
<?php
// BAD: Sending usage data without consent
function wpwo_send_telemetry() {
    $data = array(
        'site_url' => home_url(),
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version'),
        'plugin_version' => WPWO_VERSION,
        'active_plugins' => get_option('active_plugins'),
        'theme' => wp_get_theme()->get('Name')
    );
    
    // Unauthorized data transmission - PROHIBITED
    wp_remote_post('https://pluginaz.com/api/telemetry', array(
        'body' => $data,
        'blocking' => false // Even worse: hidden from user
    ));
}

add_action('admin_init', 'wpwo_send_telemetry');
```

**DO:**
```php
<?php
// GOOD: No telemetry without explicit opt-in
/**
 * If telemetry is needed:
 * 1. Make it completely optional
 * 2. Require explicit user consent
 * 3. Clearly document what data is collected
 * 4. Provide easy opt-out mechanism
 * 5. Anonymize all data
 */
```

### 5. Forced Update Notifications

**DON'T:**
```php
<?php
// BAD: Custom admin notices for updates
function wpwo_update_notice() {
    if (!wpwo_validate_license()) {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WP WebOptimizer Pro:</strong> Your license has expired. ';
        echo '<a href="https://pluginaz.com/renew">Renew now</a> to continue receiving updates.';
        echo '</p></div>';
    }
}
add_action('admin_notices', 'wpwo_update_notice');
```

**DO:**
```php
<?php
// GOOD: No forced update notifications
/**
 * Let WordPress handle update notifications naturally
 * through its built-in system
 */
```

### 6. Plugin Header with External Update URI

**DON'T:**
```php
<?php
/**
 * Plugin Name: WP WebOptimizer Pro
 * Plugin URI: https://pluginaz.com/products/wp-weboptimizer
 * Update URI: https://pluginaz.com/wp-json/plugin68-license-manager/v1/update-check
 * License: Proprietary
 * License Key: Required
 */
```

**DO:**
```php
<?php
/**
 * Plugin Name: WP WebOptimizer Pro
 * Plugin URI: https://github.com/vannamvu/WP-WebOptimizer
 * Description: WordPress Performance Optimization Plugin Pro
 * Version: 2.0.0
 * Author: Vũ Văn Nam Việt
 * Author URI: https://github.com/vannamvu
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-weboptimizer
 * Domain Path: /languages
 */
```

### 7. Admin Settings for License/Update Server

**DON'T:**
```php
<?php
// BAD: License activation page
function wpwo_license_settings_page() {
    ?>
    <div class="wrap">
        <h1>License Activation</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wpwo_license'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="wpwo_license_key">License Key</label></th>
                    <td>
                        <input type="text" id="wpwo_license_key" name="wpwo_license_key" 
                               value="<?php echo esc_attr(get_option('wpwo_license_key')); ?>" />
                        <button type="button" class="button" onclick="activateLicense()">
                            Activate License
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

function wpwo_add_license_menu() {
    add_options_page(
        'WP WebOptimizer License',
        'License',
        'manage_options',
        'wpwo-license',
        'wpwo_license_settings_page'
    );
}
add_action('admin_menu', 'wpwo_add_license_menu');
```

**DO:**
```php
<?php
// GOOD: No license activation pages
/**
 * GPL plugins don't need license activation
 * Focus on feature settings instead
 */
function wpwo_add_settings_menu() {
    add_options_page(
        'WP WebOptimizer Settings',
        'WP WebOptimizer',
        'manage_options',
        'wpwo-settings',
        'wpwo_settings_page'
    );
}
add_action('admin_menu', 'wpwo_add_settings_menu');
```

### 8. Cron Jobs for Update Checks

**DON'T:**
```php
<?php
// BAD: Scheduled update checks
function wpwo_schedule_update_check() {
    if (!wp_next_scheduled('wpwo_check_updates')) {
        wp_schedule_event(time(), 'daily', 'wpwo_check_updates');
    }
}
add_action('wp', 'wpwo_schedule_update_check');

function wpwo_cron_check_updates() {
    // External API call - PROHIBITED
    $response = wp_remote_get('https://pluginaz.com/api/check-version');
    // ... processing
}
add_action('wpwo_check_updates', 'wpwo_cron_check_updates');
```

**DO:**
```php
<?php
// GOOD: Let WordPress handle update checks
/**
 * WordPress automatically checks wordpress.org
 * for plugin updates twice daily
 * No custom cron jobs needed
 */
```

## ✅ Acceptable External Connections

These types of external connections ARE acceptable if properly documented:

### 1. User-Initiated Actions
```php
<?php
// OK: User clicks "Test Connection" button
function wpwo_test_cdn_connection() {
    check_admin_referer('wpwo_test_cdn', 'wpwo_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $cdn_url = sanitize_text_field($_POST['cdn_url']);
    
    // User explicitly requested this test
    $response = wp_remote_get($cdn_url, array('timeout' => 10));
    
    // Return result to user
    wp_send_json_success(array(
        'status' => !is_wp_error($response) ? 'connected' : 'failed'
    ));
}
```

### 2. Optional Third-Party Integrations
```php
<?php
// OK: Optional Google PageSpeed API (with user consent)
function wpwo_check_pagespeed() {
    // Only if user has provided API key
    $api_key = get_option('wpwo_google_api_key');
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key not configured');
    }
    
    // Clearly documented optional feature
    $url = add_query_arg(array(
        'url' => home_url(),
        'key' => $api_key
    ), 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed');
    
    return wp_remote_get($url);
}
```

## Summary

### Always Remember:

1. ❌ **Never** connect to external servers for plugin updates
2. ❌ **Never** require license keys or activation
3. ❌ **Never** send telemetry without explicit consent
4. ❌ **Never** use proprietary update mechanisms
5. ✅ **Always** use WordPress.org update system when possible
6. ✅ **Always** clearly document any external connections
7. ✅ **Always** make external features optional
8. ✅ **Always** respect user privacy

### Quick Test:

Ask yourself:
- Does this code connect to a server I control? → Probably wrong
- Does this code send site data anywhere? → Need explicit consent
- Would WordPress.org approve this? → If no, don't do it
- Is this feature truly optional? → If no, reconsider

---

**Document Version**: 1.0  
**Date**: October 18, 2025  
**Related PR**: #2 - Develop complete Pro version of WP WebOptimizer  
**Author**: GitHub Copilot Coding Agent
