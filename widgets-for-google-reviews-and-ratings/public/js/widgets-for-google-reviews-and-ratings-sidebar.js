(function ($) {
    'use strict';
    $(document).ready(function () {
        const $slider = $('.repocean-sidebar-main .slider-start').slick({
            dots: false,
            prevArrow: '<button class="slide-arrow prev-arrow"></button>',
            nextArrow: '<button class="slide-arrow next-arrow"></button>',
            infinite: true,
            autoplay: true,
            speed: 700,
            autoplaySpeed: 6100,
            slidesToShow: 1,
            slidesToScroll: 1,
            adaptiveHeight: true,
            arrows: repocean_sidebar_js.arrowVisibility === 'true'
        });
        $(".prev-btn").on('click', () => $slider.slick("slickPrev"));
        $(".next-btn").on('click', () => $slider.slick("slickNext"));
        $slider.on("afterChange", function (event, slick, currentSlide) {
            $(".prev-btn").toggleClass("slick-disabled", currentSlide === 0);
            $(".next-btn").toggleClass("slick-disabled", currentSlide === slick.slideCount - 1);
        });
        $(".prev-btn").addClass("slick-disabled");
        $('.repocean-sidebar-main').on('click', '.button-content .readmore-button a', function (event) {
            event.preventDefault();
            const $button = $(this);
            const $description = $button.closest('.bottom-part-inner').find('.description');
            const isExpanded = $description.hasClass('expanded');
            const fullHeight = $description[0].scrollHeight;
            const collapsedHeight = 40;
            const totalDuration = 400; // ms
            const totalSteps = 40;
            const delay = totalDuration / totalSteps;
            const heightStep = (fullHeight - collapsedHeight) / totalSteps;
            // Prevent overlapping animations
            if ($description.data('animating'))
                return;
            $description.data('animating', true);
            function expandLoop(currentHeight, stepCount) {
                if (stepCount >= totalSteps) {
                    $description.addClass('expanded').removeData('animating');
                    $description.css('max-height', fullHeight + 'px');
                    if (typeof $slider !== 'undefined' && $slider.slick) {
                        $slider.slick('refresh');
                    }
                    return;
                }
                currentHeight += heightStep;
                $description.css('max-height', currentHeight + 'px');
                setTimeout(() => expandLoop(currentHeight, stepCount + 1), delay);
            }
            function collapseLoop(currentHeight, stepCount) {
                if (stepCount >= totalSteps) {
                    $description.removeClass('expanding expanded').removeData('animating').css('max-height', '');
                    if (typeof $slider !== 'undefined' && $slider.slick) {
                        $slider.slick('refresh');
                    }
                    return;
                }
                currentHeight -= heightStep;
                $description.css('max-height', currentHeight + 'px');
                setTimeout(() => collapseLoop(currentHeight, stepCount + 1), delay);
            }
            if (isExpanded) {
                $description.removeClass('expanded');
                collapseLoop(fullHeight, 0);
                $button.text(repocean_sidebar_js.read_more);
            } else {
                $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
                expandLoop(collapsedHeight, 0);
                $button.text(repocean_sidebar_js.hide);
            }
        });
    });
})(jQuery);
