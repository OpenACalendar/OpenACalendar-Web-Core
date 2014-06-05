/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
//////////////////////////////////////////////////////////////////////////////// Menus
$(document).ready(function() {
	$('#header ul.menu > li a').click(function( event ) {
		var submenuLI = $(this).parent();
		var submenu = submenuLI.children('ul.submenu');
		if (submenu.size() > 0) {
			if (submenuLI.hasClass("user")) {
				var left = submenuLI.position().left + submenuLI.width() - submenu.width();
				submenu.css({left:left});				
			}
			if (submenu.css("display") == 'none') {
				$('#header ul.menu ul.submenu').hide();
				submenu.show();
			} else {
				$('#header ul.menu ul.submenu').hide();
			}
			event.preventDefault();
		}
	});
	if (config.currentUser) {
		loadNotifications();
		setInterval(loadNotifications,300000);
	}
});

//////////////////////////////////////////////////////////////////////////////// General Popup
function showPopup() {
	if ($('#PopupMask').size() == 0) {
		$('body').append('<div id="PopupMask"  onclick="closePopup();"></div>');
	}
	$('#PopupMask').show();
	$(document).on('keyup.close_popup', function(e) {
		if (e.keyCode == 27) { closePopup() }
	});
}


function closePopup() {
	$('.PopupBox').hide(); 
	$('#PopupMask').hide(); 
	$(document).unbind('keyup.close_popup');
}


//////////////////////////////////////////////////////////////////////////////// Help

function showHelpPopup(html) {
	var div = $('#HelpPopup');
	if (div.size() == 0) {
		var htmlOut = '<div id="HelpPopup" class="PopupBox">';
		htmlOut +=	'<div id="HelpPopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		htmlOut += '<div id="HelpPopupContents" class="PopupBoxContent">'+html+'</div>';
		htmlOut += '</div>';
		$('body').append(htmlOut);
	} else {
		$('#HelpPopupContents').html(html);
		div.show();
	}
	showPopup();
}

//////////////////////////////////////////////////////////////////////////////// Notifications

function loadNotifications() {
	$.ajax({
		dataType: "json",
		url: '/me/notification.json',
		success: function(data) {
			var html = '';
			var rootNotificationURL = (config.hasSSL ? 'https://'+config.httpsDomainIndex : 'http://'+config.httpDomainIndex) + '/me/notification/';
			var count = 0;
			var countUnread = 0;
			for(i in data.notifications) {
				var notification = data.notifications[i];
				if (!notification.read) {
					++countUnread;
					html += '<li class="unread">';
				} else {
					html += '<li class="read">';
				}
				
				html += '<a href="'+rootNotificationURL+notification.id+'" class="title">'+escapeHTML(notification.text)+'</a>';
				html += '<div class="timesince">'+escapeHTML(notification.timesince)+'</div>'
				html += '</li>';
				++count;
			}
			if (count > 0) {
				$('#NotificationSubMenu').empty().append(html);
				$('#NotificationMenuLink').show().html(countUnread > 0 ? 'notifications ('+countUnread+')' : 'notifications');
			} else {
				$('#NotificationMenuLink').hide();
			}
		}
	});
}

//////////////////////////////////////////////////////////////////////////////// General

function escapeHTMLNewLine(str, maxLength) {
	var div = document.createElement('div');
	div.appendChild(document.createTextNode(str));
	var out =  div.innerHTML;
	if (out.length > maxLength) {
		out = out.substr(0,maxLength)+" ...";
	}
	return out.replace(/\n/g,'<br>');
}
function escapeHTML(str) {
	var div = document.createElement('div');
	div.appendChild(document.createTextNode(str));
	return div.innerHTML;
}

