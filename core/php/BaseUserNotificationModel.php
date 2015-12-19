<?php


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
	
	/** @var \models\SiteModel **/
	protected $site;
	
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->user_id = $data['user_id'];
		$this->site_id = $data['site_id'];
		$this->from_extension_id = $data['from_extension_id'];
		$this->from_user_notification_type = $data['from_user_notification_type'];
		$this->is_email = $data['is_email'];
		$this->data = json_decode($data['data_json']);
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->emailed_at = $data['emailed_at'] ? new \DateTime($data['emailed_at'], $utc) : null;
		$this->read_at = $data['read_at'] ? new \DateTime($data['read_at'], $utc) : null;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function getSite() {
		return $this->site;
	}

	public function setSite(SiteModel $site=null) {
		$this->site = $site;
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
	
	public function getIsRead() {
		return (boolean)$this->read_at;
	}

	public function getData() {
		return $this->data;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}
		
	public function setId($id) {
		$this->id = $id;
	}

	public function setUserSiteAndIsEmail(UserAccountModel $user, SiteModel $site=null, $isEmail=false) {
		$this->user_id = $user->getId();
		$this->site = $site;
		$this->site_id = $site ? $site->getId() : null;
		$this->is_email = $isEmail && $user->getIsCanSendNormalEmails();
	}
	
	public function isValid() {
		return true;
	}
	
	abstract public function getNotificationText();
	
	abstract public function getNotificationURL();
	
}

