# WP WebOptimizer Pro - Architecture Documentation

## 🏗️ Plugin Architecture

### Overview
WP WebOptimizer Pro follows a modular, OOP-based architecture with clear separation of concerns.

```
WP-WebOptimizer/
├── wp-weboptimizer.php          # Main plugin file (Core class)
├── modules/                     # Feature modules
│   ├── assets-optimizer.php     # CSS/JS optimization
│   ├── lazy-load.php           # Lazy loading features
│   ├── font-optimizer.php      # Font optimization
│   ├── image-optimizer.php     # Image optimization
│   ├── cache-manager.php       # Cache management
│   ├── resource-hints.php      # Resource hints (prefetch, preconnect)
│   ├── third-party-optimizer.php # Third-party scripts
│   ├── database-optimizer.php  # Database optimization
│   ├── performance-monitor.php # Performance tracking
│   └── advanced-settings.php   # Advanced WordPress settings
├── admin/                       # Admin interface
│   └── admin-ui.php            # Admin UI and AJAX handlers
├── assets/                      # Frontend/Admin assets
│   ├── css/
│   │   └── admin-style.css     # Admin styles
│   └── js/
│       └── admin-script.js     # Admin JavaScript
└── docs/                        # Documentation
    ├── README.md
    ├── CHANGELOG.md
    ├── INSTALLATION.md
    └── ARCHITECTURE.md
```

## 🎯 Core Components

### 1. Main Plugin Class (wp-weboptimizer.php)

**Class**: `WP_WebOptimizer`

**Responsibilities**:
- Plugin initialization
- Module loading
- Option management
- Hook registration

**Key Methods**:
```php
instance()           // Singleton pattern
init()              // Initialize plugin
load_modules()      // Load all feature modules
get_option()        // Get plugin options
update_option()     // Update plugin options
activate()          // Activation hook
deactivate()        // Deactivation hook
```

**Hooks**:
- `plugins_loaded` - Initialize plugin
- `admin_menu` - Add admin menu
- `wp_enqueue_scripts` - Enqueue frontend scripts

### 2. Module System

Each module is a separate class that handles a specific optimization feature.

#### Assets Optimizer Module
**Class**: `WP_WebOptimizer_Assets_Optimizer`

**Features**:
- CSS/JS minification
- Defer JavaScript
- Remove jQuery Migrate
- Critical CSS injection

**Filters Used**:
- `script_loader_tag` - Modify script tags
- `style_loader_tag` - Modify style tags

#### Lazy Load Module
**Class**: `WP_WebOptimizer_Lazy_Load`

**Features**:
- Native lazy loading for images
- Lazy load iframes
- Lazy load videos
- Decoding async

**Filters Used**:
- `the_content` - Process content
- `post_thumbnail_html` - Process thumbnails
- `get_avatar` - Process avatars

#### Font Optimizer Module
**Class**: `WP_WebOptimizer_Font_Optimizer`

**Features**:
- Font-display swap
- Preload fonts
- Google Fonts optimization
- Preconnect hints

**Actions Used**:
- `wp_head` - Add preload/preconnect

#### Image Optimizer Module
**Class**: `WP_WebOptimizer_Image_Optimizer`

**Features**:
- Auto WebP conversion
- Image compression
- Remove EXIF data
- Add dimensions
- Responsive images

**Filters Used**:
- `wp_generate_attachment_metadata` - Process on upload
- `wp_editor_set_quality` - Set compression quality

#### Cache Manager Module
**Class**: `WP_WebOptimizer_Cache_Manager`

**Features**:
- Browser caching headers
- HTML page caching
- Mobile cache separation
- Cache invalidation

**Actions Used**:
- `template_redirect` - Serve cache
- `shutdown` - Save cache
- `save_post` - Clear cache

#### Resource Hints Module
**Class**: `WP_WebOptimizer_Resource_Hints`

**Features**:
- DNS-prefetch
- Preconnect
- Preload resources

**Actions Used**:
- `wp_head` - Add resource hints

#### Third-party Optimizer Module
**Class**: `WP_WebOptimizer_Third_Party_Optimizer`

**Features**:
- Delay Google Analytics
- Delay Facebook Pixel
- Remove WordPress embeds

**Actions Used**:
- `wp_footer` - Add delayed scripts
- `init` - Remove embeds

#### Database Optimizer Module
**Class**: `WP_WebOptimizer_Database_Optimizer`

**Features**:
- Optimize database tables
- Clean transients
- Clean revisions
- Clean spam comments
- Auto scheduling

**AJAX Endpoints**:
- `wpwo_optimize_tables`
- `wpwo_clean_transients`
- `wpwo_clean_revisions`
- `wpwo_clean_spam`

#### Performance Monitor Module
**Class**: `WP_WebOptimizer_Performance_Monitor`

**Features**:
- Core Web Vitals tracking (FCP, LCP, FID, CLS, TTFB)
- Real User Monitoring
- Performance score calculation
- Metrics history

**AJAX Endpoints**:
- `wpwo_save_metrics` (public)
- `wpwo_get_metrics`

**JavaScript Integration**:
```javascript
// Uses PerformanceObserver API
- paint (FCP)
- largest-contentful-paint (LCP)
- first-input (FID)
- layout-shift (CLS)
- navigation (TTFB)
```

#### Advanced Settings Module
**Class**: `WP_WebOptimizer_Advanced_Settings`

**Features**:
- Disable emojis
- Remove query strings
- Disable JSON API for guests
- Disable pingbacks
- Remove RSD/shortlink
- Limit revisions
- Heartbeat control

### 3. Admin Interface

