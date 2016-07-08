/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
function showEventPopup(data) {
	if ($('#EventPopup').size() == 0) {
		var html = '<div id="EventPopup" class="popupBox" style="display: none">';
		html +=	'<div id="EventPopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><div class="fa fa-times fa-lg"></div></a></div>';
		html += '<div class="popupBoxContent">';
		html += '<div id="EventPopupContent">';
		html += '</div>';
		html += '<div id="EventPopupAttendanceContent">';
		html += '</div>';		
		html += '</div>';		
		html += '</div>';
		$('body').append(html);
	}
	$('#EventPopup').fadeIn(500);
	showPopup();

	$('#EventPopupContent').html('<div class="popupShowEvent">'+
		'<div id="EventPopupTitle" class="title">Loading ...</div>'+
		'<div id="EventPopupDescription" class="description"></div>'+
		'<div id="EventPopupMedias"></div>'+
		'<div id="EventPopupGroupsWrapper"></div>'+
		'<div id="EventPopupVenueWrapper" class="popupShowVenue"></div>'+
		'<div id="EventPopupTimes" class="times"></div>'+
		'<div class="popupLink"><a href="/event/' + data.slugForURL + '">View More Details</a></div>'+
		'<div id="EventPopupTickets" class="popupLink"><a href="/event/' + data.slugForURL + '" target="_blank" rel="noopener">Tickets from </a></div>'+
		'</div>');
	$.ajax({
		url: "/api1/event/"+data.slug+"/info.json?includeMedias=yes"
	}).success(function ( eventdata ) {
	var event = eventdata.data[0];
        if (event.cancelled) {
            $('#EventPopupTitle').text(event.summaryDisplay + " [CANCELLED]");
        } else {
            $('#EventPopupTitle').text(event.summaryDisplay);
        }
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
		if (event.medias) {
			html += '<ul class="mediaGrid">';
			for(id in event.medias) {
				html += '<li class="media"><a href="/event/' + escapeHTML(event.slugforurl) + '"><img src="/media/'+escapeHTML(event.medias[id].slug)+'/thumbnail"></a></li>';
			}
			html += '</ul><div class="afterMediaGrid"></div>';
		}
		$('#EventPopupMedias').html(html);
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
		if (event.ticket_url) {
			$('#EventPopupTickets').show();
			var link = $('#EventPopupTickets a');
			link.prop('href', event.ticket_url);
			link.text("Tickets from " + link.prop('host'));
		} else {
			$('#EventPopupTickets').hide();
		}
	});
	if (showCurrentUserOptions) {
        showCurrentUserAttendanceForEventInPopupFromCalendar(data.slug,'EventPopupAttendanceContent');
	}
}


function escapeHTML(inString) {
	return  $("<div/>").text(inString).html();
}


function showCurrentUserAttendanceForEventFromCalendar(eventSlug) {
	if ($('#EventAttendancePopup').size() == 0) {
		var html = '<div id="EventAttendancePopup" class="popupBox" style="display: none">';
		html +=	'<div id="EventAttendancePopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><div class="fa fa-times fa-lg"></div></a></div>';
		html += '<div id="EventAttendancePopupContent" class="popupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	}
	$('#EventAttendancePopup').fadeIn(500);
	showPopup();
    showCurrentUserAttendanceForEventInPopupFromCalendar(eventSlug,'EventAttendancePopupContent');
}

function showCurrentUserAttendanceForEventInPopupFromCalendar(eventSlug, contentWrapperID) {

	var wrapper = $('#'+contentWrapperID);
	wrapper.html("Loading ...");
	
	var ajax = $.ajax({
		url: '/event/'+eventSlug+'/myAttendance.json',
		type: 'POST',
	}).success(function ( data ) {
		
		if (data.inPast == 1) {
			var html = '';
			if (data.attending == 'unknown') {
				html = 'We didn\'t know your attendance plans.'
			} else {
				html += 'You said you ';
				html += (data.attending == 'no' ? 'wouldn\'t' : '');
				html += (data.attending == 'maybe' ? 'might' : '');
				html += (data.attending == 'yes' ? 'would' : '');
				html += ' attend.';
			}
		
			wrapper.html(html);
		} else {
	
			var html = '<form action="/event/'+eventSlug+'/myAttendance.json" method="post">';

			html += '<input type="hidden" name="CSFRToken" value="'+data.CSFRToken+'">';

			html += 'You ';
			html += '<select name="attending">';
			html += '<option value="unknown" '+(data.attending == 'unknown'?'selected':'')+'>?</option>';
			html += '<option value="no" '+(data.attending == 'no'?'selected':'')+'>are not</option>';
			html += '<option value="maybe" '+(data.attending == 'maybe'?'selected':'')+'>might be</option>';
			html += '<option value="yes" '+(data.attending == 'yes'?'selected':'')+'>will be</option>';
			html += '</select> attending.';

			html += '<span class="UserAttendingPrivacyOptionsWrapper" '+(data.attending == 'no'|| data.attending == 'unknown' ?'style="display:none;"':'')+'>';
			html += ' This is ';
			html += '<select name="privacy">';
			html += '<option value="private" '+(data.privacy == 'private'?'selected':'')+'>private</option>';
			html += '<option value="public" '+(data.privacy == 'public'?'selected':'')+'>public</option>';
			html += '</select>';
			html += '</span>';

			html += '<span class="savingIndicator" style="display:none;"><img src="/theme/default/img/ajaxLoading.gif"> Saving ...</span>';
			html += '<span class="savedIndicator" style="display:none;">Saved!</span>';

			html += '</form>';

			wrapper.html(html);
			wrapper.children('form').change(function() {

				var formObj = $(this);
				var savingIndicatorObj = formObj.children(".savingIndicator");
				var savedIndicatorObj = formObj.children(".savedIndicator");
				savingIndicatorObj.show();
				savedIndicatorObj.hide();
				var ajax = $.ajax({
					url: formObj.attr('action'),
					type: 'POST',
					data : formObj.serialize()
				}).success(function ( eventdata ) {
					savingIndicatorObj.hide();
					savedIndicatorObj.show();
				});
				var attendingObj = formObj.children('select[name="attending"]');
				var privacyWrapperObj = formObj.children(".UserAttendingPrivacyOptionsWrapper");
				if (attendingObj.val() == 'no' || attendingObj.val() == 'unknown') {
					privacyWrapperObj.hide();
				} else {
					privacyWrapperObj.show();
				}
				var imageDiv = $('#currentUserAttendanceForEvent'+eventSlug+' a.activationLinkWrapper');
				if (attendingObj.val() == 'yes') {
					imageDiv.html('<div class="iconUserAttendingSmall" title="You are attending"></div>');
				} else if (attendingObj.val() == 'maybe') {
					imageDiv.html('<div class="iconUserMaybeAttendingSmall" title="You are maybe attending"></div>');
				} else if (attendingObj.val() == 'no') {
					imageDiv.html('<div class="iconUserNotAttendingSmall" title="You are not attending"></div>');
				} else {
					imageDiv.html('<div class="iconUserUnknownAttendingSmall" title="Are you attending?"></div>');
				}
			});
		}
	});
}

