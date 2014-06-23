/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;

$(document).ready(function() {
	
	map = L.map('Map');
		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);	
	
	if (countries.length < 20) {
		var minLat = countries[0].minLat;
		var minLng = countries[0].minLng;
		var maxLat = countries[0].maxLat;
		var maxLng = countries[0].maxLng;
		for (var i = 0; i < countries.length; i++) {
			var country = countries[i];
			if (country.minLat < minLat) minLat  = country.minLat;
			if (country.minLng < minLng) minLng  = country.minLng;
			if (country.maxLat > maxLat) maxLat  = country.maxLat;
			if (country.maxLng > maxLng) maxLng  = country.maxLng;
		}
		map.fitBounds([[minLat, minLng],[maxLat, maxLng]]);
	} else {
		map.setView([55.952035, -3.196807], 3);
	}
});
