/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$( document ).ready( function() {
	$('#GroupSearchForm input[type="submit"]').hide();
	$('#GroupSearchText').change(function() { groupSearchChanged(); });
	$('#GroupSearchText').keyup(function() { groupSearchChanged(); });
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
                if (data.data.length > 0) {
                    for(i in data.data) {
                        var group = data.data[i];
                        out += '<li class="group">';
						out += '<div class="title">'+escapeHTML(group.title)+'</div>';
                        out += '<form method="post"><input type="hidden" name="action" value="selectgroup"><input type="hidden" name="group" value="'+group.slug+'">';
						out += '<div class="bigSubmitActionWrapper"><input type="submit"  value="Create Event in this Group" class="bigSubmitAction"/></div><div class="afterBigSubmitActionWrapper"></div>';
                        out += '</form>';
                        out += '</li>';
                    }
                } else {
                    out += '<li class="group"><div class="notfound">Sorry, nothing found.</div></li>';
                }
				$('#GroupSearchList').empty();
				out += '<li class="nodata">';
				out += '<div class="title">Not these groups!</div>';
				out += '<form method="post">';
				out += '<input type="hidden" name="action" value="selectnewgroup">';
				out += '<div>It\'s a new group called: <input type="text" name="newgrouptitle" value="'+escapeHTMLAttribute(groupSearchValue)+'"></div>'
				out += '<div class="bigSubmitActionWrapper"><input type="submit"  value="Add This Group" class="bigSubmitAction"/></div><div class="afterBigSubmitActionWrapper"></div>';
				out += '</form>';
				out += '</li>';
				$('#GroupSearchList').append(out);
			});

	}
}


