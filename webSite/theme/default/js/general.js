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
				submenu.css({left:Math.max(0,left)});
			}
			if (submenu.css("display") == 'none') {
				$('#header ul.menu ul.submenu').hide();
				$('ul#innerPageActions ul.submenu').hide();
				submenu.show();
			} else {
				$('#header ul.menu ul.submenu').hide();
				$('ul#innerPageActions ul.submenu').hide();
			}
			event.preventDefault();
		}
	});
	$('ul#innerPageActions > li.actionWithSubMenu a').click(function( event ) {
		var submenuLI = $(this).parent();
		var submenu = submenuLI.children('ul.submenu');
		if (submenu.size() > 0) {
			var left = submenuLI.position().left + submenuLI.width() - submenu.width();
			submenu.css({left:Math.max(0,left)});
			if (submenu.css("display") == 'none') {
				$('#header ul.menu ul.submenu').hide();
				$('ul#innerPageActions ul.submenu').hide();
				submenu.show();
			} else {
				$('#header ul.menu ul.submenu').hide();
				$('ul#innerPageActions ul.submenu').hide();
			}
			event.preventDefault();
		}
	});
	if (config.currentUser) {
		loadNotifications();
		setInterval(loadNotifications,300000);
	}
	checkScreenSizeAndUpdate();
	$(window).resize(function() {
		checkScreenSizeAndUpdate();
	});
});

//////////////////////////////////////////////////////////////////////////////// General Popup
function showPopup() {
	if ($('#PopupMask').size() == 0) {
		$('body').append('<div id="PopupMask"  onclick="closePopup();" style="display:none;"></div>');
	}
	$('#PopupMask').fadeIn(500);
	$(document).on('keyup.close_popup', function(e) {
		if (e.keyCode == 27) { closePopup() }
	});
	$('.popupBox').css({top: ($(document).scrollTop()+25)+'px' });
}


function closePopup() {
	$('.popupBox').fadeOut(500);
	$('#PopupMask').fadeOut(500);
	$(document).unbind('keyup.close_popup');
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
				
				html += '<div class="title"><a href="'+rootNotificationURL+notification.id+'">'+escapeHTML(notification.text)+'</a></div>';
				html += '<div class="timesince">'+escapeHTML(notification.timesince)+'</div>'
				if (!config.isSingleSiteMode) {
					html += '<div class="site">'+escapeHTML(notification.site.title)+'</div>'
				}
				html += '</li>';
				++count;
			}
			if (count > 0) {
				$('#NotificationSubMenu').empty().append(html);
				$('#NotificationMenuLinkCount').show().html(countUnread > 0 ? '('+countUnread+')' : '');
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
function escapeHTMLAttribute(str) {
	return str
		.replace(/&/g, '&amp;')
		.replace(/'/g, '&apos;')
		.replace(/"/g, '&quot;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/\r/g, '')
		.replace(/\n/g, '');
	;
}

//////////////////////////////////////////////////////////////////////////////// Mobile

var isSmallScreenSize = false;

function checkScreenSizeAndUpdate() {
	var screenWidth = $('body').innerWidth();
	if (screenWidth < 500 && !isSmallScreenSize) {
		// Time to add options!
		var container = $('#innerPageActions');
		if (container.length == 1) {
			container.before('<div id="innerPageActionsShow" onclick="innerPageActionsShow(); return false;"><div class="iconBarsSmall"></div> Show Options</div>');
			container.hide();
			container.prepend('<li class="hide" onclick="innerPageActionsHide(); return false;"><div class="iconBarsSmall"></div> Hide Options</li>');
			isSmallScreenSize = true;
		}
	}
}

function innerPageActionsShow() {
	$('#innerPageActions').show();
	$('#innerPageActionsShow').hide();
}

function innerPageActionsHide() {
	$('#innerPageActions').hide();
	$('#innerPageActions ul.submenu').hide();
	$('#innerPageActionsShow').show();
}
