<?php




namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class API2ApplicationUserTokenModel {
	
	protected $api2_application_id;
	protected $user_id;
	protected $user_token;
	protected $user_secret;
	protected $is_write_user_actions;
	protected $is_write_user_profile;
	protected $is_write_calendar;
	
	public function setFromDataBaseRow($data) {
		$this->api2_application_id = $data['api2_application_id'];
		$this->user_id = $data['user_id'];
		$this->user_token = $data['user_token'];
		$this->user_secret = $data['user_secret'];
		$this->is_write_user_actions = (boolean)$data['is_write_user_actions'];
		$this->is_write_user_profile = (boolean)$data['is_write_user_profile'];
		$this->is_write_calendar = (boolean)$data['is_write_calendar'];
	}

	public function getApi2ApplicationId() {
		return $this->api2_application_id;
	}

	public function setApi2ApplicationId($api2_application_id) {
		$this->api2_application_id = $api2_application_id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	public function getUserToken() {
		return $this->user_token;
	}

	public function setUserToken($user_token) {
		$this->user_token = $user_token;
	}

	public function getUserSecret() {
		return $this->user_secret;
	}

	public function setUserSecret($user_secret) {
		$this->user_secret = $user_secret;
	}

	public function getIsWriteUserActions() {
		return $this->is_write_user_actions;
	}

	public function setIsWriteUserActions($is_write_user_actions) {
		$this->is_write_user_actions = $is_write_user_actions;
	}

	public function getIsWriteUserProfile() {
		return $this->is_write_user_profile;
	}

	public function setIsWriteUserProfile($is_write_user_profile) {
		$this->is_write_user_profile = $is_write_user_profile;
	}

	public function getIsWriteCalendar() {
		return $this->is_write_calendar;
	}

	public function setIsWriteCalendar($is_write_calendar) {
		$this->is_write_calendar = $is_write_calendar;
	}


	
			
	
	
}

