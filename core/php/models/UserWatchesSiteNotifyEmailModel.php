<?php

namespace models;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserWatchesSiteNotifyEmailModel {
	
	protected $user_account_id;
	protected $site_id;
	protected $sent_at;
	
	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->site_id = $data['site_id'];
		$utc = new \DateTimeZone("UTC");
		$this->sent_at = $data['sent_at'] ? new \DateTime($data['sent_at'], $utc) : null;
	}
	
	
	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	public function getSentAt() {
		return $this->sent_at;
	}

	public function setSentAt($sent_at) {
		$this->sent_at = $sent_at;
		return $this;
	}


	
}

