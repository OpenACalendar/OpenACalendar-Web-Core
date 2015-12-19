/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {

	$('#isAllUsersEditorsWrapper input, #isRequestAccessAllowedWrapper input').change(function() {  
		changeForm();
	});
	
	changeForm();
});


function changeForm() {
	var isAllUsersEditors = $('#isAllUsersEditorsWrapper input').is(':checked');
	if (isAllUsersEditors) {
		$('#isRequestAccessAllowedWrapper').hide();
		$('#requestAccessQuestionWrapper').hide();
	} else {
		$('#isRequestAccessAllowedWrapper').show();
		var isRequestAccessAllowed = $('#isRequestAccessAllowedWrapper input').is(':checked');
		if (isRequestAccessAllowed) {
			$('#requestAccessQuestionWrapper').show();
		} else {
			$('#requestAccessQuestionWrapper').hide();
		}
	}
}

