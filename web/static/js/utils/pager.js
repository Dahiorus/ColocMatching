/**
 * Event script for simple pager click action (without search filter)
 */
$(document).ready(function () {
	onChangePage();
	onChangeSize();
}).ajaxComplete(function () {
	onChangePage();
	onChangeSize();
});

function onChangePage() {
	$('.pager a').click(function (e) {
		e.preventDefault();
		
		if ($(this).closest('li.pager-item').hasClass('disabled')) {
			return false;
		}
		
		var /*string*/ url = $(this).attr('href');
		
		if (url.includes('list')) {
			$.get(url, {}, function (data, status, jqXHR) {
				$('#list-content').html(data);
			});
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
	});
}