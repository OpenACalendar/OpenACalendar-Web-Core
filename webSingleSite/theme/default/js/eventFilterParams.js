/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var eventListFromNowInput;
var eventListFromDiv;
var eventListFromInput;
$(document).ready(function() {
	eventListFromNowInput = $('form.filterListEvent input[name="fromNow"]');
	if (eventListFromNowInput.size() > 0) {
		eventListFromDiv = $('form.filterListEvent #eventListFilterFromWrapper');
		eventListFromInput = $('form.filterListEvent #eventListFilterFromWrapper input');
		if (eventListFromNowInput.attr('checked')) {
			eventListFromDiv.hide();
		} else {
			eventListFromDiv.show();
		}	
		eventListFromNowInput.change(function() {
			if (eventListFromNowInput.is(':checked')) {
				eventListFromDiv.hide();
			} else {
				eventListFromDiv.show();
			}
		});
		eventListFromInput.datepicker({
			dateFormat:'d MM yy'
		});
	}
});
