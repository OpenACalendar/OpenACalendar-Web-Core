<?php

use import\ImportURLNotUsHandler;
use import\ImportURLMeetupHandler;
use import\ImportURLEventbriteHandler;
use import\ImportURLLanyrdHandler;
use import\ImportURLICalHandler;
use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
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
		return array('CREATE_SITE','CALENDAR_CHANGE','CALENDAR_ADMINISTRATE',
			'AREAS_CHANGE','EVENTS_CHANGE','GROUPS_CHANGE','IMPORTURL_CHANGE','TAGS_CHANGE','VENUES_CHANGE','MEDIAS_CHANGE');
	}

	public function getUserPermission($key) {
		if ($key == 'CREATE_SITE') {
			return new \userpermissions\CreateSiteUserPermission();
		} else if ($key == 'CALENDAR_CHANGE') {
			return new \userpermissions\CalendarChangeUserPermission();
		} else if ($key == 'CALENDAR_ADMINISTRATE') {
			return new \userpermissions\CalendarAdministrateUserPermission();
		} else if ($key == 'AREAS_CHANGE') {
			return new \userpermissions\AreasChangeUserPermission();
		} else if ($key == 'EVENTS_CHANGE') {
			return new \userpermissions\EventsChangeUserPermission();
		} else if ($key == 'GROUPS_CHANGE') {
			return new \userpermissions\GroupsChangeUserPermission();
		} else if ($key == 'IMPORTURL_CHANGE') {
			return new \userpermissions\ImportURLChangeUserPermission();
		} else if ($key == 'TAGS_CHANGE') {
			return new \userpermissions\TagsChangeUserPermission();
		} else if ($key == 'VENUES_CHANGE') {
			return new \userpermissions\VenuesChangeUserPermission();
		} else if ($key == 'MEDIAS_CHANGE') {
			return new \userpermissions\MediasChangeUserPermission();
		}
	}

	public function getUserNotificationTypes() {
		return array('UpcomingEvents','UserWatchesGroupPrompt','UserWatchesGroupNotify',
			'UserWatchesSiteNotify','UserWatchesSiteGroupPrompt','UserWatchesSitePrompt',
			'ImportURLExpired','UserRequestsAccessNotifyAdmin','UserWatchesNotify');
	}
	
	public function getUserNotificationType($type) {
		if ($type == 'UpcomingEvents') {
			return new usernotifications\types\UpcomingEventsUserNotificationType();
		} else if ($type == 'UserWatchesGroupPrompt') {
			return new usernotifications\types\UserWatchesGroupPromptNotificationType();
		} else if ($type == 'UserWatchesNotify') {
			return new usernotifications\types\UserWatchesNotifyNotificationType();
		} else if ($type == 'UserWatchesGroupNotify') {
			// deprecated !!!!!!!
			return new usernotifications\types\UserWatchesGroupNotifyNotificationType();
		} else if ($type == 'UserWatchesSiteNotify') {
			// deprecated !!!!!!!
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

	public function getTasks() {
		return array(
			new \tasks\SendUserWatchesNotifyTask($this->app),
			new \tasks\UpdateVenueFutureEventsCacheTask($this->app),
			new \tasks\UpdateAreaFutureEventsCacheTask($this->app),
			new \tasks\UpdateAreaBoundsCacheTask($this->app),
			new \tasks\UpdateAreaParentCacheTask($this->app),
			new \tasks\UpdateSiteCacheTask($this->app),
			new \tasks\UpdateAreaHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateEventHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateGroupHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateImportURLHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateSiteHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateTagHistoryChangeFlagsTask($this->app),
			new \tasks\UpdateVenueHistoryChangeFlagsTask($this->app),
			new \tasks\SendUserWatchesSitePromptEmailsTask($this->app),
			new \tasks\SendUserWatchesSiteGroupPromptEmailsTask($this->app),
			new \tasks\SendUserWatchesGroupPromptEmailsTask($this->app),
			new \tasks\SendUpcomingEventsForUsersTask($this->app),
			new \tasks\RunImportURLsTask($this->app),
		);
	}

	/**
	 * @return array BaseUserWatchesNotifyContent
	 */
	public function getUserNotifyContents(SiteModel $site, UserAccountModel $userAccountModel) {
		$out = array();

		$userWatchesSiteRepo = new \repositories\UserWatchesSiteRepository();
		$data = $userWatchesSiteRepo->getUserNotifyContentForSiteAndUser($site, $userAccountModel);
		if ($data) {
			// no point carrying on; someone watching a site overrides any data contained within
			return array($data);
		}

		if ($site->getIsFeatureGroup()) {
			$userWatchesGroupRepo = new \repositories\UserWatchesGroupRepository();
			$data = $userWatchesGroupRepo->getUserNotifyContentForSiteAndUser($site, $userAccountModel);
			if ($data) {
				$out = array_merge($out, $data);
			}
		}


		$userWatchesAreaRepo = new \repositories\UserWatchesAreaRepository();
		$data = $userWatchesAreaRepo->getUserNotifyContentForSiteAndUser($site, $userAccountModel);
		if ($data) {
			$out = array_merge($out, $data);
		}


		return $out;
	}


	public function getNewsFeedModel( $interfaceHistoryModel) { // TODO can't set type InterfaceHistoryModel!!!!!!!
		if ($interfaceHistoryModel instanceof \models\AreaHistoryModel) {
			return new \newsfeedmodels\AreaHistoryNewsFeedModel($interfaceHistoryModel);
		} else if ($interfaceHistoryModel instanceof \models\EventHistoryModel) {
			return new \newsfeedmodels\EventHistoryNewsFeedModel($interfaceHistoryModel);
		} else if ($interfaceHistoryModel instanceof \models\GroupHistoryModel) {
			return new \newsfeedmodels\GroupHistoryNewsFeedModel($interfaceHistoryModel);
		} else if ($interfaceHistoryModel instanceof \models\ImportURLHistoryModel) {
			return new \newsfeedmodels\ImportURLHistoryNewsFeedModel($interfaceHistoryModel);
		} else if ($interfaceHistoryModel instanceof \models\TagHistoryModel) {
			return new \newsfeedmodels\TagHistoryNewsFeedModel($interfaceHistoryModel);
		} else if ($interfaceHistoryModel instanceof \models\VenueHistoryModel) {
			return new \newsfeedmodels\VenueHistoryNewsFeedModel($interfaceHistoryModel);
		}
	}

	public function getSeriesReports() {
		return array(
			new reports\seriesreports\UsersWithNotificationsSeriesReport(),
			new reports\seriesreports\UsersWithEventEditsSeriesReport(),
			new reports\seriesreports\UsersWithEventsEditedSeriesReport(),
			new reports\seriesreports\GroupsWithUsersWatching(),
			new reports\seriesreports\AreasWithUsersDirectlyWatching(),
			new reports\seriesreports\EventsStartAtByImportedOrNotSeriesReport(),
		);
	}

	public function getValueReports() {
		return array(
			new reports\valuereports\EventsCreatedReport(),
			new reports\valuereports\GroupsCreatedReport(),
			new reports\valuereports\VenuesCreatedReport(),
			new reports\valuereports\UsersCreatedReport(),
			new reports\valuereports\EventsEditedReport(),
			new reports\valuereports\GroupsEditedReport(),
			new reports\valuereports\VenuesEditedReport(),
		);
	}

	public function getSiteFeatures(\models\SiteModel $siteModel = null) {
		return array(
			new sitefeatures\EditCommentsFeature(),
		);
	}

	/** @return InterfaceEventCustomFieldType */
	public function getEventCustomFieldByType($type) {
		if ($type == 'TextSingleLine') {
			return new \customfieldtypes\event\TextSingleLineEventCustomFieldType();
		} else if ($type == 'TextMultiLine') {
			return new \customfieldtypes\event\TextMultiLineEventCustomFieldType();
		}
	}

}
