/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var marker;

$(document).ready(function() {
	
	if (mapData.lat && mapData.lng) {
		map = L.map('Map', { 'scrollWheelZoom':false });
		configureBasicMap(map);
		map.setView([mapData.lat,mapData.lng], 13);

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

