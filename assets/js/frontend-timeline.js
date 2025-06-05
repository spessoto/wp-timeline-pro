// assets/js/frontend-timeline.js
(function($) {
    $(document).ready(function() {
        var $timelineItems = $('.wtp-timeline-item[data-animation]');

        if (!$timelineItems.length) {
            return;
        }

        function isElementInViewport(el) {
            if (typeof jQuery === "function" && el instanceof jQuery) {
                el = el[0];
            }
            var rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            ) || ( // Part of the element is visible
                rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom > 0
            );
        }

        function checkAnimations() {
            $timelineItems.each(function() {
                var $item = $(this);
                if (isElementInViewport($item) && !$item.hasClass('wtp-item-animated')) {
                    var animationClass = $item.data('animation');
                    if (animationClass && animationClass !== 'none') {
                        $item.addClass('wtp-item-visible ' + animationClass).addClass('wtp-item-animated');
                    } else {
                        // If animation is 'none' or undefined, just mark as animated to not check again
                        $item.addClass('wtp-item-animated');
                    }
                }
            });
        }

        // Check animations on load and on page scroll
        checkAnimations();
        $(window).on('scroll resize', checkAnimations);

    });
})(jQuery);
