<?php


namespace usernotifications\models;

use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserRequestsAccessNotifyAdminNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UserRequestsAccessNotifyAdmin';
	}

	function setRequestingUser(UserAccountModel $user) {
		$this->data['user'] = $user->getId();
	}
	
	public function getNotificationText() {
		return "A user has requested access";
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/admin/users';
	}
	
}
