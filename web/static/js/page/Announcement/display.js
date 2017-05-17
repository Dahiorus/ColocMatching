/**
 * Script of the view /admin/announcement/{id}
 */

$(document).ready(function (e) {
	// setting sidebar current menu to active
	$('ul.sidebar-menu > #announcements').addClass('active');

	// initiating carousel
	$('#announcement-pictures li.carousel-indicators-item').first().addClass('active');
	$('#announcement-pictures div.item').first().addClass('active');
	
	// initializing Goole map
	initMap();
});


function initMap() {
	var /*jQuery*/ $mapContainer = $('#announcement-map');
	var /*LatLng*/ location = new google.maps.LatLng($mapContainer.data('lat'), $mapContainer.data('lng'));
	
	var /*Map*/ map = new google.maps.Map($mapContainer.get(0), {
		zoom: 15,
		center: location
	});
	var /*Marker*/ marker = new google.maps.Marker({
		position: location,
		map: map
	});
}