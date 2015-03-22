/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

var notDuplicateOfEventSlugs = "";

$(document).ready(function() {
	$('#EditEventForm').change(function() {
		loadData();
	});
	loadData();

});

var loadDataAJAX;

function loadData() {
	var dataIn = $('#EditEventForm').serialize();
	if (loadDataAJAX) {
		loadDataAJAX.abort();
	}
	loadDataAJAX = $.post('/event/'+editingEventSlug+'/edit/details/editing.json', dataIn,function(data) {
		$('#ReadableDateTimeRange').html(data.readableStartEndRange);
	});
}
