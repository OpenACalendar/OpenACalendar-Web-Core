/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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

var startDate, startHours, startMins, endDate, endHours, endMins, timezone;

function loadData() {
	// cancel old loads
	if (loadDataAJAX) {
		loadDataAJAX.abort();
	}
	// set loading indicators
    var currentStartDate = $('#FieldStartAtWrapper input[type="text"]').val();
    var currentStartHours = $('#FieldStartAtWrapper #event_edit_form_start_at_time_hour').val();
    var currentStartMins = $('#FieldStartAtWrapper #event_edit_form_start_at_time_minute').val();
    var currentEndDate = $('#FieldEndAtWrapper input[type="text"]').val();
    var currentEndHours = $('#FieldEndAtWrapper  #event_edit_form_end_at_time_hour').val();
    var currentEndMins = $('#FieldEndAtWrapper  #event_edit_form_end_at_time_minute').val();
    var currentTimezone = $('#FieldTimeZoneWrapper select').val();
	if (currentStartDate != startDate || currentStartHours != startHours || currentStartMins != startMins || currentEndDate != endDate || currentEndHours != endHours || currentEndMins != endMins || currentTimezone != timezone) {
		$('#ReadableDateTimeRange').html('&nbsp;');
		startDate = currentStartDate;
		startHours = currentStartHours;
		startMins = currentStartMins;
		endDate = currentEndDate;
		endHours = currentEndHours;
		endMins = currentEndMins;
		timezone = currentTimezone;
	}
	// load
	var dataIn = $('#EditEventForm').serialize();
	loadDataAJAX = $.post('/event/'+editingEventSlug+'/edit/details/editing.json', dataIn,function(data) {
		$('#ReadableDateTimeRange').html(data.readableStartEndRange);
	});
}
