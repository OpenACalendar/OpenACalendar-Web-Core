<?php


namespace usernotifications\models;

use models\GroupModel;

/**
 *
 *
 * @deprecated Use Type UserWatchesNotify instead!
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteNotifyNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UserWatchesSiteNotify';
	}
	
	public function getNotificationText() {
		return "There are changes to ".$this->site->getTitle();
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/history';		
	}
}

