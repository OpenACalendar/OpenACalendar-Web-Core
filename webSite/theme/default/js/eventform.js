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
	$('#FieldStartAtWrapper input, #FieldEndAtWrapper input').change(function() {
		var start = $('#FieldStartAtWrapper input');
		var end = $('#FieldEndAtWrapper input');
		if (start.val() && !end.val()) {
			end.val(start.val());
		}
	});	
	$('#FieldCountryWrapper select').change(function() {
		onCountryChange();
	});
	$('#FieldIsPhysicalWrapper input').change(function() {
		onPhysicalEventChange();
	});
	onCountryChange();
	onPhysicalEventChange();
});
	
function onPhysicalEventChange() {
	var opt = $('#FieldIsPhysicalWrapper input');
	if (opt.length == 0 || opt.is(':checked')) {
		$('#physicalEventOptions').show();
	} else {
		$('#physicalEventOptions').hide();
	}
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
			var timezoneSelect = $('#FieldTimeZoneWrapper select');
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
