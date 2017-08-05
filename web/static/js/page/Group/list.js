/**
 * Script of the view /admin/announcement/
 */

$(document).ready(function (e) {
    $('ul.sidebar-menu > #groups').addClass('active');

    // loading groups
    $.get('/admin/group/list', {
        page: 1,
        limit: 20,
        order: 'ASC',
        sort: 'id'
    }, function (data, status, jqXHR) {
        $('#group-list-box .overlay').remove();
        $('#table-content').append(data);
    });

    // setting rent price range slider
    $('#rent-price-range').ionRangeSlider({
        type: 'double',
        grid: true,
        min: 0,
        max: 3000,
        step: 50,
        postfix: ' €',
        prettify_enabled: true
    });

    onSubmitSearch();
}).ajaxComplete(function () {
    onChangePageWithSearchFilter();
    onChangeSizeWithSearchFilter();
});


function onChangePageWithSearchFilter() {
    $('.pager a').click(function (e) {
        e.preventDefault();

        if ($(this).closest('li').hasClass('disabled')) {
            return false;
        }

        var /*string*/ url = $(this).attr('href');

        if (url.includes('search')) {
            var /*Object*/ filter = getSearchFilter();
            var /*string*/ url = $(this).attr('href');

            filter = Object.assign(extractUrlParams(url), filter);

            $.post('/admin/group/search', filter, function (data, status, jqXHR) {
                $('#table-content').html(data);
            });
        }
    });
}


function onChangeSizeWithSearchFilter() {
    $('#results-per-page li').click(function (e) {
        e.preventDefault();

        var /*string*/ url = $(this).find('a').attr('href');

        if (url.includes('search')) {
            var /*Object*/ filter = getSearchFilter();
            var url = $(this).find('a').attr('href');

            filter = Object.assign(extractUrlParams(url), filter);

            $.post('/admin/group/search', filter, function (data, status, jqXHR) {
                $('#table-content').html(data);
            });
        }
    });
}


function onSubmitSearch() {
    $('#search-form').submit(function (e) {
        e.preventDefault();

        var url = $(this).attr("action");
        var /*jQuery*/ $tableContent = $('#table-content');
        var /*Object*/ filter = getSearchFilter();

        $tableContent.empty();
        $tableContent.closest('.box').append(
            '<div class="overlay">\
                <i class="fa fa-refresh fa-spin"></i>\
            </div>');

        $.post(url, filter, function (data, status, jqXHR) {
            $tableContent.closest('.box').find('.overlay').remove();
            $tableContent.empty();
            $tableContent.append(data);
        });
    });
}


function getSearchFilter() {
    var /*jQuery*/ $form = $('#search-form');
    var /*Object*/ filter = {};

    var /*Object*/ rentPriceSlider = $form.find('input[name="rentPriceRange"]').data('ionRangeSlider');
    filter.budgetMin = rentPriceSlider.result.from;
    filter.budgetMax = rentPriceSlider.result.to;

    filter.status = $form.find('input[name="status"]').val();
    filter.countMembers = $form.find('input[name="countMembers"]').val();

    var /*jQuery*/ $selectedSort = $('select[name="sort"]').find(':selected');
    filter.sort = $selectedSort.data('sort');
    filter.order = $selectedSort.data('order');

    return filter;
}