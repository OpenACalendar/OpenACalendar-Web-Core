/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

var map;
var markerGroup;
var venueMarkers = {};


$(document).ready(function() {
	$('#TitleField, #AddressField, #AreaField, #AddressCodeField').change(onSearchFormChanged).keyup(onSearchFormKeyUp);
	$('#EditEventVenueForm input[type="submit"]').hide();

	map = L.map('Map');

	mapToBounds(countryMinLat, countryMaxLat, countryMinLng, countryMaxLng );
	configureBasicMap(map);

	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);

	// We want all venues to be on map so if user scrolls down manually they see all of them and don't think one isn't missing.
	// For now load all venues in country and put on map. We could probably do this more intelligently later.
	$.ajax({
		dataType: "json",
		url: '/country/'+countryID+'/info.json?includeVenues=1',
		success: function(data) {
			for(i in data.venues) {
				if (!(data.venues[i].slug in venueMarkers) && data.venues[i].lat && data.venues[i].lng) {
					venueMarkers[data.venues[i].slug] = L.marker([data.venues[i].lat,data.venues[i].lng]);
					venueMarkers[data.venues[i].slug].bindPopup(escapeHTML(data.venues[i].title)+'<br><a href="#" onclick="useVenue('+data.venues[i].slug+'); return false">At this venue</a>');
					markerGroup.addLayer(venueMarkers[data.venues[i].slug]);
				}
			}
		}
	});
});

var keyUpTimer;

function onSearchFormKeyUp(event) {
	if (event.keyCode != '9') {
		clearTimeout(keyUpTimer);
		keyUpTimer = setTimeout(loadSearchResults, 1000);
	}
}

function onSearchFormChanged() {
	loadSearchResults();
}

var loadSearchResultsAJAX;
var lastFormSerialized = "XXX";

