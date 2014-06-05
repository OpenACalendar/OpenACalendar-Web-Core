<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionCore extends BaseExtension {
		
	public function getId() {
		return 'org.openacalendar';
	}

	public function getTitle() {
		return 'Core';
	}	
	
	public function getUserNotificationTypes() {
		return array('UpcomingEvents','UserWatchesGroupPrompt','UserWatchesGroupNotify',
			'UserWatchesSiteNotify','UserWatchesSiteGroupPrompt','UserWatchesSitePrompt',
			'ImportURLExpired');
	}
	
	public function getUserNotificationType($type) {
		if ($type == 'UpcomingEvents') {
			return new usernotifications\types\UpcomingEventsUserNotificationType();
		} else if ($type == 'UserWatchesGroupPrompt') {
			return new usernotifications\types\UserWatchesGroupPromptNotificationType();
		} else if ($type == 'UserWatchesGroupNotify') {
			return new usernotifications\types\UserWatchesGroupNotifyNotificationType();
		} else if ($type == 'UserWatchesSiteNotify') {
			return new usernotifications\types\UserWatchesSiteNotifyNotificationType();
		} else if ($type == 'UserWatchesSiteGroupPrompt') {
			return new usernotifications\types\UserWatchesSiteGroupPromptNotificationType();
		} else if ($type == 'UserWatchesSitePrompt') {
			return new usernotifications\types\UserWatchesSitePromptNotificationType();
		} else if ($type == 'ImportURLExpired') {
			return new usernotifications\types\ImportURLExpiredUserNotificationType();
		} else {
			return null;
		}
	}
	
	
	public function getUserNotificationPreferenceTypes() {
		return array('WatchPrompt','WatchNotify','UpcomingEvents','WatchImportExpired');
	}
	
	public function getUserNotificationPreference($type) {
		if ($type == 'WatchPrompt') {
			return new usernotifications\preferences\WatchPromptNotificationPreference();
		} else if ($type == 'WatchNotify') {
			return new usernotifications\preferences\WatchNotifyNotificationPreference();
		} else if ($type == 'WatchImportExpired') {
			return new usernotifications\preferences\WatchImportExpiredNotificationPreference();
		} else if ($type == 'UpcomingEvents') {
			return new usernotifications\preferences\UpcomingEventsNotificationPreference();
		}
		return null;
	}
	
}
