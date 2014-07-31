/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

var map;

$(document).ready(function() {
	$('#SearchField').change(onSearchFormChanged).keyup(onSearchFormKeyUp);
	$('#EditEventAreaForm input[type="submit"]').hide();

	map = L.map('Map');

	mapToBounds(countryMinLat, countryMaxLat, countryMinLng, countryMaxLng );

	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);
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
	var thisFormSerialized = $('#EditEventAreaForm').serialize();
	if (lastFormSerialized == thisFormSerialized) {
		return;
	}
	lastFormSerialized = thisFormSerialized;
	$('#EditEventAreaResults li').remove();
	$("#EditEventAreaResults").prepend('<li class="information"><img src="/theme/default/img/ajaxLoading.gif"> Loading, please wait ...</li>');
	loadSearchResultsAJAX = $.ajax({
		data: thisFormSerialized,
		dataType: "json",
		url: '/event/'+currentEventSlug+'/edit/area.json',
		success: function(data) {
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
						html += '<li class="area" onmouseover="mapToBounds('+areas[i].minLat+', '+areas[i].maxLat+', '+areas[i].minLng+', '+areas[i].maxLng+')">';
					} else {
						html += '<li class="area">';
					}
					html += '<form action="/event/'+currentEventSlug+'/edit/area" method="post" class="oneActionFormRight">';
					html += '<input type="hidden" name="CSFRToken" value="'+CSFRToken+'">';
					html += '<input type="hidden" name="area_slug" value="' + escapeHTML(areas[i].slug)+'">';
					html += '<input type="submit" value="Select ' + escapeHTML(areas[i].title)+'">';
					html += '</form>';
					html += '<span class="content">' + escapeHTML(areas[i].title)+(areas[i].parent1title ? ", "+escapeHTML(areas[i].parent1title):'')+'</span>';
					html += '<div class="afterOneActionFormRight"></div></li>';
				}
			} else {
				html += '<li class="information">Sorry, nothing found.</li>'
			}
			$('#EditEventAreaResults li.information').remove();
			$("#EditEventAreaResults").prepend(html);
		}
	});

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
