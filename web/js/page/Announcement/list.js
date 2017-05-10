/**
 * Script of the view /admin/announcements/
 */

$(document).ready(function (e) {
	$('ul.sidebar-menu > li#announcements').addClass('active');
	
	// loading announcements
	$.get('/admin/announcement/list', {
		page: 1,
		limit: 20,
		order: 'ASC',
		sort: 'id'
	}, function (data, status, jqXHR) {
		$('div#list-content').html(data);
	});
	
	// setting rent price range slider
	$('input#rent-price-range').ionRangeSlider({
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
}).ajaxComplete(function () {
	onChangePage();
	onChangeSize();
});


function onChangePage() {
	$('.pager a').click(function (e) {
		e.preventDefault();
		
		if (!$(this).closest('li').hasClass('disabled')) {
			var /*string*/ url = $(this).attr('href');
			
			if (url.includes('list')) {
				$.get(url, {}, function (data, status, jqXHR) {
					$('div#list-content').html(data);
				});
			}
			else if (url.includes('search')) {
				// get search filters
			}
			
			
		}
	});
}


function onChangeSize() {
	$('.results-per-page li').click(function (e) {
		e.preventDefault();
		
		var /*string*/ url = $(this).find('a').attr('href');
		
		if (url.includes('list')) {
			$.get(url, {}, function (data, status, jqXHR) {
				$('div#list-content').html(data);
			});
		}
		else if (url.includes('search')) {
			// get search filters
		}
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
	
}