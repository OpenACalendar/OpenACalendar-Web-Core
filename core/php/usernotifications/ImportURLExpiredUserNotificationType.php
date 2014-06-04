<?php

namespace usernotifications;

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
class ImportURLExpiredUserNotificationType extends \BaseUserNotificationType {
		
		public function getNewNotification(UserAccountModel $user, SiteModel $site=null) {
			$r =  new ImportURLExpiredUserNotificationModel();
			$r->setUserSiteAndIsEmail($user, $site, $this->getEmailPreference($user));
			return $r;
		}
		
	public function getUserNotificationPreferenceType() { return 'WatchImportExpired';  }
	
}

