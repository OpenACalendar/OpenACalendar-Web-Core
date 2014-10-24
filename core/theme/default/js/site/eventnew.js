/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$( document ).ready( function() {
	$('#GroupFindFallback').hide();
	$('#GroupSearchForm').show();
	$('#GroupSearchText').change(function() { groupSearchChanged(); });
	$('#GroupSearchText').keyup(function() { groupSearchChanged(); });
});

var lastGroupSearchValue = '';
var groupSearchAJAX;

function groupSearchChanged() {
	var groupSearchValue = $('#GroupSearchText').val();
	$('#NewGroupLink').attr('href','/group/new?title='+encodeURIComponent(groupSearchValue));
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
                        out += '<div class="oneActionFormRight">';
                        if (dateForEvent) {
                            out += '<a class="button" href="/group/'+group.slug+'/newevent/go?date='+dateForEvent+'">Create event in this group</a>';
                        } else {
                            out += '<a class="button" href="/group/'+group.slug+'/newevent">Create event in this group</a>';
                        }
                        out += '</div>';
                        out += '<div class="title">'+escapeHTML(group.title)+'</div>';
                        out += '<div class="afterOneActionFormRight"></div></li>';
                    }
                } else {
                    out += '<li class="group"><div class="notfound">Sorry, nothing found.</div></li>';
                }
				$('#GroupSearchList').empty();
				$('#GroupSearchList').append(out);
			});

	}
}


