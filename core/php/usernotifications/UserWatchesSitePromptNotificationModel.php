<?php

namespace usernotifications;

use models\GroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSitePromptNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UserWatchesSitePrompt';
	}
	
	function setGroup(GroupModel $group) {
		$this->data['group'] = $group->getId();
	}
	
	public function getNotificationText() {
		return "There will soon be no more events in ".$this->site->getTitle();
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug());		
	}
}

