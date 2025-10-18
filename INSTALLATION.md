# WP WebOptimizer Pro - Installation Guide

## System Requirements

### Minimum Requirements
- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **MySQL**: 5.6 or higher (or MariaDB 10.1+)
- **Memory**: 64MB minimum (128MB recommended)
- **Disk Space**: 5MB

### Recommended Requirements
- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.3+)
- **Memory**: 256MB or higher
- **Disk Space**: 10MB

### Required PHP Extensions
- **GD Library** (for WebP conversion)
- **Zlib** (for GZIP compression)
- **MySQLi** or **PDO_MySQL**

## Installation Methods

### Method 1: WordPress Admin (Recommended)

1. Download the plugin ZIP file
2. Log in to your WordPress Admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** button at the top
5. Click **Choose File** and select the downloaded ZIP file
6. Click **Install Now**
7. After installation completes, click **Activate Plugin**
8. You will be redirected to the plugin settings page

### Method 2: FTP/SFTP Upload

1. Download and extract the plugin ZIP file
2. Connect to your server via FTP/SFTP
3. Navigate to `/wp-content/plugins/`
4. Upload the extracted `wp-weboptimizer` folder
5. Log in to WordPress Admin
6. Navigate to **Plugins**
7. Find **WP WebOptimizer Pro** and click **Activate**

### Method 3: cPanel File Manager

1. Download the plugin ZIP file
2. Log in to your cPanel
3. Open **File Manager**
4. Navigate to `public_html/wp-content/plugins/`
5. Click **Upload** and select the ZIP file
6. After upload, right-click the ZIP file and select **Extract**
7. Delete the ZIP file after extraction
8. Log in to WordPress Admin and activate the plugin

### Method 4: WP-CLI (For Developers)

```bash
# Navigate to WordPress root directory
cd /path/to/wordpress

# Copy plugin folder
cp -r /path/to/wp-weboptimizer wp-content/plugins/

# Activate plugin
wp plugin activate wp-weboptimizer

# Check plugin status
wp plugin list
```

## Post-Installation Setup

### Step 1: Initial Configuration

After activation, you'll be redirected to the settings page. Follow these steps:

1. **Choose Performance Mode**
   - Go to the **Advanced** tab
   - Select one of the performance modes:
     - **Safe**: Basic optimizations, maximum compatibility
     - **Balanced**: Recommended for most websites
     - **Aggressive**: Maximum performance (test thoroughly)

2. **Enable Core Features**
   - **Assets Tab**: Enable minify and defer for CSS/JS
   - **Images Tab**: Enable lazy load and WebP conversion
   - **Fonts Tab**: Enable font-display swap
   - **Cache Tab**: Enable page cache (if not using hosting cache)

### Step 2: Test Your Website

1. **Browse Your Site**
   - Visit all important pages
   - Check homepage, posts, pages
   - Test forms and interactive elements
   - Verify e-commerce functionality (if applicable)

2. **Check Console**
   - Open browser Developer Tools (F12)
   - Look for JavaScript errors
   - Fix any issues by excluding problematic files

3. **Test Mobile**
   - View site on mobile devices
   - Check responsive behavior
   - Verify touch interactions work

### Step 3: Measure Performance

1. **Before Optimization**
   - Test with Google PageSpeed Insights
   - Record baseline scores
   - Note Core Web Vitals metrics

2. **After Optimization**
   - Clear all caches
   - Test again with PageSpeed Insights
   - Compare improvements
   - View metrics in plugin Dashboard tab

### Step 4: Fine-tune Settings

Based on your test results:

1. **If you see JavaScript errors**:
   - Add problematic scripts to exclusion list
   - Try different defer/async strategies
   - Contact support if needed

2. **If cache issues occur**:
   - Reduce cache lifetime
   - Clear cache after major updates
   - Exclude user-specific pages

3. **If WebP conversion fails**:
   - Verify GD library is installed
   - Check file permissions
   - Manually convert images if needed

## Upgrading from Other Plugins

### From WP Rocket

1. Export your WP Rocket settings (if possible)
2. Deactivate WP Rocket
3. Install and activate WP WebOptimizer Pro
4. Configure similar settings in WP WebOptimizer
5. Test thoroughly before deleting WP Rocket

### From W3 Total Cache

1. Note your W3TC configuration
2. Deactivate W3 Total Cache
3. Delete W3TC cache files
4. Install and activate WP WebOptimizer Pro
5. Configure cache settings
6. Test performance

### From Autoptimize

1. Review Autoptimize settings
2. Deactivate Autoptimize
3. Install WP WebOptimizer Pro
4. Enable similar optimizations
5. Add same exclusions
6. Test functionality

## Compatibility Check

### Compatible With

