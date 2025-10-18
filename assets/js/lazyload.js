/**
 * WP WebOptimizer Pro - Lazy Load Script
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

(function() {
	'use strict';

	// Check if browser supports IntersectionObserver
	if (!('IntersectionObserver' in window)) {
		// Fallback: Load all images immediately
		return;
	}

	// Configuration
	var config = {
		rootMargin: '50px 0px',
		threshold: 0.01
	};

	// Create observer
	var imageObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				var lazyImage = entry.target;
				
				// For images with loading="lazy"
				if (lazyImage.tagName === 'IMG') {
					if (lazyImage.dataset.src) {
						lazyImage.src = lazyImage.dataset.src;
					}
					if (lazyImage.dataset.srcset) {
						lazyImage.srcset = lazyImage.dataset.srcset;
					}
					lazyImage.classList.remove('lazy');
					lazyImage.classList.add('lazy-loaded');
				}
				
				// For iframes
				if (lazyImage.tagName === 'IFRAME') {
					if (lazyImage.dataset.src) {
						lazyImage.src = lazyImage.dataset.src;
					}
					lazyImage.classList.remove('lazy');
					lazyImage.classList.add('lazy-loaded');
				}
				
				observer.unobserve(lazyImage);
			}
		});
	}, config);

	// Observe all lazy load elements
	function observeLazyElements() {
		var lazyImages = document.querySelectorAll('img[loading="lazy"], iframe[loading="lazy"]');
		lazyImages.forEach(function(lazyImage) {
			imageObserver.observe(lazyImage);
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', observeLazyElements);
	} else {
		observeLazyElements();
	}

	// Re-observe when new content is added (e.g., AJAX)
	if (window.MutationObserver) {
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.addedNodes.length) {
					observeLazyElements();
				}
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}
})();
