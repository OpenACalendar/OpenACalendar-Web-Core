/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

var map;

var marker1;
var marker2;

$(document).ready(function() {

    map = L.map('Map');
    configureBasicMap(map);

    mapToBounds(areaData.minLat, areaData.maxLat, areaData.minLng, areaData.maxLng );

    marker1 =  new L.marker([areaData.minLat, areaData.minLng], { draggable:'true'});
    map.addLayer(marker1);

    marker2 =  new L.marker([areaData.maxLat, areaData.maxLng], { draggable:'true'});
    map.addLayer(marker2);

});

function mapToBounds(minLat, maxLat, minLng, maxLng) {
    if (minLat == maxLat || minLng == maxLng) {
        map.setView([minLat,minLng], 13);
    } else {
        var southWest = L.latLng(minLat, minLng),
            northEast = L.latLng(maxLat, maxLng),
            bounds = L.latLngBounds(southWest, northEast);
        map.fitBounds(bounds);
    }
}

function onFormSubmit() {

    $('form input[name="minLat"]').val(Math.min(marker1.getLatLng().lat, marker2.getLatLng().lat));
    $('form input[name="maxLat"]').val(Math.max(marker1.getLatLng().lat, marker2.getLatLng().lat));

    $('form input[name="minLng"]').val(Math.min(marker1.getLatLng().lng, marker2.getLatLng().lng));
    $('form input[name="maxLng"]').val(Math.max(marker1.getLatLng().lng, marker2.getLatLng().lng));

    return true;
}
