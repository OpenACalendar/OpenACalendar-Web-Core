/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var importedEventListFromNowInput;
var importedEventListFromDiv;
var importedEventListFromInput;
$(document).ready(function() {
	importedEventListFromNowInput = $('form.filterListImportedEvent input[name="fromNow"]');
	if (importedEventListFromNowInput.size() > 0) {
		importedEventListFromDiv = $('form.filterListImportedEvent #importedEventListFilterFromWrapper');
		importedEventListFromInput = $('form.filterListImportedEvent #importedEventListFilterFromWrapper input');
		if (importedEventListFromNowInput.attr('checked')) {
			importedEventListFromDiv.hide();
		} else {
			importedEventListFromDiv.show();
		}	
		importedEventListFromNowInput.change(function() {
			if (importedEventListFromNowInput.is(':checked')) {
				importedEventListFromDiv.hide();
			} else {
				importedEventListFromDiv.show();
			}
		});
		importedEventListFromInput.datepicker({
			dateFormat:'d MM yy'
		});
	}
});
