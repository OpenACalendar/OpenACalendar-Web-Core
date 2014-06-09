/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var marker;

$(document).ready(function() {
	
	if (mapData.lat && mapData.lng) {
		map = L.map('Map')
		map.setView([mapData.lat,mapData.lng], 13);
	
		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);

		marker = L.marker([mapData.lat,mapData.lng]);
		marker.addTo(map);
	} else {
		$('#Map').hide();
	}

	var PushToChildAreaForm = $('form#PushToChildAreaForm');
	if (PushToChildAreaForm.length) {
		$('form#PushToChildAreaForm input[name="newAreaTitle"]').keyup(function() {
			if ($(this).val() != '') {
				$('form#PushToChildAreaForm li.newarea input[name="area"]').prop("checked", true);
			}
		});
	}

});

