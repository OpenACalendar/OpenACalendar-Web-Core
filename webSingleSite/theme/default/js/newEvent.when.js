/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

$(document).ready(function() {

	$('#EventNewWhenDetailsForm_start_at_date, #EventNewWhenDetailsForm_end_at_date, #EventEditForm_start_at_date, #EventEditForm_end_at_date').datepicker({
		dateFormat:'dd/mm/yy'
	});
	$('#NewEventForm').change(function() {
		loadData();
	});
    $('#EventNewWhenDetailsForm_country_id').change(function() {
       onCountryChange();
    });

    var countrySelect = $('#EventNewWhenDetailsForm_country_id');
    if (countrySelect.attr('type') != 'hidden' && lastCountryIDSeen !== countrySelect.val()) {
        repopulateTimeZoneSelect(defaultCountryTimeZones);
    }
    lastCountryIDSeen = countrySelect.val();

    loadData();
});

var loadDataAJAX;

var startDate, startHours, startMins, endDate, endHours, endMins, timezone;

function loadDataSetLoadingIndicators() {
	var currentStartDate = $('#EventNewWhenDetailsForm_start_at_date').val();
	var currentStartHours = $('#EventNewWhenDetailsForm_start_at_time_hour').val();
	var currentStartMins = $('#EventNewWhenDetailsForm_start_at_time_minute').val();
	var currentEndDate = $('#EventNewWhenDetailsForm_end_at_date').val();
	var currentEndHours = $('#EventNewWhenDetailsForm_end_at_time_hour').val();
	var currentEndMins = $('#EventNewWhenDetailsForm_end_at_time_minute').val();
	var currentTimezone = $('#EventNewWhenDetailsForm_timezone').val();
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
	var countrySelect = $('#EventEditForm_country_id, #EventNewWhenDetailsForm_country_id');
	if (countrySelect.attr('type') != 'hidden' && lastCountryIDSeen !== countrySelect.val()) {
		countryDataAJAX = $.ajax({
			url: "/country/" + countrySelect.val()+"/info.json",
		}).success(function ( data ) {
			// timezones
            repopulateTimeZoneSelect(data.country.timezones);
			var timezoneSelect = $('#EventEditForm_timezone, #EventNewWhenDetailsForm_timezone');
			timezoneSelect.trigger("change");
		});
	}
	lastCountryIDSeen = countrySelect.val();
}

function repopulateTimeZoneSelect(timezones) {
    var timezoneSelect = $('#EventEditForm_timezone, #EventNewWhenDetailsForm_timezone');
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



