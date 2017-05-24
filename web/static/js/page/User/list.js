/**
 * Script of the view /admin/announcement/
 */

$(document).ready(function (e) {
	$('ul.sidebar-menu > #users').addClass('active');
	
	// loading announcements
	$.get('/admin/user/list', {
		page: 1,
		limit: 20,
		order: 'ASC',
		sort: 'id'
	}, function (data, status, jqXHR) {
		$('#user-list-box .overlay').remove();
		$('#table-content').append(data);
	});
	
	onSubmitSearch();
}).ajaxComplete(function () {
	onChangePageWithSearchFilter();
	onChangeSizeWithSearchFilter();
	onClickAnnouncementBox();
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
			
			$.post('/admin/user/search', filter, function (data, status, jqXHR) {
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
			
			$.post('/admin/user/search', filter, function (data, status, jqXHR) {
				$('#table-content').html(data);
			});
		}
	});
}


function onClickAnnouncementBox() {
	var /*jQuery*/ $box = $('.user-box');
	
	$box.hover(function () {
		$(this).css('cursor', 'pointer');
	}, function () {
		$(this).css('cursor','auto');
	});
	
	$box.click(function () {
		var /*string*/ href = $(this).data('href');
		
		window.location.href = href;
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
		
		$.post('/admin/user/search', filter, function (data, status, jqXHR) {
			$tableContent.closest('.box').find('.overlay').remove();
			$tableContent.append(data);
		});
	});
}


function getSearchFilter() {
	var /*jQuery*/ $form = $('#search-form');
	var /*Object*/ filter = {};
	
	filter.createdAtSince = $form.find('input[name="createdAtSince"]').val();
	filter.createdAtUntil = $form.find('input[name="createdAtUntil"]').val();
	filter.type = $form.find('select[name="type"]').val();
	filter.status = $form.find('select[name="status"]').val();
	// filter.lastLogin = $form.find('input[name="lastLogin"]').val();
	
	var /*jQuery*/ $selectedSort = $('select[name="sort"]').find(':selected');
	filter.sort = $selectedSort.data('sort');
	filter.order = $selectedSort.data('order');
	
	console.log(filter);
	
	return filter;
}