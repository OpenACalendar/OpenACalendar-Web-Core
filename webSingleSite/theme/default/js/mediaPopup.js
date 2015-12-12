/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/

function showMediaPopup(mediaSlug) {
	var thisMediaDataPosition = getPositionInMediaDataForSlug(mediaSlug);
	var thisMediaData = mediaData[i];
	var div = $('#MediaPopup');
	if (div.size() == 0) {
		var html = '<div id="MediaPopup" class="popupBox">';
		html +=	'<div id="MediaPopupClose" class="popupBoxClose"><a href="#" onclick="closePopup(); return false;" title="Close"><div class="fa fa-times fa-lg"></div></a></div>';
		html += '<div id="MediaPopupContents" class="popupBoxContent">'+html+'</div>';
		html += '</div>';
		$('body').append(html);
	}
	var html = '<div class="imageWrapper"><img src="/media/'+thisMediaData.slug+'/normal" alt="Image"></div>';
	html += '<div class="title">'+escapeHTMLNewLine(thisMediaData.title)+'</div>';
	if (thisMediaData.sourceURL && thisMediaData.sourceText) {
		html += '<div class="source">Source: <a href="'+thisMediaData.sourceURL+'">'+escapeHTML(thisMediaData.sourceText)+'</a></div>';
	} else if (thisMediaData.sourceURL) {
		html += '<div class="source">Source: <a href="'+thisMediaData.sourceURL+'">'+escapeHTML(thisMediaData.sourceURL)+'</a></div>';
	} else if (thisMediaData.sourceText) {
		html += '<div class="source">Source: '+escapeHTML(thisMediaData.sourceText)+'</div>';
	} 
	html += '<div class="link"><a href="/media/'+thisMediaData.slug+'/">More info</a></div>';
	$('#MediaPopupContents').html(html);
	$('#MediaPopup').show();
	showPopup();
}

function getPositionInMediaDataForSlug(mediaSlug) {
	for(i in mediaData) {
		if (mediaData[i].slug == mediaSlug) {
			return i;
		}
	}
	return -1;
}

