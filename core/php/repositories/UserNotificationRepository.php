<?php

namespace repositories;

use models\UserAccountModel;
use models\SiteModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserNotificationRepository {
	
	
	public function create(\BaseUserNotificationModel $userNotification) {
		global $DB;
		$stat = $DB->prepare("INSERT INTO user_notification ".
				"(user_id,site_id,from_extension_id,from_user_notification_type,is_email,data_json,created_at) ".
				"VALUES (:user_id,:site_id,:from_extension_id,:from_user_notification_type,:is_email,:data_json,:created_at) RETURNING id");
		$stat->execute(array(
				'user_id'=>$userNotification->getUserId(),
				'site_id'=>$userNotification->getSiteId(),
				'from_extension_id'=>$userNotification->getFromExtensionId(),
				'from_user_notification_type'=>$userNotification->getFromUserNotificationType(),
				'is_email'=>$userNotification->getIsEmail()?1:0,
				'data_json'=>json_encode($userNotification->getData()),
				'created_at'=>\TimeSource::getFormattedForDataBase(),
			));
		
		$data = $stat->fetch();
		$userNotification->setId($data['id']);
	}
	
	public function markEmailed(\BaseUserNotificationModel $userNotification) {
			global $DB;
			$stat = $DB->prepare("UPDATE user_notification SET emailed_at=:at WHERE id=:id");
			$stat->execute(array(
				'at'=>\TimeSource::getFormattedForDataBase(),
				'id'=>$userNotification->getId(),
			));
	}
	
	
	public function markRead(\BaseUserNotificationModel $userNotification) {
			global $DB;
			$stat = $DB->prepare("UPDATE user_notification SET read_at=:at WHERE id=:id");
			$stat->execute(array(
				'at'=>\TimeSource::getFormattedForDataBase(),
				'id'=>$userNotification->getId(),
			));
	}
	
	
	
	public function loadByIdForUser($id, UserAccountModel $user) {
		global $DB, $app;
		$stat = $DB->prepare("SELECT user_notification.*, ".
				" site_information.id AS site_id,  site_information.slug AS site_slug,  site_information.title AS site_title ".
				" FROM user_notification ".
				" LEFT JOIN site_information ON site_information.id = user_notification.site_id ".
				"WHERE user_notification.id =:id AND user_notification.user_id =:uid");
		$stat->execute(array( 'uid'=>$user->getId(), 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$data = $stat->fetch();
			$extension = $app['extensions']->getExtensionById($data['from_extension_id']);
			if ($extension) {
				$type = $extension->getUserNotificationType($data['from_user_notification_type']);
				if ($type) {
					$site = new SiteModel();
					$site->setId($data['site_id']);
					$site->setSlug($data['site_slug']);
					$site->setTitle($data['site_title']);
					$notification = $type->getNotificationFromData($data, $user, $site);
					if ($notification->isValid()) {
						return $notification;
					}
				}
			}		
		}
	}
	
}
