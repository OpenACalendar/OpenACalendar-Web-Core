/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/





function clearArea() {
	$('#AreaFieldsWrapper').html('<input type="hidden" name="fieldChildAreaSlug" value=""><input type="hidden" name="fieldAreaSlug" value=""><input type="hidden" name="fieldArea" value="">');
	$('#NewVenueForm').submit();
}
