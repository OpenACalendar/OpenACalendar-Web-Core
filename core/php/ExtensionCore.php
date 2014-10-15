<?php

use import\ImportURLNotUsHandler;
use import\ImportURLMeetupHandler;
use import\ImportURLEventbriteHandler;
use import\ImportURLLanyrdHandler;
use import\ImportURLICalHandler;

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

	public function getUserPermissions() {
		return array('CREATE_SITE','CALENDAR_CHANGE','CALENDAR_ADMINISTRATE');
	}

	public function getUserPermission($key) {
		if ($key == 'CREATE_SITE') {
			return new \userpermissions\CreateSiteUserPermission();
		} else if ($key == 'CALENDAR_CHANGE') {
			return new \userpermissions\CalendarChangeUserPermission();
		} else if ($key == 'CALENDAR_ADMINISTRATE') {
			return new \userpermissions\CalendarAdministrateUserPermission();
		}
	}

	public function getUserNotificationTypes() {
		return array('UpcomingEvents','UserWatchesGroupPrompt','UserWatchesGroupNotify',
			'UserWatchesSiteNotify','UserWatchesSiteGroupPrompt','UserWatchesSitePrompt',
			'ImportURLExpired','UserRequestsAccessNotifyAdmin');
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
		} else if ($type == 'UserRequestsAccessNotifyAdmin') {
			return new usernotifications\types\UserRequestsAccessNotifyAdminNotificationType();
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
	
	public function getImportURLHandlers() {
		return array(
			// Common Sense Handler
			new ImportURLNotUsHandler(),
			// rewrite URL to ICAL handlers
			new ImportURLMeetupHandler(),
			new ImportURLEventbriteHandler(),
			new ImportURLLanyrdHandler(),
			// handlers!
			new ImportURLICalHandler(),
		);
	}

	public function clearCache() {
		global $CONFIG;
		$cacheDir = APP_ROOT_DIR."/cache/";
		foreach(glob($cacheDir."/templates.cli/*/*/*.php") as $file) {
			unlink($file);
		}
		foreach(glob($cacheDir."/templates.web/*/*/*.php") as $file) {
			unlink($file);
		}
	}

}
