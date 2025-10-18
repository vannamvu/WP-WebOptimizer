# Hướng dẫn cài đặt và sử dụng WP WebOptimizer Pro

## 📦 Cài đặt

### Phương pháp 1: Upload thủ công qua FTP

1. Tải xuống plugin từ repository
2. Giải nén file zip
3. Upload thư mục `WP-WebOptimizer` vào `/wp-content/plugins/` qua FTP
4. Đăng nhập vào WordPress Admin
5. Vào **Plugins > Installed Plugins**
6. Tìm "WP WebOptimizer Pro" và click **Activate**

### Phương pháp 2: Upload qua WordPress Admin

1. Đăng nhập vào WordPress Admin
2. Vào **Plugins > Add New**
3. Click **Upload Plugin**
4. Chọn file zip của plugin
5. Click **Install Now**
6. Click **Activate Plugin**

## ⚙️ Cấu hình ban đầu

### Bước 1: Truy cập Dashboard
1. Sau khi kích hoạt, vào menu **WebOptimizer** trong admin sidebar
2. Bạn sẽ thấy Dashboard với tổng quan về plugin

### Bước 2: Cấu hình CSS/JS Optimization

**Tab: CSS/JS Optimization**

Các tùy chọn khuyến nghị:
- ✅ **Minify CSS**: Bật để nén CSS inline
- ✅ **Minify JavaScript**: Bật để nén JS inline
- ✅ **Defer JavaScript**: Bật để defer non-critical JS
- ⚠️ **Remove jQuery Migrate**: Chỉ bật nếu chắc chắn không có plugin cũ sử dụng

**Lưu ý**: Sau khi lưu, test website để đảm bảo không có lỗi JavaScript.

### Bước 3: Cấu hình Image Optimization

**Tab: Image Optimization**

Khuyến nghị:
- ✅ **Auto WebP Conversion**: Bật để tự động chuyển ảnh sang WebP khi upload
- ✅ **Image Compression**: Bật với JPEG Quality 85%
- ✅ **Lazy Load Images**: Bật để lazy load ảnh
- ✅ **Responsive Images**: Bật để tạo srcset

**Lưu ý**: 
- WebP chỉ hoạt động nếu server hỗ trợ GD library với WebP
- Ảnh cũ cần convert thủ công hoặc dùng plugin convert

### Bước 4: Cấu hình Cache

**Tab: Cache**

Khuyến nghị cho beginners:
- ✅ **Browser Caching**: Bật
- ⚠️ **HTML Page Caching**: Bật với Cache TTL 3600s (1 hour)
- ❌ **Mobile Cache**: Tắt nếu không có traffic mobile cao

**Lưu ý**:
- HTML caching không áp dụng cho logged-in users
- Clear cache sau khi cập nhật nội dung
- Kiểm tra X-WP-WebOptimizer-Cache header để verify cache hoạt động

### Bước 5: Database Optimization

**Tab: Database**

Chạy các công việc này định kỳ:

1. **Optimize Tables**: Chạy 1 lần/tháng
2. **Clean Transients**: Chạy 1 lần/tuần
3. **Clean Revisions**: Chạy 1 lần/tháng
4. **Clean Spam**: Chạy khi cần

**Auto Optimization**:
- ✅ Bật để tự động chạy optimization hàng ngày

### Bước 6: Advanced Settings

**Tab: Advanced**

Khuyến nghị:
- ✅ **Disable Emojis**: Bật (tiết kiệm 1 HTTP request)
- ✅ **Remove Query Strings**: Bật (cải thiện caching)
- ✅ **Disable Embeds**: Bật nếu không dùng WordPress embeds
- ✅ **Disable Pingbacks**: Bật (bảo mật)
- ✅ **Remove Version**: Bật (bảo mật)
- ⚠️ **Limit Post Revisions**: Đặt 5 revisions

## 🎯 Optimization Checklist

### Cấu hình tối thiểu (Beginners)
```
✅ Minify CSS/JS
✅ Defer JavaScript
✅ Lazy Load Images
✅ Auto WebP
✅ Image Compression (85%)
✅ Browser Caching
✅ Disable Emojis
✅ Remove Query Strings
```

