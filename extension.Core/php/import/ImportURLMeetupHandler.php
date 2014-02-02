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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLMeetupHandler extends ImportURLHandlerBase {

	protected $newFeedURL;
	
	public function canHandle() {
		
		$urlBits = parse_url($this->importURLRun->getRealURL());
		
		if (in_array(strtolower($urlBits['host']), array('meetup.com','www.meetup.com')) ) {
			
			$bits = explode("/", $urlBits['path']);
			
			if (count($bits) <= 3) {
				// group
				$this->newFeedURL = $this->importURLRun->getRealUrl();
				if (substr($this->newFeedURL,-1) != '/') $this->newFeedURL .= '/';
				$this->newFeedURL .= 'events/ical/x/';
				return true;
			} else if (count($bits) > 3 && $bits[2] == 'events') {
				// specific event
				$this->newFeedURL = $this->importURLRun->getRealUrl();
				if (substr($this->newFeedURL,-1) != '/') $this->newFeedURL .= '/';
				$this->newFeedURL .= 'ical/x.ics';
				return true;
			}
			
		}
		
		return false;
		
	}
	
	public function getNewFeedURL() { return $this->newFeedURL; }
	
	public function handle() {
		if ($this->newFeedURL) $this->importURLRun->setRealUrl($this->newFeedURL);
	}

	
}

