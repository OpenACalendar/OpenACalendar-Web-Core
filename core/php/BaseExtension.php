<?php

use models\VenueModel;
use models\UserAccountModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseExtension {
	
	function __construct(Application $app) {
		
	}
	
	public abstract function getId();
	
	
	public abstract function getTitle();
	
	public function getDescription() {
		return null;
	}

	
	
	public function beforeVenueSave(VenueModel $venue, UserAccountModel $user) {
		
	}
	
	public function getUserNotificationTypes() {
		return array();
	}
	
	public function getUserNotificationType($type) {
		return null;
	}
	
	public function getUserNotificationPreferenceTypes() {
		return array();
	}
	
	public function getUserNotificationPreference($type) {
		return null;
	}
	
	public function getAppConfigurationDefinitions() {
		return array();
	}
	
	
}