function loadSearchResults() {
	clearTimeout(keyUpTimer);
	var thisFormSerialized = $('#EditEventVenueForm').serialize();
	if (lastFormSerialized == thisFormSerialized) {
		return;
	}
	lastFormSerialized = thisFormSerialized;
	$('#EditEventVenueSearchResults li.result, #EditEventVenueSearchResults li.information').remove();
	$("#EditEventVenueSearchResults").prepend('<li class="information"><img src="/theme/default/img/ajaxLoading.gif"> Loading, please wait ...</li>');
	loadSearchResultsAJAX = $.ajax({
		data: $('#EditEventVenueForm').serialize(),
		dataType: "json",
		url: '/event/'+currentEventSlug+'/edit/venue.json',
		success: function(data) {
			var html = '';
			if (data.venueSearchDone) {
				if (data.venues.length > 0) {
					var venues = $.map(data.venues, function(value, index) {
						return [value];
					});;
					venues.sort(function(a,b) {
						if (a.title.toLowerCase() > b.title.toLowerCase()) {
							return 1;
						} else if (a.title.toLowerCase() < b.title.toLowerCase()) {
							return -1;
						} else {
							return 0;
						}
					});
					var resultsBounds;
					var resultsBoundsValid = false;
					for(i in venues) {
						if (venues[i].lat && venues[i].lng) {
							html += '<li class="venue result" onmouseover="mapToLatLng('+venues[i].lat+','+venues[i].lng+')">';
						} else {
							html += '<li class="venue result">';
						}
						var title = venues[i].title.split(', ').shift();
						html += '<form action="/event/'+currentEventSlug+'/edit/venue" method="post" class="oneActionFormRight">';
						html += '<input type="hidden" name="CSFRToken" value="'+CSFRToken+'">';
						html += '<input type="hidden" name="venue_slug" value="' + escapeHTML(venues[i].slug)+'">';
						html += '<input type="submit" value="Select ' + escapeHTML(title)+'">';
						html += '</form>';
						html += '<div class="title">' + escapeHTML(venues[i].title)+'</div>';
						if (data.venues[i].address) {
							html += '<div>' + escapeHTMLNewLine(venues[i].address)+'</div>';
						}
						if (data.venues[i].addresscode) {
							html += '<div>' + escapeHTML(venues[i].addresscode)+'</div>';
						}
						html += '<div class="afterOneActionFormRight"></div></li>';
						if (venues[i].lat && venues[i].lng) {
							// Ensure this venue is on the map.
							// (We may have all venues on may already but if we are doing smart loading we may not.
							if (!(venues[i].slug in venueMarkers)) {
								venueMarkers[venues[i].slug] = L.marker([venues[i].lat,venues[i].lng]);
								venueMarkers[venues[i].slug].bindPopup(escapeHTML(venues[i].title)+'<br><a href="#" onclick="useVenue('+venues[i].slug+'); return false">At this venue</a>');
								markerGroup.addLayer(venueMarkers[venues[i].slug]);
							}
							if (resultsBounds) {
								resultsBounds.extend(venueMarkers[venues[i].slug].getLatLng());
							} else {
								resultsBounds = L.latLngBounds(venueMarkers[venues[i].slug].getLatLng(), venueMarkers[venues[i].slug].getLatLng());
							}
							resultsBoundsValid = true;
						}
					}
					if (resultsBoundsValid) map.fitBounds(resultsBounds);
				} else {
					html += '<li class="information">Sorry, nothing found.</li>'
				}
				$('#VenueNewWrapper').show();
			} else {
				$('#VenueNewWrapper').hide();
			}
			$('#EditEventVenueSearchResults li.information').remove();
			$("#EditEventVenueSearchResults").prepend(html);
			var html = '';
			if (data.areas.length > 0) {
				var areas = $.map(data.areas, function(value, index) {
					return [value];
				});
				areas.sort(function(a,b) {
					if (a.title.toLowerCase() > b.title.toLowerCase()) {
						return 1;
					} else if (a.title.toLowerCase() < b.title.toLowerCase()) {
						return -1;
					} else {
						return 0;
					}
				});
				for(i in areas) {
					var htmlS = (areas[i].slug == data.searchAreaSlug) ?  'checked="checked"' : '';
					html += '<li><label><input name="searchAreaSlug" type="radio" value="'+escapeHTML(areas[i].slug)+'"'+htmlS+' onchange="onSearchFormChanged();">';
					html += escapeHTML(areas[i].title)+( areas[i].parent1title ? ", "+areas[i].parent1title:'' )+'</label></li>'
				}
			}
			$('#AreaList').html(html);
			$('#VenueNewWrapper form input[name="fieldTitle"]').val($('#TitleField').val());
			$('#VenueNewWrapper form input[name="fieldAddress"]').val($('#AddressField').val());
			$('#VenueNewWrapper form input[name="fieldArea"]').val($('#AreaField').val());
			$('#VenueNewWrapper form input[name="fieldAddressCode"]').val($('#AddressCodeField').val());
			$('#VenueNewWrapper form input[name="fieldAreaSlug"]').val(data.searchAreaSlug);
		}
	});


}

function mapToLatLng(lat, lng) {
	map.setView([lat,lng], 15);
}

function mapToBounds(minLat, maxLat, minLng, maxLng) {
	if (minLat == maxLat || minLng == maxLng) {
		map.setView([minLat,minLng], 13);
	} else {
		var southWest = L.latLng(minLat, minLng),
			northEast = L.latLng(maxLat, maxLng),
			bounds = L.latLngBounds(southWest, northEast);
		map.fitBounds(bounds);
	}
}

function useVenue(slug) {
	var html = '<form action="/event/'+currentEventSlug+'/edit/venue" method="post" id="UseVenueFunctionForm">';
	html += '<input type="hidden" name="CSFRToken" value="'+CSFRToken+'">';
	html += '<input type="hidden" name="venue_slug" value="' + slug+'">';
	html += '</form>';
	$('body').append(html);
	$('#UseVenueFunctionForm').submit();
}

