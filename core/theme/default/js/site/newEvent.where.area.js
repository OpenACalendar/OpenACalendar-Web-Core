/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

var map;


$(document).ready(function() {
	var form = $('#NewEventForm');
	form.change(function() { maybeLoadData(); });
	form.keyup(onSearchFormKeyUp);
	$('#NewEventForm input[type="submit"]').hide();

	map = L.map('Map');
	configureBasicMap(map);
	
	mapToBounds(countryMinLat, countryMaxLat, countryMinLng, countryMaxLng );
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
	$('#EditEventAreaResults li.result').remove();
	$("#EditEventAreaResults").prepend('<li class="information"><img src="/theme/default/img/ajaxLoading.gif"> Loading, please wait ...</li>');
}

function loadDataGotData(data) {

	var html = '';
	if (data.areas.length > 0) {
		var areas = $.map(data.areas, function(value, index) {
			return [value];
		});;
		areas.sort(function(a,b) {
			if (a.title.toLowerCase() > b.title.toLowerCase()) {
				return 1;
			} else if (a.title.toLowerCase() < b.title.toLowerCase()) {
				return -1;
			} else {
				return 0;
			}
		});
		var html = '';
		for(i in areas) {
			if (areas[i].minLat) {
				html += '<li class="area result" onmouseover="mapToBounds('+areas[i].minLat+', '+areas[i].maxLat+', '+areas[i].minLng+', '+areas[i].maxLng+')">';
			} else {
				html += '<li class="area result">';
			}
			html += '<form action="/event/new/'+newEventDraftSlug+'/'+currentStepID+'" method="post" class="oneActionFormRight">';
			html += '<input type="hidden" name="action" value="setthisarea">';
			html += '<input type="hidden" name="area_slug" value="' + escapeHTML(areas[i].slug)+'">';
			html += '<input type="submit" value="Select ' + escapeHTML(areas[i].title)+'">';
			html += '</form>';
			html += '<div class="title">' + escapeHTML(areas[i].title)+'</div>';
			if (areas[i].parent1title) {
				html += '<div>' + escapeHTML(areas[i].parent1title)+'</div>';
			}
			html += '<div class="afterOneActionFormRight"></div></li>';
		}

	} else {
		html += '<li class="information">Sorry, nothing found.</li>';

		html += '<li class="area">';
		html += '<form action="/event/new/'+newEventDraftSlug+'/'+currentStepID+'" method="post" class="oneActionFormRight">';
		html += '<input type="hidden" name="action" value="setnoareavenue">';
		html += '<input type="submit" value="Next">';
		html += '</form>';
		html += '<div class="afterOneActionFormRight"></div></li>';
	}
	$('#EditEventAreaResults li.information').remove();
	$("#EditEventAreaResults").append(html);
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
