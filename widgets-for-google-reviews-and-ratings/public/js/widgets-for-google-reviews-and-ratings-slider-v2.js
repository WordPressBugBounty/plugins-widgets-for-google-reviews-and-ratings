(function ($) {
    'use strict';
    $(document).ready(function () {
        console.log(repocean_slider_js_v2);
        const calculateSlidesToShow_V2 = () => Math.max(Math.floor($('.repocean-slider-main-v2 .right').width() / 280), 1);
        const initializeSlider_V2 = () => {
            const slidesToShow_V2 = calculateSlidesToShow_V2();
            $('.repocean-slider-main-v2 .slider-box-parent').slick({
                dots: false,
                autoplay: true,
                infinite: true,
                autoplaySpeed: 6100,
                prevArrow: '<button class="slide-arrow prev-arrow"></button>',
                nextArrow: '<button class="slide-arrow next-arrow"></button>',
                speed: 700,
                slidesToShow: slidesToShow_V2,
                slidesToScroll: 1,
                variableWidth: false,
                arrows: repocean_slider_js_v2.arrowVisibility === 'true'
            });
        };
        initializeSlider_V2();
        setTimeout(() => {
            $('.slider-box-inner, .left-inner').show();
        }, 1);
        $(window).on('resize', () => {
            $('.repocean-slider-main-v2 .slider-box-parent').slick('unslick');
            initializeSlider_V2();
        });
    });
    $('.repocean-slider-main-v2').on('click', '.button-content .rep-button a', function (event) {
        event.preventDefault();
        const $button = $(this);
        const $description = $button.closest('.slider-box-inner').find('.description');
        const isExpanded = $description.hasClass('expanded');
        const fullHeight = $description[0].scrollHeight;
         const collapsedHeight = 40;
        const totalDuration = 10;
        const totalSteps = 15;
        const delay = totalDuration / totalSteps;
        const heightStep = (fullHeight - collapsedHeight) / totalSteps;
        if ($description.data('animating'))
            return;
        $description.data('animating', true);
        function expandLoop(currentHeight, stepCount) {
            if (stepCount >= totalSteps) {
                $description.addClass('expanded').removeData('animating');
                $description.css('max-height', fullHeight + 'px');
                return;
            }
            currentHeight += heightStep;
            $description.css('max-height', currentHeight + 'px');
            setTimeout(() => expandLoop(currentHeight, stepCount + 1), delay);
        }
        function collapseLoop(currentHeight, stepCount) {
            if (stepCount >= totalSteps) {
                $description.removeClass('expanding expanded').removeData('animating').css('max-height', '');
                return;
            }
            currentHeight -= heightStep;
            $description.css('max-height', currentHeight + 'px');
            setTimeout(() => collapseLoop(currentHeight, stepCount + 1), delay);
        }
        if (isExpanded) {
            $description.removeClass('expanded');
            collapseLoop(fullHeight, 0);
            $button.text(repocean_slider_js_v2.read_more);
        } else {
            $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
            expandLoop(collapsedHeight, 0);
            $button.text(repocean_slider_js_v2.hide);
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        const reviewBox = document.querySelector('.review-box');
        reviewBox.style.setProperty('--rating', reviewBox.getAttribute('data-rating'));
    });
})(jQuery);
