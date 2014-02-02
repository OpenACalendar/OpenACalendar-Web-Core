<?php
namespace icalparser;


/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalParserTimeZone
{
	protected $timeZone = 'UTC';

	public function __construct() {
	}

	public function processLine($keyword, $value) {
		if ($keyword == 'TZID') {
			$timezoneIdentifiers = \DateTimeZone::listIdentifiers();
			if (in_array($value, $timezoneIdentifiers)) {
				$this->timeZone = $value;
			}
		}
	}
	
	public function getTimeZoneIdentifier() {
		return $this->timeZone;
	}
	
	public function getTimeZone() {
		return new \DateTimeZone($this->timeZone);
	}
}

