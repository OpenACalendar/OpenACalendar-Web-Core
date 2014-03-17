/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var markerGroup;

$(document).ready(function() {

	map = L.map('Map');
	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);
	
	var hasMarkers = false;
	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);
	for(i in mapData) {
		if (mapData[i].lat && mapData[i].lng) {
			var marker = L.marker([mapData[i].lat,mapData[i].lng]);
			marker.slug = mapData[i].slug;
			marker.on('click', onClickMarker);
			markerGroup.addLayer(marker);
			hasMarkers = true;
		}
	}
	
	if (area && area.maxLat && area.minLat && area.maxLng && area.minLng) {
		var southWest = L.latLng(area.minLat, area.minLng),
			northEast = L.latLng(area.maxLat, area.maxLng),
			bounds = L.latLngBounds(southWest, northEast);
		map.fitBounds(bounds);
	} else if (country && country.maxLat && country.minLat && country.maxLng && country.minLng) {
		var southWest = L.latLng(country.minLat, country.minLng),
			northEast = L.latLng(country.maxLat, country.maxLng),
			bounds = L.latLngBounds(southWest, northEast);
		map.fitBounds(bounds);
	} else if (hasMarkers) {
		map.fitBounds(markerGroup.getBounds());
	} else {
		map.setView([55.948792,-3.200115],5);
	}
	
});

function onClickMarker() {
	var div = $('#VenuePopup');
	if (div.size() == 0) {
		var html = '<div id="VenuePopup" class="PopupBox">';
		html +=	'<div id="VenuePopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div id="VenuePopupContent"  class="PopupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	} else {
		div.show();
	}
	showPopup();

	$('#VenuePopupContent').html('<div id="VenuePopupTitle" class="PopUpTitle">Loading ...</div>'+
			'<div id="VenuePopupEvents"></div>'+
			'<div id="VenuePopupLink"><a href="/venue/' + this.slug + '">View More Details</a></div>');
	$.ajax({
		url: "/api1/venue/"+this.slug+"/events.json"
	}).success(function ( venuedata ) {
		var html = '<ul class="eventSmallListings">';
		for(i in venuedata.data) {
			var event = venuedata.data[i];
			html += '<li class="eventSmallListing"><span class="time">'+event.start.displaylocal+'</span> <span class="summary">'+event.summaryDisplay+'</span></li>';
		}
		$('#VenuePopupEvents').html(html+'</ul>');
		$('#VenuePopupTitle').html(venuedata.venue.title);
	});
	
}

