/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var marker;

$(document).ready(function() {
	map = L.map('Map')
	if (mapData.min_lat && mapData.min_lng && mapData.max_lat && mapData.max_lng) {
					var southWest = L.latLng(mapData.min_lat, mapData.min_lng),
						northEast = L.latLng(mapData.max_lat, mapData.max_lng),
						bounds = L.latLngBounds(southWest, northEast);
					map.fitBounds(bounds);
	} else	if (mapData.lat && mapData.lng) {
		map.setView([mapData.lat,mapData.lng], 13);
	} else {
		map.setView([55.952035, -3.196807], 4);
	}
	
	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);

	if (mapData.lat && mapData.lng) {
		marker = L.marker([mapData.lat,mapData.lng]);
		marker.addTo(map);
	}
	
	map.on('click', onMapClick);
	
	$('#VenueNewForm_country_id, #VenueEditForm_country_id').change(function() {  onCountryChanged(); });
});

function setUpBlankVenueForm() {
	onCountryChanged();
}

function onMapClick(e) {
	var lat = e.latlng.lat;
	var lng = e.latlng.lng;
	
	if (!marker) {
		marker = L.marker([lat, lng]);
		marker.addTo(map);
	} else {
		marker.setLatLng( e.latlng );
	}
	
	$('#VenueEditForm_lat, #VenueNewForm_lat, #AreaNewVenueInAreaForm_lat').val(lat);
	$('#VenueEditForm_lng, #VenueNewForm_lng, #AreaNewVenueInAreaForm_lng').val(lng);

}


function onCountryChanged() {
	var countryID = $('#VenueNewForm_country_id, #VenueEditForm_country_id').val();
	var html = '<li class="loading">Loading</li>';
	$('#ChangeVenueAreaList').html(html);
	$.ajax({
			dataType: "json",
			url: '/country/'+countryID+'/info.json',
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
					html += '<li class="area"><span class="content"><a href="#" onclick="newAreaChoosen(); return false;">Other</a></span><span class="aftercontent">&nbsp;</span></li>'
					html += '</ul></li>';
				}
				$('#ChangeVenueAreaList').html(html);
				
				if (data.country.min_lat && data.country.min_lng && data.country.max_lat && data.country.max_lng) {
					var southWest = L.latLng(data.country.min_lat, data.country.min_lng),
						northEast = L.latLng(data.country.max_lat, data.country.max_lng),
						bounds = L.latLngBounds(southWest, northEast);
					map.fitBounds(bounds);
				}
			}
		});
}

function existingAreaChoosen(areaSlug) {
	$('#ChangeVenueAreaList li.selectArea').remove();
	loadNextArea(areaSlug,true);
}

function removeArea(removelink) {
	var areaListItem = $(removelink).parents('li.selectedarea');
	var areaList = areaListItem.parent('ul');
	areaListItem.nextAll().remove();
	areaListItem.remove();
	var lastAreaID = areaList.find('input[type="hidden"]:last').val();
	
	if (typeof lastAreaID == 'undefined') {
		onCountryChanged();
	} else if (lastAreaID.substr(0,9) == 'EXISTING:') {
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
					html += '<li class="area"><span class="content"><a href="#" onclick="newAreaChoosen(); return false;">Other</a></span><span class="aftercontent">&nbsp;</span></li>'
					html += '</ul></li>';
				}
				$('#ChangeVenueAreaList').append(html);
				
				if (data.area.min_lat && data.area.min_lng && data.area.max_lat && data.area.max_lng) {
					if (data.area.min_lat == data.area.max_lat || data.area.min_lng == data.area.max_lng) {
						map.setView(L.latLng(data.area.min_lat, data.area.min_lng), 10);
					} else {
						var southWest = L.latLng(data.area.min_lat, data.area.min_lng),
							northEast = L.latLng(data.area.max_lat, data.area.max_lng),
							bounds = L.latLngBounds(southWest, northEast);
						map.fitBounds(bounds);
					}
				}
			}
		});	
	
	
}

function newAreaChoosen() {
	var title = prompt("What is the place called?");
	if (title) {
		var html = '<li class="selectedarea"><span class="content">'+
						'<span class="title">'+escapeHTML(title)+'</span>'+
						'<a href="#" onclick="removeArea(this); return false;" class="remove">X</a></span>'+
						'<input type="hidden" name="areas[]" value="NEW:'+escapeHTML(title)+'">'+
						'</li>';
		$('#ChangeVenueAreaList li.selectArea').remove();
		$('#ChangeVenueAreaList').append(html);
	}
}


