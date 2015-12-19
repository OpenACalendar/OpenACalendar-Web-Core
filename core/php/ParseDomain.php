<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDomain {

	protected $currentDomain;
	
	public function __construct($currentDomain) {
		$this->currentDomain = $currentDomain;
	}
	
	public function isCoveredByCookies() {
		global $CONFIG;
		
		$matchAgainst = $this->stripPort($CONFIG->webCommonSessionDomain);
		$bit = substr($this->stripPort($this->currentDomain), -strlen($matchAgainst));
		if (strtolower($bit) == strtolower($matchAgainst)) {
			return true;
		}
		
		return false;
	}
	
	protected function stripPort($in) {
		$bits = explode(":",$in,2);
		return $bits[0];
	}
	
	
}