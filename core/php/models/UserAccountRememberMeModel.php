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
class UserAccountRememberMeModel {
	
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
	
	public function sendCookies() {
		global $CONFIG;
		setcookie("userID",$this->user_account_id,time()+60*60*24*365,'/',$CONFIG->webCommonSessionDomain,$CONFIG->forceSSL,true);
		setcookie("userKey",$this->access_key,time()+60*60*24*365,'/',$CONFIG->webCommonSessionDomain,$CONFIG->forceSSL,true);
	}
	
}

