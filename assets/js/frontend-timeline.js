// assets/js/frontend-timeline.js
(function($) {
    $(document).ready(function() {
        const animatedItems = document.querySelectorAll('.wtp-timeline-item[data-animation]');

        if (animatedItems.length > 0) {
            if ('IntersectionObserver' in window) {
                const observerOptions = {
                    root: null, // relative to document viewport
                    rootMargin: '0px',
                    threshold: 0.1 // 10% of the item must be visible to trigger
                };

                const animationObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const item = entry.target;
                            const animationClass = item.dataset.animation; // Using dataset for data- attributes

                            // Add 'wtp-item-visible' to start the opacity transition defined in CSS
                            item.classList.add('wtp-item-visible');

                            if (animationClass && animationClass !== 'none') {
                                item.classList.add(animationClass);
                            }

                            observer.unobserve(item); // Stop observing once animated
                        }
                    });
                }, observerOptions);

                animatedItems.forEach(function(item) {
                    animationObserver.observe(item);
                });

            } else {
                // Fallback for browsers that do not support IntersectionObserver
                // For this refactor, we'll log a warning and trigger animations immediately.
                console.warn('WP Timeline Pro: IntersectionObserver not supported. Fallback: animating all items immediately.');
                animatedItems.forEach(function(itemNode) {
                    var $item = $(itemNode); // Use jQuery for consistency if preferred for class manipulation
                    var animationClass = $item.data('animation');
                    $item.addClass('wtp-item-visible');
                    if (animationClass && animationClass !== 'none') {
                        $item.addClass(animationClass);
                    }
                });
            }
        }
    });
})(jQuery);
