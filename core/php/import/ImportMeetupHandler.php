<?php

namespace import;
use import\ImportHandlerBase;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportMeetupHandler extends ImportHandlerBase {

	public function getSortOrder() {
		return 100000;
	}
	
	public function isStopAfterHandling() { 
		return false;
	}
	
	protected $newFeedURL;
	
	public function canHandle() {
		
		$urlBits = parse_url($this->importRun->getRealURL());
		
		if (in_array(strtolower($urlBits['host']), array('meetup.com','www.meetup.com')) ) {
			
			$bits = explode("/", $urlBits['path']);
			
			if (count($bits) <= 3) {
				// group
				$this->newFeedURL = $this->importRun->getRealUrl();
				if (substr($this->newFeedURL,-1) != '/') $this->newFeedURL .= '/';
				$this->newFeedURL .= 'events/ical/x/';
				return true;
			} else if (count($bits) > 3 && $bits[2] == 'events') {
				// specific event
				$this->newFeedURL = $this->importRun->getRealUrl();
				if (substr($this->newFeedURL,-1) != '/') $this->newFeedURL .= '/';
				$this->newFeedURL .= 'ical/x.ics';
				return true;
			}
			
		}
		
		return false;
		
	}
	
	public function getNewFeedURL() { return $this->newFeedURL; }
	
	public function handle() {
		if ($this->newFeedURL) $this->importRun->setRealUrl($this->newFeedURL);
	}

	
}

