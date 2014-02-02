<?php


namespace models;

/**
 *
 * Used for:
 * 1) change emails user gets, link in email without user logging in
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountGeneralSecurityKeyModel {
	
	protected $user_account_id;
	protected $access_key;
	
	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getAccessKey() {
		return $this->access_key;
	}

	public function setAccessKey($access_key) {
		$this->access_key = $access_key;
	}

	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->access_key = $data['access_key'];
	}
	
}

