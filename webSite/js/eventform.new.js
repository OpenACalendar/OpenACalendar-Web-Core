/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {
	$('#NewEventForm').change(function() {
		var dataIn = $(this).serialize();
		$.post('/event/creatingThisNewEvent.json', dataIn,function(data) {
			if (data.duplicates.length == 0) {
				$('#DuplicateEventsContainer').hide();
			} else {
				var html = '';
				for (idx in data.duplicates) {
					html += '<li class="event">';
					html += '<div class="title">'+escapeHTML(data.duplicates[idx].summary)+'</div>';
					html += '</li>';
				}
				$('#DuplicateEventsList').empty().append(html);
				$('#DuplicateEventsContainer').show();
			}
		});
	});	
});



