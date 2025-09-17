(function ($) {
    'use strict';
    $(document).ready(function () {
        const calculateSlidesToShow_v1 = () => Math.max(Math.floor($('.repocean-content-wrapper').width() / 305), 1);
        const initializeSlider_v1 = () => {
            const slidesToShow_v1 = calculateSlidesToShow_v1();
            $('.repocean-slider-main-v1 .repocean-slider-box-parent').slick({
                dots: false,
                autoplay: true,
                infinite: true,
                autoplaySpeed: 6100,
                prevArrow: '<button class="slide-arrow prev-arrow"></button>',
                nextArrow: '<button class="slide-arrow next-arrow"></button>',
                speed: 700,
                slidesToShow: slidesToShow_v1,
                slidesToScroll: 1,
                variableWidth: false,
                arrows: repocean_slider_js_v1.arrowVisibility === 'true'
            });
        };
        initializeSlider_v1();
        setTimeout(() => {
            $('.repocean-slider-main-v1 .slider-box-inner, .review-sub-title, .repocean-footer').show();
        }, 1);
        $(window).on('resize', () => {
            $('.repocean-slider-main-v1 .repocean-slider-box-parent').slick('unslick');
            initializeSlider_v1();
        });
    });
    $('.repocean-slider-main-v1').on('click', '.button-content .readmore-button a', function (event) {
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

    console.log('--- CLICKED ---');
    console.log('Is expanded:', isExpanded);
    console.log('Full height:', fullHeight);
    console.log('Collapsed height:', collapsedHeight);
    console.log('Height step per frame:', heightStep);

    if ($description.data('animating')) {
        console.warn('Animation already in progress, skipping click.');
        return;
    }

    $description.data('animating', true);

    function expandLoop(currentHeight, stepCount) {
        console.log(`Expanding: step ${stepCount}, height = ${currentHeight}`);

        if (stepCount >= totalSteps) {
            $description.addClass('expanded').removeData('animating');
            $description.css('max-height', fullHeight + 'px');
            console.log('Expand complete. Final height:', fullHeight);
            return;
        }

        currentHeight += heightStep;
        $description.css('max-height', currentHeight + 'px');
        setTimeout(() => expandLoop(currentHeight, stepCount + 1), delay);
    }

    function collapseLoop(currentHeight, stepCount) {
        console.log(`Collapsing: step ${stepCount}, height = ${currentHeight}`);

        if (stepCount >= totalSteps) {
            $description.removeClass('expanding expanded').removeData('animating').css('max-height', '');
            console.log('Collapse complete. Final height:', collapsedHeight);
            return;
        }

        currentHeight -= heightStep;
        console.log(currentHeight);
        $description.css('max-height', currentHeight + 'px');
        setTimeout(() => collapseLoop(currentHeight, stepCount + 1), delay);
    }

    if (isExpanded) {
        console.log('Starting collapse...');
        $description.removeClass('expanded');
        collapseLoop(fullHeight, 0);
        $button.text(repocean_slider_js_v1.read_more);
    } else {
        console.log('Starting expand...');
        $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
        expandLoop(collapsedHeight, 0);
        $button.text(repocean_slider_js_v1.hide);
    }
});

    document.addEventListener('DOMContentLoaded', () => {
        const reviewBox = document.querySelector('.review-box');
        reviewBox.style.setProperty('--rating', reviewBox.getAttribute('data-rating'));
    });
})(jQuery);
