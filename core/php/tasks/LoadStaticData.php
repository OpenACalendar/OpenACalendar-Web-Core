<?php

namespace tasks;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LoadStaticData {

	
	public static function load($verbose = false) {
		global $DB;
		
		
		if ($verbose) print "Starting ".date("c")."\n";


		$data = array();

		# Step - Load Countries
		foreach(explode("\n", file_get_contents(APP_ROOT_DIR.'/staticdatatoload/iso3166.tab')) as $line) {
			if ($line && substr($line, 0,1) != '#') {
				$bits = explode("\t", $line) ;
				$data[$bits[0]] = array(
					'Title'=>$bits[1],
					'TimeZones'=>array(),
					'MaxLat'=>null,
					'MaxLng'=>null,
					'MinLat'=>null,
					'MinLng'=>null,
					'AddressCodeLabel'=>'Postcode'
				);
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

		# Step - Load Address Code Label
		foreach(explode("\n", file_get_contents(APP_ROOT_DIR.'/staticdatatoload/countryAddressCodeLabels.tab')) as $line) {
			if ($line && substr($line, 0,1) != '#') {
				$bits = explode("\t", $line) ;
				if ($bits[0] && isset($data[$bits[0]])) {
					$data[$bits[0]]['AddressCodeLabel'] = $bits[1];
				}
			}
		}


		//var_dump($data);

		# Step - wangle
		$data['GB']['Title'] = 'United Kingdom';


		# Step - Save to DB
		$statCheck = $DB->prepare("SELECT * FROM country WHERE two_char_code=:code");
		$statInsert = $DB->prepare("INSERT INTO country (two_char_code,title,timezones,max_lat,max_lng,min_lat,min_lng,address_code_label) ".
				"VALUES (:two_char_code,:title,:timezones,:max_lat,:max_lng,:min_lat,:min_lng,:address_code_label)");
		$statUpdate = $DB->prepare("UPDATE country SET title=:title, timezones=:timezones,  ".
				" max_lat=:max_lat, max_lng=:max_lng, min_lat=:min_lat, min_lng=:min_lng, address_code_label=:address_code_label  ".
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
					'address_code_label'=>$countryData['AddressCodeLabel'],
				);
			if ($statCheck->rowCount() > 0) {
				$statUpdate->execute($params);
			} else {
				$statInsert->execute($params);
			}
			if ($verbose) print ".";
		}
		if ($verbose) print "\n";

		if ($verbose) print "Finished ".date("c")."\n";
		
	}
	
}

