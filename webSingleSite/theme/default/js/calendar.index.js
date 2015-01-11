/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
function showEventPopup(data) {
	if ($('#EventPopup').size() == 0) {
		var html = '<div id="EventPopup" class="popupBox" style="display: none;">';
		html +=	'<div id="EventPopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
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

	var eventURL = (config.hasSSL ? "https://" : "http://") +
				(config.isSingleSiteMode ? '' : data.site+'.') +
				(config.hasSSL ? config.httpsDomainSite : config.httpDomainSite )+
				'/event/'+data.slugForURL;

	$('#EventPopupContent').html('<div id="EventPopupTitle">Loading ...</div>'+
			'<div id="EventPopupDescription"></div>'+
			'<div id="EventPopupGroupsWrapper"></div>'+
			'<div id="EventPopupVenueWrapper"></div>'+
			'<div id="EventPopupTimes"></div>'+
			'<div class="EventPopupLink"><a href="'+eventURL+'">View More Details</a></div>');
	var apiURL = (config.hasSSL ? "https://" : "http://") +
				(config.isSingleSiteMode ? '' : data.site+'.') +
				(config.hasSSL ? config.httpsDomainSite : config.httpDomainSite )+
				'/api1/event/'+data.slug+"/info.jsonp?callback=?";
	
	$.getJSON(apiURL,{
	}).success(function ( eventdata ) {
		var event = eventdata.data[0];
        if (event.cancelled) {
            $('#EventPopupTitle').text(event.summaryDisplay + " [CANCELLED]");
        } else {
            $('#EventPopupTitle').text(event.summaryDisplay);
        }
		$('#EventPopupDescription').html(escapeHTMLNewLine(event.description,1000));
		$('#EventPopupTimes').html(escapeHTML(event.start.displaylocal)+" to " +escapeHTML(eventdata.data[0].end.displaylocal));
		if (event.venue) {
			$('#EventPopupVenueWrapper').html(
					'<div class="venueTitle">Venue '+escapeHTML(event.venue.title)+'</div>'+
					'<div class="venueDescription">'+escapeHTMLNewLine(event.venue.description, 500)+'</div>'+
					'<div class="venueAddress">'+escapeHTMLNewLine(event.venue.address, 500)+' '+escapeHTML(event.venue.addresscode)+'</div>'
				);
		} else {
			$('#EventPopupVenueWrapper').html('&nbsp;');
		}
		var html = '';
		if (event.groups) {
			for(groupIdx in event.groups) {
				var group = event.groups[groupIdx];
				html += '<div class="groupTitle">Group '+escapeHTML(group.title)+'</div>';
				html += '<div class="groupDescription">'+escapeHTMLNewLine(group.description,500)+'</div>';
			}
		}
		$('#EventPopupGroupsWrapper').html(html);
	});
	if (showCurrentUserOptions) {
		showCurrentUserAttendanceForEventInPopup(data,'EventPopupAttendanceContent');
	}
}


function showCurrentUserAttendanceForEvent(data) {
	if ($('#EventAttendancePopup').size() == 0) {
		var html = '<div id="EventAttendancePopup" class="popupBox" style="display: none">';
		html +=	'<div id="EventAttendancePopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div id="EventAttendancePopupContent" class="popupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	}
	$('#EventAttendancePopup').fadeIn(500);
	showPopup();
	showCurrentUserAttendanceForEventInPopup(data,'EventAttendancePopupContent');
}

function showCurrentUserAttendanceForEventInPopup(data, contentWrapperID) {

	var wrapper = $('#'+contentWrapperID);
	wrapper.html("Loading ...");
	
	var ajax = $.ajax({
		url: '/site/'+data.site+'/event/'+data.slug+'/myAttendance.json',
		type: 'POST',
	}).success(function ( attendanceData ) {
		
		if (attendanceData.inPast == 1) {
			var html = '';
			html += 'You said you ';
			html += (attendanceData.attending == 'no'?'wouldn\'t':'');
			html += (attendanceData.attending == 'maybe'?'might':'');
			html += (attendanceData.attending == 'yes'?'would':'');
			html += ' attend.';
		
			wrapper.html(html);
		} else {
	
			var html = '<form action="/site/'+data.site+'/event/'+data.slug+'/myAttendance.json" method="post">';

			html += '<input type="hidden" name="CSFRToken" value="'+attendanceData.CSFRToken+'">';

			html += 'You ';
			html += '<select name="attending">';
			html += '<option value="no" '+(attendanceData.attending == 'no'?'selected':'')+'>are not</option>';
			html += '<option value="maybe" '+(attendanceData.attending == 'maybe'?'selected':'')+'>might be</option>';
			html += '<option value="yes" '+(attendanceData.attending == 'yes'?'selected':'')+'>will be</option>';
			html += '</select> attending.';

			html += '<span class="UserAttendingPrivacyOptionsWrapper" '+(attendanceData.attending == 'no'?'style="display:none;"':'')+'>';
			html += ' This is ';
			html += '<select name="privacy">';
			html += '<option value="private" '+(attendanceData.privacy == 'private'?'selected':'')+'>private</option>';
			html += '<option value="public" '+(attendanceData.privacy == 'public'?'selected':'')+'>public</option>';
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
				var imageDiv = $('#currentUserAttendanceForSite'+data.site+'Event'+data.slug+' a.activationLinkWrapper');
				if (attendingObj.val() == 'yes') {
					imageDiv.html('<div class="iconUserAttendingSmall" title="You are attending"></div>');
				} else if (attendingObj.val() == 'maybe') {
					imageDiv.html('<div class="iconUserMaybeAttendingSmall" title="You are maybe attending"></div>');
				} else {
					imageDiv.html('<div class="iconUserNotAttendingSmall" title="You are not attending"></div>');
				}
			});
		}
	});
}

