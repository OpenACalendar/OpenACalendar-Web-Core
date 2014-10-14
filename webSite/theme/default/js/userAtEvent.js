/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
/** This is used on event show page **/
$(document).ready(function() {
	$('form.UserAttendingOptions').change(function(){
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
			$( "#UserAttendingListAjaxWrapper" ).load( "/event/"+eventData.slug+"/userAttendance.html" );
		});
		var attendingObj = formObj.children('select[name="attending"]');
		var privacyWrapperObj = formObj.children(".UserAttendingPrivacyOptionsWrapper");
		if (attendingObj.val() == 'no') {
			privacyWrapperObj.hide();
		} else {
			privacyWrapperObj.show();
		}
	});
});
