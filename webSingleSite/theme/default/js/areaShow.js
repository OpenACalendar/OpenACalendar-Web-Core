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
	configureBasicMap(map);

	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);
	
	if (areaData.maxLat && areaData.maxLng && areaData.minLat && areaData.minLng) {
	
		var southWest = L.latLng(areaData.minLat, areaData.minLng),
			northEast = L.latLng(areaData.maxLat, areaData.maxLng),
			bounds = L.latLngBounds(southWest, northEast);
			
		map.fitBounds(bounds);
	
	} else {
		map.setView([55.952035, -3.196807], 3);
	}

	$.ajax({
			dataType: "json",
			url: '/area/'+areaData.slug+'/info.json?includeVenues=1',
			success: function(data) {
				
				for(i in data.venues) {
					hasMapPos = data.venues[i].lat && data.venues[i].lng;
					if (hasMapPos) {
						var marker = L.marker([data.venues[i].lat,data.venues[i].lng]);
						marker.bindPopup(escapeHTML(data.venues[i].title)+'<br><a href="/venue/'+data.venues[i].slug+'">More details</a>');
						markerGroup.addLayer(marker);
					}
				}
			}
		});


});
