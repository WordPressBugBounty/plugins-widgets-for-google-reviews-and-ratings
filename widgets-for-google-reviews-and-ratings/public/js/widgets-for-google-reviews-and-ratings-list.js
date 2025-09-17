(function ($) {
    'use strict';
    $(document).ready(function () {
        let listBoxes = $('.list-box');
        let itemsToShow = 5;
        let visibleItems = 0;
        if (listBoxes.length <= itemsToShow) {
            $('#loadMoreList').hide();
        } else {
            $('#loadMoreList').show();
        }
        listBoxes.slice(0, itemsToShow).each(function (index, element) {
            setTimeout(function () {
                $(element).addClass('show');
            }, index * 100);
        });
        visibleItems += itemsToShow;
        $('#loadMoreList').on('click', function () {
            let nextItems = listBoxes.slice(visibleItems, visibleItems + itemsToShow);
            nextItems.each(function (index, element) {
                setTimeout(function () {
                    $(element).addClass('show');
                }, index * 100);
            });
            visibleItems += itemsToShow;
            if (visibleItems >= listBoxes.length) {
                $(this).hide();
            }
        });
        $('.repocean-list-main').on('click', '.button-content .readmore-button a', function (event) {
            event.preventDefault();
            const $button = $(this);
            const $description = $button.closest('.list-box-inner').find('.description');
            const isExpanded = $description.hasClass('expanded');
            const fullHeight = $description[0].scrollHeight;
            const collapsedHeight = 40;
            const totalDuration = 10;
            const totalSteps = 15;
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
                collapseLoop(fullHeight, 0);
                $description.removeClass('expanded');
                $button.text(repocean_list_js.read_more);
            } else {
                $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
                expandLoop(collapsedHeight, 0);
                $button.text(repocean_list_js.hide);
            }
        });
    });
})(jQuery);
