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
class UserInAPI2ApplicationModel {

	protected $api2_application_id;
	protected $user_id;
	protected $is_in_app;
	protected $is_write_user_actions = false;
	protected $is_write_calendar = false;
	
	public function setFromDataBaseRow($data) {
		$this->api2_application_id = $data['api2_application_id'];
		$this->user_id = $data['user_id'];
		$this->is_in_app = (boolean)$data['is_in_app'];
		$this->is_write_user_actions = (boolean)$data['is_write_user_actions'];
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

	public function getIsInApp() {
		return $this->is_in_app;
	}

	public function setIsInApp($is_in_app) {
		$this->is_in_app = $is_in_app;
	}

	public function getIsWriteUserActions() {
		return $this->is_write_user_actions;
	}

	public function setIsWriteUserActions($is_write_user_actions) {
		$this->is_write_user_actions = $is_write_user_actions;
	}

	public function getIsWriteCalendar() {
		return $this->is_write_calendar;
	}

	public function setIsWriteCalendar($is_write_calendar) {
		$this->is_write_calendar = $is_write_calendar;
	}


	
}



