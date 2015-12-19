<?php

namespace import;
use JarOfGreen\WikiCalendarBundle\Entity\ImportURL;
use import\ImportURLHandlerBase;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLEventbriteHandler extends ImportURLHandlerBase {

	public function getSortOrder() {
		return 100000;
	}
	
	public function isStopAfterHandling() { 
		return false;
	}
	
	protected $newFeedURL;
	
	public function canHandle() {
		
		$urlBits = parse_url($this->importURLRun->getRealURL());
		
		if ($urlBits['host']== 'eventbrite.co.uk' || $urlBits['host']== 'www.eventbrite.co.uk') {
			$bits =  explode("/",$urlBits['path']);
			if (in_array($bits[1], array('event','e'))) {
				$slugBits = explode("-", $bits[2]);
				$slug = array_pop($slugBits);
				if (intval($slug)) {
					$this->newFeedURL = 'http://www.eventbrite.co.uk/calendar.ics?eid='.$slug.'&calendar=ical';
					return true;
				}
			}
		}
		
		if ($urlBits['host']== 'eventbrite.com' || $urlBits['host']== 'www.eventbrite.com') {
			$bits =  explode("/",$urlBits['path']);
			if (in_array($bits[1], array('event','e')) && intval($bits[2]) > 0) {
				$slugBits = explode("-", $bits[2]);
				$slug = array_pop($slugBits);
				if (intval($slug)) {
					$this->newFeedURL = 'http://www.eventbrite.com/calendar.ics?eid='.$slug.'&calendar=ical';
					return true;
				}
			}
		}
		
		$u = 'eventbrite.co.uk';
		if (substr(strtolower($urlBits['host']),  0-strlen($u)) == $u) {
			$data = file_get_contents($this->importURLRun->downloadURLreturnFileName());
			$bits = explode("http://www.eventbrite.co.uk/calendar?eid=",$data,2);
			if (count($bits) == 2) {
				$bits = explode('&amp;', $bits[1],2);
				$this->newFeedURL = 'http://www.eventbrite.co.uk/calendar.ics?eid='.$bits[0].'&calendar=ical';						 
				return true;
			}
			$bits = explode("https://www.eventbrite.co.uk/calendar?eid=",$data,2);
			if (count($bits) == 2) {
				$bits = explode('&amp;', $bits[1],2);
				$this->newFeedURL = 'http://www.eventbrite.co.uk/calendar.ics?eid='.$bits[0].'&calendar=ical';						 
				return true;
			}
		}
		
		$u = 'eventbrite.com';
		if (substr(strtolower($urlBits['host']),  0-strlen($u)) == $u) {
			$data = file_get_contents($this->importURLRun->downloadURLreturnFileName());
			$bits = explode("http://www.eventbrite.com/calendar?eid=",$data,2);
			if (count($bits) == 2) {
				$bits = explode('&amp;', $bits[1],2);
				$this->newFeedURL = 'http://www.eventbrite.com/calendar.ics?eid='.$bits[0].'&calendar=ical';						 
				return true;
			}
			$bits = explode("https://www.eventbrite.com/calendar?eid=",$data,2);
			if (count($bits) == 2) {
				$bits = explode('&amp;', $bits[1],2);
				$this->newFeedURL = 'http://www.eventbrite.com/calendar.ics?eid='.$bits[0].'&calendar=ical';						 
				return true;
			}
		}
		
		return false;
		
	}
	
	public function getNewFeedURL() { return $this->newFeedURL; }
	
	public function handle() {		
		if ($this->newFeedURL) {
			$this->importURLRun->setRealUrl($this->newFeedURL);
			$this->importURLRun->setFlag(ImportURLRun::$FLAG_ADD_UIDS);
			$this->importURLRun->setFlag(ImportURLRun::$FLAG_SET_TICKET_URL_AS_URL);
		}
	}

	
}

