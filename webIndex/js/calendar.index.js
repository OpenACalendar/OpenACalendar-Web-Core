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
		html +=	'<div id="EventPopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div class="PopupBoxContent">';
		html += '<div id="EventPopupContent" class="PopupBoxContent">';
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
			'<div id="EventPopupLink"><a href="http://'+data.site+'.'+config.httpDomainSite+'/event/'+data.slug+'">View More Details</a></div>');
	var url = (config.hasSSL ? "https://"+data.site+"."+config.httpsDomainSite : "http://"+data.site+"."+config.httpDomainSite ) + "/api1/event/"+data.slug+"/info.jsonp?callback=?";
	$.getJSON(url,{
	}).success(function ( eventdata ) {
		$('#EventPopupTitle').text(eventdata.data[0].summaryDisplay);
		$('#EventPopupDescription').html(escapeHTMLNewLine(eventdata.data[0].description,1000));
		$('#EventPopupTimes').html(escapeHTML(eventdata.data[0].start.displaylocal)+" to " +escapeHTML(eventdata.data[0].end.displaylocal));
	});
	if (showCurrentUserOptions) {
		showCurrentUserAttendanceForEventInPopup(data,'EventPopupAttendanceContent');
	}
}



function showCurrentUserAttendanceForEvent(data) {
	var div = $('#EventAttendancePopup');
	if (div.size() == 0) {
		var html = '<div id="EventAttendancePopup" class="PopupBox">';
		html +=	'<div id="EventAttendancePopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div id="EventAttendancePopupContent" class="PopupBoxContent">';
		html += '</div>';
		html += '</div>';
		$('body').append(html);
	} else {
		div.show();
	}
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

			html += '<span class="savingIndicator" style="display:none;"><img src="/img/ajaxLoading.gif"> Saving ...</span>';
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
					imageDiv.html('<img src="/img/actionUserAttendingIcon.png" alt="You are attending" title="You are attending">');
				} else if (attendingObj.val() == 'maybe') {
					imageDiv.html('<img src="/img/actionUserMaybeAttendingIcon.png" alt="You are maybe attending" title="You are maybe attending">');
				} else {
					imageDiv.html('<img src="/img/actionUserNotAttendingIcon.png" alt="You are not attending" title="You are not attending">');
				}
			});
		}
	});
}

