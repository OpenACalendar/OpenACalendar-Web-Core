<?php

namespace repositories;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
				'is_email'=>$userNotification->getIsEmail(),
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
	
}
