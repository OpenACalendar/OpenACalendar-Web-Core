/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var hasUserEditedSlug = false;
var titleObj;
var slugObj;

var titleFunc = function() {
	if (!hasUserEditedSlug) {
		slug = titleObj.val().replace(/\W/g, '').toLowerCase();
		slugObj.val( slug  );
	}
};

var slugFunc = function() {
	hasUserEditedSlug = true;
};

$(document).ready(function() {
	
	titleObj = $('#CreateForm_title');
	titleObj.change(titleFunc);
	titleObj.keyup(titleFunc);
	
	slugObj = $('#CreateForm_slug')
	slugObj.keyup(slugFunc);
});


