/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
function showCurrentUserAttendanceForEvent(data) {

	$('#currentUserAttendanceForSite'+data.site+'Event'+data.slug+' .formWrapper').html("Loading ...");
	
	var ajax = $.ajax({
		url: '/site/'+data.site+'/event/'+data.slug+'/myAttendance.json',
		type: 'POST',
	}).success(function ( attendanceData ) {
		
		var wrapper = $('#currentUserAttendanceForSite'+data.site+'Event'+data.slug+' .formWrapper');

		if (attendanceData.inPast == 1) {
			var html = '';
			if (attendanceData.attending == 'unknown') {
				html = 'We didn\'t know your attendance plans.'
			} else {
				html += 'You said you ';
				html += (attendanceData.attending == 'no' ? 'wouldn\'t' : '');
				html += (attendanceData.attending == 'maybe' ? 'might' : '');
				html += (attendanceData.attending == 'yes' ? 'would' : '');
				html += ' attend.';
			}
		
			wrapper.html(html);
		} else {
		
			var html = '<form action="/site/'+data.site+'/event/'+data.slug+'/myAttendance.json" method="post">';

			html += '<input type="hidden" name="CSFRToken" value="'+attendanceData.CSFRToken+'">';

			html += 'You ';
			html += '<select name="attending">';
			html += '<option value="unknown" '+(attendanceData.attending == 'unknown'?'selected':'')+'>?</option>';
			html += '<option value="no" '+(attendanceData.attending == 'no'?'selected':'')+'>are not</option>';
			html += '<option value="maybe" '+(attendanceData.attending == 'maybe'?'selected':'')+'>might be</option>';
			html += '<option value="yes" '+(attendanceData.attending == 'yes'?'selected':'')+'>will be</option>';
			html += '</select> attending.';

			html += '<span class="UserAttendingPrivacyOptionsWrapper" '+(attendanceData.attending == 'no' || attendanceData.attending == 'unknown' ?'style="display:none;"':'')+'>';
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
			if (data.attending == 'yes' || data.attending == 'maybe') {
				wrapper.parents('.currentUserAttendance').children('.ticketWrapper').show();
			} else {
				wrapper.parents('.currentUserAttendance').children('.ticketWrapper').hide();
			}
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
				var imageDiv = formObj.parents('.currentUserAttendance').children('.activationLinkWrapper');
				var ticketDiv = formObj.parents('.currentUserAttendance').children('.ticketWrapper');
				if (attendingObj.val() == 'yes') {
					imageDiv.html('<div class="iconUserAttendingSmall" title="You are attending"></div>');
					ticketDiv.show();
				} else if (attendingObj.val() == 'maybe') {
					imageDiv.html('<div class="iconUserMaybeAttendingSmall" title="You are maybe attending"></div>');
					ticketDiv.show();
				} else if (attendingObj.val() == 'no') {
					imageDiv.html('<div class="iconUserNotAttendingSmall" title="You are not attending"></div>');
					ticketDiv.hide();
				} else {
					imageDiv.html('<div class="iconUserUnknownAttendingSmall" title="Are you attending?"></div>');
					ticketDiv.hide();
				}

			});
		
		}
		
	});
}