### Cấu hình nâng cao (Advanced)
```
✅ All from Beginner
✅ HTML Page Caching
✅ Mobile Cache Separation
✅ Remove jQuery Migrate
✅ Auto Database Optimization
✅ Disable Embeds
✅ Limit Revisions to 5
```

### Cấu hình chuyên nghiệp (Expert)
```
✅ All from Advanced
✅ Custom Resource Hints
✅ Third-party Script Delay
✅ Performance Monitoring
✅ Critical CSS (manual input)
```

## 🧪 Testing

### 1. Test Frontend
- Mở website trong Incognito mode
- Kiểm tra tất cả pages hoạt động bình thường
- Verify lazy loading images
- Check responsive images

### 2. Test Performance
- Chạy Google PageSpeed Insights
- Target: Score 90+ cho cả Mobile và Desktop
- Check Core Web Vitals:
  - FCP < 1.8s
  - LCP < 2.5s
  - FID < 100ms
  - CLS < 0.1

### 3. Test Compatibility
- Test với các plugins phổ biến:
  - WooCommerce
  - Contact Form 7
  - Yoast SEO
  - Elementor
- Test trên các browsers:
  - Chrome
  - Firefox
  - Safari
  - Edge

## 🔧 Troubleshooting

### Website bị lỗi sau khi kích hoạt

**Giải pháp**:
1. Tắt plugin qua FTP (rename thư mục)
2. Hoặc vào Database, table `wp_options`
3. Xóa option `active_plugins` có chứa plugin
4. Report issue

### JavaScript không hoạt động

**Nguyên nhân**: Defer JS gây xung đột

**Giải pháp**:
1. Vào tab CSS/JS Optimization
2. Tắt "Defer JavaScript"
3. Hoặc thêm scripts vào exclude list (sẽ có trong phiên bản sau)

### Images không lazy load

**Nguyên nhân**: Theme đã tự implement lazy load

**Giải pháp**:
1. Tắt lazy load của plugin
2. Hoặc tắt lazy load của theme

### Cache không hoạt động

**Kiểm tra**:
1. View Page Source
2. Tìm comment `<!-- Cached by WP WebOptimizer -->`
3. Check header `X-WP-WebOptimizer-Cache: HIT`

**Nếu không có**:
1. Đảm bảo không logged in
2. Đảm bảo không có query string
3. Clear cache và thử lại

## 📊 Performance Monitoring

### Xem Core Web Vitals
1. Vào tab Dashboard
2. Xem Performance Score
3. Chi tiết metrics trong Performance Monitor (coming soon)

### Real User Monitoring
Plugin tự động thu thập metrics từ users thực tế:
- FCP (First Contentful Paint)
- LCP (Largest Contentful Paint)
- FID (First Input Delay)
- CLS (Cumulative Layout Shift)
- TTFB (Time to First Byte)

Data được lưu trong `wp_options` table với key `wp_weboptimizer_metrics_history`

## 🆘 Hỗ trợ

Nếu gặp vấn đề:

1. **Check Documentation**: Đọc lại hướng dẫn
2. **Check Changelog**: Xem các known issues
3. **Contact Support**:
   - Author: Vũ Văn Nam Việt
   - Website: https://vuvannamviet.com
   - Hotline: 0971.735.735

## 🔄 Updates

Plugin tự động check updates từ server:
- Update Server: https://pluginaz.com/wp-json/plugin68-license-manager/v1/update-check

WordPress sẽ thông báo khi có phiên bản mới.

## ⚠️ Important Notes

1. **Backup trước khi cài đặt**: Luôn backup database và files
2. **Test trên staging**: Test trên môi trường staging trước khi deploy production
3. **Monitor sau khi deploy**: Theo dõi website trong 24-48h đầu
4. **Clear cache thường xuyên**: Clear cache sau mỗi thay đổi quan trọng
5. **Database backup**: Backup database trước khi chạy optimization

## 📝 Best Practices

1. **Incremental Optimization**: Bật từng tính năng một, test kỹ
2. **Regular Maintenance**: Chạy database cleanup hàng tháng
3. **Performance Testing**: Test PageSpeed định kỳ
4. **Keep Updated**: Luôn update plugin lên phiên bản mới nhất
5. **Monitor Metrics**: Theo dõi Core Web Vitals qua Google Search Console

---

**Version**: 2.0.0  
**Last Updated**: 2024-10-18  
**Author**: Vũ Văn Nam Việt
