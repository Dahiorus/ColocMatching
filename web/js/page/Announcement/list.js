/**
 * Script of the view /admin/announcements/
 */

$(document).ready(function (e) {
	$('ul.sidebar-menu > li#announcements').addClass('active');
	
	$.get('/admin/announcement/list', {
		page: 1,
		limit: 20,
		order: 'ASC',
		sort: 'id'
	}, function (data, status, jqXHR) {
		$('div#list-content').html(data);
	});
	
}).ajaxComplete(function () {
	onChangePage();
	onChangeSize();
});


function onChangePage() {
	$('.pager li').click(function (e) {
		e.preventDefault();
		
		if (!$(this).hasClass('disabled')) {
			var /*string*/ url = $(this).find('a').attr('href');
			
			$.get(url, {}, function (data, status, jqXHR) {
				// get filters
				
				$('div#list-content').html(data);
			});
		}
	});
}


function onChangeSize() {
	$('.results-per-page li').click(function (e) {
		e.preventDefault();
		
		var /*string*/ url = $(this).find('a').attr('href');
		
		$.get(url, {}, function (data, status, jqXHR) {
			// get filters
			
			$('div#list-content').html(data);
		});
	});
}