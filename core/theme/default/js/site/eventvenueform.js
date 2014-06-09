/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
var map;
var markerGroup;
var venueMarkers = {};

$(document).ready(function() {
	map = L.map('Map')
	if (mapData.min_lat && mapData.min_lng && mapData.max_lat && mapData.max_lng) {
		if (mapData.min_lat == mapData.max_lat || mapData.min_lng == mapData.max_lng) {
			map.setView([mapData.min_lat,mapData.min_lng], 13);
		} else {			
			var southWest = L.latLng(mapData.min_lat, mapData.min_lng),
				northEast = L.latLng(mapData.max_lat, mapData.max_lng),
				bounds = L.latLngBounds(southWest, northEast);
			map.fitBounds(bounds);
		}
	} else	if (mapData.lat && mapData.lng) {
		map.setView([mapData.lat,mapData.lng], 13);
	} else {
		map.setView([55.952035, -3.196807], 4);
	}
	
	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);

	markerGroup = new L.MarkerClusterGroup();
	map.addLayer(markerGroup);

	var lastAreaID = $('#ChangeEventAreaList').find('input[type="hidden"]:last').val();
	
	if (typeof lastAreaID == 'undefined') {
		loadCountry(currentCountryID);
	} else if (lastAreaID.substr(0,9) == 'EXISTING:') {
		loadNextArea(lastAreaID.substr(9),false);
	}
	
});

function removeArea(removelink) {
	var areaListItem = $(removelink).parents('li.selectedarea');
	var areaList = areaListItem.parent('ul');
	areaListItem.nextAll().remove();
	areaListItem.remove();
	var lastAreaID = areaList.find('input[type="hidden"]:last').val();
	
	if (typeof lastAreaID == 'undefined') {
		loadCountry(currentCountryID);
	} else if (lastAreaID.substr(0,9) == 'EXISTING:') {
		loadNextArea(lastAreaID.substr(9),false);
	}
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
					html += '<li class="area"><span class="content"><a href="#" onclick="newAreaChoosen(); return false;">Create new</a></span><span class="aftercontent">&nbsp;</span></li>'
					html += '</ul></li>';
				}
				$('#ChangeEventAreaList').html(html);
				
				if (data.country.min_lat && data.country.min_lng && data.country.max_lat && data.country.max_lng) {
					var southWest = L.latLng(data.country.min_lat, data.country.min_lng),
						northEast = L.latLng(data.country.max_lat, data.country.max_lng),
						bounds = L.latLngBounds(southWest, northEast);
					map.fitBounds(bounds);
				}
				
				listVenues(data.venues);
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
			url: '/area/'+areaSlug+'/info.json?includeVenues=1',
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
					html += '<li class="area"><span class="content"><a href="#" onclick="newAreaChoosen(); return false;">Create new</a></span><span class="aftercontent">&nbsp;</span></li>'
					html += '</ul></li>';
				}
				$('#ChangeEventAreaList').append(html);
				
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
				
				listVenues(data.venues);
			}
		});	
	
	
}

function listVenues(venueList) {
	var html = '';
	var venueListArray = $.map(venueList, function(value, index) {
		return [value];
	});
	venueListArray.sort(function(a,b) {
		if (a.title > b.title) {
			return 1;
		} else if (a.title < b.title) {
			return -1;
		} else {
			return 0;
		}
	});
	for(i in venueListArray) {
		hasMapPos = venueListArray[i].lat && venueListArray[i].lng;
		html += '<li class="venue"><label>';
		html += '<input type="radio" name="venue_id" value="'+venueListArray[i].slug+'" '+(venueListArray[i].slug==currentVenueSlug?'checked="checked" ':'')+'>'+escapeHTML(venueListArray[i].title);
		html += '</label>';
		if (hasMapPos) html += ' <span class="mapLink">(<a href="#" onclick="showMarkerOnMap('+venueListArray[i].slug+');">map</a>)</span>';
		html += '</li>';
		
		if (!(venueListArray[i].slug in venueMarkers) && hasMapPos) {
			venueMarkers[venueList[i].slug] = L.marker([venueListArray[i].lat,venueListArray[i].lng]);
			venueMarkers[venueList[i].slug].bindPopup(escapeHTML(venueListArray[i].title)+'<br><a href="#" onclick="useVenue('+venueListArray[i].slug+'); return false">At this venue</a>');
			markerGroup.addLayer(venueMarkers[venueListArray[i].slug]);
		}
		
		
	}
	html += '<li class="newvenue">';
	html += '<label><input type="radio" name="venue_id" value="new">Other new venue</label>';
	html += '<div class="newvenueform">';
	html += '<label>Venue: <input type="text" name="newVenueTitle" class=""></label>';
	html += '<label>Address: <input type="text" name="newVenueAddress" class=""></label>';
	html += '<label>Postcode: <input type="text" name="newVenueAddressCode" class=""></label>';
	html += '</div></li>'
	html += '<li class="novenue"><label>';
	html += '<input type="radio" name="venue_id" value="no">Exact Venue not known.';
	html += '</label></li>'
	$('#ChangeEventVenueList').empty().html(html);
	$('#ChangeEventVenueList li.newvenue input[name="newVenueTitle"], #ChangeEventVenueList li.newvenue input[name="newVenueAddress"], #ChangeEventVenueList li.newvenue input[name="newVenueAddressCode"]').keyup(function (e) {
		if ($(this).val().trim() != "") {
			$('#ChangeEventVenueList li.newvenue input[type="radio"]').prop('checked',true);
		}
	});
}

function useVenue(venueSlug) {
	$('#ChangeEventVenueList')
			.empty()
			.html('<li class="venue"><label><input type="radio" name="venue_id" value="'+venueSlug+'" checked="checked"></label></li>')
			.parents('form')
			.submit();
}

function showMarkerOnMap(venueSlug) {
	if (venueSlug in venueMarkers) {
		map.setView(venueMarkers[venueSlug].getLatLng(), 16);
		venueMarkers[venueSlug].openPopup();
	}
}

function newAreaChoosen() {
	var title = prompt("What is the place called?");
	if (title) {
		var html = '<li class="selectedarea"><span class="content">'+
						'<span class="title">'+escapeHTML(title)+'</span>'+
						'<a href="#" onclick="removeArea(this); return false;" class="remove">X</a></span>'+
						'<input type="hidden" name="areas[]" value="NEW:'+escapeHTML(title)+'">'+
						'</li>';
		$('#ChangeEventAreaList li.selectArea').remove();
		$('#ChangeEventAreaList').append(html);
		listVenues([]);
	}
}





