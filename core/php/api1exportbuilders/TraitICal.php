<?php

namespace api1exportbuilders;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


trait TraitICal {
	
	
	/**
	 * Public for testing
	 * @param type $key
	 * @param type $value
	 * @return type 
	 */
	public function getIcalLine($key,$value) {		
		// should be wrapping long lines and escaping new lines
		$value = str_replace("\\", "\\\\", $value);
		$value = str_replace("\r", "", str_replace("\n", '\\n', $value));
		$value = str_replace(";", "\\;", $value);
		$value = str_replace(",", "\\,", $value);
		$value = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
		// google calendar does not like a space after the ':'.
		$out = $key.":".$value;
		if(strlen($out) > 75) {
			$out = $key.":";
			# first Line;
			$charsToAdd = 75-strlen($out);
			$out .= substr($value, 0, $charsToAdd)."\r\n";
			$value = substr($value, $charsToAdd);
			# rest of the lines
			while ($value) {
				$out .= " ".substr($value,0,74)."\r\n";
				$value = substr($value, 74);
			}
			return $out;
		} else {
			return $out."\r\n";			
		}		
	}
	
	
	
	
}

