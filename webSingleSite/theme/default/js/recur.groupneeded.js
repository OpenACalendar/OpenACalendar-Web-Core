/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$( document ).ready( function() {
	$('#GroupSearchText').change(function() { groupSearchChanged(); });
	$('#GroupSearchText').keyup(function() { groupSearchChanged(); });
	groupSearchChanged();
});

var lastGroupSearchValue = '';
var groupSearchAJAX;

function groupSearchChanged() {
	var groupSearchValue = $('#GroupSearchText').val();

	if (groupSearchValue == '') {
		lastGroupSearchValue = '';
		$('#GroupSearchList').empty();
	} else if (groupSearchValue != lastGroupSearchValue) {
		lastGroupSearchValue = groupSearchValue;
	
		if (groupSearchAJAX) {
			groupSearchAJAX.abort();
		}
	
		groupSearchAJAX = $.ajax({
				url: "/api1/groups.json?includeDeleted=no&search=" + groupSearchValue,
			}).success(function ( data ) {
				var out = '';
				for(i in data.data) {
					var group = data.data[i];
					out += '<li class="group">';
					out += '<form action="" method="POST" class="oneActionFormRight">';
					out += '<input type="hidden" name="intoGroupSlug" value="'+group.slug+'">';
					out += '<input type="submit" value="Put event in this group" class="button">';
					out += '</form>';
					out += '<div class="title">'+escapeHTML(group.title)+'</div>';
					out += '<div class="afterOneActionFormRight"></div></li>';
				}
				$('#GroupSearchList').empty();
				$('#GroupSearchList').append(out);
			});

	}
}


