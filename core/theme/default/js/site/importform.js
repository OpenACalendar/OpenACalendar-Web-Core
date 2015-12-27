/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
$(document).ready(function() {
	$('#ImportEditForm_country_id, #ImportNewForm_country_id').change(function() {
		onCountryChange();
	});
});
	
	
function getCurrentCountry() {
	return $('#ImportEditForm_country_id, #ImportNewForm_country_id').val();
}
	
var lastCountryIDSeen = -1;	
var countryDataAJAX;
function onCountryChange() {
	var currentCountryID = getCurrentCountry();
	if (lastCountryIDSeen !== currentCountryID) {
		loadCountry(currentCountryID);
	}
	lastCountryIDSeen = currentCountryID;
}


function loadCountry(countryID) {
	var html = '<li class="loading">Loading</li>';
	$('#ChangeEventAreaList').html(html);
	$.ajax({
		dataType: "json",
		url: '/country/'+countryID+'/info.json?includeVenues=1',
		success: function(data) {
			$('#ChangeEventAreaList li.loading').remove();
			var html = '';
			if (data.childAreas.length > 0) {
				html += '<li class="selectArea"><ul class="areas">';
				for(i in data.childAreas) {
					// must have space at start so items break over long lines
					html += ' <li class="area"><span class="content"><a href="#" onclick="existingAreaChoosen('+data.childAreas[i].slug+'); return false;">' + escapeHTML(data.childAreas[i].title) + '</a></span><span class="aftercontent">&nbsp;</span></li>';
				}
				html += '</ul></li>';
				$('#ChangeEventAreaListLabel').show();
			} else {
				$('#ChangeEventAreaListLabel').hide();
			}
			$('#ChangeEventAreaList').html(html);
		}
	});
}


function existingAreaChoosen(areaSlug) {
	$('#ChangeEventAreaList li.selectArea').remove();
	loadNextArea(areaSlug,true);
}

function loadNextArea(areaSlug, includeCurrentArea) {
	var html = '<li class="loading">Loading</li>';
	$('#ChangeEventAreaList').append(html);
	$.ajax({
		dataType: "json",
		url: '/area/'+areaSlug+'/info.json?includeVenues=0',
		success: function(data) {
			$('#ChangeEventAreaList li.loading').remove();
			var html = '';
			if (includeCurrentArea) html += '<li class="selectedarea"><span class="content">'+
					'<span class="title">'+escapeHTML(data.area.title)+'</span>'+
					'<a href="#" onclick="removeArea(this); return false;" class="remove">X</a></span>'+
					'<input type="hidden" name="areas[]" value="EXISTING:'+data.area.slug+'">'+
					'</li>';
			if (data.childAreas.length > 0) {
				html += '<li class="selectArea"><ul class="areas">';
				for(i in data.childAreas) {
					// must have space at start so items break over long lines
					html += ' <li class="area"><span class="content"><a href="#" onclick="existingAreaChoosen('+data.childAreas[i].slug+'); return false;">' + escapeHTML(data.childAreas[i].title) + '</a></span><span class="aftercontent">&nbsp;</span></li>';
				}
				html += '</ul></li>';
			}
			$('#ChangeEventAreaList').append(html);
		}
	});	
}


function removeArea(removelink) {
	var areaListItem = $(removelink).parents('li.selectedarea');
	var areaList = areaListItem.parent('ul');
	areaListItem.nextAll().remove();
	areaListItem.remove();
	var lastAreaID = areaList.find('input[type="hidden"]:last').val();
	
	if (typeof lastAreaID == 'undefined') {
		loadCountry(getCurrentCountry());
	} else if (lastAreaID.substr(0,9) == 'EXISTING:') {
		loadNextArea(lastAreaID.substr(9),false);
	}
}
	
function escapeHTML(inString) {
	return  $("<div/>").text(inString).html();
}
