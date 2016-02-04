<?php


namespace repositories;

use models\UserNotificationPreferenceModel;
use models\UserAccountModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserNotificationPreferenceRepository {


    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function load(UserAccountModel $user, $extensionId, $userNotificationPreferenceType) {

		$stat = $this->app['db']->prepare("SELECT user_notification_preference.* FROM user_notification_preference ".
				"WHERE user_id =:user_id AND extension_id=:extension_id AND user_notification_preference_type = :user_notification_preference_type");
		$stat->execute(array( 
				'user_id'=>$user->getId(), 
				'extension_id'=>$extensionId, 
				'user_notification_preference_type'=>$userNotificationPreferenceType, 
			));
		$pm = new UserNotificationPreferenceModel();
		if ($stat->rowCount() > 0) {
			$pm->setFromDataBaseRow($stat->fetch());
		} else {
			// set the default
			$pm->setIsEmail(true);
			
			// But wait, can we try to load setting from old fields on User Table?
			if (($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchPrompt') || 
				($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchNotify') ||
				($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchImportExpired') ||
				($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'UpcomingEvents') || 
				($extensionId == 'org.openacalendar.newsletter' && $userNotificationPreferenceType == 'Newsletter')) {
				
				$stat = $this->app['db']->prepare("SELECT user_account_information.* FROM user_account_information WHERE id = :id");
				$stat->execute(array('id'=>$user->getId()));
				$oldData = $stat->fetch();
				
				if ($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchPrompt' && isset($oldData['is_email_watch_prompt'])) {
					$pm->setIsEmail($oldData['is_email_watch_prompt']);
				}
				if ($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchNotify' && isset($oldData['is_email_watch_notify'])) {
					$pm->setIsEmail($oldData['is_email_watch_notify']);
				}
				if ($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'WatchImportExpired' && isset($oldData['is_email_watch_import_expired'])) {
					$pm->setIsEmail($oldData['is_email_watch_import_expired']);
				}
				if ($extensionId == 'org.openacalendar' && $userNotificationPreferenceType == 'UpcomingEvents' && isset($oldData['email_upcoming_events'])) {
					$pm->setIsEmail($oldData['email_upcoming_events'] != 'n');
				}				
				if ($extensionId == 'org.openacalendar.newsletter' && $userNotificationPreferenceType == 'Newsletter' && isset($oldData['is_email_newsletter'])) {
					$pm->setIsEmail($oldData['is_email_newsletter']);
				}				
			}
		}
		return $pm;
	}
	
	public function editEmailPreference(UserAccountModel $user, $extensionId, $userNotificationPreferenceType, $value) {

		# is already in DB?
		$stat = $this->app['db']->prepare("SELECT user_notification_preference.* FROM user_notification_preference ".
				"WHERE user_id =:user_id AND extension_id=:extension_id AND user_notification_preference_type = :user_notification_preference_type");
		$stat->execute(array( 
				'user_id'=>$user->getId(), 
				'extension_id'=>$extensionId, 
				'user_notification_preference_type'=>$userNotificationPreferenceType, 
			));
		
		# update or insert
		if ($stat->rowCount() > 0) {
			$stat = $this->app['db']->prepare("UPDATE user_notification_preference SET is_email = :is_email ".
				"WHERE user_id =:user_id AND extension_id=:extension_id AND user_notification_preference_type = :user_notification_preference_type");
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_notification_preference (user_id,extension_id,user_notification_preference_type,is_email) ".
					"VALUES (:user_id,:extension_id,:user_notification_preference_type,:is_email)");
		}
		$stat->execute(array( 
				'user_id'=>$user->getId(), 
				'extension_id'=>$extensionId, 
				'user_notification_preference_type'=>$userNotificationPreferenceType, 
				'is_email'=>$value?1:0,
			));

	}
	
}

