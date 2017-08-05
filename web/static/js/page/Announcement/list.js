/**
 * Script of the view /admin/announcement/
 */

$(document).ready(function (e) {
	$('ul.sidebar-menu > #announcements').addClass('active');
	
	// loading announcements
	$.get('/admin/announcement/list', {
		page: 1,
		limit: 20,
		order: 'ASC',
		sort: 'id'
	}, function (data, status, jqXHR) {
		$('#announcement-list-box .overlay').remove();
		$('#table-content').append(data);
	});
	
	// setting rent price range slider
	$('#rent-price-range').ionRangeSlider({
		type: 'double',
		grid: true,
        min: 0,
        max: 3000,
        step: 100,
		postfix: ' €',
		prettify_enabled: true
	});
	
	// setting Google address autocomplete
	initAddressAutocomplete('input#address');
	
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
			
			$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
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
			
			$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
				$('#table-content').html(data);
			});
		}
	});
}



function onSubmitSearch() {
	$('#search-form').submit(function (e) {
		e.preventDefault();
		
		var /*jQuery*/ $tableContent = $('#table-content');
		var /*Object*/ filter = getSearchFilter();
		
		$tableContent.empty();
		$tableContent.closest('.box').append(
			'<div class="overlay">\
				<i class="fa fa-refresh fa-spin"></i>\
			</div>');
		
		$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
			$tableContent.closest('.box').find('.overlay').remove();
			$tableContent.empty();
			$tableContent.append(data);
		});
	});
}


function getSearchFilter() {
	var /*jQuery*/ $form = $('#search-form');
	var /*Object*/ filter = {};
	
	filter.address = $form.find('input[name="address"]').val();
	filter.types = $form.find('select[name="types"]').val();
	
	var /*Object*/ rentPriceSlider = $form.find('input[name="rentPriceRange"]').data('ionRangeSlider');
	filter.rentPriceStart = rentPriceSlider.result.from;
	filter.rentPriceEnd = rentPriceSlider.result.to;
	
	filter.startDateBefore = $form.find('input[name="startDateBefore"]').val();
	filter.startDateAfter = $form.find('input[name="startDateAfter"]').val();
	filter.endDateBefore = $form.find('input[name="endDateBefore"]').val();
	filter.endDateAfter = $form.find('input[name="endDateAfter"]').val();
	
	var /*jQuery*/ $selectedSort = $('select[name="sort"]').find(':selected');
	filter.sort = $selectedSort.data('sort');
	filter.order = $selectedSort.data('order');
	
	return filter;
}