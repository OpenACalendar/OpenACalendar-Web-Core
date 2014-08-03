/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
//////////////////////////////////////////////////////////////////////////////// Export

function showExportPopup() {
	if ($('#ExportPopup').size() == 0) {
		var html = '<div id="ExportPopup" class="PopupBox" style="display: none">';
		html +=	'<div id="ExportPopupClose" class="PopupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><img src="/theme/default/img/actionClosePopup.png" alt="Close"></a></div>';
		html += '<div id="ExportPopupIntroText">Export your data.';
		if (exportData.hasOwnProperty("user")) {
			html += '<label><input type="radio" name="ExportWhat" id="ExportUserPublic" checked> the public calendar for '+exportData.user+' (events publically attending)</label>';
		}
		if (exportData.hasOwnProperty("user") &&  exportData.hasOwnProperty("userAccessKey") ) {
			html += '<label><input type="radio" name="ExportWhat" id="ExportUserPrivateA" checked> the private calendar for '+exportData.user+' (events attending)</label>';
			html += '<label><input type="radio" name="ExportWhat" id="ExportUserPrivateAW" checked> the private calendar for '+exportData.user+' (events attending and watching)</label>';
		}
		html += '</div>';
		html += '<ul id="ExportPopupMenu">';
		// space needed at start, then no spaces in tag. So can get wrap to work.
		html += ' <li class="ical" id="ExportToGoogleCalendarTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToGoogleCalendar\'); return false;"><div class="iconGoogleSmall"></div> Google Calendar</a></span></li>';
		html += ' <li class="ical" id="ExportToAppleCalendarTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToAppleCalendar\'); return false;"><div class="iconAppleSmall"></div> Mac/iPhone/iPad</a></span></li>';
		html += ' <li class="ical" id="ExportToICALTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToICAL\'); return false;">ics/ical file</a></span></li>';
		html += '</ul>';
		html += '<div class="content" id="ExportToGoogleCalendar">';
			html += '<p>In Google Calendar, click the drop down menu next to "Other calendars". Select "Add by URL" and copy and paste this in:</p><input type="text" class="exportlink"></p>';
		html += '</div>'
		html += '<div class="content" id="ExportToAppleCalendar">';
			html += '<p>For Apple Mac/iPhone/iPad <a href="#" target="_blank" class="exportlink">click here to subscribe</a>.</p>';
		html += '</div>'
		html += '<div class="content" id="ExportToICAL">';
			html += '<p>For ical <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
		html += '</div>'
		html += '</div>';
		$('body').append(html);
		div = $('#ExportPopup');
		div.find('#ExportToGoogleCalendar input.exportlink').focus(function() { $(this).select(); } );
		var showLinksForTab;
		if (exportData.hasOwnProperty("user")) {
			$('#ExportUserPublic').change(function() {
				if ($(this).is(':checked')) {
					showLinksFor("userpublic");
				}
			});
			showLinksForTab = "userpublic";
			$('#ExportUserPublic').prop('checked', true)
		}		
		if (exportData.hasOwnProperty("user") &&  exportData.hasOwnProperty("userAccessKey") ) {
			$('#ExportUserPrivateAW').change(function() {
				if ($(this).is(':checked')) {
					showLinksFor("userprivateattendingwatching");
				}
			});
			$('#ExportUserPrivateA').change(function() {
				if ($(this).is(':checked')) {
					showLinksFor("userprivateattending");
				}
			});
			showLinksForTab = "userprivateattendingwatching";
			$('#ExportUserPrivateAW').prop('checked', true)
		}
	}
	$('#ExportPopup').fadeIn(500);
	showPopup();
	showLinksFor(showLinksForTab);
	exportPopupTabClickNone();	
}

function showLinksFor(showFor) {
	var icalURL = "http://" + config.httpDomain+'/api1';
	if (exportData.hasOwnProperty("user") &&  exportData.hasOwnProperty("userAccessKey")  && showFor == "userprivateattendingwatching") {
		icalURL += "/person/"+exportData.user+"/private/"+exportData.userAccessKey+"/events.aw.ical";
	} else if (exportData.hasOwnProperty("user") &&  exportData.hasOwnProperty("userAccessKey")  && showFor == "userprivateattending") {
		icalURL += "/person/"+exportData.user+"/private/"+exportData.userAccessKey+"/events.a.ical";
	} else if (exportData.hasOwnProperty("user")  && showFor == "userpublic") {
		icalURL += "/person/"+exportData.user+"/events.ical";
	}
	var div = $('#ExportPopup');
	div.find('#ExportPopupIntroText a.icalexportlink').attr('href',icalURL);
	div.find('#ExportToGoogleCalendar input.exportlink').val(icalURL);
	div.find('#ExportToAppleCalendar a.exportlink').attr('href',icalURL.replace("http://","webcal://"));
	div.find('#ExportToICAL a.exportlink').attr('href',icalURL);
}

function exportPopupTabClick(tabID) {
	var div = $('#ExportPopup');
	div.find('.content').hide();
	div.find('#'+tabID).show();
	div.find('ul#ExportPopupMenu li').removeClass('current');
	div.find('ul#ExportPopupMenu li#'+tabID+'Tab').addClass('current');
}
function exportPopupTabClickNone() {
	var div = $('#ExportPopup');
	div.find('.content').hide();
	div.find('ul#ExportPopupMenu li').removeClass('current');
}


