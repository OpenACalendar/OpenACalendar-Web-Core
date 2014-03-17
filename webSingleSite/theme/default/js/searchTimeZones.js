/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {
	if ($('ul.timezones li').size() > 10) {
		$('#searchWrapper').show();
		$('#searchTerm').change(function() {  searchTimeZones(); });
		$('#searchTerm').keyup(function() {  searchTimeZones(); });	
	}
});

function searchTimeZones() {
	var searchTerm = $('#searchTerm').val().trim().toLowerCase();
	if (searchTerm == '') {
		$('ul.timezones li').show();
	} else {
		$('ul.timezones li').each(function() {
			var elem = $(this);
			var text = elem.text().toLowerCase();
			if (text.search(searchTerm) == -1) {
				elem.hide();
			} else {
				elem.show();
			}
		});
	}
	$('#searchTerm').focus();
}

