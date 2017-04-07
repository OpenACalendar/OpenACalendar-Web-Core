/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var markerGroup;

var iconWithEvents;
var iconWithNoEvents;

$(document).ready(function() {

	map = L.map('Map');
	configureBasicMap(map);

	iconWithNoEvents = L.icon({
		iconUrl: '/theme/default/img/mapMarkerNoeventsIcon.png',
		shadowUrl: '/theme/default/img/mapMarkerShadow.png',

		iconSize:     [25, 41], // size of the icon
		shadowSize:   [41, 41], // size of the shadow
		iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
		shadowAnchor: [12, 41],  // the same for the shadow
		popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
	});

	iconWithEvents = L.icon({
		iconUrl: '/theme/default/img/mapMarkerEventsIcon.png',
		shadowUrl: '/theme/default/img/mapMarkerShadow.png',

		iconSize:     [25, 41], // size of the icon
		shadowSize:   [41, 41], // size of the shadow
		iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
		shadowAnchor: [12, 41],  // the same for the shadow
		popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
	});

	var hasMarkers = false;
	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);
	for(i in mapData) {
		if (mapData[i].lat && mapData[i].lng) {
			var marker;
			if (mapData[i].cached_events == 0) {
				marker = L.marker([mapData[i].lat,mapData[i].lng], { icon: iconWithNoEvents});
			} else {
				marker = L.marker([mapData[i].lat,mapData[i].lng], { icon: iconWithEvents});
			}
			marker.slug = mapData[i].slug;
			marker.on('click', onClickMarker);
			markerGroup.addLayer(marker);
			hasMarkers = true;
		}
	}

	if (venue) {
		map.setView([venue.lat, venue.lng],17);
	} else if (area && area.maxLat && area.minLat && area.maxLng && area.minLng) {
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
		var html = '<div id="VenuePopup" class="popupBox" style="display: none;">';
		html +=	'<div id="VenuePopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><div class="fa fa-times fa-lg"></div></a></div>';
		html += '<div id="VenuePopupContent"  class="popupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	}
	showPopup();
	$('#VenuePopup').fadeIn(500);

	$('#VenuePopupContent').html('<div class="popupShowPlace"><div id="VenuePopupTitle" class="title">Loading ...</div></div>'+
			'<div id="VenuePopupEvents"></div>'+
			'<div class="popupLink"><a href="/venue/' + this.slug + '">View More Details</a></div>');
	$.ajax({
		url: "/api1/venue/"+this.slug+"/events.json"
	}).success(function ( venuedata ) {
		var html = '<ul class="popupListEvents">';
		if (venuedata.data.length == 0) {
			html += '<li class="nodata">No future events.</li>';
		} else {
			for(i in venuedata.data) {
				var event = venuedata.data[i];
				html += '<li class="event"><a href="/event/'+event.slugforurl+'"><span class="time">'+event.start.displaylocal+'</span> <span class="summary">'+event.summaryDisplay+'</span></a></li>';
			}
		}
		$('#VenuePopupEvents').html(html+'</ul>');
		$('#VenuePopupTitle').html(venuedata.venue.title);
	});
	
}

