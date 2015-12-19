<?php


/**
 *
 * Holds information about the current UserAgent interacting with the site
 * eg Web Browser, API2 App
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAgent {
	

	protected $api2_application_id;
	
	public function hasApi2ApplicationId() {
		return (boolean)$this->api2_application_id;
	}
	
	public function getApi2ApplicationId() {
		return $this->api2_application_id;
	}

	public function setApi2ApplicationId($api2_application_id) {
		$this->api2_application_id = $api2_application_id;
	}
	
}

