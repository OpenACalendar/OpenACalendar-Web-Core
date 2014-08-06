<?php

namespace org\openacalendar\meetup;

use appconfiguration\AppConfigurationDefinition;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionMeetup extends \BaseExtension {
	
	public function getId() {
		return 'org.openacalendar.meetup';
	}
	
	public function getTitle() {
		return "Meetup Integration";
	}
	
	public function getDescription() {
		return "Meetup Integration";
	}
	
	public function getAppConfigurationDefinitions() {
		return array(
			new AppConfigurationDefinition($this->getId(),'app_key','password',true),
		);
	}
	
	public function getImportURLHandlers() {
		return array(
			new ImportURLMeetupHandler(),
		);
	}

	public function getSysAdminLinks() {
		return array(
			new \SysAdminLink("Setup Meetup Access",'/sysadmin/meetupuser')
		);
	}
}
