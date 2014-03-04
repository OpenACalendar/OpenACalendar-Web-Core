/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

var notDuplicateOfEventSlugs = "";

$(document).ready(function() {
	$('#NewEventForm').change(function() {
		loadDupes();
	});	
	loadDupes();
});

function loadDupes() {
	var dataIn = $('#NewEventForm').serialize();
	$.post('/event/creatingThisNewEvent.json?notDuplicateSlugs='+notDuplicateOfEventSlugs, dataIn,function(data) {
		if (data.duplicates.length == 0) {
			$('#DuplicateEventsContainer').hide();
		} else {
			var html = '';
			for (idx in data.duplicates) {
				var event = data.duplicates[idx];
				html += '<li class="event">';
				html += '<div class="dateTimeIcon"><a href="#" onclick="showEventPopup('+ event.slug +'); return false;">';
				html += '<div class="dateIcon">';
				html += '<span class="startDay">'+event.startDay+'</span>';
				html += '<span class="startDate">'+event.startDate+'</span>';
				html += '<span class="startMonthYear">'+event.startMonthYear+'</span>';
				html += '</div>';
				html += '<div class="timeIcon">';
				html += '<span class="startTime">'+event.startTime+'</span>';
				html += '</div>';
				html += '</a></div>';
				html += '<div class="title"><a href="#" onclick="showEventPopup('+ event.slug +'); return false;">'+escapeHTML(event.summary)+'</a></div>';
				html += '<div class="description">'+escapeHTMLNewLine(event.description)+'</div>';
				html += '</li>';
			}
			$('#DuplicateEventsList').empty().append(html);
			$('#DuplicateEventsContainer').show();
		}
	});
}

function showEventPopup(eventSlug) {
	var div = $('#EventPopup');
	if (div.size() == 0) {
		var html = '<div id="EventPopup" class="PopupBox">';
		html +=	'<div id="EventPopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div class="PopupBoxContent">';
		html += '<div id="EventPopupContent">';
		html += '</div>';
		html += '<div id="EventPopupAttendanceContent">';
		html += '</div>';		
		html += '</div>';		
		html += '</div>';
		$('body').append(html);
	} else {
		div.show();
	}
	showPopup();

	$('#EventPopupContent').html('<div id="EventPopupTitle">Loading ...</div>'+
			'<div id="EventPopupDescription"></div>'+
			'<div id="EventPopupTimes"></div>'+
			'<div id="EventPopupLink"><a href="/event/' + eventSlug + '">Yes, this is the event!</a></div>'+
			'<div id="EventPopupLink"><a href="#" onclick="notDuplicateOfEvent(' + eventSlug + '); return false;">No, this is a different event!</a></div>');
	$.ajax({
		url: "/api1/event/"+eventSlug+"/info.json"
	}).success(function ( eventdata ) {
		$('#EventPopupTitle').text(eventdata.data[0].summaryDisplay);
		$('#EventPopupDescription').html(escapeHTMLNewLine(eventdata.data[0].description,1000));
		$('#EventPopupTimes').html(escapeHTML(eventdata.data[0].start.displaylocal)+" to " +escapeHTML(eventdata.data[0].end.displaylocal));
	});
}

function notDuplicateOfEvent(eventSlug) {
	notDuplicateOfEventSlugs += eventSlug+",";
	closePopup();
	loadDupes();
}


