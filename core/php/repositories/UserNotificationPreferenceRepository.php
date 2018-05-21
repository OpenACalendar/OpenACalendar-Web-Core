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

    public function hasUserExpressedAnyPreferences(UserAccountModel $userAccountModel) {
        $stat = $this->app['db']->prepare("SELECT user_notification_preference.* FROM user_notification_preference ".
            "WHERE user_id =:user_id");
        $stat->execute(array(
            'user_id'=>$userAccountModel->getId(),
        ));
        return $stat->rowCount() > 0;
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
            $pm->setIsEmail(false);
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
			$stat = $this->app['db']->prepare("UPDATE user_notification_preference SET is_email = :is_email, last_save_at = :last_save_at ".
				"WHERE user_id =:user_id AND extension_id=:extension_id AND user_notification_preference_type = :user_notification_preference_type");
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_notification_preference (user_id,extension_id,user_notification_preference_type,is_email,last_save_at) ".
					"VALUES (:user_id,:extension_id,:user_notification_preference_type,:is_email,:last_save_at)");
		}
		$stat->execute(array( 
				'user_id'=>$user->getId(), 
				'extension_id'=>$extensionId, 
				'user_notification_preference_type'=>$userNotificationPreferenceType, 
				'is_email'=>$value?1:0,
                'last_save_at'=>$this->app['timesource']->getFormattedForDataBase(),
        ));

	}
	
}

