<?php

namespace org\openacalendar\meetup;

use appconfiguration\AppConfigurationDefinition;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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
	
	public function getImportHandlers() {
		return array(
			new ImportExpandShortenerHandler($this->app),
			new ImportMeetupHandler($this->app),
		);
	}

	public function getSysAdminLinks() {
		return array(
			new \SysAdminLink("Setup Meetup Access",'/sysadmin/meetupuser')
		);
	}

	public function getImportURLRecommendations(\import\ImportURLRecommendationDataToCheck $dataToCheck) {
		$importURLRecommendation = new ImportURLRecommendation($dataToCheck->getUrl());
		if ($importURLRecommendation->hasNewURL()) {
			return array($importURLRecommendation);
		} else {
			return array();
		}
	}

}
