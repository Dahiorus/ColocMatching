/**
 * Event script for action on <table>
 */

/**
 * Callback for click event on a table row with the CSS class 'clickable-row'
 */
function onClickRow() {
	var /*jQuery*/ $clickableRow = $('table > tbody > tr.clickable-row');
	
	$clickableRow.hover(function () {
		$(this).css('cursor', 'pointer');
	}, function () {
		$(this).css('cursor','auto');
	});
	
	$clickableRow.click(function (e) {
		var /*string*/ href = $(this).data('href');
		
		window.location = href;
	});
}


$(document).ready(function (e) {
	onClickRow();
}).ajaxComplete(function (e) {
	onClickRow();
});