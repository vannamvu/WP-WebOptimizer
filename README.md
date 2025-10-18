# WP WebOptimizer Pro

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-brightgreen.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)

**WordPress Performance Optimization Plugin Pro** - Plugin tối ưu hiệu suất WordPress toàn diện để đạt điểm tối đa trên Google PageSpeed Insights.

## ✨ Tính năng chính

### 🚀 Speed Optimization
- **CSS/JS Optimization**: Minify, defer, async loading
- **Critical CSS**: Inline critical CSS để cải thiện FCP
- **Remove Render-blocking Resources**: Loại bỏ tài nguyên chặn render

### 🖼️ Image Optimization
- **Auto WebP Conversion**: Tự động chuyển đổi ảnh sang WebP
- **Image Compression**: Nén ảnh tự động với chất lượng tùy chỉnh
- **Lazy Loading**: Native lazy loading cho images, iframes, videos
- **Responsive Images**: Tự động tạo srcset
- **Remove EXIF Data**: Xóa metadata để giảm kích thước file

### ⚡ Performance Features
- **Browser Caching**: Tự động cấu hình cache headers
- **HTML Page Caching**: Cache HTML pages với mobile separation
- **Font Optimization**: Font-display swap, preload fonts
- **Resource Hints**: DNS-prefetch, preconnect, preload
- **Third-party Scripts**: Delay loading Analytics, Facebook Pixel

### 🗄️ Database Optimization
- **Table Optimization**: Tối ưu database tables
- **Transient Cleaner**: Xóa transients hết hạn
- **Revision Cleanup**: Dọn dẹp post revisions
- **Spam Remover**: Xóa spam comments
- **Auto Scheduling**: Tự động tối ưu hàng ngày

### 📊 Performance Monitoring
- **Core Web Vitals Tracking**: FCP, LCP, FID, CLS, TTFB
- **Real User Monitoring**: Thu thập dữ liệu thực tế từ người dùng
- **Performance Dashboard**: Xem metrics và history
- **Performance Score**: Đánh giá điểm hiệu suất

### ⚙️ Advanced Settings
- Disable Emojis
- Remove Query Strings
- Disable Embeds
- Disable Pingbacks
- Limit Post Revisions
- Remove WP Version
- Heartbeat Control

## 📋 Yêu cầu hệ thống

- WordPress 6.0 hoặc cao hơn
- PHP 7.4 hoặc cao hơn
- MySQL 5.6 hoặc cao hơn

## 🔧 Cài đặt

### Cài đặt thủ công

1. Tải xuống plugin từ repository
2. Upload thư mục `wp-weboptimizer` vào `/wp-content/plugins/`
3. Kích hoạt plugin trong menu 'Plugins' của WordPress
4. Vào **WebOptimizer** trong admin menu để cấu hình

### Cài đặt qua WordPress Admin

1. Vào **Plugins > Add New**
2. Tìm kiếm "WP WebOptimizer Pro"
3. Click **Install Now** và sau đó **Activate**

## 🎯 Hướng dẫn sử dụng

### Dashboard
Xem tổng quan về các tối ưu đang kích hoạt, kích thước cache và điểm hiệu suất.

### CSS/JS Optimization
1. Vào tab **CSS/JS Optimization**
2. Bật các tùy chọn minify, defer JS
3. Click **Save Settings**

### Image Optimization
1. Vào tab **Image Optimization**
2. Bật Auto WebP Conversion và Compression
3. Điều chỉnh JPEG quality (khuyến nghị: 85%)
4. Bật Lazy Loading cho images

### Cache Settings
1. Vào tab **Cache**
2. Bật Browser Caching và HTML Page Caching
3. Đặt Cache TTL (mặc định: 3600 giây)
4. Click **Clear All Cache** khi cần

### Database Optimization
1. Vào tab **Database**
2. Click các nút để tối ưu tables, clean transients, revisions, spam
3. Bật Auto Optimize để chạy tự động hàng ngày

### Advanced Settings
1. Vào tab **Advanced**
2. Bật các tùy chọn phù hợp với website của bạn
3. Lưu ý: Một số tùy chọn có thể ảnh hưởng đến chức năng

## 🎨 Tính năng nổi bật

### Critical CSS Implementation
Plugin tự động inline critical CSS để cải thiện First Contentful Paint (FCP).

### WebP Auto Generation
Tự động tạo phiên bản WebP cho mọi ảnh upload, giảm đến 30% kích thước file.

### Smart Caching
Cache pages với điều kiện thông minh, không cache cho logged-in users.

### Performance Monitoring
Thu thập và phân tích Core Web Vitals từ người dùng thực tế.

## 📈 Kết quả

Sau khi cài đặt và cấu hình đúng, bạn có thể đạt được:
- **FCP < 1.8s** ✅
- **LCP < 2.5s** ✅
- **FID < 100ms** ✅
- **CLS < 0.1** ✅
- **PageSpeed Score 90+** ✅

## 🔒 Bảo mật

Plugin tuân thủ các best practices về bảo mật WordPress:
- Nonce verification cho mọi AJAX requests
- Capability checks cho admin functions
- Input sanitization và output escaping
- Secure file operations

## 🤝 Hỗ trợ

- **Author**: Vũ Văn Nam Việt
- **Website**: [vuvannamviet.com](https://vuvannamviet.com)
- **Hotline**: 0971.735.735
- **Update Server**: https://pluginaz.com/wp-json/plugin68-license-manager/v1/update-check

## 📝 License

GPL v2 or later

Copyright (C) 2024 Vũ Văn Nam Việt

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

## 🙏 Credits

Developed by Vũ Văn Nam Việt

## 📖 Changelog

See [CHANGELOG.md](CHANGELOG.md) for all version history.

## 🌟 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
