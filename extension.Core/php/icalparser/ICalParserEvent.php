<?php
namespace icalparser;


/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalParserEvent
{

	protected $timeZone;
	protected $timeZoneUTC;
	
	/** @var \DateTime **/
	protected $start;

	/** @var \DateTime **/
	protected $end;
	
	protected $summary;
	
	protected $location;
	
	protected $description;
	
	protected $uid;
	
	protected $deleted = false;

	protected $url;

	public function __construct(\DateTimeZone $timeZone = null) {
		$this->timeZoneUTC =  new \DateTimeZone('UTC');
		$this->timeZone = $timeZone ? $timeZone : $this->timeZoneUTC;
	} 
	
	public function processLine($keyword, $value) {
		if ($keyword == 'UID') {
			$this->uid = $value;
		} else if ($keyword == 'LOCATION') {
			$this->location = $value;
		} else if ($keyword == 'SUMMARY') {
			$this->summary = $value;
		} else if ($keyword == 'DESCRIPTION') {
			$this->description = $value;
		} else if ($keyword == 'URL') {
			$this->url = $value;
		} else if ($keyword == 'DTSTART') {
			$this->start = $this->parseDateTime($value, true);
		} else if ($keyword == 'DTEND') {
			$this->end = $this->parseDateTime($value, false);
		} else if ($keyword == 'METHOD' && $value == 'CANCEL') {
			$this->deleted = true;
		} else if ($keyword == 'STATUS' && $value == 'CANCELLED') {
			$this->deleted = true;
		}
	}
	
	
	/*
	 * Based on ....
	* @author   Martin Thoma <info@martin-thoma.de>
	* @license  http://www.opensource.org/licenses/mit-license.php  MIT License
	* @link     http://code.google.com/p/ics-parser/
	**/
	protected function parseDateTime($value, $isStart) {
        $value = str_replace('Z', '', $value);
		$pattern  = '/([0-9]{4})';   // 1: YYYY
        $pattern .= '([0-9]{2})';    // 2: MM
        $pattern .= '([0-9]{2})';    // 3: DD
        
		$hasTimePart = false;
		if (strpos($value, "T") > 1) {
			$value = str_replace('T', '', $value);
			$pattern .= '([0-9]{0,2})';  // 4: HH
			$pattern .= '([0-9]{0,2})';  // 5: MM
			$pattern .= '([0-9]{0,2})/'; // 6: SS
			$hasTimePart = true;
		} else {
			$pattern .= '/';
		}
        preg_match($pattern, $value, $date);

        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return null;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.
		
		$out = new \DateTime('', $this->timeZone);
		$out->setDate((int)$date[1], (int)$date[2], (int)$date[3]);
		if ($hasTimePart) {
			$out->setTime((int)$date[4], (int)$date[5], (int)$date[6]);
		} else if ($isStart) {
			$out->setTime(0,0,0);
		} else if (!$isStart) {
			$out->setTime(23,59,59);
		}
		if ($this->timeZone->getName() != 'UTC') {
			$out->setTimezone($this->timeZoneUTC);
		}
		return $out;
	}
			
	
	public function getUid() {
		return $this->uid;
	}
	
	public function setUid($uid) {
		$this->uid = $uid;
	}	
	
	public function getStart() {
		return $this->start;
	}

	public function getEnd() {
		return $this->end;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getDescription() {
		return $this->description;
	}
	
	public function getUrl() {
		return $this->url;
	}

	public function isDeleted() {
		return $this->deleted;
	}
	
}

