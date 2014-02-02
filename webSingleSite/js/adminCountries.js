/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {
	$('#searchWrapper').show();
	$('#searchTerm').change(function() {  searchCountries(); });
	$('#searchTerm').keyup(function() {  searchCountries(); });	
});

function searchCountries() {
	var searchTerm = $('#searchTerm').val().trim().toLowerCase();
	if (searchTerm == '') {
		$('ul.selectCountries li').show();
	} else {
		$('ul.selectCountries li').each(function() {
			var elem = $(this);
			var text = elem.text().toLowerCase();
			if (text.search(searchTerm) == -1) {
				elem.hide();
			} else {
				elem.show();
			}
		});
	}
	$('#searchTerm').focus();
}

function selectAll() {
	$('#searchTerm').val('');
	searchCountries();
	$('#CountriesForm input[type=checkbox]').prop('checked', true);
}

function selectNone() {
	$('#searchTerm').val('');
	searchCountries();
	$('#CountriesForm input[type=checkbox]').prop('checked', false)
}

