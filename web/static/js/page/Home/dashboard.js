/**
 * Script of the view /admin
 */

$(document).ready(function() {
	var total = $('#announcement-map').data('total');
	
	initMap();
});


function initMap() {
	// center of France
	var /*LatLng*/ center = new google.maps.LatLng(46.52863469527167, 2.43896484375);
	var /*jQuery*/ $mapContainer = $('#announcement-map');
	
	var /*Map*/ map = new google.maps.Map($mapContainer.get(0), {
		zoom: 6,
		center: center
	});
	
	$('span.announcement-latlng').each(function (index, object) {
		var lat = $(object).data('lat');
		var lng = $(object).data('lng');
		
		var position = new google.maps.LatLng(lat, lng);
		var marker = new google.maps.Marker({
			position: position,
			map: map
		});
	});
}