<?php

namespace org\openacalendar\addresscode\gb\opencodepoint;


/**
 *
 * @package org.openacalendar.addresscode.gb.opencodepoint
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AddressCodeGBOpenCodePointGet {
	
	static function get($postcode) {
		$postcode = strtoupper(str_replace(" ","",$postcode));
		$postcodeTwoChars = substr($postcode, 0,2);
		
		$ourdir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'..'.
				DIRECTORY_SEPARATOR.'data');
		$ourfile = $ourdir.DIRECTORY_SEPARATOR.$postcodeTwoChars.'.csv';
		
		if (is_file($ourfile)) {
			if (($handle = fopen($ourfile, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if (count($data) > 2 && $data[0] == $postcode) {
						fclose($handle);
						return array($data[1],$data[2]);
					}
				}
				fclose($handle);
			}
		}
		
		return array(null,null);
	}
	
	
}

