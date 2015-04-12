/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/


var map;
var markerGroup;
var venueMarkers = {};

$(document).ready(function() {
	var form = $('#NewEventForm');
	form.change(function() { maybeLoadData(); });
	form.keyup(onSearchFormKeyUp);
	$('#NewEventForm input[type="submit"]').hide();

	map = L.map('Map');
	configureBasicMap(map);
	
	mapToBounds(countryMinLat, countryMaxLat, countryMinLng, countryMaxLng );

	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);
});

var keyUpTimer;

function onSearchFormKeyUp(event) {
	if (event.keyCode != '9') {
		clearTimeout(keyUpTimer);
		keyUpTimer = setTimeout(maybeLoadData, 1000);
	}
}


var lastFormSerialized = "XXX";

function maybeLoadData() {
	clearTimeout(keyUpTimer);
	var thisFormSerialized = $('#NewEventForm').serialize();
	if (lastFormSerialized == thisFormSerialized) {
		return;
	}
	lastFormSerialized = thisFormSerialized;
	loadData();
}


function loadDataSetLoadingIndicators() {
	$('#EditEventVenueSearchResults li.result, #EditEventVenueSearchResults li.nodata, #EditEventVenueSearchResults li.information').remove();
	$("#EditEventVenueSearchResults").prepend('<li class="information"><img src="/theme/default/img/ajaxLoading.gif"> Loading, please wait ...</li>');
}

function loadDataGotData(data) {
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
				html += '<form action="/event/new/'+newEventDraftSlug+'/'+currentStepID+'" method="post" class="oneActionFormRight">';
				html += '<input type="hidden" name="action" value="setthisvenue">';
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
			html += '<li class="nodata">Sorry, we don\'t know about that venue yet.</li>'
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

/** We make sure we are sending the latest the user typed to the next page, even if the search AJAX hasn't loaded yet. **/
function onSubmitNewVenue() {
	$('#VenueNewWrapper form input[name="fieldTitle"]').val($('#TitleField').val());
	$('#VenueNewWrapper form input[name="fieldAddress"]').val($('#AddressField').val());
	$('#VenueNewWrapper form input[name="fieldAreaSearchText"]').val($('#AreaField').val());
	$('#VenueNewWrapper form input[name="fieldAddressCode"]').val($('#AddressCodeField').val());
    var selectedVal = "";
    var selected = $("#NewEventForm input[type='radio'][name='searchAreaSlug']:checked");
    if (selected.length > 0) {
        selectedVal = selected.val();
    }
	$('#VenueNewWrapper form input[name="fieldAreaSlug"]').val(selectedVal);
}
