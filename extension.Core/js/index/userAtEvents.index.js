/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
				var imageDiv = formObj.parent().parent().children('.activationLinkWrapper');
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

