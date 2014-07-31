/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

$(document).ready(function() {

	$('#TitleField, #AddressField, #AreaField, #AddressCodeField').change(onSearchFormChanged).keyup(onSearchFormKeyUp);

});

var keyUpTimer;

function onSearchFormKeyUp(event) {
	if (event.keyCode != '9') {
		clearTimeout(keyUpTimer);
		keyUpTimer = setTimeout(loadSearchResults, 1000);
	}
}

function onSearchFormChanged() {
	loadSearchResults();
}

var loadSearchResultsAJAX;
var lastFormSerialized = "XXX";

function loadSearchResults() {
	clearTimeout(keyUpTimer);
	var thisFormSerialized = $('#EditEventVenueForm').serialize();
	if (lastFormSerialized == thisFormSerialized) {
		return;
	}
	lastFormSerialized = thisFormSerialized;
	$('#EditEventVenueSearchResults li.result').remove();
	$("#EditEventVenueSearchResults").prepend('<li class="loading">Loading, please wait ...</li>');
	loadSearchResultsAJAX = $.ajax({
		data: $('#EditEventVenueForm').serialize(),
		dataType: "json",
		url: '/event/'+currentEventSlug+'/edit/venue.json',
		success: function(data) {
			var html = '';
			if (data.venueSearchDone) {
				var venues = $.map(data.venues, function(value, index) {
					return [value];
				});;
				venues.sort(function(a,b) {
					if (a.title.toLowerCase() > b.title.toLowerCase()) {
						return 1;
					} else if (a.title.toLowerCase() < b.title.toLowerCase()) {
						return -1;
					} else {
						return 0;
					}
				});
				for(i in venues) {
					html += '<li class="venue result">';
					html += '<div class="title">' + escapeHTML(venues[i].title)+'</div>';
					if (data.venues[i].address) {
						html += '<div>' + escapeHTMLNewLine(venues[i].address)+'</div>';
					}
					if (data.venues[i].addresscode) {
						html += '<div>' + escapeHTML(venues[i].addresscode)+'</div>';
					}
					html += '<form action="/event/'+currentEventSlug+'/edit/venue" method="post" class="styled">';
					html += '<input type="hidden" name="CSFRToken" value="'+CSFRToken+'">';
					html += '<input type="hidden" name="venue_slug" value="' + escapeHTML(venues[i].slug)+'">';
					html += '<div class="actionWrapperBig"><input type="submit" value="Select ' + escapeHTML(venues[i].title)+'"></div>';
					html += '</form>';
					html += '</li>';
				}
				$('#VenueNewWrapper').show();
			} else {
				$('#VenueNewWrapper').hide();
			}
			$('#EditEventVenueSearchResults li.loading').remove();
			$("#EditEventVenueSearchResults").prepend(html);

			var areas = $.map(data.areas, function(value, index) {
				return [value];
			});;
			areas.sort(function(a,b) {
				if (a.title.toLowerCase() > b.title.toLowerCase()) {
					return 1;
				} else if (a.title.toLowerCase() < b.title.toLowerCase()) {
					return -1;
				} else {
					return 0;
				}
			});
			var html = '';
			for(i in areas) {
				var htmlS = (areas[i].slug == data.searchAreaSlug) ?  'checked="checked"' : '';
				html += '<li><label><input name="searchAreaSlug" type="radio" value="'+escapeHTML(areas[i].slug)+'"'+htmlS+' onchange="onSearchFormChanged();">'+escapeHTML(areas[i].title)+'</label></li>'
			}
			$('#AreaList').html(html);
			$('#VenueNewWrapper form input[name="fieldTitle"]').val($('#TitleField').val());
			$('#VenueNewWrapper form input[name="fieldAddress"]').val($('#AddressField').val());
			$('#VenueNewWrapper form input[name="fieldArea"]').val($('#AreaField').val());
			$('#VenueNewWrapper form input[name="fieldAddressCode"]').val($('#AddressCodeField').val());
			$('#VenueNewWrapper form input[name="fieldAreaSlug"]').val(data.searchAreaSlug);
		}
	});


}

