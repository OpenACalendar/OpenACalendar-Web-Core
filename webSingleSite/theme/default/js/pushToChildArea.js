/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


function onCountryChanged() {
	var html = '<li class="loading">Loading</li>';
	$('#ChangeVenueAreaList').html(html);
	$('#PushToChildAreaForm input[name="area"]').val("");
	$.ajax({
		dataType: "json",
		url: '/country/'+country.slug+'/info.json',
		success: function(data) {
			$('#ChangeVenueAreaList li.loading').remove();
			var html = '';
			if (data.childAreas.length > 0) {
				html += '<li class="selectArea"><ul class="areas">';
				for(i in data.childAreas) {
					html += '<li class="area"><span class="content"><a href="#" onclick="existingAreaChoosen('+data.childAreas[i].slug+'); return false;">' + escapeHTML(data.childAreas[i].title) + '</a></span><span class="aftercontent">&nbsp;</span></li>';
					// Must have a breaking space between items to stop it becoming one long line.
					html += ' ';
				}
				html += '</ul></li>';
			}
			$('#ChangeVenueAreaList').html(html);
		}
	});
}

function existingAreaChoosen(areaSlug) {
	$('#PushToChildAreaForm input[name="area"]').val(areaSlug);
	$('#ChangeVenueAreaList li.selectArea').remove();
	loadNextArea(areaSlug,true);
}

function removeArea(removelink) {
	var areaListItem = $(removelink).parents('li.selectedarea');
	var areaList = areaListItem.parent('ul');
	areaListItem.nextAll().remove();
	areaListItem.remove();
	var lastAreaID = areaList.find('input[name="areas[]"]:last').val();

	if (typeof lastAreaID == 'undefined') {
		onCountryChanged();
	} else if (lastAreaID.substr(0,9) == 'EXISTING:') {
		$('#PushToChildAreaForm input[name="area"]').val(lastAreaID.substr(9));
		loadNextArea(lastAreaID.substr(9),false);
	}
}

function loadNextArea(areaSlug, includeCurrentArea) {
	var html = '<li class="loading">Loading</li>';
	$('#ChangeVenueAreaList').append(html);
	$.ajax({
		dataType: "json",
		url: '/area/'+areaSlug+'/info.json',
		success: function(data) {
			$('#ChangeVenueAreaList li.loading').remove();
			var html = '';
			if (includeCurrentArea) html += '<li class="selectedarea"><span class="content">'+
				'<span class="title">'+escapeHTML(data.area.title)+'</span>'+
				'<a href="#" onclick="removeArea(this); return false;" class="remove">X</a></span>'+
				'<input type="hidden" name="areas[]" value="EXISTING:'+data.area.slug+'">'+
				'</li>';
			if (data.childAreas.length > 0) {
				html += '<li class="selectArea"><ul class="areas">';
				for(i in data.childAreas) {
					html += '<li class="area"><span class="content"><a href="#" onclick="existingAreaChoosen('+data.childAreas[i].slug+'); return false;">' + escapeHTML(data.childAreas[i].title) + '</a></span><span class="aftercontent">&nbsp;</span></li>';
					// Must have a breaking space between items to stop it becoming one long line.
					html += ' ';
				}
				html += '</ul></li>';
			}
			$('#ChangeVenueAreaList').append(html);
		}
	});
}


