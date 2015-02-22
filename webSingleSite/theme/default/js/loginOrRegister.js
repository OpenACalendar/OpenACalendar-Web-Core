/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/


function loginOrRegisterRemoveEvent(id) {
	$('#afterGetUserEvent'+id).remove();

	var wrapper = $('#afterGetUserWrapper');
	if (wrapper.find(".afterGetUserEvent").length == 0 && wrapper.find(".afterGetUserArea").length == 0) {
		wrapper.remove();
	}

	$.ajax({
		url: "/you/aftergetuserapi?removeEventId="+id
	});
}


function loginOrRegisterRemoveArea(id) {
	$('#afterGetUserArea'+id).remove();

	var wrapper = $('#afterGetUserWrapper');
	if (wrapper.find(".afterGetUserEvent").length == 0 && wrapper.find(".afterGetUserArea").length == 0) {
		wrapper.remove();
	}

	$.ajax({
		url: "/you/aftergetuserapi?removeAreaId="+id
	});
}
