/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

$(document).ready(function() {

	$('#FieldStartAtWrapper input, #FieldEndAtWrapper input').datepicker({
		dateFormat:'dd/mm/yy'
	});
	$('#NewEventForm').change(function() {
		loadData();
	});
    $('#FieldCountryWrapper select').change(function() {
       onCountryChange();
    });

    var countrySelect = $('#FieldCountryWrapper select');
    if (countrySelect.attr('type') != 'hidden' && lastCountryIDSeen !== countrySelect.val()) {
        repopulateTimeZoneSelect(defaultCountryTimeZones);
    }
    lastCountryIDSeen = countrySelect.val();

    loadData();
});

var loadDataAJAX;

var startDate, startHours, startMins, endDate, endHours, endMins, timezone;

function loadDataSetLoadingIndicators() {
	var currentStartDate = $('#FieldStartAtWrapper input[type="text"]').val();
	var currentStartHours = $('#FieldStartAtWrapper #event_new_when_details_form_start_at_time_hour').val();
	var currentStartMins = $('#FieldStartAtWrapper #event_new_when_details_form_start_at_time_minute').val();
	var currentEndDate = $('#FieldEndAtWrapper input[type="text"]').val();
	var currentEndHours = $('#FieldEndAtWrapper  #event_new_when_details_form_end_at_time_hour').val();
	var currentEndMins = $('#FieldEndAtWrapper  #event_new_when_details_form_end_at_time_minute').val();
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
}



function loadDataGotData(data) {
	$('#ReadableDateTimeRange').html(data.readableStartEndRange);
}



var lastCountryIDSeen = -1;
var countryDataAJAX;
function onCountryChange() {
	var countrySelect = $('#FieldCountryWrapper select');
	if (countrySelect.attr('type') != 'hidden' && lastCountryIDSeen !== countrySelect.val()) {
		countryDataAJAX = $.ajax({
			url: "/country/" + countrySelect.val()+"/info.json",
		}).success(function ( data ) {
			// timezones
            repopulateTimeZoneSelect(data.country.timezones);
			var timezoneSelect = $('#FieldTimeZoneWrapper select');
			timezoneSelect.trigger("change");
		});
	}
	lastCountryIDSeen = countrySelect.val();
}

function repopulateTimeZoneSelect(timezones) {
    var timezoneSelect = $('#FieldTimeZoneWrapper select');
    var timezoneSelectVal = timezoneSelect.val();
    var timezoneSelectValFound = false;
    timezoneSelect.children('option').remove();
    var html = '';
    for(var i in timezones) {
        html += '<option value="'+timezones[i]+'">'+timezones[i]+'</option>';
        if (timezones[i] == timezoneSelectVal) {
            timezoneSelectValFound = true;
        }
    }
    timezoneSelect.append(html);
    if (timezoneSelectValFound) {
        timezoneSelect.val(timezoneSelectVal);
    }
}



