<?php

namespace usernotifications\types;

use models\UserAccountModel;
use models\SiteModel;
use usernotifications\models\UserWatchesSiteGroupPromptNotificationModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteGroupPromptNotificationType extends \BaseUserNotificationType {
		
		public function getNewNotification(UserAccountModel $user, SiteModel $site=null) {
			$r =  new UserWatchesSiteGroupPromptNotificationModel();
			$r->setUserSiteAndIsEmail($user, $site, $this->getEmailPreference($user));
			return $r;
		}
	
	public function getNotificationFromData($data, UserAccountModel $user=null, SiteModel $site=null) {
		$r =  new UserWatchesSiteGroupPromptNotificationModel();
		$r->setFromDataBaseRow($data);
		$r->setSite($site);
		return $r;		
	}	
		
		
		
	public function getUserNotificationPreferenceType() { return 'WatchPrompt';  }
	
}

