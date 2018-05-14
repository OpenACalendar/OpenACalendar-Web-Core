/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
function showCurrentUserAttendanceForEvent(eventSlug) {

	$('#currentUserAttendanceForEvent'+eventSlug+' .formWrapper').html("Loading ...");
	
	var ajax = $.ajax({
		url: '/event/'+eventSlug+'/myAttendance.json',
		type: 'POST',
	}).success(function ( data ) {
		
		var wrapper = $('#currentUserAttendanceForEvent'+eventSlug+' .formWrapper');

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

			html += '<span class="UserAttendingPrivacyOptionsWrapper" '+(data.attending == 'no'|| data.attending == 'unknown'?'style="display:none;"':'')+'>';
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
                    formObj.parents('li.event').removeClass('notAttending');
                } else if (attendingObj.val() == 'maybe') {
                    imageDiv.html('<div class="iconUserMaybeAttendingSmall" title="You are maybe attending"></div>');
                    ticketDiv.show();
                    formObj.parents('li.event').removeClass('notAttending');
                } else if (attendingObj.val() == 'no') {
                    imageDiv.html('<div class="iconUserNotAttendingSmall" title="You are not attending"></div>');
                    ticketDiv.hide();
                    formObj.parents('li.event').addClass('notAttending');
                } else {
                    imageDiv.html('<div class="iconUserUnknownAttendingSmall" title="Are you attending?"></div>');
                    ticketDiv.hide();
                    formObj.parents('li.event').removeClass('notAttending');
                }
			});
		
		}
		
	});
}

