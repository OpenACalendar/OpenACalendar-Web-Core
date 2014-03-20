/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
function showEventPopup(data) {
	var div = $('#EventPopup');
	if (div.size() == 0) {
		var html = '<div id="EventPopup" class="PopupBox">';
		html +=	'<div id="EventPopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
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
			'<div id="EventPopupGroupsWrapper"></div>'+
			'<div id="EventPopupVenueWrapper"></div>'+
			'<div id="EventPopupTimes"></div>'+
			'<div class="EventPopupLink"><a href="/event/' + data.slug + '">View More Details</a></div>');
	$.ajax({
		url: "/api1/event/"+data.slug+"/info.json"
	}).success(function ( eventdata ) {
	var event = eventdata.data[0];
		$('#EventPopupTitle').text(event.summaryDisplay);
		$('#EventPopupDescription').html(escapeHTMLNewLine(event.description,1000));
		$('#EventPopupTimes').html(escapeHTML(event.start.displaylocal)+" to " +escapeHTML(eventdata.data[0].end.displaylocal));
		if (event.venue) {
			$('#EventPopupVenueWrapper').html(
					'<div class="venueTitle">Venue '+escapeHTML(event.venue.title)+'</div>'+
					'<div class="venueDescription">'+(event.venue.description ? escapeHTMLNewLine(event.venue.description, 500) : '')+'</div>'+
					'<div class="venueAddress">'+(event.venue.address ? escapeHTMLNewLine(event.venue.address, 500) : '')+' '+(event.venue.addresscode ? escapeHTML(event.venue.addresscode) : '')+'</div>'
				);
		} else {
			$('#EventPopupVenueWrapper').html('&nbsp;');
		}
		var html = '';
		if (event.groups) {
			for(groupIdx in event.groups) {
				var group = event.groups[groupIdx];
				html += '<div class="groupTitle">Group '+escapeHTML(group.title)+'</div>';
				html += '<div class="groupDescription">'+(group.description ? escapeHTMLNewLine(group.description,500) : '')+'</div>';
			}
		}
		$('#EventPopupGroupsWrapper').html(html);
	});
	if (showCurrentUserOptions) {
		showCurrentUserAttendanceForEventInPopup(data.slug,'EventPopupAttendanceContent');
	}
}


function escapeHTML(inString) {
	return  $("<div/>").text(inString).html();
}


function showCurrentUserAttendanceForEvent(eventSlug) {
	var div = $('#EventAttendancePopup');
	if (div.size() == 0) {
		var html = '<div id="EventAttendancePopup" class="PopupBox">';
		html +=	'<div id="EventAttendancePopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div id="EventAttendancePopupContent" class="PopupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	} else {
		div.show();
	}
	showPopup();
	showCurrentUserAttendanceForEventInPopup(eventSlug,'EventAttendancePopupContent');
}

function showCurrentUserAttendanceForEventInPopup(eventSlug, contentWrapperID) {

	var wrapper = $('#'+contentWrapperID);
	wrapper.html("Loading ...");
	
	var ajax = $.ajax({
		url: '/event/'+eventSlug+'/myAttendance.json',
		type: 'POST',
	}).success(function ( data ) {
		
		if (data.inPast == 1) {
			var html = '';
			html += 'You said you ';
			html += (data.attending == 'no'?'wouldn\'t':'');
			html += (data.attending == 'maybe'?'might':'');
			html += (data.attending == 'yes'?'would':'');
			html += ' attend.';
		
			wrapper.html(html);
		} else {
	
			var html = '<form action="/event/'+eventSlug+'/myAttendance.json" method="post">';

			html += '<input type="hidden" name="CSFRToken" value="'+data.CSFRToken+'">';

			html += 'You ';
			html += '<select name="attending">';
			html += '<option value="no" '+(data.attending == 'no'?'selected':'')+'>are not</option>';
			html += '<option value="maybe" '+(data.attending == 'maybe'?'selected':'')+'>might be</option>';
			html += '<option value="yes" '+(data.attending == 'yes'?'selected':'')+'>will be</option>';
			html += '</select> attending.';

			html += '<span class="UserAttendingPrivacyOptionsWrapper" '+(data.attending == 'no'?'style="display:none;"':'')+'>';
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
				if (attendingObj.val() == 'no') {
					privacyWrapperObj.hide();
				} else {
					privacyWrapperObj.show();
				}
				var imageDiv = $('#currentUserAttendanceForEvent'+eventSlug+' a.activationLinkWrapper');
				if (attendingObj.val() == 'yes') {
					imageDiv.html('<img src="/theme/default/img/actionUserAttendingIcon.png" alt="You are attending" title="You are attending">');
				} else if (attendingObj.val() == 'maybe') {
					imageDiv.html('<img src="/theme/default/img/actionUserMaybeAttendingIcon.png" alt="You are maybe attending" title="You are maybe attending">');
				} else {
					imageDiv.html('<img src="/theme/default/img/actionUserNotAttendingIcon.png" alt="You are not attending" title="You are not attending">');
				}
			});
		}
	});
}