**Class**: `WP_WebOptimizer_Admin_UI`

**Tabs**:
1. Dashboard - Overview & quick stats
2. CSS/JS Optimization - Assets settings
3. Image Optimization - Image settings
4. Cache - Cache settings
5. Database - Database tools
6. Advanced - Advanced settings

**AJAX Handlers**:
- `wpwo_save_settings` - Save module settings
- `wpwo_clear_cache` - Clear all cache

**Assets**:
- `admin-style.css` - Modern, responsive admin UI
- `admin-script.js` - AJAX handling, UI interactions

## 📊 Data Flow

### Settings Management

```
User Input (Admin UI)
    ↓
AJAX Request (admin-script.js)
    ↓
Admin Handler (admin-ui.php)
    ↓
WP_WebOptimizer::update_option()
    ↓
WordPress Options API
    ↓
Database (wp_options table)
```

### Cache Flow

```
Page Request
    ↓
template_redirect hook
    ↓
Cache_Manager::maybe_serve_cache()
    ↓
[Cache Hit] → Serve cached HTML
    ↓
[Cache Miss] → Generate page
    ↓
shutdown hook
    ↓
Cache_Manager::maybe_cache_page()
    ↓
Save to cache file
```

### Image Upload Flow

```
Image Upload
    ↓
wp_generate_attachment_metadata filter
    ↓
Image_Optimizer::generate_webp_on_upload()
    ↓
- Convert to WebP
- Compress image
- Remove EXIF
    ↓
Save files
```

### Performance Monitoring Flow

```
Page Load (Frontend)
    ↓
Performance APIs (JavaScript)
    ↓
Collect metrics (FCP, LCP, FID, CLS)
    ↓
navigator.sendBeacon
    ↓
AJAX: wpwo_save_metrics
    ↓
Performance_Monitor::save_metrics()
    ↓
Store in wp_options
```

## 🔐 Security Architecture

### Input Sanitization
- `sanitize_text_field()` - Text inputs
- `absint()` - Integer inputs
- `sanitize_url()` - URLs
- `wp_unslash()` - Slashes removal

### Output Escaping
- `esc_html()` - HTML content
- `esc_attr()` - HTML attributes
- `esc_url()` - URLs
- `esc_js()` - JavaScript

### Capability Checks
- `current_user_can('manage_options')` - Admin functions
- Admin pages only accessible to administrators

### Nonce Verification
- All AJAX requests use nonces
- Nonce field: `wpwo_ajax_nonce`

### File Security
- Direct access prevention: `if (!defined('ABSPATH')) exit;`
- Secure file operations with `@` error suppression
- File permission checks

## 🎨 Code Standards

### WordPress Coding Standards
- PHP DocBlocks for all classes and methods
- Proper indentation (tabs)
- Single quotes for strings
- Spaces around operators

### OOP Principles
- Single Responsibility Principle
- Each module handles one feature
- Clear separation of concerns
- Singleton pattern for main class

### Performance Considerations
- Lazy loading of modules
- Conditional loading based on settings
- Minimal database queries
- Use of transients for caching
- Efficient file operations

## 📈 Scalability

### Module Addition
To add a new module:

1. Create file in `/modules/`
2. Add class with constructor
3. Register hooks in `init_hooks()`
4. Add to `load_modules()` in main class
5. Add admin UI in `admin-ui.php`

### Hook System
All modules use WordPress hooks:
- Actions for execution
- Filters for modification
- Priority management for order

### Database Design
- Single option for all settings: `wp_weboptimizer_options`
- Nested array structure for modules
- Easy backup and restore
- No custom tables needed

## 🧪 Testing Strategy

### Manual Testing
1. Activate plugin
2. Configure each module
3. Test frontend functionality
4. Verify admin UI
5. Check performance metrics

### Compatibility Testing
- Test with popular plugins
- Test with popular themes
- Test on different PHP versions
- Test on different WordPress versions

### Performance Testing
- Google PageSpeed Insights
- GTmetrix
- WebPageTest
- Core Web Vitals

## 📚 API Reference

### Main Class Methods

```php
// Get plugin instance
$plugin = wp_weboptimizer();

// Get option
$value = WP_WebOptimizer::get_option('module.setting', 'default');

// Update option
WP_WebOptimizer::update_option('module.setting', $value);
```

### Hooks

```php
// After plugin init
do_action('wp_weboptimizer_init');

// After modules loaded
do_action('wp_weboptimizer_modules_loaded', $modules);

// After activation
do_action('wp_weboptimizer_activated');

// After deactivation
do_action('wp_weboptimizer_deactivated');
```

### Filters

```php
// Modify default options
apply_filters('wp_weboptimizer_default_options', $options);

// Modify cache key
apply_filters('wp_weboptimizer_cache_key', $key);
```

## 🔧 Configuration

### Default Options Structure

```php
[
    'assets_optimizer' => [
        'minify_css' => true,
        'minify_js' => true,
        'defer_js' => true,
        'critical_css' => false,
    ],
    'lazy_load' => [
        'images' => true,
        'iframes' => true,
        'videos' => false,
    ],
    // ... other modules
]
```

### Constants

```php
WP_WEBOPTIMIZER_VERSION  // Plugin version
WP_WEBOPTIMIZER_FILE     // Main plugin file
WP_WEBOPTIMIZER_PATH     // Plugin directory path
WP_WEBOPTIMIZER_URL      // Plugin URL
WP_WEBOPTIMIZER_BASENAME // Plugin basename
```

---

**Version**: 2.0.0  
**Last Updated**: 2024-10-18  
**Author**: Vũ Văn Nam Việt
