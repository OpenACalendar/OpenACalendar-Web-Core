/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
//////////////////////////////////////////////////////////////////////////////// Export

var atomBeforeDays = 3;

function showExportSharePopup() {
	if ($('#ExportSharePopup').size() == 0) {
		var html = '<div id="ExportSharePopup" class="popupBox" style="display: none">';
		html +=	'<div id="ExportSharePopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><div class="fa fa-times fa-lg"></div></a></div>';
        html += '<div class="popupBoxContent">';

		if (config.isWebRobotsAllowed) {
			var url = "http://" + config.httpDomain;
			var text = "";
			var emailSubject = "";
			var emailBody = "";
			/** Sometimes more than one will be set (eg event and group) so must check most important one first **/
			if (exportData.hasOwnProperty("event")) {
				url += exportData.hasOwnProperty("eventSlugURL") ? "/event/"+exportData.eventSlugURL :  "/event/"+exportData.event ;
				text = exportData.eventTitle;
				emailSubject = exportData.eventTitle;
				emailBody = exportData.eventTitle + "\n" + exportData.eventStartLocal + "\n\n" + url;
			} else if (exportData.hasOwnProperty("group")) {
				url += exportData.hasOwnProperty("groupSlugURL") ? "/group/"+exportData.groupSlugURL : "/group/"+exportData.group;
				text = exportData.hasOwnProperty("groupTwitterUsername") && exportData.groupTwitterUsername ? exportData.groupTitle + " @" + exportData.groupTwitterUsername :  exportData.groupTitle;
				emailSubject = exportData.groupTitle;
			} else if (exportData.hasOwnProperty("venueVirtual") && exportData.venueVirtual) {
				url +=  "/venue/virtual";
				text = 'Virtual Events';
				emailSubject = 'Virtual Events';
			} else if (exportData.hasOwnProperty("venue")) {
				url +=  exportData.hasOwnProperty("venueSlugURL") ? "/venue/"+exportData.venueSlugURL : "/venue/"+exportData.venue;
				text = exportData.venueTitle;
				emailSubject = exportData.venueTitle;
			} else if (exportData.hasOwnProperty("tag")) {
				url +=  exportData.hasOwnProperty("tagSlugURL") ? "/tag/"+exportData.tagSlugURL : "/tag/"+exportData.tag;
				text = exportData.tagTitle;
				emailSubject = exportData.tagtitle;
			} else if (exportData.hasOwnProperty("area")) {
				url +=  exportData.hasOwnProperty("areaSlugURL") ? "/area/"+exportData.areaSlugURL : "/area/"+exportData.area;
				text = exportData.areaTitle;
				emailSubject = exportData.areaTitle;
			} else if (exportData.hasOwnProperty("country")) {
				url += "/country/"+exportData.country;
				text = exportData.country;
				emailSubject = exportData.country;
			} else if (exportData.hasOwnProperty("curatedlist")) {
				url += exportData.hasOwnProperty("curatedlistSlugURL") ? "/curatedlist/"+exportData.curatedlistSlugURL : "/curatedlist/"+exportData.curatedlist;
				text = exportData.curatedlistTitle;
				emailSubject = exportData.curatedlistTitle;
			} else {
				url += "/";
				text = config.installTitle;
				emailSubject = config.installTitle;
			}
			html += '<div id="ExportSharePopupShareIntroText"><p class="header">Share this:</p></div>';
			html += '<ul class="ExportSharePopupShareMenu">'

			html += '<li><a href="mailto:?subject='+encodeURIComponent(emailSubject)+'&body='+encodeURIComponent(emailBody ? emailBody : text+"\n\n"+url)+'" title="Email"><div class="fa fa-envelope-o fa-2x" title="Email"></div></li>';
			html += '<li><a href="https://twitter.com/intent/tweet?text='+encodeURIComponent(url+" "+text+( config.twitter ? " via @"+config.twitter : ""))+'" target="_blank" title="Twitter"><div class="fa fa-twitter fa-2x" title="Twitter"></div></li>';
			html += '<li><a href="https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url)+'" target="_blank" title="Facebook"><div class="fa fa-facebook-official fa-2x" title="Facebook"></div></a></li>';
			html += '<li><a href="https://plus.google.com/share?url='+encodeURIComponent(url)+'" target="_blank" title="Google Plus"><div class="fa fa-google-plus-square fa-2x" title="Google Plus"></div></a></li>';

			html += '</ul>'

		}
		html += '<div id="ExportSharePopupExportIntroText"><p class="header">Export your data!</p>';
		if (exportData.hasOwnProperty("event") || exportData.hasOwnProperty("tag") || exportData.hasOwnProperty("area")  || exportData.hasOwnProperty("group") || exportData.hasOwnProperty("country") || exportData.hasOwnProperty("venue") || exportData.hasOwnProperty("curatedlist")) {
			html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportAll" checked> all events</label></div>';
			if (exportData.hasOwnProperty("country") ) {
				html += '<label><input type="radio" name="ExportWhat" id="ExportCountry"> all events from ';
				html += (exportData.hasOwnProperty("countryTitle") ? 'country: '+ escapeHTML(exportData.countryTitle) : 'this country' );
				html += '</label></div>';
			}
			if (exportData.hasOwnProperty("area") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportArea"> all events from ';
				html += (exportData.hasOwnProperty("areaTitle") ? 'area: '+ escapeHTML(exportData.areaTitle) : 'this area' );
				html += '</label></div>';
			}
			if (exportData.hasOwnProperty("venueVirtual") && exportData.venueVirtual) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportVenueVirtual"> all virtual events</label></div>';
			} else if (exportData.hasOwnProperty("venue") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportVenue"> all events from ';
				html += (exportData.hasOwnProperty("venueTitle") ? 'venue: '+ escapeHTML(exportData.venueTitle) : 'this venue' );
				html += '</label></div>';
			}
			if (exportData.hasOwnProperty("group") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportGroup"> all events from ';
				html += (exportData.hasOwnProperty("groupTitle") ? 'group: '+ escapeHTML(exportData.groupTitle) : 'this group' );
				html += '</label></div>';
			}
			if (exportData.hasOwnProperty("tag") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportTag"> all events from ';
				html += (exportData.hasOwnProperty("tagTitle") ? 'tag: '+ escapeHTML(exportData.tagTitle) : 'this tag' );
				html += '</label></div>';
			}
			if (exportData.hasOwnProperty("curatedlist") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportCuratedList"> all events from ';
				html += (exportData.hasOwnProperty("curatedlistTitle") ? 'curated list: '+ escapeHTML(exportData.curatedlistTitle) : 'this curated list' );
				html += '</label></div>';
			}			
			if (exportData.hasOwnProperty("event") ) {
				html += '<div class="ExportSharePopupExportFilterOption"><label><input type="radio" name="ExportWhat" id="ExportEvent"> just this event</label>';
                if (exportData.eventTicketURL) {
                    var link = $('<a/>');
                    link.prop('href', exportData.eventTicketURL);
                    html += ' <div id="ExportSharePopupExportFilterEventOnlyTicketWarning">(Make sure you <a href="'+ escapeHTMLAttribute(exportData.eventTicketURL) +'" target="_blank" rel="noopener">get your ticket from '+link.prop('host')+'</a>)</div>';
                }
                html += '</div>';
			}
		} else {
			html  += '<p>Hint: if you browse round the calendar, you can export filtered feeds of only what interests you!</p>';
		}
		html += '</div>';
		html += '<ul id="ExportSharePopupExportMenu">';
		// space needed at start, then no spaces in tag. So can get wrap to work.
		html += ' <li class="ical" id="ExportToGoogleCalendarTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToGoogleCalendar\'); return false;"><div class="fa fa-google"></div> Google Calendar</a></span></li>';
		html += ' <li class="ical" id="ExportToAppleCalendarTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToAppleCalendar\'); return false;"><div class="fa fa-apple"></div> Mac/iPhone/iPad</a></span></li>';
		html += ' <li class="ical" id="ExportToATOMTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToATOM\'); return false;">News reader (ATOM/RSS)</a></span></li>';
		html += ' <li class="ical" id="ExportToICALTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToICAL\'); return false;">ics/ical file</a></span></li>';
		html += ' <li class="ical" id="ExportToCSVTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToCSV\'); return false;">CSV</a></span></li>';
		html += ' <li class="ical" id="ExportToJSONTab"><span class="wrapper"><a href="#" onclick="exportPopupTabClick(\'ExportToJSON\'); return false;">JSON</a></span></li>';
		html += '</ul>';
		html += '<div class="content" id="ExportToGoogleCalendar">';
			html += '<p>In Google Calendar, click the drop down menu next to "Other calendars". Select "Add by URL" and copy and paste this in:</p><input type="text" class="exportlink"></p>';
			if (exportData.hasOwnProperty("event")) {
				html += '<p id="ExportToExistingGoogleCalendar">This will create a new calendar. You can <a href="/event/'+exportData.event+'/export/existinggooglecalendar" rel="nofollow" target="_blank">also add the event to an existing calendar</a>, but it will not receive future updates automatically.</p>';
			}
		html += '</div>'
		html += '<div class="content" id="ExportToAppleCalendar">';
			html += '<p>For Apple Mac/iPhone/iPad <a href="#" target="_blank" class="exportlink">click here to subscribe</a>.</p>';
		html += '</div>'
		html += '<div class="content" id="ExportToICAL">';
			html += '<p>For ical <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
		html += '</div>'
		html += '<div class="content" id="ExportToCSV">';
			html += '<p>For csv <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
		html += '</div>'
		html += '<div class="content" id="ExportToJSON">';
			html += '<p class="json">For JSON <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
			html += '<p class="jsonp">For JSONP <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
		html += '</div>'
		html += '<div class="content" id="ExportToATOM">';
			html += '<p class="atomCreate">For a feed of events as they are created, <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
			html += '<p class="atomBefore">For a feed of events <span id="atomBeforeDays">3</span> days before they happen, <a href="#" target="_blank" class="exportlink">click here</a>.</p>';
			html += '<p>(<a href="#" onclick="atomBeforeDaysDecrease(); return false">Minus a day</a>)</p>';
			html += '<p>(<a href="#" onclick="atomBeforeDaysIncrease(); return false;">Plus a day</a>)</p>';
		html += '</div>'
		html += '</div>';
		html += '</div>';
		$('body').append(html);
		var div = $('#ExportSharePopup');
		div.find('#ExportToGoogleCalendar input.exportlink').focus(function() { $(this).select(); } );
		var showLinksForTab = "all";
		if (exportData.hasOwnProperty("event") || exportData.hasOwnProperty("tag") || exportData.hasOwnProperty("area")  || exportData.hasOwnProperty("group") || exportData.hasOwnProperty("country")  || exportData.hasOwnProperty("venue")  || exportData.hasOwnProperty("curatedlist") ) {
			$('#ExportAll').change(function() {
				if ($(this).is(':checked')) {
					showLinksFor("all");
				}
			});
			if (exportData.hasOwnProperty("country") ) {
				$('#ExportCountry').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("country");
					}
				});
				showLinksForTab = "country";
				$('#ExportCountry').prop('checked', true)
			}			
			if (exportData.hasOwnProperty("area") ) {
				$('#ExportArea').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("area");
					}
				});
				showLinksForTab = "area";
				$('#ExportArea').prop('checked', true)
			}					
			if (exportData.hasOwnProperty("venueVirtual") ) {
				$('#ExportVenueVirtual').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("venueVirtual");
					}
				});
				showLinksForTab = "venue";
				$('#ExportVenue').prop('checked', true)
			} else if (exportData.hasOwnProperty("venue") ) {
				$('#ExportVenue').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("venue");
					}
				});
				showLinksForTab = "venue";
				$('#ExportVenue').prop('checked', true)
			}
			if (exportData.hasOwnProperty("group") ) {
				$('#ExportGroup').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("group");
					}
				});
				showLinksForTab = "group";
				$('#ExportGroup').prop('checked', true)
			}
			if (exportData.hasOwnProperty("tag") ) {
				$('#ExportTag').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("tag");
					}
				});
				showLinksForTab = "tag";
				$('#ExportTag').prop('checked', true)
			}
			if (exportData.hasOwnProperty("curatedlist") ) {
				$('#ExportCuratedList').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("curatedlist");
					}
				});
				showLinksForTab = "curatedlist";
				$('#ExportCuratedList').prop('checked', true)
			}
			if (exportData.hasOwnProperty("event") ) {
				$('#ExportEvent').change(function() {
					if ($(this).is(':checked')) {
						showLinksFor("event");
					}
				});
				showLinksForTab = "event";
				$('#ExportEvent').prop('checked', true)
			}
		}
	}
	$('#ExportSharePopup').fadeIn(500);
	showPopup();
	showLinksFor(showLinksForTab);
	exportPopupTabClickNone();	
}

