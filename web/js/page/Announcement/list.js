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
		$('#list-content').html(data);
	});
	
	// setting rent price range slider
	$('#rent-price-range').ionRangeSlider({
		type: 'double',
		grid: true,
		min: 300,
		max: 1500,
		step: 50,
		postfix: ' €',
		prettify_enabled: true
	});
	
	// setting datepicker locale
	$('.date').datepicker({
		language: conf.locale
	});
	
	// setting Google address autocomplete
	initAddressAutocomplete('input#address');
	
	onSubmitSearch();
}).ajaxComplete(function () {
	onChangePage();
	onChangeSize();
	onClickAnnouncementBox();
});


function onChangePage() {
	$('.pager a').click(function (e) {
		e.preventDefault();
		
		if (!$(this).closest('li').hasClass('disabled')) {
			var /*string*/ url = $(this).attr('href');
			
			if (url.includes('list')) {
				$.get(url, {}, function (data, status, jqXHR) {
					$('#list-content').html(data);
				});
			}
			else if (url.includes('search')) {
				var /*Object*/ filter = getSearchFilter();
				var /*string*/ url = $(this).attr('href');
				
				filter = Object.assign(extractUrlParams(url), filter);
				
				$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
					$('#list-content').html(data);
				});
			}			
		}
	});
}


function onChangeSize() {
	$('#results-per-page li').click(function (e) {
		e.preventDefault();
		
		var /*string*/ url = $(this).find('a').attr('href');
		
		if (url.includes('list')) {
			$.get(url, {}, function (data, status, jqXHR) {
				$('#list-content').html(data);
			});
		}
		else if (url.includes('search')) {
			var /*Object*/ filter = getSearchFilter();
			var url = $(this).find('a').attr('href');
			
			filter = Object.assign(extractUrlParams(url), filter);
			
			$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
				$('#list-content').html(data);
			});
		}
	});
}


function onClickAnnouncementBox() {
	var /*jQuery*/ $box = $('.announcement-box');
	
	$box.hover(function () {
		$(this).css('cursor', 'pointer');
	}, function () {
		$(this).css('cursor','auto');
	});
	
	$box.click(function () {
		var /*string*/ id = $(this).data('id');
		
		window.location.replace('/admin/announcement/' + id);
	});
}


function onSubmitSearch() {
	$('#search-form').submit(function (e) {
		e.preventDefault();
		
		var /*jQuery*/ $listContent = $('#list-content');
		var /*Object*/ filter = getSearchFilter();
		
		$listContent.empty();
		
		$.post('/admin/announcement/search', filter, function (data, status, jqXHR) {
			$listContent.append(data);
		});
	});
}


function initAddressAutocomplete(/*string*/ inputSelector) {
	var /*jQuery*/ $input = $(inputSelector);
	var /*Autocomplete*/ autocomplete = new google.maps.places.Autocomplete($input.get(0), {
		componentRestrictions: {
			country: conf.locale
		}
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


function extractUrlParams(/*string*/ url) {
	var /*string*/ query = url.split('?')[1];
	var /*array*/ joinedParams = query.split('&');
	var /*array*/ params = {};
	
	for (var i = 0; i < joinedParams.length; i++) {
		var words = joinedParams[i].split('=');
		
		params[words[0]] = words[1];
	}
	
	return params;
}