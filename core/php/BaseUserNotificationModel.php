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
abstract class BaseUserNotificationModel {
	
	protected $id;
	protected $user_id;
	protected $site_id;
	protected $from_extension_id;
	protected $from_user_notification_type;
	protected $is_email;
	protected $data = array();
	protected $created_at;
	protected $emailed_at;
	protected $read_at;
			
	
	public function getId() {
		return $this->id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function getFromExtensionId() {
		return $this->from_extension_id;
	}

	public function getFromUserNotificationType() {
		return $this->from_user_notification_type;
	}

	public function getIsEmail() {
		return $this->is_email;
	}

	public function getData() {
		return $this->data;
	}
	
	
	public function setId($id) {
		$this->id = $id;
	}

	public function setUserSiteAndIsEmail(UserAccountModel $user, SiteModel $site=null, $isEmail=false) {
		$this->user_id = $user->getId();
		$this->site_id = $site ? $site->getId() : null;
		$this->is_email = $isEmail && $user->getIsCanSendNormalEmails();
	}

}

