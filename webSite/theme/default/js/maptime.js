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
	
	map.setView([55.948792,-3.200115],5);
	
});

var mapData;
var mapDataPosition = 0;
var mapDataTimer;
var mapDataCurrentEvents;
var mapDataCurrentVenueMarkers;

function loadNewData() {
	
	$('#MapTimeControls').hide();
	
	$.ajax({
		url: "/map/time/getdata.json",
		context: document.body,
		data: $('#MapTimeControls').serialize(),
		method: 'POST'
	}).done(function( data ) {
		
		if (mapDataCurrentVenueMarkers) {
			for(venueSlug in mapDataCurrentVenueMarkers) {
				map.removeLayer(mapDataCurrentVenueMarkers[venueSlug]);
			}		
		}
		
		mapData = data;
		mapDataPosition = 0;
		mapDataCurrentEvents = {};
		mapDataCurrentVenueMarkers = {};
		$('#CurrentEvents li').remove();
		
		showNextDataItem();
		$('#MapTimeDisplay').show();
		
		mapDataTimer = window.setInterval(function(){showNextDataItem()},1000);
		
	});
	
}


function stop() {
	clearInterval(mapDataTimer);
	
	var thisData = mapData.data[mapDataPosition];
	$('#MapTimeControls input[name="day"]').val(thisData.day);
	$('#MapTimeControls select[name="month"]').val(thisData.month);
	$('#MapTimeControls input[name="year"]').val(thisData.year);
	$('#MapTimeControls input[name="hour"]').val(thisData.hour);
	$('#MapTimeControls input[name="min"]').val(thisData.min);

	$('#MapTimeControls').show();
	$('#MapTimeDisplay').hide();
}


function showNextDataItem() {

	var thisData = mapData.data[mapDataPosition];


	$('#MapTimeDisplayDay').html(thisData.day);
	$('#MapTimeDisplayMonth ').html(monthNames[thisData.month]);
	$('#MapTimeDisplayYear').html(thisData.year);
	$('#MapTimeDisplayHour').html(thisData.hour);
	$('#MapTimeDisplayMin').html(thisData.min);

	//console.log(mapDataPosition);

	mapDataPosition += 1;

	// add events
	var html = '';
	for(i in thisData.events) {
		mapDataCurrentVenueMarkers[thisData.events[i].venue_slug] = L.marker([thisData.events[i].venue_lat,thisData.events[i].venue_lng]);
		mapDataCurrentVenueMarkers[thisData.events[i].venue_slug].addTo(map);	
		mapDataCurrentEvents[thisData.events[i].slug] = thisData.events[i];
		
		html += '<li id="Event'+thisData.events[i].slug+'">';
		
		html += '<p class="eventTitle"><a href="/event/'+thisData.events[i].slug+'">'+escapeHTML(thisData.events[i].event_title)+'</a></p>';
		html += '<p class="venueTitle">'+escapeHTML(thisData.events[i].venue_title)+'</p>';
		html += '</li>';
		
	}
	if (html) {
		$('#CurrentEvents').append(html);
	}
	
	for(eventSlug in mapDataCurrentEvents) {
		if (!(eventSlug in thisData.eventsContinuing ) && !(eventSlug in thisData.events )) {
			delete mapDataCurrentEvents[eventSlug];
			$('#CurrentEvents li#Event'+eventSlug).remove();
		}
	}
	
	for(venueSlug in mapDataCurrentVenueMarkers) {
		var found = false;
		for(eventSlug in mapDataCurrentEvents) {
			if (venueSlug == mapDataCurrentEvents[eventSlug].venue_slug) {
				found = true;
			}
		}
		if (!found) {
			map.removeLayer(mapDataCurrentVenueMarkers[venueSlug]);
			delete mapDataCurrentVenueMarkers[venueSlug];
		}
	}
	
	
	
	if (!(mapDataPosition in mapData.data)) {
		clearInterval(mapDataTimer);
	}
}

 function escapeHTML(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
};

var monthNames = {1:'Jan', 2:'Feb',3:'Mar',4:'Apr',5:'May',6:'Jun',7:'Jul',8:'Aug',9:'Sep',10:'Oct',11:'Nov',12:'Dec'};

