/**
 * Global application JS script
 */
$(document).ready(function (e) {
	// adding skin to the page
	$('body').addClass('sidebar-mini skin-blue fixed');
	$('.breadcrumb').find('.breadcrumb-item').last().addClass('active');
	
	// setting datepicker locale
	$('.date').datepicker({
		language: conf.locale
	});
});


function initAddressAutocomplete(/*string*/ inputSelector) {
	var /*jQuery*/ $input = $(inputSelector);
	var /*Autocomplete*/ autocomplete = new google.maps.places.Autocomplete($input.get(0), {
		componentRestrictions: {
			country: conf.locale
		}
	});
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
