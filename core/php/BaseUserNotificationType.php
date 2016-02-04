<?php


use models\UserAccountModel;
use models\SiteModel;
use repositories\UserNotificationPreferenceRepository;

/**
 *
 * Each User Notification has a seperate type. Types should be represented by classes that extend this.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseUserNotificationType {
	
	public abstract function getNewNotification(UserAccountModel $user, SiteModel $site=null);
	
	public abstract function getNotificationFromData($data, UserAccountModel $user=null, SiteModel $site=null);
	
	public function getUserNotificationPreferenceExtensionID() {
		return 'org.openacalendar';
	}
	public abstract function getUserNotificationPreferenceType();

	public function getEmailPreference(UserAccountModel $user) {
        global $app;
		$repo = new UserNotificationPreferenceRepository($app);
		$pref = $repo->load($user, $this->getUserNotificationPreferenceExtensionID(), $this->getUserNotificationPreferenceType());
		return $pref->getIsEmail();
	}
	
}

