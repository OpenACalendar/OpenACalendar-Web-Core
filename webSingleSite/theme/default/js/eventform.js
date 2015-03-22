/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {
	$('#EventNewForm_start_at_date, #EventNewForm_end_at_date, #EventEditForm_start_at_date, #EventEditForm_end_at_date').datepicker({
		dateFormat:'dd/mm/yy'
	});
	$('#EventNewForm_start_at_date,  #EventEditForm_start_at_date').change(function() {
		var start = $('#EventNewForm_start_at_date,  #EventEditForm_start_at_date');
		var end = $('#EventNewForm_end_at_date,  #EventEditForm_end_at_date');
		if (start.val() && !end.val()) {
			end.val(start.val());
		}
	});	
	$('#EventEditForm_country_id, #EventNewForm_country_id').change(function() {
		onCountryChange();
	});
	$('#EventNewForm_is_physical, #EventEditForm_is_physical').change(function() {
		onPhysicalEventChange();
	});
	onCountryChange();
	onPhysicalEventChange();
});
	
function onPhysicalEventChange() {
	var opt = $('#EventNewForm_is_physical, #EventEditForm_is_physical');
	if (opt.length == 0 || opt.is(':checked')) {
		$('#physicalEventOptions').show();
	} else {
		$('#physicalEventOptions').hide();
	}
}
	
var lastCountryIDSeen = -1;	
var countryDataAJAX;
function onCountryChange() {
	var countrySelect = $('#EventEditForm_country_id, #EventNewForm_country_id');
	if (countrySelect.attr('type') != 'hidden' && lastCountryIDSeen !== countrySelect.val()) {
		countryDataAJAX = $.ajax({
			url: "/country/" + countrySelect.val()+"/info.json",
		}).success(function ( data ) {
			// timezones
			var timezoneSelect = $('#EventEditForm_timezone, #EventNewForm_timezone');
			var timezoneSelectVal = timezoneSelect.val();
			var timezoneSelectValFound = false;
			timezoneSelect.children('option').remove();
			var html = '';
			for(var i in data.country.timezones) {
				html += '<option value="'+data.country.timezones[i]+'">'+data.country.timezones[i]+'</option>';
				if (data.country.timezones[i] == timezoneSelectVal) {
					timezoneSelectValFound = true;
				}
			}
			timezoneSelect.append(html);
			if (timezoneSelectValFound) {
				timezoneSelect.val(timezoneSelectVal);
			}
			timezoneSelect.trigger("change");
		});
	}
	lastCountryIDSeen = countrySelect.val();
}

	
function escapeHTML(inString) {
	return  $("<div/>").text(inString).html();
}