- ✅ Most WordPress themes
- ✅ WooCommerce
- ✅ Contact Form 7
- ✅ Yoast SEO
- ✅ Elementor
- ✅ Gutenberg
- ✅ Classic Editor
- ✅ WPML
- ✅ Polylang

### May Conflict With

- ⚠️ Other caching plugins (choose one)
- ⚠️ Other minification plugins (choose one)
- ⚠️ Certain page builders (test carefully)

### Not Compatible With

- ❌ Very old themes (pre-2015)
- ❌ Themes with inline critical CSS conflicts
- ❌ Plugins modifying .htaccess extensively

## Troubleshooting Installation

### Issue: Cannot upload ZIP file

**Solutions**:
1. Check file size limits in php.ini
2. Increase `upload_max_filesize` and `post_max_size`
3. Use FTP method instead

### Issue: Plugin doesn't appear after upload

**Solutions**:
1. Verify folder structure: `/wp-content/plugins/wp-weboptimizer/`
2. Check file permissions (755 for folders, 644 for files)
3. Ensure main file is `wp-weboptimizer.php`

### Issue: Activation error

**Solutions**:
1. Check PHP version (minimum 7.2)
2. Verify WordPress version (minimum 5.0)
3. Check for plugin conflicts
4. Review error logs

### Issue: White screen after activation

**Solutions**:
1. Disable via FTP: rename plugin folder
2. Check PHP error logs
3. Increase PHP memory limit
4. Contact support with error details

### Issue: Settings page not loading

**Solutions**:
1. Clear browser cache
2. Disable other plugins temporarily
3. Switch to default theme
4. Check for JavaScript conflicts

## Verification

### Check Installation Success

1. **Plugin appears in admin menu**
   - Look for "WP WebOptimizer" in WordPress admin sidebar
   - Icon should be a performance dashboard icon

2. **Settings page loads**
   - Click on menu item
   - All 8 tabs should display
   - No JavaScript errors in console

3. **Basic functionality works**
   - Toggle a setting
   - Save should work with green notification
   - Reload page and verify setting persisted

4. **Frontend optimization active**
   - View page source
   - Look for `loading="lazy"` on images
   - Verify defer on JavaScript tags

### File Permissions

Correct permissions are crucial:

```bash
# Recommended permissions
chmod 755 wp-content/plugins/wp-weboptimizer
chmod 644 wp-content/plugins/wp-weboptimizer/wp-weboptimizer.php
chmod 644 wp-content/plugins/wp-weboptimizer/includes/*.php
chmod 644 wp-content/plugins/wp-weboptimizer/admin/css/*.css
chmod 644 wp-content/plugins/wp-weboptimizer/admin/js/*.js
```

## Security Considerations

### Important Notes

1. **No External Connections**
   - Plugin does not connect to external servers
   - No automatic update checks
   - Complete local control

2. **User Capabilities**
   - Only administrators can access settings
   - Settings page requires `manage_options` capability
   - AJAX requests are nonce-protected

3. **Data Privacy**
   - Performance data stored locally
   - No data sent to external services
   - No tracking or analytics

### Recommended Security Practices

1. **Keep WordPress Updated**
   - Update WordPress core regularly
   - Keep PHP version current
   - Update other plugins and themes

2. **Use Strong Passwords**
   - Secure admin accounts
   - Enable two-factor authentication
   - Limit login attempts

3. **Regular Backups**
   - Backup before major changes
   - Test backups regularly
   - Store backups securely

## Support

### Need Help?

- **Website**: https://vuvannamviet.com
- **Email**: contact@vuvannamviet.com
- **Hotline**: 0971.735.735

### Before Contacting Support

Please have ready:
1. WordPress version
2. PHP version
3. Active theme name
4. List of active plugins
5. Error messages (if any)
6. Browser console errors
7. Steps to reproduce issue

### Emergency Deactivation

If you need to quickly disable the plugin:

**Via Admin**:
1. Go to Plugins page
2. Find WP WebOptimizer Pro
3. Click Deactivate

**Via FTP**:
1. Connect via FTP
2. Navigate to `/wp-content/plugins/`
3. Rename `wp-weboptimizer` to `wp-weboptimizer-disabled`

**Via Database** (last resort):
```sql
UPDATE wp_options 
SET option_value = '' 
WHERE option_name = 'active_plugins';
```

## Next Steps

After successful installation:

1. ✅ Read the [Vietnamese Documentation](README-vi.md)
2. ✅ Configure performance mode
3. ✅ Enable key optimizations
4. ✅ Test your website thoroughly
5. ✅ Monitor Core Web Vitals
6. ✅ Fine-tune settings as needed

---

**Congratulations!** WP WebOptimizer Pro is now installed and ready to optimize your WordPress site.

For detailed usage instructions, please see [README-vi.md](README-vi.md).