var showLinksForLastCalled;
function showLinksFor(showFor) {
	showLinksForLastCalled = showFor;
	var icalURL = "http://" + config.httpDomain + "/api1";
	var jsonURL = "http://" + config.httpDomain + "/api1";
	var jsonpURL = "http://" + config.httpDomain + "/api1";
	var atomCreateURL  = "http://" + config.httpDomain + "/api1";
	var atomBeforeURL  = "http://" + config.httpDomain + "/api1";
	var csvURL  = "http://" + config.httpDomain + "/api1";
	var hasAtom = true;
	var hasCSV = true;
	var hasExistingGoogleCalendar = false;
	if (exportData.hasOwnProperty("event") && showFor == "event") {
		icalURL += "/event/"+exportData.event+"/info.ical";
		jsonURL += "/event/"+exportData.event+"/info.json";
		jsonpURL += "/event/"+exportData.event+"/info.jsonp?callback=myfunc";
		hasAtom = false;
        hasCSV = false;
		hasExistingGoogleCalendar = true;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').show();
	} else if (exportData.hasOwnProperty("group") && showFor == "group") {
		icalURL += "/group/"+exportData.group+"/events.ical";
		csvURL += "/group/"+exportData.group+"/events.csv";
		jsonURL += "/group/"+exportData.group+"/events.json";
		jsonpURL += "/group/"+exportData.group+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/group/"+exportData.group+"/events.create.atom";
		atomBeforeURL += "/group/"+exportData.group+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("venueVirtual") && showFor == "venueVirtual") {
		icalURL += "/venue/virtual/events.ical";
		csvURL += "/venue/virtual/events.csv";
		jsonURL += "/venue/virtual/events.json";
		jsonpURL += "/venue/virtual/events.jsonp?callback=myfunc";
		atomCreateURL += "/venue/virtual/events.create.atom";
		atomBeforeURL += "/venue/virtual/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("venue") && showFor == "venue") {
		icalURL += "/venue/"+exportData.venue+"/events.ical";
        csvURL += "/venue/"+exportData.venue+"/events.csv";
		jsonURL += "/venue/"+exportData.venue+"/events.json";
		jsonpURL += "/venue/"+exportData.venue+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/venue/"+exportData.venue+"/events.create.atom";
		atomBeforeURL += "/venue/"+exportData.venue+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("area") && showFor == "area") {
		icalURL += "/area/"+exportData.area+"/events.ical";
		csvURL += "/area/"+exportData.area+"/events.csv";
		jsonURL += "/area/"+exportData.area+"/events.json";
		jsonpURL += "/area/"+exportData.area+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/area/"+exportData.area+"/events.create.atom";
		atomBeforeURL += "/area/"+exportData.area+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("tag") && showFor == "tag") {
		icalURL += "/tag/"+exportData.tag+"/events.ical";
		csvURL += "/tag/"+exportData.tag+"/events.csv";
		jsonURL += "/tag/"+exportData.tag+"/events.json";
		jsonpURL += "/tag/"+exportData.tag+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/tag/"+exportData.tag+"/events.create.atom";
		atomBeforeURL += "/tag/"+exportData.tag+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("country") && showFor == "country") {
		icalURL += "/country/"+exportData.country+"/events.ical";
		csvURL += "/country/"+exportData.country+"/events.csv";
		jsonURL += "/country/"+exportData.country+"/events.json";
		jsonpURL += "/country/"+exportData.country+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/country/"+exportData.country+"/events.create.atom";
		atomBeforeURL += "/country/"+exportData.country+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else if (exportData.hasOwnProperty("curatedlist") && showFor == "curatedlist") {
		csvURL += "/curatedlist/"+exportData.curatedlist+"/events.csv";
		icalURL += "/curatedlist/"+exportData.curatedlist+"/events.ical";
		jsonURL += "/curatedlist/"+exportData.curatedlist+"/events.json";
		jsonpURL += "/curatedlist/"+exportData.curatedlist+"/events.jsonp?callback=myfunc";
		atomCreateURL += "/curatedlist/"+exportData.curatedlist+"/events.create.atom";
		atomBeforeURL += "/curatedlist/"+exportData.curatedlist+"/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	} else {
		icalURL += "/events.ical";
		csvURL += "/events.csv";
		jsonURL += "/events.json";
		jsonpURL += "/events.jsonp?callback=myfunc";
		atomCreateURL += "/events.create.atom";
		atomBeforeURL += "/events.before.atom?days="+atomBeforeDays;
        $('#ExportSharePopupExportFilterEventOnlyTicketWarning').hide();
	}
	var div = $('#ExportSharePopup');
	div.find('#ExportSharePopupExportIntroText a.icalexportlink').attr('href',icalURL);
	div.find('#ExportToGoogleCalendar input.exportlink').val(icalURL);
	div.find('#ExportToAppleCalendar a.exportlink').attr('href',icalURL.replace("http://","webcal://"));
	div.find('#ExportToICAL a.exportlink').attr('href',icalURL);
	div.find('#ExportToJSON .json a.exportlink').attr('href',jsonURL);
	div.find('#ExportToJSON .jsonp a.exportlink').attr('href',jsonpURL);	
	if (hasAtom) {
		$('#ExportToATOMTab').show();
		div.find('#ExportToATOM .atomCreate a.exportlink').attr('href',atomCreateURL);	
		div.find('#ExportToATOM .atomBefore a.exportlink').attr('href',atomBeforeURL);	
	} else {
		$('#ExportToATOM').hide();
		$('#ExportToATOMTab').hide();
	}
    if (hasCSV) {
		$('#ExportToCSVTab').show();
        div.find('#ExportToCSV a.exportlink').attr('href',csvURL);
    } else {
		$('#ExportToCSV').hide();
		$('#ExportToCSVTab').hide();
	}
	if (hasExistingGoogleCalendar) {
		$('#ExportToExistingGoogleCalendar').show();
	} else {
		$('#ExportToExistingGoogleCalendar').hide();
	}
}

function atomBeforeDaysDecrease() {
	if (atomBeforeDays > 1) {
		atomBeforeDays = atomBeforeDays - 1;
		showLinksFor(showLinksForLastCalled);
		$('#atomBeforeDays').html(atomBeforeDays);
	}
}

function atomBeforeDaysIncrease() {
	if (atomBeforeDays < 99) {
		atomBeforeDays = atomBeforeDays + 1;
		showLinksFor(showLinksForLastCalled);
		$('#atomBeforeDays').html(atomBeforeDays);
	}
}

function exportPopupTabClick(tabID) {
	var div = $('#ExportSharePopup');
	div.find('.content').hide();
	div.find('#'+tabID).show();
	div.find('ul#ExportSharePopupExportMenu li').removeClass('current');
	div.find('ul#ExportSharePopupExportMenu li#'+tabID+'Tab').addClass('current');
}
function exportPopupTabClickNone() {
	var div = $('#ExportSharePopup');
	div.find('.content').hide();
	div.find('ul#ExportSharePopupExportMenu li').removeClass('current');
}



//////////////////////////////////////////////////////////////////////////////// Edit Comment
function onClickFormRowEditCommentToggler() {
	var wrapper = $('#formRowEditCommentWrapper');
	if (wrapper.is(":visible")) {
		wrapper.hide();
		$('#formRowEditCommentToggler .arrow').html('&#x25B2;');
	} else  {
		wrapper.show();
		$('#formRowEditCommentToggler .arrow').html('&#x25BC;');
	}
}
