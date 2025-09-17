(function ($) {
    'use strict';
    $(document).ready(function () {
        const calculateSlidesToShow = () => Math.max(Math.floor($('.repocean-slider-main .repocean-content-wrapper').width() / 305), 1);
        const initializeSlider = () => {
            const slidesToShow = calculateSlidesToShow();
            $('.repocean-slider-main .repocean-slider-box-parent').slick({
                dots: false,
                autoplay: true,
                infinite: true,
                autoplaySpeed: 6100,
                prevArrow: '<button class="slide-arrow prev-arrow"></button>',
                nextArrow: '<button class="slide-arrow next-arrow"></button>',
                speed: 700,
                slidesToShow: slidesToShow,
                slidesToScroll: 1,
                variableWidth: false,
                arrows: repocean_slider_js.arrowVisibility === 'true'
            });
        };
        console.log(repocean_slider_js.hide);
        initializeSlider();
        $(window).on('resize', () => {
            $('.repocean-slider-main .repocean-slider-box-parent').slick('unslick');
            initializeSlider();
        });
        setTimeout(() => {
            $('.slider-box-inner, .repocean-footer').show();
        }, 1);
    });
    $('.repocean-slider-main').on('click', '.button-content .readmore-button a', function (event) {
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

    console.log('%c[CLICK EVENT]', 'color: green; font-weight: bold;');
    console.log('Description element:', $description[0]);
    console.log('Expanded state before:', isExpanded);
    console.log('Full height:', fullHeight, 'px');
    console.log('Collapsed height:', collapsedHeight, 'px');
    console.log('Height step:', heightStep.toFixed(2), 'px per frame');
    console.log('Delay per frame:', delay.toFixed(2), 'ms');

    if ($description.data('animating')) {
        console.warn('[Animation Skipped] Already animating.');
        return;
    }

    $description.data('animating', true);
    const startTime = Date.now();

    function expandLoop(currentHeight, stepCount) {
        console.log(`%c[Expand] Step ${stepCount} | Height: ${currentHeight.toFixed(2)}px`, 'color: blue;');

        if (stepCount >= totalSteps) {
            $description.addClass('expanded').removeData('animating');
            $description.css('max-height', fullHeight + 'px');
            console.log('%c[Expand Complete]', 'color: green;');
            console.log('Final height set to:', fullHeight + 'px');
            console.log('Total expand time:', Date.now() - startTime, 'ms');
            return;
        }

        currentHeight += heightStep;
        $description.css('max-height', currentHeight + 'px');

        setTimeout(() => expandLoop(currentHeight, stepCount + 1), delay);
    }

    function collapseLoop(currentHeight, stepCount) {
        console.log(`%c[Collapse] Step ${stepCount} | Height: ${currentHeight.toFixed(2)}px`, 'color: red;');

        if (stepCount >= totalSteps) {
            $description.removeClass('expanding expanded').removeData('animating').css('max-height', '');
            console.log('%c[Collapse Complete]', 'color: green;');
            console.log('Final height reset to auto (collapsed)');
            console.log('Total collapse time:', Date.now() - startTime, 'ms');
            return;
        }

        currentHeight -= heightStep;
        $description.css('max-height', currentHeight + 'px');

        setTimeout(() => collapseLoop(currentHeight, stepCount + 1), delay);
    }

    if (isExpanded) {
        console.log('%c[Action] Starting collapse...', 'color: orange; font-weight: bold;');
        $description.removeClass('expanded');
        collapseLoop(fullHeight, 0);
        $button.text(repocean_slider_js.read_more);
    } else {
        console.log('%c[Action] Starting expand...', 'color: cyan; font-weight: bold;');
        $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
        expandLoop(collapsedHeight, 0);
        $button.text(repocean_slider_js.hide);
    }
});

    document.addEventListener('DOMContentLoaded', () => {
        const reviewBox = document.querySelector('.review-box');
        reviewBox.style.setProperty('--rating', reviewBox.getAttribute('data-rating'));
    });
})(jQuery);
