<?php

namespace import;
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
class ImportURLLanyardHandler extends ImportURLHandlerBase {

	protected $newFeedURL;
	
	public function canHandle() {
		$urlBits = parse_url($this->importURLRun->getRealURL());
		
		if (in_array(strtolower($urlBits['host']), array('lanyrd.com','www.lanyrd.com')) ) {
			$bits = explode("/", $urlBits['path']);
			$allowedYears = array(date('Y',  TimeSource::time()),date('Y',  TimeSource::time())+1);
			if (count($bits) > 3 && in_array($bits[1],$allowedYears) && $bits[2]) {
				$this->newFeedURL = $this->importURLRun->getRealURL();
				if (substr($this->newFeedURL,-1) != '/') $this->newFeedURL .= '/';
				$this->newFeedURL .= $bits[2].".ics";
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

