<?php


use models\UserAccountModel;
use models\SiteModel;

/**
 *
 * Users can choose whether to have user notifications emailed to them.
 * 
 * They turn on or off several different categories of notification (a Preference),
 * each category is represented by a class that extends this.
 *
 * This is done seperately from BaseUserNotificationType because several 
 * types of notification may be turned on or off by one category or Preference.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseUserNotificationPreference {

	public function getUserNotificationPreferenceExtensionID() {
		return 'org.openacalendar';
	}
	public abstract function getUserNotificationPreferenceType();

	public abstract function getLabel();
	
}
