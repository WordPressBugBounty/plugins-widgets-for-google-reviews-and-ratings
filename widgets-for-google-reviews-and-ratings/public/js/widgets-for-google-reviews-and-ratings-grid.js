(function ($) {
    'use strict';
    $(document).ready(function () {
        let gridBoxes = $('.grid-box');
        let itemsToShow = 9;
        let visibleItems = 0;
        if (gridBoxes.length <= itemsToShow) {
            $('#loadMore').hide();
        } else {
            $('#loadMore').show();
        }
        gridBoxes.slice(0, itemsToShow).each(function (index, element) {
            setTimeout(function () {
                $(element).addClass('show');
            }, index * 100);
        });
        visibleItems += itemsToShow;
        $('#loadMore').on('click', function () {
            let nextItems = gridBoxes.slice(visibleItems, visibleItems + itemsToShow);
            nextItems.each(function (index, element) {
                setTimeout(function () {
                    $(element).addClass('show');
                }, index * 100);
            });
            visibleItems += itemsToShow;
            if (visibleItems >= gridBoxes.length) {
                $(this).hide();
            }
        });
        $('.repocean-grid-main .button-content .readmore-button a').on('click', function (event) {
            event.preventDefault();
            const $description = $(this).closest('.grid-box-inner').find('.description');
            const isExpanded = $description.hasClass('expanded');
            const fullHeight = $description[0].scrollHeight;
            const collapsedHeight = 40;
            const totalDuration = 10;
            const totalSteps = 15;
            const delay = totalDuration / totalSteps;
            const heightStepExpand = (fullHeight - collapsedHeight) / totalSteps;
            const heightStepCollapse = (fullHeight - collapsedHeight) / totalSteps;
            function expandLoop(currentHeight, stepCount) {
                if (stepCount >= totalSteps) {
                    $description.addClass('expanded');
                    $description.css('max-height', fullHeight + 'px');
                    return;
                }
                currentHeight += heightStepExpand;
                $description.css('max-height', currentHeight + 'px');
                setTimeout(() => expandLoop(currentHeight, stepCount + 1), delay);
            }
            function collapseLoop(currentHeight, stepCount) {
                if (stepCount >= totalSteps) {
                    $description.removeClass('expanding expanded').removeData('animating').css('max-height', '');
                    return;
                }
                currentHeight -= heightStepCollapse;
                $description.css('max-height', currentHeight + 'px');
                setTimeout(() => collapseLoop(currentHeight, stepCount + 1), delay);
            }
            if (isExpanded) {
                const startHeight = fullHeight;
                collapseLoop(startHeight, 0);
                $description.removeClass('expanded');
                $(this).text(repocean_grid_js.read_more);
            } else {
                $description.addClass('expanding').css('max-height', collapsedHeight + 'px');
                expandLoop(collapsedHeight, 0);
                $(this).text(repocean_grid_js.hide);
            }
        });
    });
})(jQuery);
