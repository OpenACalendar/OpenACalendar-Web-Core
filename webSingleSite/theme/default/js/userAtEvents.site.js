/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
				var imageDiv = formObj.parent().parent().children('.activationLinkWrapper');
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

