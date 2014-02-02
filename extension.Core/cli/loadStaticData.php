<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$data = array();

# Step - Load Countries
foreach(explode("\n", file_get_contents(APP_ROOT_DIR.'/staticdatatoload/iso3166.tab')) as $line) {
	if ($line && substr($line, 0,1) != '#') {
		$bits = explode("\t", $line) ;
		$data[$bits[0]] = array('Title'=>$bits[1],'TimeZones'=>array(),'MaxLat'=>null,'MaxLng'=>null,'MinLat'=>null,'MinLng'=>null );
	}
}

# Step - Load Timezones
foreach(explode("\n", file_get_contents(APP_ROOT_DIR.'/staticdatatoload/zone.tab')) as $line) {
	if ($line && substr($line, 0,1) != '#') {
		$bits = explode("\t", $line) ;
		$data[$bits[0]]['TimeZones'][] = $bits[2];
	}
}

# Step - Load Country bounds we got from http://wiki.openstreetmap.org/wiki/User:Ewmjc/Country_bounds
foreach(explode("\n", file_get_contents(APP_ROOT_DIR.'/staticdatatoload/countryBounds.tab')) as $line) {
	if ($line && substr($line, 0,1) != '#') {
		$bits = explode("\t", $line) ;
		if ($bits[1] && isset($data[$bits[1]])) {
			$data[$bits[1]]['MaxLat'] = $bits[6];
			$data[$bits[1]]['MaxLng'] = $bits[5];
			$data[$bits[1]]['MinLat'] = $bits[4];
			$data[$bits[1]]['MinLng'] = $bits[3];
		}
	}
}


//var_dump($data);

# Step - wangle
$data['GB']['Title'] = 'United Kingdom';


# Step - Save to DB
$statCheck = $DB->prepare("SELECT * FROM country WHERE two_char_code=:code");
$statInsert = $DB->prepare("INSERT INTO country (two_char_code,title,timezones,max_lat,max_lng,min_lat,min_lng) ".
		"VALUES (:two_char_code,:title,:timezones,:max_lat,:max_lng,:min_lat,:min_lng)");
$statUpdate = $DB->prepare("UPDATE country SET title=:title, timezones=:timezones,  ".
		" max_lat=:max_lat, max_lng=:max_lng, min_lat=:min_lat, min_lng=:min_lng  ".
		" WHERE two_char_code=:two_char_code");
foreach($data as $code=>$countryData) {
	$statCheck->execute(array('code'=>$code));
	$params = array(
			'two_char_code'=>  strtoupper($code),
			'timezones'=>implode(",",$countryData['TimeZones']),
			'title'=>$countryData['Title'],
			'max_lat'=>$countryData['MaxLat'],
			'max_lng'=>$countryData['MaxLng'],
			'min_lat'=>$countryData['MinLat'],
			'min_lng'=>$countryData['MinLng'],
		);
	if ($statCheck->rowCount() > 0) {
		$statUpdate->execute($params);
	} else {
		$statInsert->execute($params);
	}
	print ".";
}
print "\n";

