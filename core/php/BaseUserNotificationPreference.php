<?php


use models\UserAccountModel;
use models\SiteModel;

/**
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
