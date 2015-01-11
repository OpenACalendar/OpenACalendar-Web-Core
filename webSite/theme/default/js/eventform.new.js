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
				html += '<div class="description">'+(event.description ? escapeHTMLNewLine(event.description) : '')+'</div>';
				html += '<div class="afterEventListing"></div>';
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
		var html = '<div id="EventPopup" class="popupBox">';
		html +=	'<div id="EventPopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div class="popupBoxContent">';
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

	$('#EventPopupContent').html('<div class="popupShowEvent">'+
			'<div id="EventPopupTitle" class="title">Loading ...</div>'+
			'<div id="EventPopupDescription" class="description"></div>'+
			'<div id="EventPopupGroupsWrapper"></div>'+
			'<div id="EventPopupVenueWrapper" class="popupShowVenue"></div>'+
			'<div id="EventPopupTimes" class="times"></div>'+
			'<div class="popupLink" id="EventPopupLinkYes"><a href="/event/' + eventSlug + '">Yes, this is the event!</a></div>'+
			'<div class="popupLink" id="EventPopupLinkNo"><a href="#" onclick="notDuplicateOfEvent(' + eventSlug + '); return false;">No, this is a different event!</a></div>'+
			'</div>');
	$.ajax({
		url: "/api1/event/"+eventSlug+"/info.json"
	}).success(function ( eventdata ) {
		var event = eventdata.data[0];
		$('#EventPopupTitle').text(event.summaryDisplay);
		$('#EventPopupDescription').html(escapeHTMLNewLine(event.description,500));
		$('#EventPopupTimes').html(escapeHTML(event.start.displaylocal)+" to " +escapeHTML(eventdata.data[0].end.displaylocal));
		if (event.venue) {
			$('#EventPopupVenueWrapper').html(
					'<div class="title">Venue '+escapeHTML(event.venue.title)+'</div>'+
					'<div class="description">'+(event.venue.description ? escapeHTMLNewLine(event.venue.description, 300) : '')+'</div>'+
					'<div class="address">'+(event.venue.address ? escapeHTMLNewLine(event.venue.address, 300) : '')+' '+(event.venue.addresscode ? escapeHTML(event.venue.addresscode) : '')+'</div>'
				);
		} else {
			$('#EventPopupVenueWrapper').html('&nbsp;');
		}
		var html = '';
		if (event.groups) {
			html += '<ul class="popupListGroups">';
			for(groupIdx in event.groups) {
				var group = event.groups[groupIdx];
				html += '<li class="group">';
				html += '<div class="title">Group '+escapeHTML(group.title)+'</div>';
				html += '<div class="description">'+(group.description ? escapeHTMLNewLine(group.description,300) : '')+'</div>';
				html += '</li>';
			}
			html += '</ul>';
		}
		$('#EventPopupGroupsWrapper').html(html);
	});
}

function notDuplicateOfEvent(eventSlug) {
	notDuplicateOfEventSlugs += eventSlug+",";
	closePopup();
	loadDupes();
}


